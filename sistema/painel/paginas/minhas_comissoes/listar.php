<?php 
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'pagar';
$data_hoje = date('Y-m-d');


$id_usuario = $_SESSION['id'];

$dataInicial = @$_POST['dataInicial'];
$dataFinal = @$_POST['dataFinal'];
$status = '%'.@$_POST['status'].'%';
$funcionario = $id_usuario;

if($dataInicial == ""){
	$dataInicial = $data_hoje;
}

if($dataFinal == ""){
	$dataFinal = $data_hoje;
}

$query2 = $pdo->query("SELECT * FROM usuarios where id = '$funcionario'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_func2 = $res2[0]['nome'];
		}else{
			$nome_func2 = 'Sem Referência!';
		}

$total_pago = 0;
$total_a_pagar = 0;
$total_pendente = 0;

$query = $pdo->query("SELECT * FROM $tabela where data_lanc >= '$dataInicial' and data_lanc <= '$dataFinal' and pago LIKE '$status' and funcionario = '$funcionario' and empresa = '$id_empresa' and referencia = 'Comissão' ORDER BY pago asc, vencimento asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
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
html body #listar.bc-mycommission-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-mycommission-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-mycommission-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-mycommission-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-mycommission-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-mycommission-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-mycommission-cards #tabela thead{display:none!important}
html body #listar.bc-mycommission-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-mycommission-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-mycommission-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-mycommission-cards #tabela tbody td:nth-child(1){font-weight:700;color:#63351e}
#listar.bc-mycommission-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-mycommission-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-mycommission-cards #tabela tbody tr:hover,html body #listar.bc-mycommission-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-mycommission-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-mycommission-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-mycommission-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-mycommission-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-mycommission-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-mycommission-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
html body #listar.bc-mycommission-cards #tabela tbody td:nth-child(2){font-size:20px;color:#63351e;font-weight:600}
html body #listar.bc-mycommission-cards #tabela tbody td:last-child::before{content:none!important}
html body #listar #tabela tbody td:last-child a.btn-primary-light{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela thead th{background:#f4e9df!important;color:#63351e!important}
html body #listar #tabela .bc-no-attachment{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid #e5d4c6;border-radius:8px;background:#f8efe6;color:#795038;font-size:12px}
html body #listar #tabela tbody td:last-child a.btn i{color:inherit!important}
html body #listar #tabela tbody td:last-child a.btn-primary-light{--bc-action:#17658a;--bc-bg:#e1f2fa}
#listar.bc-mycommission-cards #tabela tbody td:nth-child(2)::before{content:'Valor: '}
#listar.bc-mycommission-cards #tabela tbody td:nth-child(3)::before{content:'Data do serviço: '}
#listar.bc-mycommission-cards #tabela tbody td:nth-child(4)::before{content:'Vencimento: '}
#listar.bc-mycommission-cards #tabela tbody td:nth-child(5)::before{content:'Pagamento: '}
html body #listar #tabela tbody td:last-child a.btn-primary{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child a.btn-danger{--bc-action:#b42332;--bc-bg:#fde5e7}
html body #listar #tabela tbody td:last-child a.btn-success{--bc-action:#13745a;--bc-bg:#def3e9}
html body #listar.bc-mycommission-cards #tabela tbody tr,html body #listar.bc-mycommission-cards #tabela tbody td{overflow:visible!important}
html body #listar #tabela tbody td:last-child a.btn-info{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar.bc-mycommission-cards #tabela tbody tr.vermelho-escuro{border-top:4px solid #b42332!important}
.bc-mycommission-totals{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-top:16px}
.bc-mycommission-totals>div{background:#fff;border:1px solid #e5d4c6;border-radius:12px;padding:16px;color:#63351e}
.bc-mycommission-totals strong{display:block;font-size:20px;margin-top:5px}
</style>
<div id="bc-mycommission-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-mycommission-list-view');
 if(!root||!bar)return;
 
 function apply(mode){root.dataset.myCommissionView=mode;root.classList.toggle('bc-mycommission-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.myCommissionView||'cards');
})();
</script>
BCVIEW;
if($total_reg > 0){

echo <<<HTML
		<table class="table table-bordered"  id="tabela">
	<thead> 
	<tr> 
	<th>Descrição</th>
	<th>Valor</th> 	
	<th>Data Serviço</th>		
	<th>Vencimento</th>	
	<th>Pagamento</th>
	
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];	
	$descricao = $res[$i]['descricao'];
	$tipo = $res[$i]['referencia'];
	$valor = $res[$i]['valor'];
	$data_lanc = $res[$i]['data_lanc'];
	$data_pgto = $res[$i]['data_pgto'];
	$data_venc = $res[$i]['vencimento'];
	$usuario_lanc = $res[$i]['usuario_lanc'];
	$usuario_baixa = $res[$i]['usuario_pgto'];
	$foto = $res[$i]['arquivo'];	
	$funcionario = $res[$i]['funcionario'];	
	
	$pago = $res[$i]['pago'];
	
	
	$valorF = number_format($valor, 2, ',', '.');
	$data_lancF = implode('/', array_reverse(@explode('-', $data_lanc)));
	$data_pgtoF = implode('/', array_reverse(@explode('-', $data_pgto)));
	$data_vencF = implode('/', array_reverse(@explode('-', $data_venc)));
	

		


		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_baixa'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_usuario_pgto = $res2[0]['nome'];
		}else{
			$nome_usuario_pgto = 'Nenhum!';
		}




		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_lanc'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_usuario_lanc = $res2[0]['nome'];
		}else{
			$nome_usuario_lanc = 'Sem Referência!';
		}



		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$funcionario'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_func = $res2[0]['nome'];
			$tel_func = $res2[0]['telefone'];
			$chave_pix_func = $res2[0]['pix'];			
		}else{
			$nome_func = 'Sem Referência!';
			$chave_pix_func = '';
			$tel_func = '';			

		}		



		if($pago == 'Não'){
			$classe_alerta = 'text-danger';
			$data_pgtoF = 'Pendente';
			$visivel = '';
			$total_a_pagar += $valor;
			$total_pendente += 1;
		}else{
			$classe_alerta = 'verde';
			$visivel = 'ocultar';
			$total_pago += $valor;
		}


			//extensão do arquivo
$ext = pathinfo($foto, PATHINFO_EXTENSION);
if($ext == 'pdf'){
	$tumb_arquivo = 'pdf.png';
}else if($ext == 'rar' || $ext == 'zip'){
	$tumb_arquivo = 'rar.png';
}else{
	$tumb_arquivo = $foto;
}
		

if($data_venc < $data_hoje and $pago != 'Sim'){
	$classe_debito = 'vermelho-escuro';
}else{
	$classe_debito = '';
}
		


echo <<<HTML
<tr class="{$classe_debito}">
<td><i class="fa fa-square {$classe_alerta}"></i> {$descricao}</td>
<td>R$ {$valorF}</td>
<td>{$data_lancF}</td>
<td>{$data_vencF}</td>
<td>{$data_pgtoF}</td>

<td>
		

		<a class="btn btn-info btn-sm" href="#" onclick="mostrar('{$descricao}', '{$valorF}', '{$data_lancF}', '{$data_vencF}',  '{$data_pgtoF}', '{$nome_usuario_lanc}', '{$nome_usuario_pgto}', '{$nome_func}', '{$tel_func}', '{$chave_pix_func}')" title="Ver Dados"><i class="fa fa-info-circle"></i></a>



		<div class="dropdown" style="display: inline-block;">                      
		<a class="btn btn-danger btn-sm" href="#" aria-expanded="false" aria-haspopup="true" data-bs-toggle="dropdown"><i class="fa fa-trash"></i> </a>
		<div  class="dropdown-menu tx-13">
		<div style="width: 240px; padding:15px 5px 0 10px;" class="dropdown-item-text">
		<p>Confirmar Exclusão? <a href="#" onclick="excluir('{$id}')"><span class="text-danger"><button class="btn-danger">Sim</button></span></a></p>
		</div>
		</div>
		</div>


		


		
	
		</td>
</tr>
HTML;

}

$total_pagoF = number_format($total_pago, 2, ',', '.');
$total_a_pagarF = number_format($total_a_pagar, 2, ',', '.');

echo <<<HTML
</tbody>
</table>
<div align="center" id="mensagem-excluir"></div>

<br>	
<div class="bc-mycommission-totals"><div>Total recebido<strong class="text-success">R$ {$total_pagoF}</strong></div><div>Total a receber<strong class="text-danger">R$ {$total_a_pagarF}</strong></div></div>
HTML;


}else{
	echo 'Nenhuma comissão encontrada no período.';
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
	function mostrar(descricao, valor, data_lanc, data_venc, data_pgto, usuario_lanc, usuario_pgto, func, tel, tipo_chave){

		$('#nome_dados').text(descricao);
		$('#valor_dados').text(valor);
		$('#data_lanc_dados').text(data_lanc);
		$('#data_venc_dados').text(data_venc);
		$('#data_pgto_dados').text(data_pgto);
		$('#usuario_lanc_dados').text(usuario_lanc);
		$('#usuario_baixa_dados').text(usuario_pgto);
		$('#nome_func_dados').text(func);
		$('#telefone_dados').text(tel);		
		$('#chave_pix_dados').text(tipo_chave);	
		

		$('#modalDados').modal('show');
	}
</script>




