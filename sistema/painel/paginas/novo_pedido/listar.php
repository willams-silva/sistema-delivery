<?php
@session_start();
$id_empresa = filter_var($_SESSION['empresa'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id_empresa) { http_response_code(403); exit('Sessão expirada'); }
require_once("../../../conexao.php");
require_once("../../buscar_config.php");

$tabela = 'mesas';

$totalF = 0;
$buscar_cliente = @$_POST['p1'];

$query = $pdo->query("SELECT * FROM $tabela where empresa = '$id_empresa' order by id asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$indicadoresMesa = ['livres'=>0,'ocupadas'=>0,'inativas'=>0,'total'=>0];
if ($total_reg > 0) {


	for ($i = 0; $i < $total_reg; $i++) {
		$id = $res[$i]['id'];
		$nome = $res[$i]['nome'];
		$ativo = $res[$i]['ativo'];
		$status = $res[$i]['status'];

		$mostrar_fechar = '';



		//abertura da mesa
		$query2 = $pdo->query("SELECT * FROM abertura_mesa where mesa = '$id' and empresa = '$id_empresa' and status = 'Aberta'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$total = $res2[0]['total'];
			$cliente = $res2[0]['cliente'];
			$horario_abertura = $res2[0]['horario_abertura'];
			$garcon = $res2[0]['garcon'];
			$obs = $res2[0]['obs'];
			$id_abertura_mesa = $res2[0]['id'];
			$pessoas = $res2[0]['pessoas'];
			$status_abertura = $res2[0]['status'];

			if ($obs == "") {
				$obsF = '';
			} else {
				$obsF = '(' . $res2[0]['obs'] . ')';
			}


			$query2 = $pdo->query("SELECT * FROM usuarios where id = '$garcon' and empresa = '$id_empresa' ");
			$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
			$nome_garcon = @$res2[0]['nome'];

			
			$nome_cliente = @$cliente;



			$horario_aberturaF = date('H:i', @strtotime($horario_abertura));

			$pessoas_mesa = '<img class="icon-rounded-vermelho" src="../../img/user.png" width="15px" height="15px"> (' . $pessoas . ')';
		} else {
			$total = 0;
			$cliente = "";
			$horario_aberturaF = "<span style='color:green; font-size:10px'>(Disponível)</span>";
			$nome_garcon = "";
			$totalF = '';
			$obs = '';
			$pessoas_mesa = '';
			$mostrar_fechar = 'ocultar';
			$pessoas = '0';
			$id_abertura_mesa = '';
			$garcon = '';
			$status_abertura = '';
			$obsF = '';
			$nome_cliente = '';
			$area_nome_garcon = '&nbsp;';
			$area_nome_cliente = '&nbsp; <br>';
		}


		if($buscar_cliente != ""){
			    if(stripos($nome_cliente, $buscar_cliente) === false){
			        continue;
			    }
			}


		$ocultar_botoes = 'ocultar';
		if ($ativo == 'Sim') {
			$imagem_mesa = 'mesa_verde.png';
			$ocultar_indisponivel = '';
			if ($status == 'Aberta') {
				$imagem_mesa = 'mesa_vermelha.png';
				$ocultar_botoes = '';
			} else {
				$imagem_mesa = 'mesa_verde.png';
			}
		} else {
			$ocultar_indisponivel = 'ocultar';
			$imagem_mesa = 'mesa_inativa.png';
			$horario_aberturaF = "<span style='color:gray; font-size:10px'>(Inativa)</span>";
		}



		//totalizar o valor dos itens
		$total_mesa = 0;
		$query2 = $pdo->query("SELECT * FROM carrinho where mesa = '$id_abertura_mesa' and empresa = '$id_empresa' ");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0 and $status_abertura == 'Aberta') {
			for ($i2 = 0; $i2 < $total_reg2; $i2++) {
				$total_mesa += $res2[$i2]['total_item'];
			}
		} else {
			$total_mesa = 0;
		}



		if ($ativo == 'Sim' and $status == 'Aberta') {
			$totalF = 'R$ ' . @number_format($total_mesa, 2, ',', '.');
		}



		//calcular a comissao
		if ($comissao_garcon > 0 and $total_mesa > 0) {
			$total_comissao = $total_mesa * $comissao_garcon / 100;
		} else {
			$total_comissao = 0;
		}

		$total_comissaoC = @number_format($total_comissao, 2);

		$total_comissaoF = @number_format($total_comissao, 2, ',', '.');


		//calcular o couvert
		$total_couvert = $pessoas * $couvert;
		$total_couvertF = @number_format($total_couvert, 2, ',', '.');

		$subtotal = $total_mesa + $total_comissao + $total_couvert;
		$subtotalF = @number_format($subtotal, 2, ',', '.');


		//totalizar valores pagos
		$total_valor = 0;
		$total_valorF = 0;
		
		$query2 = $pdo->query("SELECT * FROM valor_adiantamento where abertura = '$id_abertura_mesa' and empresa = '$id_empresa' order by id desc");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {

			for ($i2 = 0; $i2 < $total_reg2; $i2++) {
				$valor2 = $res2[$i2]['valor'];

				$valorF2 = number_format($valor2, 2, ',', '.');

				$total_valor += $valor2;
				$total_valorF = number_format($total_valor, 2, ',', '.');
			}
		}

		$subtotal = $total_mesa + $total_comissao + $total_couvert - $total_valor;
		$subtotalF = @number_format($subtotal, 2, ',', '.');
		$subtotalC = @number_format($subtotal, 2);

		$mesaOcupada = $ativo === 'Sim' && $status_abertura === 'Aberta';
        $classeMesa = $ativo !== 'Sim' ? 'mesa-card-inativa' : ($mesaOcupada ? 'mesa-card-ocupada' : 'mesa-card-livre');
        $situacaoMesa = $ativo !== 'Sim' ? 'Inativa' : ($mesaOcupada ? 'Ocupada' : 'Livre');
        $duracaoMesa = '';
        if ($mesaOcupada && !empty($horario_abertura)) {
            $inicioMesa = strtotime($horario_abertura);
            if ($inicioMesa !== false) {
                // Horários sem data são tratados como início no dia anterior se necessário.
                if ($inicioMesa > time()) $inicioMesa -= 86400;
                $minutosMesa = max(0, (int)floor((time() - $inicioMesa) / 60));
                $duracaoMesa = '<span class="mesa-tempo">⏱ ' . intdiv($minutosMesa, 60) . 'h ' . ($minutosMesa % 60) . 'min</span>';
            }
        }
        $nomeEscapado = htmlspecialchars((string)$nome, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $situacaoMesaEscapada = htmlspecialchars($situacaoMesa, ENT_QUOTES, 'UTF-8');
        $indicadoresMesa['total']++;
        if ($ativo !== 'Sim') $indicadoresMesa['inativas']++;
        elseif ($mesaOcupada) $indicadoresMesa['ocupadas']++;
        else $indicadoresMesa['livres']++;
		if($nome_cliente != ""){
			$area_nome_cliente = '<span style="color:#133591; font-size: 12px; font-family: Arial, Helvetica, sans-serif;
font-weight:600;
letter-spacing:0.5px;">CLIENTE: '.mb_strtoupper($nome_cliente).'</span><br>';
		}


		if($nome_garcon != ""){
			$area_nome_garcon = '<span style="color:#616161; font-size: 11px; font-family: Arial, Helvetica, sans-serif;
font-weight:600;
letter-spacing:0.5px; ">GARÇON: '.mb_strtoupper($nome_garcon).'</span>';
		}

		

		echo <<<HTML
			<div class="col-xs-12 col-md-3 widget cardTarefas mesa-card {$classeMesa}" data-situacao="{$situacaoMesaEscapada}" style="margin-right: 5px; margin-bottom: 5px; display: inline-block; width:100%">
        		<div class="r3_counter_box">
                <div class="mesa-card-topo"><strong>Mesa {$nomeEscapado}</strong><span class="mesa-status">{$situacaoMesaEscapada}</span></div>

				<span  class="{$mostrar_fechar}" style="position:absolute; top:5px; right:8px">
				<a href="#" onclick="fecharMesa('{$id}', '{$nome}', '{$obs}', '{$id_abertura_mesa}', '{$total_couvert}', '{$total_comissaoC}', '{$subtotalC}', '{$garcon}', '{$total_mesa}', '{$total_valor}')" title="Fechar Mesa">
					<span aria-hidden="true" >
						<small >Fechar </small></span>x</span>			
						
				</a>
				</span>

				

		<div class="">  
        	
        		<div style="display:inline-block;">
							
        			<h5><span class="" onclick="abrirMesa('{$id}', '{$status}', '{$nome}','{$obsF}', '{$id_abertura_mesa}','{$ativo}')" style="padding:23px; font-size: 28px; color:#FFF; background-image: url('../../img/{$imagem_mesa}'); width:80px; height:80px; background-size: 100%; cursor: pointer;">{$nome}</span>   

        			
        			<a  class="{$ocultar_botoes}" href="#" onclick="editar('{$id_abertura_mesa}', '{$cliente}', '{$garcon}', '{$obs}', '{$pessoas}')" title="Editar Mesa" class=""> <img class="icon-rounded-vermelho" src="../../img/editar.png" width="20px" height="20px"></a>

        			<a class="{$ocultar_botoes}" href="rel/comprovante_mesa.php?id={$id_abertura_mesa}" title="Detalhamento da Mesa" target="_blank"><img class="icon-rounded-vermelho" src="../../img/pdf.png" width="20px" height="20px"></a>

        			<a class="$ocultar_indisponivel" href="#" onclick="abrirMesa('{$id}', '{$status}', '{$nome}', '{$obsF}', '{$id_abertura_mesa}','{$ativo}')" title="Adicionar Itens" class=""> <img class="icon-rounded-vermelho" src="../../img/plus.png" width="20px" height="20px"></a>

							<a class="{$ocultar_botoes}" href="#" onclick="adiantamento('{$id}', '{$nome}', '{$id_abertura_mesa}')" title="Adiantamento" class=""> <img class="icon-rounded-vermelho" src="../../img/valor.png" width="20px" height="20px"></a>
							

							<a class="{$ocultar_botoes}" href="#" onclick="trocarMesa('{$id}', '{$nome}', '{$id_abertura_mesa}')" title="Trocar Mesa" class=""> <img class="icon-rounded-vermelho" src="../../img/troca.png" width="20px" height="20px"></a>
        			</h5>       			

        		</div>
        		</div>     		
       					

        		<div class="mesa-duracao">{$duracaoMesa}</div>
                <hr style="margin-bottom: 3px">
                    <div class="stats" align="center">
                      <span>                     

                        <small><span style="color:green" class="vazio">{$totalF}</span> <span style="color:red; font-size: 10px">{$horario_aberturaF} </span> <span style="color:blue; font-size: 13px">{$pessoas_mesa}</span><br>                         	
                        	{$area_nome_cliente}
                        	{$area_nome_garcon} 
                       
                        </small></span>
                    </div>
                </div>
        	</div>
HTML;
	}
} else {
	echo 'Não possui registros cadastrados!';
}



?>
<div class="mesa-resumo" id="mesa-resumo">
    <div><small>Total de mesas</small><strong><?= (int)$indicadoresMesa['total'] ?></strong></div>
    <div><small>Livres</small><strong><?= (int)$indicadoresMesa['livres'] ?></strong></div>
    <div><small>Ocupadas</small><strong><?= (int)$indicadoresMesa['ocupadas'] ?></strong></div>
    <div><small>Inativas</small><strong><?= (int)$indicadoresMesa['inativas'] ?></strong></div>
</div>
<div class="mesa-filtros" role="group" aria-label="Filtrar mesas">
    <button type="button" class="btn btn-sm btn-outline-secondary mesa-filtro ativo" data-filtro="Todas">Todas</button>
    <button type="button" class="btn btn-sm btn-outline-success mesa-filtro" data-filtro="Livre">Livres</button>
    <button type="button" class="btn btn-sm btn-outline-warning mesa-filtro" data-filtro="Ocupada">Ocupadas</button>
    <button type="button" class="btn btn-sm btn-outline-secondary mesa-filtro" data-filtro="Inativa">Inativas</button>
</div>
<style>
.mesa-resumo{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;clear:both;margin:14px 0}
.mesa-resumo>div{border:1px solid #e2e8f0;border-radius:12px;background:#fff;padding:12px}
.mesa-resumo small{display:block;color:#64748b}.mesa-resumo strong{font-size:22px}
.mesa-filtros{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.mesa-filtro.ativo{font-weight:700;box-shadow:0 0 0 2px #94a3b8}
@media(max-width:640px){.mesa-resumo{grid-template-columns:repeat(2,minmax(0,1fr))}}
.mesa-card{border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 3px 12px #00000010;transition:box-shadow .2s,transform .2s}
.mesa-card:hover{box-shadow:0 6px 18px #00000020;transform:translateY(-2px)}
.mesa-card-topo{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:12px;padding-right:50px;font-size:14px}
.mesa-status{border-radius:20px;padding:4px 10px;font-size:11px;font-weight:700;background:#e2e8f0;color:#334155}
.mesa-card-livre{border-top:4px solid #16a34a}.mesa-card-livre .mesa-status{background:#dcfce7;color:#166534}
.mesa-card-ocupada{border-top:4px solid #ea580c}.mesa-card-ocupada .mesa-status{background:#ffedd5;color:#9a3412}
.mesa-card-inativa{border-top:4px solid #94a3b8;opacity:.75}
.mesa-duracao{font-size:12px;font-weight:600;color:#475569;margin-top:8px}
@media(max-width:600px){.mesa-card{margin-right:0!important}.mesa-card-topo{padding-right:45px}}
</style>

<script type="text/javascript">
    (function(){
        const raiz=document.getElementById('listar');
        if(!raiz)return;
        const botoes=Array.from(raiz.querySelectorAll('.mesa-filtro'));
        const filtroAnterior=raiz.dataset.filtroMesa||'Todas';
        function filtrar(situacao){
            raiz.dataset.filtroMesa=situacao;
            raiz.querySelectorAll('.mesa-card').forEach(function(card){
                card.style.display=(situacao==='Todas'||card.dataset.situacao===situacao)?'inline-block':'none';
            });
            botoes.forEach(function(botao){
                botao.classList.toggle('ativo',botao.dataset.filtro===situacao);
                botao.setAttribute('aria-pressed',String(botao.dataset.filtro===situacao));
            });
        }
        botoes.forEach(function(botao){botao.addEventListener('click',function(){filtrar(botao.dataset.filtro)});});
        filtrar(filtroAnterior);
    })();
	function editar(id, cliente, garcon, obs, pessoas) {
		$('#id').val(id);
		$('#nome').val(cliente);
		$('#garcon').val(garcon).change();
		$('#obs').val(obs);
		$('#pessoas').val(pessoas);


		$('#titulo_inserir').text('Editar Mesa');
		$('#modalAbertura').modal('show');

	}

	function abrirMesa(id, status, nome, obs, id_abertura_mesa, ativo) {

		$('#titulo_inserir').text('Abrir Mesa');

		if (ativo != 'Sim') {
			//alert('Mesa Indiponível!!')

			Swal.fire({
				text: "Mesa Indiponível!!!",
				icon: "warning"
			});

			return;
		}

		if (status == 'Aberta') {
			itens(id, nome, obs, id_abertura_mesa);
		} else {
			$('#id_abertura').val(id);
			$('#nome_da_mesa').text(nome);
			$('#modalAbertura').modal('show');
		}

		limparCampos();
	}

	function itens(id, nome, obs, id_abertura_mesa) {

		$('#nome_da_mesa_itens').text(nome);
		$('#obs_da_mesa_itens').text(obs);
		$('#modalItens').modal('show');
		$('#id_itens').val(id);
		$('#id_abertura_mesa').val(id_abertura_mesa);

		setTimeout(function() {
			listarCarrinho();
		}, 600)

		//window.open('../../index.php?id_mesa='+id);
	}

	function limparCampos() {
		$('#id').val('');
		$('#nome').val('');
		$('#obs').val('');
		$('#pessoas').val(1);
	}

	function fecharMesa(id, nome, obs, id_abertura_mesa, couvert, comissao, subtotal, garcon, total_itens, total_valor) {

		$('#id_fechamento').val(id);
		$('#id_ab_fechamento').val(id_abertura_mesa);
		$('#obs_fechamento').val(obs);
		$('#nome_da_mesa_fechamento').text(nome);
		$('#couvert_fechamento').val(couvert);
		$('#comissao_fechamento').val(comissao);
		$('#subtotal_fechamento').val(subtotal);
		$('#total_mesa_fechamento').val(subtotal);
		$('#garcon_fechamento').val(garcon).change();
		$('#total_itens_fechamento').val(total_itens);
		$('#total_valor_fechamento').val(total_valor);
		$('#modalFechamento').modal('show');

		totalizar();


	}

	function adiantamento(id, nome, id_abertura_mesa) {

		$('#nome_valor').text(nome);
		$('#modalValores').modal('show');
		$('#id_valor').val(id_abertura_mesa);

		setTimeout(function() {
			listarValores();
		}, 300)

	}


	function trocarMesa(id_mesa, nome, id_abertura_mesa) {

		$('#nome_troca').text(nome);
		$('#modalTroca').modal('show');
		$('#id_abertura_troca').val(id_abertura_mesa);
		$('#id_mesa_troca').val(id_mesa);		

	}
</script>