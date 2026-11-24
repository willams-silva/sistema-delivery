<?php
@session_start();
$id_empresa = (int) ($_SESSION['empresa'] ?? 0);
$id_usuario = (int) ($_SESSION['id'] ?? 0);
$tabela = 'cargos';
require_once("../../../conexao.php");
require_once("../../buscar_config.php");


if ($id_empresa <= 0 || $id_usuario <= 0) { http_response_code(403); exit('Sessão inválida'); }
$operador = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$operador->execute([':id'=>$id_usuario, ':empresa'=>$id_empresa]);
$nivel = $operador->fetchColumn();
if ($nivel === false) { http_response_code(403); exit('Operador não autorizado'); }
if ($nivel !== 'Administrador') {
    $permissao = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up INNER JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = 'cargos' LIMIT 1");
    $permissao->execute([':usuario'=>$id_usuario, ':empresa'=>$id_empresa]);
    if (!$permissao->fetchColumn()) { http_response_code(403); exit('Sem permissão para consultar cargos'); }
}
$query = $pdo->prepare("SELECT c.id, c.nome, COUNT(u.id) AS total_funcionarios FROM cargos c LEFT JOIN usuarios u ON u.empresa = c.empresa AND u.nivel = c.nome WHERE c.empresa = :empresa GROUP BY c.id, c.nome ORDER BY c.id DESC");
$query->execute([':empresa'=>$id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = count($res);
echo <<<'BCVIEW'
<style>
html body #listar #tabela tbody td:last-child :is(a,button).btn{display:inline-flex!important;align-items:center;justify-content:center;width:38px;height:38px;padding:0!important;margin:3px;border:1px solid currentColor!important;border-radius:9px!important;color:var(--bc-action,#795038)!important;background:var(--bc-bg,#fff0df)!important;transition:background .18s,color .18s,transform .18s}
html body #listar #tabela tbody td:last-child :is(a,button).btn.ocultar{display:none!important}
html body #listar #tabela tbody td:last-child :is(a,button).btn.btn-info-light,html body #listar #tabela tbody td:last-child :is(a,button).btn.btn-info{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child :is(a,button).btn.btn-danger-light{--bc-action:#b42332;--bc-bg:#fde5e7}
html body #listar #tabela tbody td:last-child :is(a,button).btn.btn-success-light{--bc-action:#13745a;--bc-bg:#def3e9}
html body #listar #tabela tbody td:last-child :is(a,button).btn.btn-dark-light{--bc-action:#41434a;--bc-bg:#e9eaed}
html body #listar #tabela tbody td:last-child :is(a,button).btn i{color:inherit!important;font-size:16px}
html body #listar #tabela tbody td:last-child :is(a,button).btn:hover{background:var(--bc-action,#795038)!important;color:#fff!important;transform:translateY(-2px)}
html body #listar #tabela tbody td:last-child :is(a,button).btn:focus-visible{outline:2px solid #914a29;outline-offset:2px}
html body #listar.bc-role-cards #tabela tbody tr td:first-child{display:none!important}
html body #listar.bc-role-cards #tabela tbody td:last-child :is(a,button).btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-role-cards #tabela tbody td:last-child :is(a,button).btn{transition:none!important}
#listar #bc-role-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-role-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-role-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-role-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-role-cards #tabela thead{display:none!important}
html body #listar.bc-role-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-role-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-role-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-role-cards #tabela tbody td:nth-child(2){font-weight:700;color:#63351e}


#listar.bc-role-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-role-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-role-cards #tabela tbody tr:hover,html body #listar.bc-role-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-role-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-role-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-role-cards #tabela tbody tr td:last-child :is(a,button).btn:hover,html body #listar.bc-role-cards #tabela tbody tr td:last-child :is(a,button).btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-role-cards #tabela tbody tr td:last-child :is(a,button).btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-role-cards #tabela tbody tr td:last-child :is(a,button).btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
html body #listar #tabela tbody td:last-child button.btn{width:auto;min-width:38px;padding:0 10px!important;gap:6px}
html body #listar.bc-role-cards #tabela tbody td:last-child{display:flex!important;justify-content:center;align-items:center;flex-wrap:wrap}
</style>
<div id="bc-role-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-role-list-view');
 if(!root||!bar)return;
 
 function apply(mode){root.dataset.roleView=mode;root.classList.toggle('bc-role-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable&&jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.roleView||'cards');
})();
</script>
BCVIEW;
$cargos_em_uso = count(array_filter($res, static function ($cargo) { return (int) $cargo['total_funcionarios'] > 0; }));
echo '<script>$("#bc-total-cargos").text(' . $linhas . ');$("#bc-cargos-em-uso").text(' . $cargos_em_uso . ');</script>';
if ($linhas > 0) {
	echo <<<HTML
<small>
	<table class="table table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr> 
	<th align="center" width="5%" class="text-center">Selecionar</th>
	<th class="width80">Nome</th><th>Funcionários</th>		
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;


	for ($i = 0; $i < $linhas; $i++) {
		$id = $res[$i]['id'];
		$nome = $res[$i]['nome'];
        $total_funcionarios = (int) $res[$i]['total_funcionarios'];
        $nome_html = htmlspecialchars((string) $nome, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $nome_js = htmlspecialchars(json_encode((string) $nome, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');


		echo <<<HTML
<tr>
<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$id}" onchange="selecionar('{$id}')">
<label for="seletor-{$id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>
<td><strong>{$nome_html}</strong></td><td><span class="badge bg-light text-dark">{$total_funcionarios} funcionário(s)</span></td>

<td>
	<button type="button" class="btn btn-outline-secondary btn-sm" onclick="bcAbrirCargoPermissoes('{$id}',{$nome_js})" title="Configurar modelo de acessos"><i class="fa-solid fa-shield-halved"></i> Acessos</button>
	<big><a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}',{$nome_js})" title="Editar Dados"><i class="fa fa-edit "></i></a></big>

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
	echo '<div class="text-center py-5"><i class="fa-solid fa-id-badge fa-2x text-muted mb-3"></i><h5>Nenhum cargo cadastrado</h5><p class="text-muted">Clique em Novo Cargo para cadastrar a primeira função da equipe.</p></div>';
}

?>



<script type="text/javascript">
	$(document).ready(function() {
		if (!$('#tabela').length) return;
		$('#tabela').DataTable({
			"ordering": true,
            "responsive": false,
            "pageLength": 10,
            "language": {"search": "Buscar cargo:", "lengthMenu": "Mostrar _MENU_ cargos", "info": "Exibindo _START_ a _END_ de _TOTAL_ cargos", "infoEmpty": "Nenhum cargo", "zeroRecords": "Nenhum cargo encontrado", "paginate": {"previous": "Anterior", "next": "Próximo"}},
			"stateSave": true
		});
	});
</script>

<script type="text/javascript">
	function editar(id, nome) {
		$('#mensagem').text('');
		$('#titulo_inserir').text('Editar Registro');

		$('#id').val(id);
		$('#nome').val(nome);

		$('#modalForm').modal('show');
	}



	function limparCampos() {
		$('#id').val('');
		$('#nome').val('');


		$('#ids').val('');
		$('#btn-deletar').hide();
	}
</script>

