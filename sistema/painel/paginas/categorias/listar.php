<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'categorias';

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
html body #listar.bc-category-cards #tabela tbody tr td:first-child{display:none!important}
html body #listar.bc-category-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-category-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-category-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-category-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-category-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-category-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-category-cards #tabela thead{display:none!important}
html body #listar.bc-category-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-category-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-category-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-category-cards #tabela tbody td:nth-child(2){font-weight:700;color:#63351e}
#listar.bc-category-cards #tabela tbody td:nth-child(3)::before{content:none}
#listar.bc-category-cards #tabela tbody td:nth-child(4)::before{content:none}
#listar.bc-category-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-category-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-category-cards #tabela tbody tr:hover,html body #listar.bc-category-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-category-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-category-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-category-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-category-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-category-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-category-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
#listar.bc-category-cards #tabela tbody td:nth-child(3){order:-3;text-align:center}
#listar.bc-category-cards #tabela tbody td:nth-child(3) img{width:80px;height:80px;object-fit:cover}
#listar.bc-category-cards #tabela tbody td:nth-child(4){order:-2;font-weight:700;font-size:17px;color:#63351e}
#listar.bc-category-cards #tabela tbody td:nth-child(2)::before{content:'Posição: '}
#listar.bc-category-cards #tabela tbody td:nth-child(6)::before{content:'Mais sabores: '}
#listar.bc-category-cards #tabela tbody td:nth-child(7)::before{content:'Delivery: '}
#listar.bc-category-cards #tabela tbody td:nth-child(8)::before{content:'Produtos: '}
html body #listar #tabela tbody td:last-child a.btn.btn-warning-light{--bc-action:#946000;--bc-bg:#fff2d0}
html body #listar #tabela tbody td:last-child a.btn.btn-primary-light{--bc-action:#795038;--bc-bg:#fff0df}
</style>
<div id="bc-category-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-category-list-view');
 if(!root||!bar)return;
 var old=document.getElementById('bc-category-view');if(old)old.style.display='none';
 function apply(mode){root.dataset.categoryView=mode;root.classList.toggle('bc-category-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.categoryView||'cards');
})();
</script>
BCVIEW;
if ($total_reg > 0) {

	echo <<<HTML
	<small>
	<table class="table table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr> 
		<th align="center" width="5%" class="text-center">Selecionar</th>
		<th>Posição</th>
		<th>Foto</th>
	<th>Nome</th>	
	<th>Descrição</th> 	
	<th>Mais Sabores</th> 	
	<th>Delivery</th> 
	<th>Produtos</th> 
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
		$ativo = $res[$i]['ativo'];
		$foto = $res[$i]['foto'];
		$descricao = $res[$i]['descricao'];
		$mais_sabores = $res[$i]['mais_sabores'];
		$delivery = $res[$i]['delivery'];
		$posicao = $res[$i]['posicao'];

		$ocultar_ad = 'ocultar';
		if ($mais_sabores == 'Sim') {
			$ocultar_ad = '';
		}

		$descricaoF = mb_strimwidth($descricao, 0, 50, "...");

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

		$query2 = $pdo->query("SELECT * from produtos where categoria = '$id'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$produtos = @count($res2);

		$nomeF = rawurlencode($nome);


		echo <<<HTML
<tr class="{$classe_linha}">
	<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$id}" onchange="selecionar('{$id}')">
<label for="seletor-{$id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>
	<td><input value="{$posicao}" onblur="editarPosicao('{$id}')" type="text" name="posicao" id="posicao_{$id}" style="width:35px; border:none; border-bottom:1px solid #000; outline:none; background:transparent;"></td>
	<td class="text-center" style="color:{$classe_linha}">
	<img alt="avatar" class="rounded-circle" src="/media-delivery.php?tipo=categorias&amp;arquivo={$foto}" width="30px" height="30px">
	</td>
<td style="color:{$classe_linha}">{$nome}</td>
<td style="color:{$classe_linha}">{$descricaoF}</td>
<td style="color:{$classe_linha}">{$mais_sabores}</td>
<td style="color:{$classe_linha}">{$delivery}</td>
<td align="center" style="color:{$classe_linha}">{$produtos}</td>
<td>
	<a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}','{$nomeF}', '{$descricao}', '{$foto}', '{$mais_sabores}', '{$delivery}')" title="Editar Dados"><i class="fa fa-edit"></i></a>

	
<big><a href="#" class="btn btn-danger-light btn-sm" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>


		<a class="btn btn-success-light btn-sm" href="#" onclick="ativar('{$id}', '{$acao}')" title="{$titulo_link}"><i class="fa {$icone}"></i></a>

		<a class="$ocultar_ad btn btn-primary-light btn-sm" href="#" onclick="variacoes('{$id}','{$nome}')" title="Variações do Produto"><i class="fa fa-list"></i></a>

		<a class="{$ocultar_ad} btn btn-success-light btn-sm" href="#" onclick="adicionais('{$id}','{$nome}')" title="Adicionais da Categoria"><i class="fa fa-plus "></i></a>

		<a class="{$ocultar_ad} btn btn-warning-light btn-sm" href="#" onclick="bordas('{$id}','{$nome}')" title="Opções de Bordas"><i class="fa fa-plus-circle"></i></a>


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
	function editar(id, nome, descricao, foto, mais_sabores, delivery) {
		$('#id').val(id);
		$('#nome').val(decodeURIComponent(nome));
		$('#descricao').val(descricao);
		$('#mais_sabores').val(mais_sabores).change();
		$('#delivery').val(delivery).change();

		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		$('#foto').val('');
		$('#target').attr('src', '/media-delivery.php?tipo=categorias&arquivo=' + encodeURIComponent(foto));
	}




	function limparCampos() {
		$('#id').val('');
		$('#nome').val('');
		$('#descricao').val('');
		$('#foto').val('');
		$('#delivery').val('Sim').change()
		$('#target').attr('src', 'images/categorias/sem-foto.jpg');
	}
</script>


<script type="text/javascript">
	function variacoes(id, nome) {

		$('#titulo_nome_var').text(nome);
		$('#id_var').val(id);

		listarVariacoes(id);
		$('#modalVariacoes').modal('show');
		$('#btn_var').text('Salvar');
		$('#id_var_editar').val('');
		limparCamposVar();
	}
</script>


<script type="text/javascript">
	function adicionais(id, nome) {

		window.categoriaSalgadaRainha = window.lojaRainhaTapiocas === true && nome.trim().toLowerCase() === 'tapiocas salgadas';
		$('#titulo_nome_adc').text(nome);
		$('#id_adc').val(id);

		$('#btn_editar_adc').text('Salvar');
		$('#id_adc_editar').val('');
		limparCamposVar()

		listarAdicionais(id);
		$('#modalAdicionais').modal('show');
		limparCamposAdc();
	}
</script>


<script type="text/javascript">
	function bordas(id, nome) {

		$('#titulo_nome_bordas').text(nome);
		$('#id_bordas').val(id);

		listarBordas(id);
		$('#modalBordas').modal('show');
		limparCamposBordas();
	}

	function editarPosicao(id) {
		var posicao = $('#posicao_'+id).val();

		$.ajax({
			url: 'paginas/' + pag + "/editar_posicao.php",
			method: 'POST',
			data: {
				id, posicao
			},
			dataType: "html",

			success: function(result) {
				if(result.trim() == "Editado com Sucesso"){
					listar();
				}else{
					alert(result)
				}
				
			}
		});
	}
</script>

