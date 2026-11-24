<?php
@session_start();
$id_empresa = @$_SESSION['empresa'];
$tabela = 'chamados';
$id_usuario = @$_SESSION['id'];

require_once("../../../conexao.php");
require_once("../../verificar.php");
require_once("../../buscar_config.php");

$query = $pdo->query("SELECT * from $tabela where empresa = '$id_empresa' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
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
html body #listar.bc-ticket-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-ticket-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-ticket-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-ticket-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-ticket-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-ticket-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-ticket-cards #tabela thead{display:none!important}
html body #listar.bc-ticket-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-ticket-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-ticket-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-ticket-cards #tabela tbody td:nth-child(1){font-weight:700;color:#63351e}
#listar.bc-ticket-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-ticket-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-ticket-cards #tabela tbody tr:hover,html body #listar.bc-ticket-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-ticket-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-ticket-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-ticket-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-ticket-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-ticket-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-ticket-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
html body #listar.bc-ticket-cards #tabela tbody td:nth-child(3){font-size:18px;color:#63351e;font-weight:600}
html body #listar.bc-ticket-cards #tabela tbody td:last-child::before{content:none!important}
html body #listar #tabela tbody td:last-child a.btn-primary-light{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela thead th{background:#f4e9df!important;color:#63351e!important}
html body #listar #tabela .bc-no-attachment{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid #e5d4c6;border-radius:8px;background:#f8efe6;color:#795038;font-size:12px}
html body #listar #tabela tbody td:last-child a.btn i{color:inherit!important}
html body #listar #tabela tbody td:last-child a.btn-primary-light{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child a.btn-primary{--bc-action:#17658a;--bc-bg:#e1f2fa}
html body #listar #tabela tbody td:last-child a.btn-danger{--bc-action:#b42332;--bc-bg:#fde5e7}
html body #listar #tabela tbody td:last-child a.btn-success{--bc-action:#13745a;--bc-bg:#def3e9}
html body #listar.bc-ticket-cards #tabela tbody tr,html body #listar.bc-ticket-cards #tabela tbody td{overflow:visible!important}
#listar.bc-ticket-cards #tabela tbody td:nth-child(1)::before{content:'Chamado #'}
#listar.bc-ticket-cards #tabela tbody td:nth-child(2)::before{content:'Data: '}
#listar.bc-ticket-cards #tabela tbody td:nth-child(3){order:-1}
#listar.bc-ticket-cards #tabela tbody td:nth-child(4)::before{content:'Status: '}
#listar.bc-ticket-cards #tabela tbody td:nth-child(5)::before{content:'Respondido: '}
html body #listar.bc-ticket-cards #tabela tbody td .badge{width:auto!important;padding:6px 10px}
html body #listar #tabela tbody td:last-child a.btn-info:not(.btn-info-light){--bc-action:#795038;--bc-bg:#fff0df}
</style>
<div id="bc-ticket-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-ticket-list-view');
 if(!root||!bar)return;
 
 function apply(mode){root.dataset.ticketView=mode;root.classList.toggle('bc-ticket-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.ticketView||'cards');
})();
</script>
BCVIEW;
if ($linhas > 0) {
	echo <<<HTML
	<table class="table table-bordered text-nowrap border-bottom" id="tabela">
	<thead> 
	<tr>
	<th>ID</th>
	<th>Data</th>		
	<th>Título</th>
	<th>Status</th>
	<th>Respondido</th>
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

	for ($i = 0; $i < $linhas; $i++) {
		$id = $res[$i]['id'];
		$titulo = $res[$i]['titulo'];
		$texto = $res[$i]['texto'];		
		$data = $res[$i]['data'];
		$status = $res[$i]['status'];
		$respondido = $res[$i]['respondido'];
		
		
		$dataF = implode('/', array_reverse(@explode('-', $data)));

		$textoF = rawurlencode($texto);

		if($status == 'Aberto'){
			$cor_status = 'bg-danger-transparent text-danger';
		}else{
			$cor_status = 'bg-success-transparent text-success';
		}


		if($respondido == 'Não'){
			$cor_respondido = 'bg-danger-transparent text-danger';
		}else{
			$cor_respondido = 'bg-success-transparent text-success';
		}




echo <<<HTML

<tr class="">
<td>{$id}</td>
<td>{$dataF}</td>
<td>{$titulo}</td>
<td><span class="badge font-weight-semibold {$cor_status} tx-12" style="width:90%;">{$status}</span></td>
<td><span class="badge font-weight-semibold {$cor_respondido} tx-12" style="width:90%;">{$respondido}</span></td>

<td>
	<a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}','{$titulo}','{$textoF}')" title="Editar Dados"><i class="fa fa-edit"></i></a>

	
	<big><a href="#" class="btn btn-danger-light btn-sm" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can"></i></a></big>


	<a class="btn btn-info btn-sm" href="#" onclick="respostas('{$id}','{$titulo}')" title="Respostas Chamado"><i class="fa-regular fa-comment"></i></a>


	<a class="btn btn-dark-light btn-sm" href="#" onclick="arquivo('{$id}', '{$titulo}')" title="Inserir / Ver Arquivos"><i class="fa fa-file-o taxt-secondary"></i></a>


</td>
</tr>
HTML;
	}
echo <<<HTML
</tbody>
</table>
<div align="center" id="mensagem-excluir"></div>
HTML;
} else {
	echo '<p>Nenhum chamado encontrado.</p>';
}
?>



<script type="text/javascript">
	$(document).ready(function () {
		if ($('#tabela').length) $('#tabela').DataTable({
			"language": {
				//"url" : '//cdn.datatables.net/plug-ins/1.13.2/i18n/pt-BR.json'
			},
			"ordering": false,
 "responsive": false,
			"stateSave": true
		});
	});
</script>

<script type="text/javascript">
	function editar(id, titulo, texto) {
		

		
		// Atualiza os campos do formulário
		$('#titulo_inserir').text('Editar Registro');
		$('#id').val(id);
		$('#titulo').val(titulo);
		
		// Atualiza o conteúdo do editor Quill
		quill.root.innerHTML = decodeURIComponent(texto); // Define o conteúdo no editor
		// Sincroniza o conteúdo com o textarea oculto
		document.getElementById('hiddenTextarea').value = texto;

		// Mostra a aba de edição
		$('#nav-tab button[data-bs-target="#nav-profile"]').tab('show');
	}



	


	function limparCampos() {
		$('#id').val('');
		$('#titulo').val('');		
		// Limpa o conteúdo do editor Quill
		quill.root.innerHTML = '';
		// Limpa os textareas ocultos
		document.getElementById('hiddenTextarea').value = ''; // Limpa o primeiro textarea oculto

		$('#ids').val('');
		$('#btn-deletar').hide();
	}
</script>

<script type="text/javascript">
	function respostas(id, titulo) {
		
		$('#id_resposta').val(id);
		$('#titulo_resposta').text(titulo);
		
		
		$('#texto_resposta').val('');

		listarRespostas()
		$('#modalResposta').modal('show');

		
	}

	function arquivo(id, nome) {
		$('#id-arquivo').val(id);
		$('#nome-arquivo').text(nome);
		$('#modalArquivos').modal('show');
		$('#mensagem-arquivo').text('');
		$('#arquivo_conta').val('');
		listarArquivos();
	}
</script>