<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
$tabela = 'cargos';
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$nivel_usuario = $_SESSION['nivel'];
$id_usuario = $_SESSION['id'];



$pagina = 'caixas';

$data_atual = date('Y-m-d');

//trazer valor fechamento ultimo caixa
$query = $pdo->query("SELECT * from $pagina where empresa = '$id_empresa' order by id desc limit 1");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$ultimo_valor_fechado = @$res[0]['valor_fechamento'];

if ($ultimo_valor_fechado == "") {
	$ultimo_valor_fechado = 0;
}

$dataInicial = (string)($_POST['p1'] ?? $data_atual);
$dataFinal = (string)($_POST['p2'] ?? $data_atual);
$operador = (string)($_POST['p3'] ?? '');
foreach (['dataInicial','dataFinal'] as $campo) {
    if ($$campo === '') $$campo = $data_atual;
    $data = DateTimeImmutable::createFromFormat('!Y-m-d', $$campo);
    if (!$data || $data->format('Y-m-d') !== $$campo) {
        http_response_code(400); exit('Período inválido.');
    }
}
if ($dataInicial > $dataFinal) { http_response_code(400); exit('Período inválido.'); }
if (!in_array($nivel_usuario, ['Administrador','Gerente'], true)) {
    $operador = (string)$id_usuario;
}
if ($operador !== '' && (!ctype_digit($operador) || (int)$operador < 1)) {
    http_response_code(400); exit('Operador inválido.');
}
$sql = 'SELECT * FROM caixas WHERE data_abertura BETWEEN :inicio AND :fim AND empresa=:empresa';
$parametros = [':inicio'=>$dataInicial, ':fim'=>$dataFinal, ':empresa'=>$id_empresa];
if ($operador !== '') {
    $sql .= ' AND operador=:operador';
    $parametros[':operador']=(int)$operador;
}
$sql .= ' ORDER BY id DESC';
$query=$pdo->prepare($sql);
$query->execute($parametros);

echo <<<HTML
<small>
HTML;


$res = $query->fetchAll(PDO::FETCH_ASSOC);

$total_reg = @count($res);

if ($total_reg > 0) {

	echo <<<HTML
	<table class="table table-hover table-bordered text-nowrap border-bottom" id="tabela">
		<thead> 
			<tr> 				

				<th>Operador</th>				
				<th>Data Abertura</th>					
				<th>Valor Abertura</th> 
				<th>Valor Fechamento</th> 
				<th>Sangrias</th> 
				<th>Quebra</th>	
				<th>Aberto Por</th> 
				<th>Fechado Por</th> 	
				<th>Ações</th>

			</tr> 

		</thead> 

		<tbody> 

HTML;

	for ($i = 0; $i < $total_reg; $i++) {

		foreach ($res[$i] as $key => $value) {
		}

		$id = $res[$i]['id'];
		$operador = $res[$i]['operador'];
		$data_abertura = $res[$i]['data_abertura'];
		$data_fechamento = $res[$i]['data_fechamento'];
		$valor_abertura = $res[$i]['valor_abertura'];
		$valor_fechamento = $res[$i]['valor_fechamento'];
		$quebra = $res[$i]['quebra'];
		$usuario_abertura = $res[$i]['usuario_abertura'];
		$usuario_fechamento = $res[$i]['usuario_fechamento'];
		$obs = $res[$i]['obs'];

		$data_aberturaF = @implode('/', array_reverse(@explode('-', $data_abertura)));
		$data_fechamentoF = @implode('/', array_reverse(@explode('-', $data_fechamento)));

		$valor_aberturaF = @number_format($valor_abertura, 2, ',', '.');
		$valor_fechamentoF = @number_format($valor_fechamento, 2, ',', '.');
		$quebraF = @number_format($quebra, 2, ',', '.');






		$query2 = $pdo->prepare('SELECT nome FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1');
        $query2->execute([':id'=>$operador, ':empresa'=>$id_empresa]);
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);

		if (@count($res2) > 0) {

			$nome_operador = $res2[0]['nome'];
		} else {

			$nome_operador = '';
		}



		$query2 = $pdo->prepare('SELECT nome FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1');
        $query2->execute([':id'=>$usuario_abertura, ':empresa'=>$id_empresa]);
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_usu_abertura = $res2[0]['nome'];
		} else {
			$nome_usu_abertura = '';
		}




		$query2 = $pdo->prepare('SELECT nome FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1');
        $query2->execute([':id'=>$usuario_fechamento, ':empresa'=>$id_empresa]);
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_usu_fechamento = $res2[0]['nome'];
		} else {
			$nome_usu_fechamento = '';
		}



		if (empty($data_fechamento)) {
			$classe_aberto = 'text-danger';
			$ocultar = '';
		} else {
			$classe_aberto = 'verde';
			$ocultar = 'ocultar';
		}

		if ($quebra != 0) {
			$classe_quebra = 'text-danger';
		} else {
			$classe_quebra = 'text-verde';
		}


		if ($data_fechamento == "") {
			$data_fechamento_consulta = $data_atual;
		} else {
			$data_fechamento_consulta = $data_fechamento;
		}


        // Mesmo cálculo utilizado na confirmação do fechamento.
        require_once __DIR__ . '/calculo_saldo.php';
        try {
            $resumoCaixa = calcularSaldoCaixa($pdo, (int)$id_empresa, (int)$id, (float)$valor_abertura);
            $total_sangriasF = number_format($resumoCaixa['sangrias'], 2, ',', '.');
            $total_caixa = $resumoCaixa['esperado'];
        } catch (Throwable $erroSaldo) {
            error_log('Saldo caixa na listagem: '.$erroSaldo->getMessage());
            $total_sangriasF = 'Indisponível';
            $total_caixa = 0;
            $ocultar = 'ocultar'; // Não oferecer fechamento com saldo não calculado.
        }

        // Escape de valores vindos do banco antes de interpolar no HTML/JS.
        $nome_operador = htmlspecialchars($nome_operador, ENT_QUOTES, 'UTF-8');
        $nome_usu_abertura = htmlspecialchars($nome_usu_abertura, ENT_QUOTES, 'UTF-8');
        $nome_usu_fechamento = htmlspecialchars($nome_usu_fechamento, ENT_QUOTES, 'UTF-8');
        $obsJs = htmlspecialchars(json_encode((string)$obs, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        $nomeOperadorJs = htmlspecialchars(json_encode((string)$nome_operador, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');


		echo <<<HTML
			<tr> 
				<td class="caixa-operador-cell"><span class="caixa-operador-inline"><button type="button" class="caixa-expand-btn" aria-label="Expandir detalhes do caixa" aria-expanded="false">+</button><span class="caixa-operador-nome"><i class="fa fa-square {$classe_aberto} mr-1"></i> {$nome_operador}</span></span></td> 
				
				<td>{$data_aberturaF}</td>				
				<td>R$ {$valor_aberturaF}</td>
				<td>R$ {$valor_fechamentoF}</td>
				<td>R$ {$total_sangriasF}</td>
				<td class="{$classe_quebra}">R$ {$quebraF}</td>
				<td>{$nome_usu_abertura}</td>
				<td>{$nome_usu_fechamento}</td>
				<td>

		<big><a class="{$ocultar} btn btn-info-light btn-sm" href="#" onclick="editar('{$id}', '{$operador}', '{$data_abertura}','{$valor_abertura}',{$obsJs})" title="Editar Dados"><i class="fa fa-edit"></i></a></big>


		<big><a href="#" class="btn btn-danger-light btn-sm {$ocultar}" onclick="excluirBusca('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>


		<big><a class="{$ocultar} btn btn-success-light btn-sm" href="#" onclick="fechamento('{$id}', {$nomeOperadorJs}, '{$valor_abertura}', '{$total_caixa}')" title="Fechar Caixa"><i class="fa fa-check-square"></i></a></big>


		<big><a class="{$ocultar} btn btn-success-light btn-sm" href="#" onclick="sangria('{$id}')" title="Sangria"><i class="fa fa-money"></i></a></big>



		<form   method="POST" action="rel/detalhamento_caixa_class.php" target="_blank" style="display:inline-block">
			<input type="hidden" name="id" value="{$id}">
			<button class="btn btn-danger-light btn-sm" title="Detalhamento do Caixa"><i class="fa fa-file-pdf-o" ></i></button>
		</form>


				</td>  
			</tr> 

HTML;
	}
	echo <<<HTML
		</tbody> 
	</table>
    <div id="mensagem-excluir" class="text-center small" aria-live="polite"></div>
</small>

HTML;
} else {

	echo 'Não possui nenhuma conta para esta data!';
}


?>



<style>
.bc-caixa-icon{display:inline-block!important;color:#914a29!important;font-size:16px!important;margin-right:5px}.bc-caixa-toolbar{display:flex;gap:6px;margin-bottom:16px}.bc-caixa-toolbar button{border:1px solid #d7c5b7;color:#63351e}.bc-caixa-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,300px),1fr));gap:14px;margin:12px 0}.bc-caixa-card{background:#fffaf5;border:1px solid #e5d4c6;border-radius:12px;padding:15px;color:#4e2d20}.bc-caixa-top{display:flex;align-items:center;justify-content:space-between;gap:8px;border-bottom:1px solid #e5d4c6;padding-bottom:12px}.bc-caixa-status{font-size:12px;border-radius:20px;padding:4px 9px}.bc-caixa-status.aberto{background:#e1f4e9;color:#13745a}.bc-caixa-status.fechado{background:#f8e7e7;color:#b42332}.bc-caixa-fields{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:12px 0}.bc-caixa-fields small,.bc-caixa-fields strong{display:block;overflow-wrap:anywhere}.bc-caixa-fields small{color:#795038}.bc-caixa-actions{border-top:1px solid #e5d4c6;padding-top:10px;display:flex;gap:6px;flex-wrap:wrap}.bc-caixa-actions .ocultar{display:none!important}
html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons){display:flex!important;flex-wrap:wrap!important;justify-content:center!important;gap:7px!important;align-items:center;justify-items:center;padding-top:12px!important}
html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn{--bc-fg:#795038;--bc-bg:#f4e7dc;min-width:36px!important;min-height:36px!important;padding:7px!important;border-radius:9px!important;border:1px solid var(--bc-fg)!important;background:var(--bc-bg)!important;color:var(--bc-fg)!important;transition:background .2s,color .2s,transform .2s,box-shadow .2s!important}
html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn-info-light{--bc-fg:#17658a;--bc-bg:#e1f2fa}
html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn-primary-light,html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn-warning-light{--bc-fg:#89500a;--bc-bg:#fff0d7}
html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn-danger-light{--bc-fg:#b42332;--bc-bg:#fde5e7}
html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn-success-light{--bc-fg:#13745a;--bc-bg:#def3e9}
html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn i{color:inherit!important;font-size:16px!important}
html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn:not(.disabled):not([aria-disabled=true]):hover{background:var(--bc-fg)!important;color:#fff!important;transform:translateY(-2px);box-shadow:0 4px 9px #63351e30!important}
html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn:focus-visible{outline:2px solid var(--bc-fg)!important;outline-offset:2px}
html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn.ocultar,html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn.d-none{display:none!important}
@media(prefers-reduced-motion:reduce){html body #listar :is(.bc-caixa-actions,.caixa-detail-actions-buttons) .btn{transition:none!important;transform:none!important}}
</style>
<script type="text/javascript">
	$(document).ready(function() {
        var tabelaCaixa=$('#tabela').DataTable({
            "ordering": false,
            "responsive": false,
            "scrollX": false,
            "autoWidth": false,
            "stateSave": false,
            "columnDefs": [{"targets":8,"visible":false,"searchable":false}]
        });
        var modo=window.bcCaixaModo||'cartao';
        var barra=$('<div class="bc-caixa-toolbar"><button type="button" class="btn btn-sm" data-modo="cartao"><i class="bi bi-grid"></i> Cartões</button> <button type="button" class="btn btn-sm" data-modo="tabela"><i class="bi bi-list-ul"></i> Tabela</button></div>');
        var grade=$('<div class="bc-caixa-grid"></div>');
        $('#tabela_wrapper').prepend(barra);
        $('#tabela').after(grade);
        var campos=['Operador','Data de abertura','Valor de abertura','Valor de fechamento','Sangrias','Quebra','Aberto por','Fechado por'];
        var icones=['bi-person-circle','bi-calendar-event','bi-wallet2','bi-cash-stack','bi-arrow-up-circle','bi-calculator','bi-person-plus','bi-person-check'];
        function renderCaixas(){
            grade.empty();
            barra.find('button').each(function(){ $(this).toggleClass('btn-primary',$(this).data('modo')===modo); });
            $('#tabela').toggle(modo==='tabela');grade.toggle(modo==='cartao');
            if(modo!=='cartao')return;
            tabelaCaixa.rows({page:'current',search:'applied'}).every(function(){
                var dados=this.data(), tr=$(this.node()), tds=tr.children('td');
                var aberto=tds.eq(0).find('i.fa-square').hasClass('text-danger');
                var card=$('<article class="bc-caixa-card"></article>');
                var topo=$('<div class="bc-caixa-top"></div>');
                topo.append($('<strong></strong>').append($('<i class="bi bi-person-circle bc-caixa-icon me-2" aria-hidden="true"></i>'),document.createTextNode(tds.eq(0).find('.caixa-operador-nome').text().trim())));
                topo.append($('<span class="bc-caixa-status"></span>').addClass(aberto?'aberto':'fechado').text(aberto?'Aberto':'Fechado'));
                card.append(topo);
                var corpo=$('<div class="bc-caixa-fields"></div>');
                for(var i=1;i<8;i++){
                    var campo=$('<div></div>');campo.append($('<small></small>').append($('<i class="bi bc-caixa-icon" aria-hidden="true"></i>').addClass(icones[i]),document.createTextNode(' '+campos[i])));
                    campo.append($('<strong></strong>').text(tds.eq(i).text().trim()||'—'));corpo.append(campo);
                }
                card.append(corpo);
                card.append($('<div class="bc-caixa-actions"></div>').html(dados[8]||''));
                grade.append(card);
            });
        }
        barra.on('click','button',function(){modo=$(this).data('modo');window.bcCaixaModo=modo;renderCaixas();});
        tabelaCaixa.on('draw',renderCaixas);
        renderCaixas();
        // Detalhes por linha, sem sobrepor o indicador de caixa aberto.
        $('#tabela tbody').off('click.caixaExpand').on('click.caixaExpand', '.caixa-expand-btn', function(){
            var btn=$(this), tr=btn.closest('tr'), row=$('#tabela').DataTable().row(tr);
            if(row.child.isShown()){
                row.child.hide();
                tr.removeClass('caixa-expanded');
                btn.text('+').attr('aria-expanded','false').attr('aria-label','Expandir detalhes do caixa');
                return;
            }
            var nomes=['Operador','Data Abertura','Valor Abertura','Valor Fechamento','Sangrias','Quebra','Aberto Por','Fechado Por'];
            var detalhe=$('<div class="caixa-detail-card"></div>');
            tr.children('td').slice(0,8).each(function(i){
                var campo=$('<div class="caixa-detail-field"></div>');
                campo.append($('<strong></strong>').text(nomes[i]));
                var texto=i===0 ? $(this).find('.caixa-operador-nome').text().trim() : $(this).text().trim();
                campo.append($('<span></span>').text(texto || '—'));
                detalhe.append(campo);
            });
            // A coluna Acoes fica oculta; seus controles aparecem somente
            // dentro do cartao expandido, mantendo os handlers existentes.
            var dados=row.data();
            if(dados && dados[8]){
                var acoes=$('<div class="caixa-detail-actions"></div>');
                acoes.append($('<strong class="caixa-detail-actions-title"></strong>').text('Ações'));
                acoes.append($('<div class="caixa-detail-actions-buttons"></div>').html(dados[8]));
                detalhe.append(acoes);
            }
            row.child(detalhe).show();
            tr.addClass('caixa-expanded');
            btn.text('−').attr('aria-expanded','true').attr('aria-label','Recolher detalhes do caixa');
        });
		$('#tabela_filter label input').focus();
	});



	function editar(id, operador, data_abertura, valor_abertura, obs) {



		$('#id').val(id);
		$('#operador').val(operador).change();
		$('#valor_abertura').val(valor_abertura);
		$('#data_abertura').val(data_abertura);
		$('#obs').val(obs);

		$('#tituloModal').text('Editar Registro');
		$('#modalForm').modal('show');
		$('#mensagem').text('');
		$('#titulo_inserir').text('Editar Registro');


	}




	function fechamento(id, operador, valor_abertura, total_caixa) {
		

		$('#id_fechar').val(id);
		$('#nome_operador').val(operador);
		$('#valor_abertura_fechar').val(valor_abertura);
		$('#total_caixa_fechar').val('');
        const esperado = Number(total_caixa);
        $('#saldo_esperado_visual').text(Number.isFinite(esperado) ? esperado.toLocaleString('pt-BR', {style:'currency',currency:'BRL'}) : 'Indisponível');

		$('#tituloModalFechar').text('Fechar Caixa');
		$('#modalFechar').modal('show');
		$('#mensagem_fechar').text('');

	}



	function sangria(id) {

		$('#id_sangria').val(id);
		//$('#nome_operador_sangria').val(operador);
		$('#tituloModalSangria').text('Efetuar Sangria');

		$('#modalSangria').modal('show');
		$('#mensagem_sangria').text('');


	}


	function limparCampos() {

		$('#id').val('');
		//$('#operador').val('').change();		
		$('#valor_abertura').val('<?= $ultimo_valor_fechado ?>');
		$('#data_abertura').val('<?= $data_atual ?>');

		$('#obs').val('');
		$('#ids').val('');
		$('#div_botoes').hide();

	}


</script>