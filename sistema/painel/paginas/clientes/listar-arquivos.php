<?php
require_once("../../../conexao.php");
$pagina = 'arquivos';
@session_start();
$empresa = (int) ($_SESSION['empresa'] ?? 0);
$usuario = (int) ($_SESSION['id'] ?? 0);
$id = (int) ($_POST['id'] ?? 0);
if ($empresa <= 0 || $usuario <= 0 || $id <= 0) {
    http_response_code(403);
    exit('Sessão ou registro inválido');
}
$operador = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$operador->execute([':id' => $usuario, ':empresa' => $empresa]);
$nivel = $operador->fetchColumn();
if ($nivel === false) {
    http_response_code(403);
    exit('Operador não autorizado');
}
if ($nivel !== 'Administrador') {
    $permissao = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up INNER JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = 'clientes' LIMIT 1");
    $permissao->execute([':usuario' => $usuario, ':empresa' => $empresa]);
    if (!$permissao->fetchColumn()) {
        http_response_code(403);
        exit('Sem permissão');
    }
}
$cadastro = $pdo->prepare("SELECT 1 FROM clientes WHERE id = :id AND empresa = :empresa LIMIT 1");
$cadastro->execute([':id' => $id, ':empresa' => $empresa]);
if (!$cadastro->fetchColumn()) {
    http_response_code(404);
    exit('Cadastro não encontrado');
}

echo <<<HTML
<small>
HTML;
$query = $pdo->prepare("SELECT * FROM arquivos WHERE id_reg = :id AND registro = :registro ORDER BY id DESC");
$query->execute([':id' => $id, ':registro' => 'Cliente']);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
	echo <<<HTML
	<table class="table table-striped table-hover table-bordered text-nowrap border-bottom dt-responsive" id="">
		<thead> 
			<tr> 				
				<th>Nome</th>
				<th class="esc">Data</th>				
				<th>Arquivo</th>				
				<th>Excluir</th>
			</tr> 
		</thead> 
		<tbody> 
HTML;
	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$id = $res[$i]['id'];
		$nome = $res[$i]['nome'];
		$data_cad = $res[$i]['data_cad'];
		$arquivo = $res[$i]['arquivo'];

		//extensão do arquivo
		$ext = pathinfo($arquivo, PATHINFO_EXTENSION);
		if ($ext == 'pdf' || $ext == 'PDF') {
			$tumb_arquivo = 'pdf.png';
		} else if ($ext == 'rar' || $ext == 'zip' || $ext == 'RAR' || $ext == 'ZIP') {
			$tumb_arquivo = 'rar.png';
		} else if ($ext == 'doc' || $ext == 'docx' || $ext == 'DOC' || $ext == 'DOCX') {
			$tumb_arquivo = 'word.png';
		} else if ($ext == 'xlsx' || $ext == 'xlsm' || $ext == 'xls') {
			$tumb_arquivo = 'excel.png';
		} else if ($ext == 'xml') {
			$tumb_arquivo = 'xml.png';
		} else {
			$tumb_arquivo = $arquivo;
		}

		$data_cadF = implode('/', array_reverse(@explode('-', $data_cad)));

		echo <<<HTML
			<tr>					
				<td class="">{$nome}</td>
				<td class="esc">{$data_cadF}</td>				
				<td><a href="images/arquivos/{$arquivo}" target="_blank"><img src="images/arquivos/{$tumb_arquivo}" width="18px" height="18px"></a></td>
				<td>

					<div class="dropdown" style="display: inline-block;">                      
						<a href="#" aria-expanded="false" aria-haspopup="true" data-bs-toggle="dropdown" class="dropdown"><i class="fe fe-trash-2 text-danger"></i> 
						</a>
							<div  class="dropdown-menu tx-13">
								<div class="dropdown-item-text botao_excluir_listar">
								<p>Confirmar Exclusão? <a href="#" onclick="excluirArquivo('{$id}', '{$nome}')"><span class="botao_excluir_listar_sim">Sim</span></a></p>
								</div>
							</div>
					</div>
					
				</td>  
			</tr> 
HTML;
	}
	echo <<<HTML
		</tbody> 
	</table>
</small>
HTML;
} else {
	echo 'Não possui nenhum arquivo cadastrado!';
}

?>


<script type="text/javascript">
	$(document).ready(function() {
		$('#tabela_arquivos').DataTable({
			"ordering": false,
			"stateSave": true,
		});
		$('#tabela_filter label input').focus();
	});


	function excluirArquivo(id, nome) {

		$.ajax({
			url: 'paginas/' + pag + "/excluir-arquivo.php",
			method: 'POST',
			data: {
				id,
				nome
			},
			dataType: "text",

			success: function(mensagem) {

				$('#mensagem-arquivo').text('');
				$('#mensagem-arquivo').removeClass()
				if (mensagem.trim() == "Excluído com Sucesso") {
					listarArquivos();
				} else {

					$('#mensagem-arquivo').addClass('text-danger')
					$('#mensagem-arquivo').text(mensagem)
				}


			},

		});
	}
</script>