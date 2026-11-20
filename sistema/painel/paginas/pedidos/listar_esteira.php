<?php
@session_start();
$id_empresa = filter_var($_SESSION['empresa'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id_empresa || !filter_var($_SESSION['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]])) {
    http_response_code(403);
    exit('Sessão expirada. Entre novamente no painel.');
}
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'vendas';
$data_hoje = date('Y-m-d');

$statusInformado = trim((string)($_POST['status'] ?? ''));
$status = '%' . $statusInformado . '%';
$ult_ped = @$_POST['ult_pedido'];

$total_vendas = 0;

$sql_pedido_balcao = " and empresa = '$id_empresa' ";
if($pedidos_pdv_delivery != 'Sim'){
	$sql_pedido_balcao = " and tipo_pedido != 'Balcão' and empresa = '$id_empresa' ";
}

//TOTAIS DOS PEDIDOS
$query = $pdo->query("SELECT * FROM $tabela where status = 'Iniciado' $sql_pedido_balcao");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_ini = @count($res);

$query = $pdo->query("SELECT * FROM $tabela where status = 'Aceito' $sql_pedido_balcao");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_ace = @count($res);

$query = $pdo->query("SELECT * FROM $tabela where status = 'Preparando' $sql_pedido_balcao");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_prep = @count($res);

$query = $pdo->query("SELECT * FROM $tabela where status = 'Entrega' $sql_pedido_balcao");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_ent = @count($res);

$query = $pdo->query("SELECT * FROM $tabela where status != 'Finalizado' and status != 'Cancelado' $sql_pedido_balcao");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_pedidos = @count($res);


$query = $pdo->query("SELECT * FROM $tabela where status != 'Finalizado' and status != 'Cancelado' $sql_pedido_balcao order by id desc limit 1");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_ult_pedido = @$res[0]['id'];

// Notificações de novos pedidos são controladas no navegador, sem autoplay repetitivo.

$ids = '';
$query = $pdo->prepare("SELECT * FROM vendas WHERE status LIKE :status
    AND status NOT IN ('Finalizado','Cancelado') $sql_pedido_balcao ORDER BY hora ASC");
$query->execute([':status'=>$status]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
// Resumo de produção em lote: não altera carrinho, valores nem a automação.
$itensPorVenda = [];
if ($total_reg > 0) {
    $idsVenda = array_values(array_unique(array_map('intval', array_column($res, 'id'))));
    $marcadores = implode(',', array_fill(0, count($idsVenda), '?'));
    $consultaItens = $pdo->prepare("SELECT c.id, c.pedido, c.quantidade, c.nome_produto, c.produto, c.obs, c.sabores,
                                          p.nome AS nome_catalogo
                                   FROM carrinho c
                                   LEFT JOIN produtos p ON p.id = c.produto AND p.empresa = ?
                                   WHERE c.pedido IN ($marcadores) ORDER BY c.id ASC");
    $consultaItens->execute(array_merge([(int)$id_empresa], $idsVenda));
    $linhasItens = $consultaItens->fetchAll(PDO::FETCH_ASSOC);
    $idsCarrinho = array_values(array_unique(array_map('intval', array_column($linhasItens, 'id'))));
    $extrasPorItem = [];
    if ($idsCarrinho) {
        $marcadoresExtras = implode(',', array_fill(0, count($idsCarrinho), '?'));
        $consultaExtras = $pdo->prepare("SELECT t.carrinho, t.tabela, t.quantidade,
                                               COALESCE(ac.nome, aa.nome, g.nome, ing.nome, ig.texto) AS nome_extra
                                        FROM temp t
                                        LEFT JOIN adicionais ac ON ac.id=t.id_item AND t.tabela='adicionais'
                                        LEFT JOIN adicionais_cat aa ON aa.id=t.id_item AND t.tabela='adicionais'
                                        LEFT JOIN guarnicoes g ON g.id=t.id_item AND t.tabela='guarnicoes'
                                        LEFT JOIN ingredientes ing ON ing.id=t.id_item AND t.tabela='ingredientes'
                                        LEFT JOIN itens_grade ig ON ig.id=t.id_item AND t.grade IS NOT NULL
                                        WHERE t.carrinho IN ($marcadoresExtras)
                                        ORDER BY t.id ASC");
        $consultaExtras->execute($idsCarrinho);
        foreach ($consultaExtras->fetchAll(PDO::FETCH_ASSOC) as $extra) {
            $nomeExtra = trim((string)($extra['nome_extra'] ?? ''));
            if ($nomeExtra === '') continue;
            $tipoExtra = (string)$extra['tabela'];
            $rotulo = $tipoExtra === 'ingredientes' ? 'Sem ' : '+ ';
            $extrasPorItem[(int)$extra['carrinho']][] = $rotulo . $nomeExtra;
        }
    }
    foreach ($linhasItens as $itemLinha) {
        $nome = trim((string)($itemLinha['nome_produto'] ?: $itemLinha['nome_catalogo'] ?: 'Produto'));
        $quantidade = (float)$itemLinha['quantidade'];
        $quantidadeTexto = $quantidade == (int)$quantidade ? (string)(int)$quantidade : number_format($quantidade, 2, ',', '.');
        $textoItem = $quantidadeTexto . ' × ' . $nome;
        $detalhes = $extrasPorItem[(int)$itemLinha['id']] ?? [];
        if ((int)$itemLinha['sabores'] > 1) $detalhes[] = (int)$itemLinha['sabores'] . ' sabores';
        if (trim((string)$itemLinha['obs']) !== '') $detalhes[] = 'Obs.: ' . trim((string)$itemLinha['obs']);
        $itensPorVenda[(int)$itemLinha['pedido']][] = [
            'nome' => htmlspecialchars($textoItem, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'detalhes' => htmlspecialchars(implode(' · ', $detalhes), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        ];
    }
}
if ($total_reg > 0) {


	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$id = $res[$i]['id'];
		$cliente = $res[$i]['cliente'];
		$valor = $res[$i]['valor'];
		$total_pago = $res[$i]['total_pago'];
		$troco = $res[$i]['troco'];
		$data = $res[$i]['data'];
		$hora = $res[$i]['hora'];
		$status = $res[$i]['status'];
		$pago = $res[$i]['pago'];
		$obs = $res[$i]['obs'];
		$taxa_entrega = $res[$i]['taxa_entrega'];
		$tipo_pgto = $res[$i]['tipo_pgto'];
		$usuario_baixa = $res[$i]['usuario_baixa'];
		$entrega = $res[$i]['entrega'];
		$mesa = $res[$i]['mesa'];
		$nome_cliente_ped = $res[$i]['nome_cliente'];
		$pedido = $res[$i]['pedido'];
		$impressao = $res[$i]['impressao'];
		$codigo_pix = $res[$i]['ref_api'];
		$tipo_pedido = $res[$i]['tipo_pedido'];

		//verificar pgto pix
		if ($codigo_pix != "" and $pago != "Sim") {
			require("verificar_pgto.php");
			if ($pagamento_pix_validado === true && $status_api === 'approved') {
                $atualizaPix=$pdo->prepare("UPDATE vendas SET pago='Sim', tipo_pgto='Pix'
                    WHERE id=:id AND empresa=:empresa AND pago<>'Sim'");
                $atualizaPix->execute([':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
			}
		}

		$valorF = number_format($valor, 2, ',', '.');
		$total_pagoF = number_format($total_pago, 2, ',', '.');
		$trocoF = number_format($troco, 2, ',', '.');
		$taxa_entregaF = number_format($taxa_entrega, 2, ',', '.');
		$dataF = implode('/', array_reverse(explode('-', $data)));
		$horaF = date("H:i", strtotime($hora));

		$hora_pedido = date('H:i', strtotime("+$previsao_entrega minutes", strtotime($hora)));

		if ($hora_pedido < date('H:i')) {
			$classe_pago2 = '#ffdddd';
		} else {
			$classe_pago2 = '#ffffff';
		}

		$query2 = $pdo->prepare("SELECT * FROM usuarios WHERE id=:id AND empresa=:empresa");
        $query2->execute([':id'=>(int)$usuario_baixa,':empresa'=>(int)$id_empresa]);
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$nome_usuario_pgto = $res2[0]['nome'];
		} else {
			$nome_usuario_pgto = 'Nenhum!';
		}


		$query2 = $pdo->prepare("SELECT * FROM clientes WHERE id=:id AND empresa=:empresa");
        $query2->execute([':id'=>(int)$cliente,':empresa'=>(int)$id_empresa]);
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$nome_cliente = $res2[0]['nome'];
			$telefone_cliente = $res2[0]['telefone'];
			$telefone_clienteF = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);
		} else {
			if ($mesa != '0' and $mesa != '') {
				$nome_cliente = 'Mesa: ' . $mesa;
			} else {
				$nome_cliente = $nome_cliente_ped;
			}
			$telefone_cliente = '';
			$telefone_clienteF = '';
		}


		$whatsapp_cliente = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);


		if ($status == 'Iniciado') {
			$classe_alerta = '#eb0000';
			$total_vendas += $valor;
			$titulo_link = 'Mudar para Aceito!';
			$acao_link = 'Aceito';
			$class_btn = 'btn-danger';
		} else if ($status == 'Aceito') {
			$classe_alerta = '#0097ff';
			$total_vendas += 0;
			$titulo_link = 'Mudar para Preparando!';
			$acao_link = 'Preparando';
			$class_btn = 'btn-info';
		} else if ($status == 'Preparando') {
			$classe_alerta = '#ff8400';
			$total_vendas += 0;
			$titulo_link = 'Mudar para Entrega!';
			$acao_link = 'Entrega';
			$class_btn = 'btn-warning';
		} else {
			$classe_alerta = '#00dc49';
			$total_vendas += $valor;
			$titulo_link = 'Mudar para Finalizado!';
			$acao_link = 'Finalizado';
			$class_btn = 'btn-success';
		}

		if ($pago == 'Sim') {
			$classe_excluir = 'ocultar';
			$visivel = 'ocultar';
			$classe_pago = 'text-verde';
			$texto_pago = '(Pago)';
		} else {
			$classe_excluir = '';
			$visivel = '';
			$classe_pago = 'text-danger';
			$texto_pago = '';
		}

		if ($obs != "") {
			$classe_info = 'warning';
		} else {
			$classe_info = 'info';
		}

		if ($entrega == 'Delivery') {
			$classe_entrega = 'text-danger';
		} else if ($entrega == 'Retirar') {
			$classe_entrega = 'text-primary';
		} else {
			$classe_entrega = 'text-verde';
		}


        // A listagem nunca confirma impressão: a confirmação é explícita após imprimir.
        $impressaoPendente = $impressao !== 'Sim';
        $etiquetaImpressao = $impressaoPendente ? 'Impressão pendente' : 'Impressão confirmada';
        $classeImpressao = $impressaoPendente ? 'bg-warning text-dark' : 'bg-success';

		$nome_clienteF = mb_strimwidth($nome_cliente, 0, 16, "...");

		$classe_troco = '';
		if ($troco == "" || $troco == 0) {
			$classe_troco = 'ocultar';
		}

		if ($telefone_cliente == '' || $telefone_cliente == "Sem Registro") {
			$ocultar_whats = 'ocultar';
		} else {
			$ocultar_whats = '';
		}

		$tel_whatsF = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);


		if ($api_whatsapp != 'Não') {
			$ocultar_modal_whats = '';
			$ocultar_icone_whats = 'ocultar';
		}else{
			$ocultar_modal_whats = 'ocultar';
			$ocultar_icone_whats = '';
		}

		$resumoItens = '';
        foreach (($itensPorVenda[(int)$id] ?? []) as $itemResumo) {
            $resumoItens .= '<li><strong>' . $itemResumo['nome'] . '</strong>';
            if ($itemResumo['detalhes'] !== '') $resumoItens .= '<small class="d-block text-muted">' . $itemResumo['detalhes'] . '</small>';
            $resumoItens .= '</li>';
        }
        if ($resumoItens === '') $resumoItens = '<li>Itens indisponíveis — consulte os detalhes</li>';
        $resumoItens = '<ul class="esteira-lista-itens">' . $resumoItens . '</ul>';
        $obsPedido = trim((string)$obs);
        if ($obsPedido !== '') $resumoItens .= '<p class="esteira-obs-pedido"><strong>Observação:</strong> ' . htmlspecialchars($obsPedido, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
		echo <<<HTML
<div class="col-md-3 widget esteira-cartao" data-id="{$id}" data-status="{$status}" data-criado="{$data} {$hora}" data-tipo="{$entrega}" style="display: inline-block;">
			<div class="" style="padding:4px; min-height: 100px; background: {$classe_pago2};">
				<div style="border-bottom: 1px solid #000">
					<span style="color:{$classe_alerta}"> <i class="fa fa-square"></i></span> Pedido <b>{$pedido}</b>
					<span style="position:absolute; right:5px"><i>{$horaF}</i></span>
				</div>

				<div style="margin-top: 5px">
					<span><small><i><span style="color:#000">{$nome_clienteF} <span class="{$classe_entrega}"><small>({$entrega}) </small></span></span></i></small></span>					
				</div>

				<div>
					<span id="area_status_{$id}"><small><i>Status <b><a class="btn {$class_btn} btn-rounded mb-1" style="font-size: 9px; padding: 5px" title="{$titulo_link}" href="#" onclick="ativarPedido('{$id}','{$acao_link}','{$telefone_cliente}','{$valorF}','{$tipo_pgto}','{$hora_pedido}','{$entrega}')">
					{$status} 
					<i class="fa fa-arrow-right"></i>
					</a></b></i></small></span>		
					<img id="status_link_{$id}" src="../../img/loading.gif" width="80px" style="display:none">			
				</div>
					<div style="border-bottom: 1px solid #000; padding-bottom: 5px">
						<small>
							<span class=" {$classe_pago}">R$ {$total_pagoF} <small>{$texto_pago}</small></span>	
							<span class="{$classe_troco}">Troco: {$trocoF}</span>
							<span class="text-primary"><small>({$tipo_pgto})</small></span>	
						</small>		
					</div>
					<div class="mb-2"><span class="badge {$classeImpressao}">{$etiquetaImpressao}</span></div>
				{$resumoItens}
				<div align="center" style="">
					<span>	

		<a class="btn btn-{$classe_info}-light btn-sm" href="#" onclick="mostrar('{$id}', '{$nome_cliente}', '{$pedido}')" title="Ver Dados"><i class="fa fa-info-circle"></i></a>



<big><a href="#" class="btn btn-danger-light btn-sm {$classe_excluir}" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>

	<a id="btn_baixar_{$id}" class="btn btn-success-light btn-sm {$classe_excluir}" href="#" onclick="baixar('{$id}', '{$nome_cliente}', '{$tipo_pgto}')" title="Confirmar Pgto"><i class="fa fa-check-square"></i></a>


		<a class="btn btn-dark-light btn-sm" href="#" onclick="imprimirPedidoEsteira('{$id}', '{$tipo_pedido}');return false" title="Abrir cupom para impressão"><i class="fa fa-print"></i></a>

	<a href="#" class="btn btn-success-light btn-sm  {$ocultar_modal_whats}" onclick="modalWhats('{$cliente}','Cliente')" title="Enviar Mensagem para o Cliente"><i class="fa-brands fa-whatsapp"></i></a>

<a class="btn btn-success-light btn-sm {$ocultar_icone_whats}" target="_blank" href="http://api.whatsapp.com/send?1=pt_BR&phone={$telefone_clienteF}" title="Whatsapp Cliente">
	<i class="fa-brands fa-whatsapp"></i></a>




		
	
		</span>				
				</div>
				
			</div>
		</div>	
HTML;
	}

	$total_vendasF = number_format($total_vendas, 2, ',', '.');
} else {
	echo '<small>Não possui nenhum Pedido Hoje ainda!</small>';
}



$query = $pdo->prepare("SELECT id FROM vendas WHERE empresa=:empresa AND data=CURDATE() AND status='Iniciado'");
$query->execute([':empresa'=>(int)$id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_dos_itens_pedidos = @count($res);


?>

<script type="text/javascript">
	$(document).ready(function() {

		// A listagem não abre janelas nem marca cupons como impressos automaticamente.

		$('#todos_pedidos').text("<?= $total_pedidos ?>");
		$('#ini_pedidos').text("<?= $total_ini ?>");
		$('#prep_pedidos').text("<?= $total_prep ?>");
		$('#ent_pedidos').text("<?= $total_ent ?>");
		$('#id_pedido').val("<?= $id_ult_pedido ?>");

		$('#total-dos-pedidos').text("<?= $total_dos_itens_pedidos ?>");

		
		// A esteira não deve roubar o foco do operador durante atualizações.
	});
</script>


<script type="text/javascript">
	function mostrar(id, cliente, pedido) {
		listarPedido(id);
		$('#nome_dados').text('Pedido ' + pedido + ' - Cliente: ' + cliente);
		$('#modalDados').modal('show');
	}
</script>

<script type="text/javascript">
	function baixar(id, cliente, pgto) {

		$('#nome_baixar').text('Confirmar Pagamento: Pedido ' + id + ' - Cliente: ' + cliente);
		$('#pgto').val(pgto).change();
		$('#id_baixar').val(id);
		$('#modalBaixar').modal('show');
	}
</script>


<script type="text/javascript">
	function ativarPedido(id, acao, telefone, total, pagamento, hora, entrega) {
		var pedido_whatsapp = "<?= $api_whatsapp ?>";

		$('#status_link_' + id).show();
		$('#area_status_' + id).hide();


		if (entrega == 'Delivery') {

			var texto = 'Seu Pedido saiu para entrega';
		} else if (entrega == 'Retirar') {
			var texto = 'Seu Pedido ficou pronto, pode vir retirá-lo';
		} else {
			var texto = 'Seu Pedido já ficou pronto, pode vir!!';
		}

		var imp_aut = '<?= $impressao_automatica ?>';

		if (imp_aut == 'Sim' && acao == 'Preparando') {
			let a = document.createElement('a');
			a.target = '_blank';
			a.href = 'rel/comprovante.php?id=' + id;
			a.click();
		}

		if (acao == 'Entrega' && entrega == 'Delivery') {
			$('#id_entregador').val(id);
			$('#modalEntregador').modal('show');
		}

		setTimeout(function() {

			$.ajax({
				url: 'paginas/pedidos/mudar-status.php',
				method: 'POST',
				data: {
					id,
					acao,
					telefone,
					total,
					pagamento,
					texto
				},
				dataType: "text",

				success: function(mensagem) {
					var split = mensagem.split("***");

					var msg = split[0];

					if (msg == "Alterado com Sucesso") {

						if (acao.trim() === 'Entrega') {
							if (pedido_whatsapp != 'Não') {
								//let a= document.createElement('a');
								// a.target= '_blank';
								// a.href= 'http://api.whatsapp.com/send?1=pt_BR&phone='+telefone+'&text= *Atençãoooo*  %0A '+texto+' %0A *Total* R$'+total+' %0A *Tipo Pagamento* '+pagamento;
								// a.click();
							}
						}
						listar();
						$('#status_link_' + id).hide();
						$('#area_status_' + id).show();
					} else {
						alertInformativoFechar(mensagem)
						//$('#mensagem-excluir').addClass('text-danger')
						//$('#mensagem-excluir').text(mensagem)

						if(mensagem.trim() === 'O pedido ainda está pendente de pagamento, dê a baixa no pagamento antes!'){
							setTimeout(function() {
								$('#btn_baixar_' + id).click();
								}, 2000);
						}


						$('#status_link_' + id).hide();
						$('#area_status_' + id).show();
					}


				},

			});

		}, 1000);
	}
</script>


<script type="text/javascript">
	function gerarComprovante(id, tipo_pedido) {


		let a = document.createElement('a');
		a.target = '_blank';
		if(tipo_pedido == "Balcão"){
			a.href = 'rel/comprovante_balcao.php?id=' + id;
		}else{
			a.href = 'rel/comprovante.php?id=' + id;
		}
		
		a.click();
	}

</script>


	<script type="text/javascript">
		function modalWhats(id, tipo) {
		$('#id_whats').val(id);
		$('#tipo_whats').val(tipo);
		$('#mensagem_whatsapp').val('');
		$('#modalWhats').modal('show');
	}

	</script>