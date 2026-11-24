<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'saidas';

$query = $pdo->query("SELECT * FROM $tabela where empresa = '$id_empresa' ORDER BY id desc");
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
html body #listar.bc-outbound-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-outbound-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-outbound-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-outbound-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-outbound-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-outbound-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-outbound-cards #tabela thead{display:none!important}
html body #listar.bc-outbound-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-outbound-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-outbound-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-outbound-cards #tabela tbody td:first-child{font-weight:700;color:#63351e;font-size:16px}
#listar.bc-outbound-cards #tabela tbody td:nth-child(2)::before{content:'Quantidade: '}
#listar.bc-outbound-cards #tabela tbody td:nth-child(3)::before{content:'Motivo: '}
#listar.bc-outbound-cards #tabela tbody td:nth-child(4)::before{content:'Usuário: '}
#listar.bc-outbound-cards #tabela tbody td:nth-child(5)::before{content:'Data: '}
#listar.bc-outbound-cards #tabela tbody td:nth-child(2){font-weight:700;color:#b42332}
html body #listar.bc-outbound-cards #tabela tbody td:first-child img{display:block;width:72px!important;height:72px;object-fit:contain;margin:0 auto 10px!important}
html body #listar.bc-outbound-cards #tabela tbody td:first-child{text-align:center}
html body #listar.bc-outbound-cards #tabela tbody td.esc{display:block!important}
#listar.bc-outbound-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-outbound-cards #tabela tbody tr:hover,html body #listar.bc-outbound-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-outbound-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-outbound-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-outbound-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-outbound-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-outbound-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-outbound-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
</style>
<div id="bc-outbound-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>

BCVIEW;
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr> 
	<th>Produto</th>	
	<th class="">Quantidade</th> 	
	<th class="esc">Motivo</th> 	
	<th class="esc">Usuário Lançou</th> 
	<th class="esc">Data</th>	
	
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];
	$produto = $res[$i]['produto'];	
	$quantidade = $res[$i]['quantidade'];
	$motivo = $res[$i]['motivo'];
	$usuario = $res[$i]['usuario'];
	$data = $res[$i]['data'];
			

		$query2 = $pdo->query("SELECT * FROM produtos where id = '$produto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_produto = $res2[0]['nome'];
			$foto_produto = $res2[0]['foto'];
		}else{
			$nome_produto = 'Sem Referência!';
			$foto_produto = 'sem-foto.jpg';
		}


		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_usuario = $res2[0]['nome'];
			
		}else{
			$nome_usuario = 'Sem Referência!';
			
		}


		$dataF = implode('/', array_reverse(explode('-', $data)));
		


echo <<<HTML
<tr class="">
<td>
<img src="images/produtos/{$foto_produto}" width="27px" class="mr-2">
{$nome_produto}
</td>
<td class="">{$quantidade}</td>
<td class="esc">{$motivo}</td>
<td class="esc"> {$nome_usuario}</td>
<td class="esc">{$dataF}</td>

</tr>
HTML;

}

echo <<<HTML
</tbody>
</table>
<small><div align="center" id="mensagem-excluir"></div></small>
</small>
HTML;


}else{
	echo '<small>Não possui nenhum registro Cadastrado!</small>';
}

?>

<script type="text/javascript">
	$(document).ready( function () {
var root=document.getElementById('listar'),bar=document.getElementById('bc-outbound-list-view');
        if(root&&bar){
          function apply(mode){
            root.dataset.outboundView=mode;
            root.classList.toggle('bc-outbound-cards',mode==='cards');
            $('#tabela').toggleClass('table-hover',mode==='table');
            $(bar).find('button').each(function(){this.setAttribute('aria-pressed',String(this.dataset.view===mode));});

          }
          $(bar).off('click.bcoutbound').on('click.bcoutbound','button[data-view]',function(){apply(this.dataset.view);if($.fn.dataTable&&$.fn.dataTable.isDataTable('#tabela'))$('#tabela').DataTable().columns.adjust();});
          apply(root.dataset.outboundView||'cards');
        }

		

if (!$('#tabela').length) return;
    $('#tabela').DataTable({
    		"ordering": false,
            "responsive": false,
			"stateSave": true
    	});
    $('#tabela_filter label input').focus();
} );
</script>

