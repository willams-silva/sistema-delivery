<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'vendas';
$data_hoje = date('Y-m-d');

$status = '%' . @$_POST['status'] . '%';
$status_busca = '%' . @$_POST['status'] . '%';
$ult_ped = @$_POST['ult_pedido'];

$total_vendas = 0;

$busca = @$_POST['p1'];
$pagina = min(10000,max(0,(int)($_POST['p2'] ?? 0)));
$itens_paginacao = 10;
$limite = $pagina * $itens_paginacao;

$sql_pedido_balcao = " and empresa = '$id_empresa' ";
if($pedidos_pdv_delivery != 'Sim'){
	$sql_pedido_balcao = " and tipo_pedido != 'Balcão' and empresa = '$id_empresa' ";
}

// Contadores em uma consulta agregada, sem carregar todas as vendas na memória.
$mostrarBalcao = $pedidos_pdv_delivery === 'Sim';
$condicaoBalcao = $mostrarBalcao ? '' : " AND tipo_pedido != 'Balcão'";
$contagemSt = $pdo->prepare("SELECT
 COALESCE(SUM(CASE WHEN status='Iniciado' THEN 1 ELSE 0 END),0) iniciados,
 COALESCE(SUM(CASE WHEN status='Aceito' THEN 1 ELSE 0 END),0) aceitos,
 COALESCE(SUM(CASE WHEN status='Preparando' THEN 1 ELSE 0 END),0) preparando,
 COALESCE(SUM(CASE WHEN status='Entrega' THEN 1 ELSE 0 END),0) entrega,
 COALESCE(SUM(CASE WHEN status NOT IN ('Finalizado','Cancelado') THEN 1 ELSE 0 END),0) abertos,
 COALESCE(MAX(CASE WHEN status NOT IN ('Finalizado','Cancelado') THEN id ELSE NULL END),0) ultimo
 FROM vendas WHERE empresa=:empresa $condicaoBalcao");
$contagemSt->execute([':empresa'=>(int)$id_empresa]);
$contagem=$contagemSt->fetch(PDO::FETCH_ASSOC);
$total_ini=(int)$contagem['iniciados'];
$total_ace=(int)$contagem['aceitos'];
$total_prep=(int)$contagem['preparando'];
$total_ent=(int)$contagem['entrega'];
$total_pedidos=(int)$contagem['abertos'];
$id_ult_pedido=(int)$contagem['ultimo'];
// Não tocar som no primeiro carregamento: só há pedido novo quando
// o navegador já enviou o último ID observado.
if (filter_var($ult_ped,FILTER_VALIDATE_INT) && (int)$ult_ped<$id_ult_pedido) {
 echo '<audio autoplay="true"><source src="../../img/audio.mp3" type="audio/mpeg" /></audio>';
}

$ids = '';
// Pesquisa parametrizada: impede que a busca do operador altere a consulta SQL.
$pagina = min(10000, max(0, (int)$pagina));
$limite = $pagina * $itens_paginacao;
$busca = trim((string)$busca);
$statusFiltro = trim((string)($_POST['status'] ?? ''));
$sqlFiltroBalcao = $pedidos_pdv_delivery != 'Sim' ? " AND tipo_pedido != 'Balcão'" : '';
// Filtro opcional por tipo de pedido, restrito aos tipos utilizados pelo sistema.
$tipoFiltro=trim((string)($_POST['tipo_pedido'] ?? ''));
$tiposPermitidos=['Balcão','Delivery','Retirar'];
if (!in_array($tipoFiltro,$tiposPermitidos,true)) $tipoFiltro='';
if ($tipoFiltro==='Balcão' && $pedidos_pdv_delivery!='Sim') $tipoFiltro='';
$sqlFiltroTipo=$tipoFiltro!=='' ? ' AND tipo_pedido=:tipo_pedido' : '';
$parametrosPedidos=[
    ':empresa'=>(int)$id_empresa,
    ':status'=>'%'.$statusFiltro.'%',
    ':busca'=>'%'.$busca.'%'
];
if ($tipoFiltro!=='') $parametrosPedidos[':tipo_pedido']=$tipoFiltro;
$queryPedidos = $pdo->prepare("SELECT * FROM vendas
    WHERE empresa=:empresa AND status NOT IN ('Finalizado','Cancelado')
      AND status LIKE :status AND nome_cliente LIKE :busca
      $sqlFiltroBalcao $sqlFiltroTipo ORDER BY hora ASC, id ASC LIMIT $limite, $itens_paginacao");
$queryPedidos->execute($parametrosPedidos);
$res = $queryPedidos->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$totalRegSt=$pdo->prepare("SELECT COUNT(*) FROM vendas
    WHERE empresa=:empresa AND status NOT IN ('Finalizado','Cancelado')
      AND status LIKE :status AND nome_cliente LIKE :busca $sqlFiltroBalcao $sqlFiltroTipo");
$totalRegSt->execute($parametrosPedidos);
$num_total=(int)$totalRegSt->fetchColumn();
$num_paginas=(int)ceil($num_total/$itens_paginacao);
// Se o filtro mudou e a página anterior não existe mais, retorna à primeira.
if ($num_total>0 && $pagina>=$num_paginas) {
    $pagina=0;
    $limite=0;
    $queryPedidos=$pdo->prepare("SELECT * FROM vendas
        WHERE empresa=:empresa AND status NOT IN ('Finalizado','Cancelado')
          AND status LIKE :status AND nome_cliente LIKE :busca
          $sqlFiltroBalcao $sqlFiltroTipo ORDER BY hora ASC,id ASC LIMIT 0,$itens_paginacao");
    $queryPedidos->execute($parametrosPedidos);
    $res=$queryPedidos->fetchAll(PDO::FETCH_ASSOC);
    $total_reg=count($res);
}


if ($total_reg > 0) {

	echo <<<HTML
	<small>
	<div class="ux-orders-scroll-region">
	<table class="table table-bordered text-nowrap border-bottom dt-responsive ux-orders-table" id="">
	<thead> 
	<tr> 
	<th>Cliente</th>	
	<th>Valor</th> 	
	<th>Total Pago</th> 
	<th>Troco</th>	
	<th>Pagamento</th> 	
	<th>Status</th>	 	
	<th>Hora</th>		
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

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
			if (@$status_api == 'approved') {
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
		//$horaF = date("H:i", strtotime($hora));	

		$dataHoraPedido=strtotime($data.' '.$hora);
        $prazoPedido=$dataHoraPedido!==false
            ? strtotime('+'.max(0,(int)$previsao_entrega).' minutes',$dataHoraPedido)
            : false;
        $hora_pedido=$prazoPedido!==false ? date('H:i',$prazoPedido) : '';
        if ($prazoPedido!==false && $prazoPedido<time()) {
			$classe_pago2 = '#ffdddd';
		} else {
			$classe_pago2 = '';
		}

		$consultaUsuario=$pdo->prepare("SELECT nome FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1");
        $consultaUsuario->execute([':id'=>(int)$usuario_baixa,':empresa'=>(int)$id_empresa]);
        $query2=$consultaUsuario;
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$nome_usuario_pgto = $res2[0]['nome'];
		} else {
			$nome_usuario_pgto = 'Nenhum!';
		}


		$consultaCliente=$pdo->prepare("SELECT nome,telefone FROM clientes WHERE id=:id AND empresa=:empresa LIMIT 1");
        $consultaCliente->execute([':id'=>(int)$cliente,':empresa'=>(int)$id_empresa]);
        $query2=$consultaCliente;
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





		if ($status == 'Iniciado') {
			$classe_alerta = '#eb0000';
			$total_vendas += $valor;
			$titulo_link = 'Mudar para Aceito!';
			$cor_icone_link = '#eb0000';
			$acao_link = 'Aceito';
		} else if ($status == 'Aceito') {
			$classe_alerta = '#0097ff';
			$total_vendas += 0;
			$titulo_link = 'Mudar para Preparando!';
			$cor_icone_link = '#0097ff';
			$acao_link = 'Preparando';
		} else if ($status == 'Preparando') {
			$classe_alerta = '#ff8400';
			$total_vendas += 0;
			$titulo_link = 'Mudar para Entrega!';
			$cor_icone_link = '#ff8400';
			$acao_link = 'Entrega';
		} else {
			$classe_alerta = '#00dc49';
			$total_vendas += $valor;
			$titulo_link = 'Mudar para Finalizado!';
			$cor_icone_link = '#00dc49';
			$acao_link = 'Finalizado';
		}

		if ($pago == 'Sim') {
			$classe_excluir = 'ocultar';
			$visivel = 'ocultar';
			$classe_pago = 'text-verde';
			$texto_pago = '(Pago)';
		} else {
			$classe_excluir = '';
			$visivel = '';
			$classe_pago = '';
			$texto_pago = '';
		}

		if ($obs != "") {
			$classe_info = 'primary';
		} else {
			$classe_info = 'info';
		}

		if ($entrega == 'Delivery') {
			$classe_entrega = 'text-danger';
		} else if ($entrega == 'Retirar') {
			$classe_entrega = 'text-azul';
		} else {
			$classe_entrega = 'text-success';
		}


		//validando impressao automatica
		if ($status == 'Iniciado' and $impressao_automatica == 'Site' and $impressao != "Sim") {
			$ids .= $id . '-';
			//coloco a impressao = sim
			$marcaImpressao=$pdo->prepare("UPDATE vendas SET impressao='Sim'
                WHERE id=:id AND empresa=:empresa AND (impressao IS NULL OR impressao<>'Sim')");
            $marcaImpressao->execute([':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
		}


		if($pago == 'Não'){
			$total_pagoF = 'Pendente';
		}


		if ($api_whatsapp != 'Não') {
			$ocultar_modal_whats = '';
			$ocultar_icone_whats = 'ocultar';
		}else{
			$ocultar_modal_whats = 'ocultar';
			$ocultar_icone_whats = '';
		}


		// Total de páginas já calculado fora do loop, sem consultas por pedido.

		// Escapa textos exibidos no HTML da tabela.
        $nome_cliente=htmlspecialchars((string)$nome_cliente,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $tipo_pgto=htmlspecialchars((string)$tipo_pgto,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $entrega=htmlspecialchars((string)$entrega,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $pedido=htmlspecialchars((string)$pedido,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
        $span_balcao = '';
		if($tipo_pedido == 'Balcão'){
			$span_balcao = '<span style="font-size:10px; color:gray">(Balcão)</span>';
		}


		echo <<<HTML
<tr style="background: {$classe_pago2}">
<td class="ux-order-customer-nowrap"><span style="color:{$classe_alerta}"><i class="fa fa-square"></i></span> <b>Pedido ({$pedido})</b> / {$nome_cliente} <span class="{$classe_entrega}"><small>({$entrega}) {$span_balcao} </small></span>




		<a style="display:inline-block; margin-left:10px" href="#" onclick="darDesconto('{$id}')" title="Dar Desconto"><i class="fa fa-usd text-success"></i></a>

		<form method="POST" action="../../site/{$url_site}" target="_blank" style="display:inline-block;">
					<input type="hidden" name="id_edicao" value="{$id}">
					<button style="outline:none; background:transparent; border:none; " type="submit" title="Editar Pedido"><i class="fa fa-edit text-info"></i>
				</form>


</td>
<td>R$ {$valorF}</td>
<td class="{$classe_pago}">R$ {$total_pagoF} <small>{$texto_pago}</small></td>
<td>R$ {$trocoF}</td>
<td>{$tipo_pgto}</td>
<td>
<span id="area_status_{$id}">
<a style="color:#FFF; background:{$cor_icone_link}; padding:8px; width:100% !important; text-align: center; font-size: 12px; border-radius: 6px;" title="{$titulo_link}" href="#" onclick="ativarPedido('{$id}','{$acao_link}','{$telefone_cliente}','{$valorF}','{$tipo_pgto}','{$hora_pedido}','{$entrega}')">
{$status}
<i class="fa fa-arrow-right "></i>
</a>
</span>
<img id="status_link_{$id}" src="../../img/loading.gif" width="80px" style="display:none">

</td>
<td>{$hora}</td>
<td>	

		<a class="btn btn-{$classe_info}-light btn-sm" href="#" onclick="mostrar('{$id}', '{$nome_cliente}', '{$pedido}')" title="Ver Dados"><i class="fa fa-info-circle "></i></a>



		<big><a href="#" class="btn btn-danger-light btn-sm {$classe_excluir}" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>



<a id="btn_baixar_{$id}" class="btn btn-success-light btn-sm {$classe_excluir}" href="#" onclick="baixar('{$id}', '{$nome_cliente}', '{$tipo_pgto}')" title="Confirmar Pgto">
<i class="fa fa-check-square"></i></a>



<a href="#" class="btn btn-success-light btn-sm  {$ocultar_modal_whats}" onclick="modalWhats('{$cliente}','Cliente')" title="Enviar Mensagem para o Cliente"><i class="fa-brands fa-whatsapp"></i></a>

<a class="btn btn-success-light btn-sm {$ocultar_icone_whats}" target="_blank" href="http://api.whatsapp.com/send?1=pt_BR&phone={$telefone_clienteF}" title="Whatsapp Cliente">
	<i class="fa-brands fa-whatsapp"></i></a>



			<a class="btn btn-dark-light btn-sm" href="#" onclick="gerarComprovante('{$id}', '{$tipo_pedido}')" title="Gerar Comprovante"><i class="fa fa-file-pdf-o"></i></a>		
			
		
	
		</td>
</tr>
HTML;
	}

	$total_vendasF = number_format($total_vendas, 2, ',', '.');

	echo <<<HTML
</tbody>
<small><div align="center" id="mensagem-excluir"></div></small>
</table>
</div>

<br>	
<div align="right">Total de Pedidos: <span class=""> {$num_total}</span> </div>

</small>


<div class="" align="center">
<a href="#" onclick="listar('{$busca}', 0)" class="btn btn-outline-primary "><i class="fa fa-chevron-left"></i></a>
HTML;

for($i = 0; $i < @$num_paginas; $i++){
	$estilo = '';
	if($pagina == $i){
		$estilo = 'active';
	}
	$pagina_cont = $i + 1;
	$ultima_pagina = $num_paginas - 1;

	if($pagina >= ($i - 2) && $pagina <= ($i + 2)){ 
echo <<<HTML
		<a style="width:35px" href="#" onclick="listar('{$busca}', '{$i}')" class="btn btn btn-outline-primary {$estilo}">{$pagina_cont}</a>   
HTML;                  

}

}
echo <<<HTML
<a href="#" onclick="listar('{$busca}', '{$ultima_pagina}')" class="btn btn-outline-primary "><i class="fa fa-chevron-right"></i></a>
</div>

HTML;
} else {
	$pagina = 0;
	echo '<small>Não possui nenhum Pedido Hoje ainda!</small>';
}







$totalHojeSt=$pdo->prepare("SELECT COUNT(*) FROM vendas WHERE empresa=:empresa
    AND data=CURDATE() AND status='Iniciado' $condicaoBalcao");
$totalHojeSt->execute([':empresa'=>(int)$id_empresa]);
$total_dos_itens_pedidos=(int)$totalHojeSt->fetchColumn();


?>

<script type="text/javascript">
	$(document).ready(function() {

		var pagina = "<?= $pagina ?>";
		$('#pagina').val(pagina);

		var ids = "<?= $ids ?>";
		var id_imp = ids.split("-");
		//alert(ids)

		for (i = 0; i < id_imp.length - 1; i++) {
			var id_pedido = id_imp[i];

			let a = document.createElement('a');
			a.target = '_blank';
			a.href = 'rel/comprovante.php?id=' + id_pedido;
			a.click();
		}


		$('#todos_pedidos').text("<?= $total_pedidos ?>");
		$('#ini_pedidos').text("<?= $total_ini ?>");
		$('#prep_pedidos').text("<?= $total_prep ?>");
		$('#ent_pedidos').text("<?= $total_ent ?>");
		$('#id_pedido').val("<?= $id_ult_pedido ?>");
		$('#ace_pedidos').text("<?= $total_ace ?>");

		$('#total-dos-pedidos').text("<?= $total_dos_itens_pedidos ?>");

		if ($.fn.DataTable.isDataTable('#tabela')) {
        $('#tabela').DataTable().destroy();
    }

		$('#tabela').DataTable({
			"ordering": false,
			"stateSave": true
		});
		$('#tabela_filter label input').focus();
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
				url: 'paginas/' + pag + "/mudar-status.php",
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


	function darDesconto(id){
		$('#id_desconto').val(id);
		$('#modalDesconto').modal('show');
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