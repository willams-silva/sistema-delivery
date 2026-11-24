<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'usuarios';


$query = $pdo->query("SELECT * from $tabela where nivel != 'Cliente' and nivel != 'Administrador' and empresa = '$id_empresa' order by id desc");
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
html body #listar.bc-employee-cards #tabela tbody tr td:first-child{display:none!important}
html body #listar.bc-employee-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-employee-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-employee-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-employee-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-employee-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-employee-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-employee-cards #tabela thead{display:none!important}
html body #listar.bc-employee-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-employee-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-employee-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-employee-cards #tabela tbody td:nth-child(2){font-weight:700;color:#63351e}
#listar.bc-employee-cards #tabela tbody td:nth-child(3)::before{content:'Telefone: '}
#listar.bc-employee-cards #tabela tbody td:nth-child(4)::before{content:'E-mail: '}
#listar.bc-employee-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-employee-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-employee-cards #tabela tbody tr:hover,html body #listar.bc-employee-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-employee-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-employee-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-employee-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-employee-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-employee-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-employee-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
#listar.bc-employee-cards #tabela tbody td:nth-child(5)::before{content:'Acessar painel: ';font-weight:600}
#listar.bc-employee-cards #tabela tbody td:nth-child(6)::before{content:'Nível: ';font-weight:600}
#listar.bc-employee-cards #tabela tbody td:nth-child(6) .badge{width:auto!important}
#listar.bc-employee-cards #tabela tbody td:nth-child(7){order:-1;text-align:center}
#listar.bc-employee-cards #tabela tbody td:nth-child(7) img{width:64px;height:64px;object-fit:cover}
html body #listar.bc-employee-cards #tabela tbody tr td:first-child{display:none!important}
html body #listar #tabela tbody td:last-child a.btn.btn-success{--bc-action:#13745a;--bc-bg:#def3e9}
</style>
<div id="bc-employee-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-employee-list-view');
 if(!root||!bar)return;
 
 function apply(mode){root.dataset.employeeView=mode;root.classList.toggle('bc-employee-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable&&jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.employeeView||'cards');
})();
</script>
BCVIEW;
if ($linhas > 0) {
	echo <<<HTML
<small>
	<table class="table table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr> 
	<th align="center" width="5%" class="text-center">Selecionar</th>
	<th>Nome</th>	
	<th>Telefone</th>	
	<th>Email</th>	
	<th>Acessar Painel</th>
	<th>Nível</th>
	<th class="text-center">Foto</th>	
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

	for ($i = 0; $i < $linhas; $i++) {
		$id = $res[$i]['id'];
		$nome = $res[$i]['nome'];
		$telefone = $res[$i]['telefone'];
		$email = $res[$i]['email'];
		$senha = $res[$i]['senha'];
		$foto = $res[$i]['foto'];
		$nivel = $res[$i]['nivel'];
		$endereco = $res[$i]['endereco'];
		$ativo = $res[$i]['ativo'];
		$data = $res[$i]['data'];
		$pix = $res[$i]['pix'];
		$numero = $res[$i]['numero'];
		$bairro = $res[$i]['bairro'];
		$cidade = $res[$i]['cidade'];
		$estado = $res[$i]['estado'];
		$cep = $res[$i]['cep'];
		$data_nasc = $res[$i]['data_nasc'];
		$acessar_painel = $res[$i]['acessar_painel'];
		$complemento = $res[$i]['complemento'];
		$tipo_chave = $res[$i]['tipo_chave'];

		$dataF = implode('/', array_reverse(@explode('-', $data)));

		$data_nascF = implode('/', array_reverse(@explode('-', $data_nasc)));

		if ($ativo == 'Sim') {
			$icone = 'fa-check-square';
			$titulo_link = 'Desativar Usuário';
			$acao = 'Não';
			$classe_ativo = '';
		} else {
			$icone = 'fa-square-o';
			$titulo_link = 'Ativar Usuário';
			$acao = 'Sim';
			$classe_ativo = '#c4c4c4';
		}

		if ($nivel == 'Administrador') {
			$senha = '******';
			$cor_adm = 'bg-danger-transparent text-danger';
		} else {
			$cor_adm = 'bg-success-transparent text-success';
		}


		if($nivel == "Entregador" || $nivel == "Motoboy"){
			$classe_nivel = '';
		}else{
			$classe_nivel = 'ocultar';
		}


		echo <<<HTML
<tr >
<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$id}" onchange="selecionar('{$id}')">
<label for="seletor-{$id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>
<td style="color:{$classe_ativo}">{$nome}</td>
<td style="color:{$classe_ativo}">{$telefone}</td>
<td style="color:{$classe_ativo}">{$email}</td>
<td style="color:{$classe_ativo}">{$acessar_painel}</td>
<td><span class="badge font-weight-semibold {$cor_adm} tx-12" style="width:90%;">{$nivel}</span></td>
<td class="text-center" style="color:{$classe_ativo}"><img alt="avatar" width="30px" height="30px" class="rounded-circle" onclick="mostrar('{$nome}','{$email}','{$telefone}','{$endereco}','{$ativo}','{$dataF}', '{$senha}', '{$nivel}', '{$foto}','{$pix}','{$data_nasc}','{$numero}','{$bairro}','{$cidade}','{$estado}','{$cep}','{$acessar_painel}','{$complemento}','{$tipo_chave}')" src="images/perfil/{$foto}" class="hovv" width="25px"></td>
<td>
	<a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}','{$nome}','{$email}','{$telefone}','{$endereco}','{$nivel}','{$pix}','{$data_nasc}','{$numero}','{$bairro}','{$cidade}','{$estado}','{$cep}','{$acessar_painel}','{$complemento}','{$tipo_chave}', '{$foto}')" title="Editar Dados"><i class="fa fa-edit "></i></a>

<a href="#" class="btn btn-danger-light btn-sm" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a>

<a class="btn btn-primary-light btn-sm" href="#" onclick="mostrar('{$nome}','{$email}','{$telefone}','{$endereco}','{$ativo}','{$dataF}', '{$senha}', '{$nivel}', '{$foto}','{$pix}','{$data_nasc}','{$numero}','{$bairro}','{$cidade}','{$estado}','{$cep}','{$acessar_painel}','{$complemento}','{$tipo_chave}')" title="Mostrar Dados"><i class="fa fa-info-circle "></i></a>


<a class="btn btn-success-light btn-sm" href="#" onclick="ativar('{$id}', '{$acao}')" title="{$titulo_link}"><i class="fa {$icone} "></i></a>


<a class="btn btn-dark-light btn-sm" href="#" onclick="arquivo('{$id}', '{$nome}')" title="Inserir / Ver Arquivos"><i class="fa fa-file-o"></i></a>


<a class="{$classe_nivel} btn btn-success btn-sm" href="#" onclick="entregas('{$id}', '{$nome}')" title="Ver Entregas Pendentes"><i class="fa fa-usd"></i></a>

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
 echo 'Não possui nenhum cadastro!';
}
?>




<script type="text/javascript">
	$(document).ready(function() {
		if (!$('#tabela').length) return;
		$('#tabela').DataTable({
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
	function editar(id, nome, email, telefone, endereco, nivel, pix, data_nasc, numero, bairro, cidade, estado, cep, acessar_painel, complemento, tipo_chave, foto) {
		$('#mensagem').text('');
		$('#titulo_inserir').text('Editar Registro');

		$('#id').val(id);
		$('#nome').val(nome);
		$('#email').val(email);
		$('#telefone').val(telefone);
		$('#endereco').val(endereco);
		$('#nivel').val(nivel).change();
		$('#pix').val(pix);
		$('#numero').val(numero);
		$('#bairro').val(bairro);
		$('#cidade').val(cidade);
		$('#estado').val(estado).change();
		$('#cep').val(cep);
		$('#data_nasc').val(data_nasc);
		$('#complemento').val(complemento);
		$('#acessar_painel').val(acessar_painel).change();
		$('#tipo_chave').val(tipo_chave).change();

		$('#target').attr("src", "images/perfil/" + foto);

		$('#modalForm').modal('show');
	}


	function mostrar(nome, email, telefone, endereco, ativo, data, senha, nivel, foto, pix, data_nasc, numero, bairro, cidade, estado, cep, acessar_painel, complemento, tipo_chave) {

		if (pix != '') {
			$('#pix_dados').text(tipo_chave + ': ' + pix);
		} else {
			$('#pix_dados').text(tipo_chave);
		}

		$('#titulo_dados').text(nome);
		$('#email_dados').text(email);
		$('#telefone_dados').text(telefone);
		$('#endereco_dados').text(endereco);
		$('#ativo_dados').text(ativo);
		$('#data_dados').text(data);
		$('#senha_dados').text(senha);
		$('#nivel_dados').text(nivel);
		$('#data_nasc_dados').text(data_nasc);
		$('#numero_dados').text(numero);
		$('#bairro_dados').text(bairro);
		$('#cidade_dados').text(cidade);
		$('#estado_dados').text(estado);
		$('#cep_dados').text(cep);
		$('#complemento_dados').text(complemento);
		$('#acessar_painel_dados').text(acessar_painel);

		$('#foto_dados').attr("src", "images/perfil/" + foto);

		$('#modalDados').modal('show');
	}

	function limparCampos() {
		$('#id').val('');
		$('#nome').val('');
		$('#email').val('');
		$('#telefone').val('');
		$('#endereco').val('');
		$('#pix').val('');
		$('#data_nasc').val('');
		$('#numero').val('');
		$('#bairro').val('');
		$('#cidade').val('');
		$('#estado').val('').change();
		$('#tipo_chave').val('').change();
		$('#cep').val('');
		$('#complemento').val('');

		$('#target').attr("src", "images/perfil/sem-foto.jpg");

		$('#ids').val('');
		$('#btn-deletar').hide();
	}



	function arquivo(id, nome) {
		$('#id-arquivo').val(id);
		$('#nome-arquivo').text(nome);
		$('#modalArquivos').modal('show');
		$('#mensagem-arquivo').text('');
		$('#arquivo_conta').val('');
		listarArquivos();
	}

	function entregas(id, nome){
		$('#id_entrega').val(id);
		$('#nome_entrega').text(nome);

		listarEntregas(id);
		$('#modalEntregas').modal('show');
	}
</script>