<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
$tabela = 'adicionais';
require_once("../../../conexao.php");
require_once("../../buscar_config.php");

$query = $pdo->query("SELECT * FROM $tabela where empresa = '$id_empresa' order by id desc");
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
html body #listar.bc-extra-cards #tabela tbody tr td:first-child{display:none!important}
html body #listar.bc-extra-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-extra-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-extra-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-extra-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-extra-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-extra-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-extra-cards #tabela thead{display:none!important}
html body #listar.bc-extra-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-extra-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-extra-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-extra-cards #tabela tbody td:nth-child(2){font-weight:700;color:#63351e}
#listar.bc-extra-cards #tabela tbody td:nth-child(3)::before{content:'Valor: R$ '}
#listar.bc-extra-cards #tabela tbody td:nth-child(4)::before{content:'Ativo: '}
#listar.bc-extra-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-extra-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-extra-cards #tabela tbody tr:hover,html body #listar.bc-extra-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-extra-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-extra-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-extra-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-extra-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-extra-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-extra-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
</style>
<div id="bc-extra-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>

BCVIEW;
if ($total_reg > 0) {

	echo <<<HTML
	<small>
	<table class="table table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr> 
		<th align="center" width="5%" class="text-center">Selecionar</th>
	<th>Nome</th>	
	<th>Valor</th>		
	<th>Ativo</th>		
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
		$valor = $res[$i]['valor'];
		$valorF = number_format($valor, 2, ',', '.');
		$ativo = $res[$i]['ativo'];

		if ($ativo == 'Sim') {
			$icone = 'fa-check-square';
			$titulo_link = 'Desativar Item';
			$acao = 'Não';
			$classe_linha = '';
		} else {
			$icone = 'fa-square-o';
			$titulo_link = 'Ativar Item';
			$acao = 'Sim';
			$classe_linha = '#c4c4c4';
		}

		echo <<<HTML
<tr>
	<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$id}" onchange="selecionar('{$id}')">
<label for="seletor-{$id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>

<td style="color:{$classe_linha}">{$nome}</td>
<td style="color:{$classe_linha}">{$valorF}</td>
<td style="color:{$classe_linha}">{$ativo}</td>
<td>
	<a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}','{$nome}','{$valor}')" title="Editar Dados"><i class="fa fa-edit"></i></a>

	

<big><a href="#" class="btn btn-danger-light btn-sm" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>


			<a class="btn btn-success-light btn-sm" href="#" onclick="ativar('{$id}', '{$acao}')" title="{$titulo_link}"><i class="fa {$icone}"></i></a>


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
	echo 'Não possui registros cadastrados!';
}


?>


<script type="text/javascript">
	$(document).ready(function() {
        var root=document.getElementById('listar'),bar=document.getElementById('bc-extra-list-view');
        if(root&&bar){
          function apply(mode){
            root.dataset.extraView=mode;
            root.classList.toggle('bc-extra-cards',mode==='cards');
            $('#tabela').toggleClass('table-hover',mode==='table');
            $(bar).find('button').each(function(){this.setAttribute('aria-pressed',String(this.dataset.view===mode));});
            if(mode==='cards'){$(root).find('input[type=checkbox]').prop('checked',false);$('#ids').val('');$('#btn-deletar').hide();}
          }
          $(bar).off('click.bcExtra').on('click.bcExtra','button[data-view]',function(){apply(this.dataset.view);if($.fn.dataTable&&$.fn.dataTable.isDataTable('#tabela'))$('#tabela').DataTable().columns.adjust();});
          apply(root.dataset.extraView||'cards');
        }

		if (!$('#tabela').length) return;
		$('#tabela').DataTable({
			"ordering": false,
            "responsive": false,
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

