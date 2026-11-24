<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'vendas';
$data_hoje = date('Y-m-d');

$dataInicial = @$_POST['dataInicial'];
$dataFinal = @$_POST['dataFinal'];
$status = '%'.@$_POST['status'].'%';


$total_vendas = 0;

// Inclui vendas pendentes para permitir cancelamento seguro e conferência.
// Os filtros são parametrizados para evitar SQL injection.
$stListar = $pdo->prepare("SELECT * FROM vendas WHERE data >= :inicio AND data <= :fim AND status LIKE :status AND empresa = :empresa ORDER BY data ASC, hora DESC");
$stListar->execute([':inicio'=>$dataInicial, ':fim'=>$dataFinal, ':status'=>$status, ':empresa'=>(int)$id_empresa]);
$res = $stListar->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
echo <<<'BCVIEW'
<style>
html body #listar #tabela tbody td:last-child a.btn{display:inline-flex!important;align-items:center;justify-content:center;width:38px;height:38px;padding:0!important;margin:3px;border:1px solid currentColor!important;border-radius:9px!important;color:var(--bc-action,#795038)!important;background:var(--bc-bg,#fff0df)!important;transition:background .18s,color .18s,transform .18s}
html body #listar #tabela tbody td:last-child a.btn.ocultar{display:none!important}
html body #listar #tabela tbody td:last-child a.btn.btn-info-light,html body #listar #tabela tbody td:last-child a.btn.btn-info{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child a.btn.btn-danger-light{--bc-action:#b42332;--bc-bg:#fde5e7}
html body #listar #tabela tbody td:last-child a.btn.btn-success-light{--bc-action:#13745a;--bc-bg:#def3e9}
html body #listar #tabela tbody td:last-child a.btn.btn-dark-light{--bc-action:#41434a;--bc-bg:#e9eaed}
html body #listar #tabela tbody td:last-child a.btn i{color:inherit!important;font-size:16px}
html body #listar #tabela tbody td:last-child a.btn:hover{background:var(--bc-action,#795038)!important;color:#fff!important;transform:translateY(-2px)}
html body #listar #tabela tbody td:last-child a.btn:focus-visible{outline:2px solid #914a29;outline-offset:2px}
html body #listar.bc-sales-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-sales-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-sales-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-sales-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-sales-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-sales-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-sales-cards #tabela thead{display:none!important}
html body #listar.bc-sales-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-sales-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-sales-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-sales-cards #tabela tbody td:nth-child(1){font-weight:700;color:#63351e}
#listar.bc-sales-cards #tabela tbody td:nth-child(3)::before{content:'Total pago: '}
#listar.bc-sales-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-sales-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-sales-cards #tabela tbody tr:hover,html body #listar.bc-sales-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-sales-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-sales-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-sales-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-sales-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-sales-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-sales-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
html body #listar.bc-sales-cards #tabela tbody td:nth-child(2){font-size:20px;color:#63351e;font-weight:600}
html body #listar.bc-sales-cards #tabela tbody td:last-child::before{content:none!important}
#listar.bc-sales-cards #tabela tbody td:nth-child(2)::before{content:'Valor: '}
#listar.bc-sales-cards #tabela tbody td:nth-child(4)::before{content:'Troco: '}
#listar.bc-sales-cards #tabela tbody td:nth-child(5)::before{content:'Forma de pagamento: '}
#listar.bc-sales-cards #tabela tbody td:nth-child(6)::before{content:'Pago: '}
#listar.bc-sales-cards #tabela tbody td:nth-child(7)::before{content:'Data: '}
#listar.bc-sales-cards #tabela tbody td:nth-child(8)::before{content:'Hora: '}
html body #listar #tabela tbody td:last-child a.btn-primary-light{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela thead th{background:#f4e9df!important;color:#63351e!important}
</style>
<div id="bc-sales-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-sales-list-view');
 if(!root||!bar)return;
 
 function apply(mode){root.dataset.salesView=mode;root.classList.toggle('bc-sales-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.salesView||'cards');
})();
</script>
BCVIEW;
if($total_reg > 0){

echo <<<HTML
	<table class="table table-bordered text-nowrap border-bottom" id="tabela">
	<thead> 
	<tr> 
	<th>Cliente</th>
	<th>Valor</th> 
	<th>Total Pago</th>
	<th>Troco</th>
	<th>Forma PGTO</th>
	<th>Pago?</th>
	<th>Data</th>
	<th>Hora</th>
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
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
	$mesa = $res[$i]['mesa'];
	$nome_cliente_ped = $res[$i]['nome_cliente'];
	$pedido = $res[$i]['pedido'];
	
	$valorF = number_format($valor, 2, ',', '.');
	$total_pagoF = number_format($total_pago, 2, ',', '.');
	$trocoF = number_format($troco, 2, ',', '.');
	$taxa_entregaF = number_format($taxa_entrega, 2, ',', '.');
	$dataF = implode('/', array_reverse(explode('-', $data)));
	//$horaF = date("H:i", strtotime($hora));	

	

		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_baixa'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_usuario_pgto = $res2[0]['nome'];
		}else{
			$nome_usuario_pgto = 'Nenhum!';
		}


		$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_cliente = $res2[0]['nome'];
			$telefone_cliente = $res2[0]['telefone'];
		}else{
			if($mesa != '0' and $mesa != ''){
				$nome_cliente = 'Mesa: '.$mesa;
			}else{
				$nome_cliente = $nome_cliente_ped;
			}
			$telefone_cliente = '';
		}


		if($status == 'Finalizado'){
			$classe_alerta = 'text-verde';	
			if ($pago === 'Sim') $total_vendas += $valor;
			$classe_linha = '';
			$classe_ativo = '';
			$classe_ocultar = '';
		}else if($status == 'Cancelado'){
			$classe_alerta = 'text-danger';
			$total_vendas += 0;
			$classe_linha = 'text-muted';
			$classe_ativo = '#c4c4c4';
			$classe_ocultar = 'ocultar';
		}else{
			$classe_alerta = 'text-primary';
			if ($pago === 'Sim') $total_vendas += $valor;
			$classe_linha = '';
			$classe_ativo = '';
			$classe_ocultar = '';
		}


		if($pedido == 0){
			$pedido = '';
		}else{
			$pedido = 'Pedido ('.$pedido.') / ';
		}



echo <<<HTML
<tr class="{$classe_linha}">
<td style="color:{$classe_ativo}"><i class="fa fa-square {$classe_alerta}"></i> <b>{$pedido}</b> {$nome_cliente}</td>
<td style="color:{$classe_ativo}">R$ {$valorF}</td>
<td style="color:{$classe_ativo}">R$ {$total_pagoF}</td>
<td style="color:{$classe_ativo}">R$ {$trocoF}</td>
<td style="color:{$classe_ativo}">{$tipo_pgto}</td>
<td style="color:{$classe_ativo}">{$pago}</td>
<td style="color:{$classe_ativo}">{$dataF}</td>
<td style="color:{$classe_ativo}">{$hora}</td>
<td>	

		<a class="btn btn-primary-light btn-sm" href="#" onclick="mostrar('{$nome_cliente}', '{$valorF}', '{$total_pagoF}', '{$trocoF}',  '{$dataF}', '{$hora}', '{$status}', '{$pago}', '{$obs}', '{$taxa_entregaF}', '{$tipo_pgto}', '{$nome_usuario_pgto}')" title="Ver Dados"><i class="fa fa-info-circle"></i></a>


<big><a href="#" class="btn btn-danger-light btn-sm {$classe_ocultar}" onclick="solicitarCancelamentoVenda('{$id}', '{$pago}'); return false;" title="Analisar cancelamento / estorno"><i class="fa fa-ban text-danger"></i></a></big>

		<a class="btn btn-dark-light btn-sm" href="#" onclick="gerarComprovante('{$id}')" title="Gerar Comprovante"><i class="fa fa-file-pdf-o"></i></a>
	
		</td>
</tr>
HTML;

}

$total_vendasF = number_format($total_vendas, 2, ',', '.');

echo <<<HTML
</tbody>
</table>
<div align="center" id="mensagem-excluir"></div>

<br>	
<div align="right">Total de vendas recebidas (antes de estornos): <span class="text-verde">R$ {$total_vendasF}</span> </div>

HTML;


}else{
	echo '<small>Não possui nenhum registro Cadastrado!</small>';
}

?>

<script type="text/javascript">
	$(document).ready( function () {
    if ($('#tabela').length) $('#tabela').DataTable({
    		"ordering": false,
 "responsive": false,
			"stateSave": true
    	});
    $('#tabela_filter label input').focus();
} );
</script>


<script type="text/javascript">
	function mostrar(cliente, valor, total_pago, troco, data, hora, status, pago, obs, taxa_entrega, tipo_pgto, usuario_pgto){

		$('#nome_dados').text(cliente);
		$('#valor_dados').text(valor);
		$('#total_pago_dados').text(total_pago);
		$('#troco_dados').text(troco);
		$('#data_dados').text(data);
		$('#hora_dados').text(hora);
		$('#status_dados').text(status);
		$('#pago_dados').text(pago);
		$('#obs_dados').text(obs);
		$('#taxa_entrega_dados').text(taxa_entrega);
		$('#tipo_pgto_dados').text(tipo_pgto);
		$('#usuario_pgto_dados').text(usuario_pgto);		
	
		$('#modalDados').modal('show');
	}
</script>




<script type="text/javascript">
	function gerarComprovante(id){
		let a= document.createElement('a');
		                a.target= '_blank';
		                a.href= 'rel/comprovante.php?id='+ id + '&imp=Não';
		                a.click();
	}
</script>