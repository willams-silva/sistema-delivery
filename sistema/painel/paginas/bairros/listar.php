<?php
@session_start();
$id_empresa = (int) ($_SESSION['empresa'] ?? 0);
if ($id_empresa <= 0 || (int) ($_SESSION['id'] ?? 0) <= 0) { http_response_code(403); exit('Sessão inválida'); }
$tabela = 'bairros';
require_once("../../../conexao.php");
require_once("../../buscar_config.php");

$query = $pdo->prepare("SELECT * FROM bairros WHERE empresa = :empresa ORDER BY id DESC");
$query->execute([':empresa'=>$id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = count($res);
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
html body #listar.bc-neighborhood-cards #tabela tbody tr td:first-child{display:none!important}
html body #listar.bc-neighborhood-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-neighborhood-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-neighborhood-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-neighborhood-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-neighborhood-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-neighborhood-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-neighborhood-cards #tabela thead{display:none!important}
html body #listar.bc-neighborhood-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-neighborhood-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-neighborhood-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-neighborhood-cards #tabela tbody td:nth-child(2){font-weight:700;color:#63351e}


#listar.bc-neighborhood-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-neighborhood-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-neighborhood-cards #tabela tbody tr:hover,html body #listar.bc-neighborhood-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-neighborhood-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-neighborhood-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-neighborhood-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-neighborhood-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-neighborhood-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-neighborhood-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
#listar.bc-neighborhood-cards #tabela tbody td:nth-child(3)::before{content:'Taxa de entrega: ';font-weight:600}
</style>
<div id="bc-neighborhood-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-neighborhood-list-view');
 if(!root||!bar)return;
 
 function apply(mode){root.dataset.neighborhoodView=mode;root.classList.toggle('bc-neighborhood-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable&&jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.neighborhoodView||'cards');
})();
</script>
BCVIEW;
echo '<script>$("#bc-total-bairros").text(' . $total_reg . ');</script>';
if ($total_reg > 0) {

	echo <<<HTML
	<small>
	<table class="table table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr> 
<th align="center" width="5%" class="text-center">Selecionar</th>
	<th class="width70">Nome</th>	
	<th>Valor</th>		
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$id = $res[$i]['id'];
		$nome = $res[$i]['nome'];
        $nome_html = htmlspecialchars((string) $nome, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $nome_js = htmlspecialchars(json_encode((string) $nome, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$valor = $res[$i]['valor'];
		$valorF = number_format($valor, 2, ',', '.');

		echo <<<HTML
<tr>
	<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$id}" onchange="selecionar('{$id}')">
<label for="seletor-{$id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>
<td>{$nome_html}</td>
<td>R$ {$valorF}</td>
<td>
	<a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}',{$nome_js},'{$valor}')" title="Editar Dados"><i class="fa fa-edit"></i></a>

	

<big><a href="#" class="btn btn-danger-light btn-sm" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>




</td>
</tr>
HTML;
	}

	echo <<<HTML
	</tbody>
	</table>
	<small><div align="center" id="mensagem-excluir"></div></small>
	</small>
HTML;
} else {
	echo '<div class="text-center py-5"><i class="fa-solid fa-map-location-dot fa-2x text-muted mb-3"></i><h5>Nenhuma localidade cadastrada</h5><p class="text-muted">Adicione uma localidade para configurar a taxa de entrega.</p></div>';
}


?>


<script type="text/javascript">
	$(document).ready(function() {
		if (!$('#tabela').length) return;
		$('#tabela').DataTable({
			"ordering": true,
            "responsive": false,
            "pageLength": 10,
            "language": {"search": "Buscar:", "lengthMenu": "Mostrar _MENU_", "info": "Exibindo _START_ a _END_ de _TOTAL_", "infoEmpty": "Nenhum registro", "zeroRecords": "Nenhum resultado", "paginate": {"previous": "Anterior", "next": "Próximo"}},
			"stateSave": true
		});
		$('#tabela_filter label input').focus();
	});
</script>


<script type="text/javascript">
	function editar(id, nome, valor) {
		$('#id').val(id);
		$('#nome').val(nome);
		$('#valor').val(valor);



		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');

	}

	function limparCampos() {
		$('#id').val('');
		$('#nome').val('');
		$('#valor').val('');
	}
</script>
