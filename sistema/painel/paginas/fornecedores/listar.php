<?php
@session_start();
$id_empresa = (int) ($_SESSION['empresa'] ?? 0);
$id_operador = (int) ($_SESSION['id'] ?? 0);
if ($id_empresa <= 0 || $id_operador <= 0) {
    http_response_code(403);
    exit('Sessão inválida');
}
require_once("../../../conexao.php");
$operador = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$operador->execute([':id' => $id_operador, ':empresa' => $id_empresa]);
$nivel_operador = $operador->fetchColumn();
if ($nivel_operador === false) {
    http_response_code(403);
    exit('Operador não autorizado');
}
if ($nivel_operador !== 'Administrador') {
    $permissao = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up INNER JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = 'fornecedores' LIMIT 1");
    $permissao->execute([':usuario' => $id_operador, ':empresa' => $id_empresa]);
    if (!$permissao->fetchColumn()) {
        http_response_code(403);
        exit('Sem permissão para consultar fornecedores');
    }
}

require_once("../../buscar_config.php");

$tabela = 'fornecedores';

require_once("../../verificar.php");

$query = $pdo->prepare("SELECT * FROM fornecedores WHERE empresa = :empresa ORDER BY id DESC");
$query->execute([':empresa' => $id_empresa]);
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
html body #listar.bc-supplier-cards #tabela tbody tr td:first-child{display:none!important}
html body #listar.bc-supplier-cards #tabela tbody td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;transform:none!important;box-shadow:none!important}
html body #listar.bc-supplier-cards #tabela tbody td:last-child a.btn{transition:none!important}
#listar #bc-supplier-list-view{display:flex!important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;color:#4e2d20;font-weight:600}
#listar #bc-supplier-list-view button{border:1px solid #d7c5b7!important;border-radius:9px;background:#fff!important;color:#6c4631!important;padding:9px 14px;font-weight:600}
#listar #bc-supplier-list-view button[aria-pressed=true]{background:#914a29!important;color:#fff!important}
html body #listar.bc-supplier-cards #tabela{display:block!important;width:100%!important;border:0!important}
html body #listar.bc-supplier-cards #tabela thead{display:none!important}
html body #listar.bc-supplier-cards #tabela tbody{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr));gap:16px;padding:12px 0}
html body #listar.bc-supplier-cards #tabela tbody tr:not(.child){display:flex!important;flex-direction:column;min-width:0;border:1px solid #e5d4c6;border-radius:14px;padding:16px;background:#fff}
html body #listar.bc-supplier-cards #tabela tbody td{display:block!important;width:auto!important;border:0!important;white-space:normal!important;overflow-wrap:anywhere;padding:7px 0!important}
#listar.bc-supplier-cards #tabela tbody td:nth-child(2){font-weight:700;color:#63351e}
#listar.bc-supplier-cards #tabela tbody td:nth-child(3)::before{content:'Telefone: '}
#listar.bc-supplier-cards #tabela tbody td:nth-child(4)::before{content:'PIX: '}
#listar.bc-supplier-cards #tabela tbody td:last-child{margin-top:auto;border-top:1px solid #eaded4!important;text-align:center}
#listar.bc-supplier-cards #tabela tbody tr.table-danger{border-top:4px solid #b42332!important}

html body #listar.bc-supplier-cards #tabela tbody tr:hover,html body #listar.bc-supplier-cards #tabela tbody tr:hover > td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;--bs-table-hover-bg:transparent!important;box-shadow:none!important;transform:none!important;filter:none!important;background-image:none!important}
html body #listar.bc-supplier-cards #tabela tbody tr:hover{background:#fff!important;border-color:#e5d4c6!important}
html body #listar.bc-supplier-cards #tabela tbody tr:hover > td{background:transparent!important}
html body #listar.bc-supplier-cards #tabela tbody tr td:last-child a.btn:hover,html body #listar.bc-supplier-cards #tabela tbody tr td:last-child a.btn:hover i{transform:none!important;filter:none!important;box-shadow:none!important;opacity:1!important;animation:none!important;transition:none!important}
html body #listar.bc-supplier-cards #tabela tbody tr td:last-child a.btn:hover{background:var(--bc-bg,#fff0df)!important;color:var(--bc-action,#795038)!important;border-color:var(--bc-action,#795038)!important}
html body #listar.bc-supplier-cards #tabela tbody tr td:last-child a.btn:hover i{color:inherit!important}

html body #listar #tabela_wrapper .dataTables_paginate{padding:16px 0 0!important;float:none!important;text-align:center!important}
html body #listar #tabela_wrapper .dataTables_paginate ul.pagination{display:flex!important;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button{display:inline-flex!important;align-items:center;justify-content:center;min-width:40px;min-height:40px;padding:8px 12px!important;margin:0!important;border:1px solid #d7c5b7!important;border-radius:8px!important;background:#fff!important;color:#6c4631!important;box-shadow:none!important;text-shadow:none!important;font-weight:600;line-height:1.2!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.active .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.current,html body #listar #tabela_wrapper .dataTables_paginate .page-link:hover,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button:hover{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #listar #tabela_wrapper .dataTables_paginate .page-item.disabled .page-link,html body #listar #tabela_wrapper .dataTables_paginate a.paginate_button.disabled{background:#f4eee8!important;color:#806e63!important;border-color:#e5d4c6!important;opacity:1!important;cursor:default!important;pointer-events:none}
html body #listar #tabela_wrapper .dataTables_paginate li.page-item:before,html body #listar #tabela_wrapper .dataTables_paginate li.page-item:after{content:none!important}
#listar.bc-supplier-cards #tabela tbody td:nth-child(5)::before{content:'CNPJ: ';font-weight:600}
html body #listar.bc-supplier-cards #tabela tbody tr td:first-child{display:none!important}
</style>
<div id="bc-supplier-list-view"><span>Visualização:</span><button type="button" data-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button><button type="button" data-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button></div>
<script>
(function(){
 var root=document.getElementById('listar'), bar=document.getElementById('bc-supplier-list-view');
 if(!root||!bar)return;
 
 function apply(mode){root.dataset.supplierView=mode;root.classList.toggle('bc-supplier-cards',mode==='cards');var table=root.querySelector('#tabela');if(table)table.classList.toggle('table-hover',mode!=='cards');bar.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===mode));});}
 bar.addEventListener('click',function(e){var b=e.target.closest('button[data-view]');if(b){apply(b.dataset.view);if(jQuery.fn.dataTable&&jQuery.fn.dataTable.isDataTable('#tabela'))jQuery('#tabela').DataTable().columns.adjust();}});
 apply(root.dataset.supplierView||'cards');
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
	<th>Pix</th>
	<th>CNPJ</th>
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
		$endereco = $res[$i]['endereco'];
		$data = $res[$i]['data'];
		$pix = $res[$i]['pix'];
		$numero = $res[$i]['numero'];
		$bairro = $res[$i]['bairro'];
		$cidade = $res[$i]['cidade'];
		$estado = $res[$i]['estado'];
		$cep = $res[$i]['cep'];
		$cnpj = $res[$i]['cnpj'];
		$complemento = $res[$i]['complemento'];
		$tipo_chave = $res[$i]['tipo_chave'];

		$dataF = implode('/', array_reverse(@explode('-', $data)));

		if ($pix != '') {
			$chave_pix_mod = $tipo_chave . ': ' . $pix;
		} else {
			$chave_pix_mod = '';
		}



        // Dados em HTML e argumentos JS devem ser codificados separadamente.
        $html_id = htmlspecialchars((string) $id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_id = htmlspecialchars(json_encode((string) $id, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_nome = htmlspecialchars((string) $nome, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_nome = htmlspecialchars(json_encode((string) $nome, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_telefone = htmlspecialchars((string) $telefone, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_telefone = htmlspecialchars(json_encode((string) $telefone, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_email = htmlspecialchars((string) $email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_email = htmlspecialchars(json_encode((string) $email, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_endereco = htmlspecialchars((string) $endereco, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_endereco = htmlspecialchars(json_encode((string) $endereco, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_pix = htmlspecialchars((string) $pix, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_pix = htmlspecialchars(json_encode((string) $pix, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_numero = htmlspecialchars((string) $numero, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_numero = htmlspecialchars(json_encode((string) $numero, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_bairro = htmlspecialchars((string) $bairro, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_bairro = htmlspecialchars(json_encode((string) $bairro, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_cidade = htmlspecialchars((string) $cidade, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_cidade = htmlspecialchars(json_encode((string) $cidade, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_estado = htmlspecialchars((string) $estado, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_estado = htmlspecialchars(json_encode((string) $estado, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_cep = htmlspecialchars((string) $cep, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_cep = htmlspecialchars(json_encode((string) $cep, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_cnpj = htmlspecialchars((string) $cnpj, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_cnpj = htmlspecialchars(json_encode((string) $cnpj, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_complemento = htmlspecialchars((string) $complemento, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_complemento = htmlspecialchars(json_encode((string) $complemento, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_tipo_chave = htmlspecialchars((string) $tipo_chave, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_tipo_chave = htmlspecialchars(json_encode((string) $tipo_chave, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_dataF = htmlspecialchars((string) $dataF, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_dataF = htmlspecialchars(json_encode((string) $dataF, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html_chave_pix_mod = htmlspecialchars((string) $chave_pix_mod, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $js_chave_pix_mod = htmlspecialchars(json_encode((string) $chave_pix_mod, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

		echo <<<HTML
<tr>
<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$html_id}" onchange="selecionar({$js_id})">
<label for="seletor-{$html_id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>
<td>{$html_nome}</td>
<td>{$html_telefone}</td>
<td>{$html_chave_pix_mod}</td>
<td>{$html_cnpj}</td>
<td>
	<a class="btn btn-info-light btn-sm" href="#" onclick="editar({$js_id},{$js_nome},{$js_email},{$js_telefone},{$js_endereco},{$js_pix},{$js_numero},{$js_bairro},{$js_cidade},{$js_estado},{$js_cep},{$js_cnpj},{$js_complemento}, {$js_tipo_chave})" title="Editar Dados"><i class="fa fa-edit"></i></a>

<big><a href="#" class="btn btn-danger-light btn-sm" onclick="excluir({$js_id})" title="Excluir"><i class="fa fa-trash-can"></i></a></big>

<a class="btn btn-warning-light btn-sm" href="#" onclick="mostrar({$js_nome},{$js_email},{$js_telefone},{$js_endereco},{$js_pix},{$js_dataF},{$js_numero},{$js_bairro},{$js_cidade},{$js_estado},{$js_cep},{$js_cnpj},{$js_complemento}, {$js_tipo_chave})" title="Mostrar Dados"><i class="fa fa-info-circle"></i></a>

<a class="btn btn-dark-light btn-sm" href="#" onclick="arquivo({$js_id}, {$js_nome})" title="Inserir / Ver Arquivos"><i class="fa fa-file-o " ></i></a>



</td>
</tr>
HTML;
	}
} else {
	echo '<small>Não possui nenhum cadastro!</small>';
}


echo <<<HTML
</tbody>
<small><div align="center" id="mensagem-excluir"></div></small>
</table>
HTML;
?>



<script type="text/javascript">
	$(document).ready(function() {
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
	function editar(id, nome, email, telefone, endereco, pix, numero, bairro, cidade, estado, cep, cnpj, complemento, tipo_chave) {
		$('#mensagem').text('');
		$('#titulo_inserir').text('Editar Registro');

		$('#id').val(id);
		$('#nome').val(nome);
		$('#email').val(email);
		$('#telefone').val(telefone);
		$('#endereco').val(endereco);
		$('#pix').val(pix);
		$('#numero').val(numero);
		$('#bairro').val(bairro);
		$('#cidade').val(cidade);
		$('#estado').val(estado).change();
		$('#tipo_chave').val(tipo_chave).change();
		$('#cep').val(cep);
		$('#cnpj').val(cnpj);
		$('#complemento').val(complemento);

		$('#modalForm').modal('show');
	}


	function mostrar(nome, email, telefone, endereco, pix, data, numero, bairro, cidade, estado, cep, cnpj, complemento, tipo_chave) {

		if (pix != '') {
			$('#pix_dados').text(tipo_chave + ': ' + pix);
		} else {
			$('#pix_dados').text(tipo_chave);
		}

		$('#titulo_dados').text(nome);
		$('#email_dados').text(email);
		$('#telefone_dados').text(telefone);
		$('#endereco_dados').text(endereco);

		$('#data_dados').text(data);
		$('#numero_dados').text(numero);
		$('#bairro_dados').text(bairro);
		$('#cidade_dados').text(cidade);
		$('#estado_dados').text(estado);
		$('#cep_dados').text(cep);
		$('#cnpj_dados').text(cnpj);
		$('#complemento_dados').text(complemento);

		$('#modalDados').modal('show');
	}

	function limparCampos() {
		$('#id').val('');
		$('#nome').val('');
		$('#email').val('');
		$('#telefone').val('');
		$('#endereco').val('');
		$('#pix').val('');
		$('#numero').val('');
		$('#bairro').val('');
		$('#cidade').val('');
		$('#estado').val('').change();
		$('#cep').val('');
		$('#cnpj').val('');
		$('#complemento').val('');
		$('#tipo_chave').val('').change();


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


</script>