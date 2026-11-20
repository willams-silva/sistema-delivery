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
if ($linhas > 0) {
	echo <<<HTML
<small>
	<table class="table table-striped table-hover table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
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