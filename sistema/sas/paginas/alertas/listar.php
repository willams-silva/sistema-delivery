<?php
$tabela = 'alertas_sas';
require_once("../../../conexao.php");
@session_start();
$id_usuario = @$_SESSION['id'];

$query = $pdo->query("SELECT * from $tabela order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	echo <<<HTML
<small>
	<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr> 
	
	<th>Título</th>	
	<th>Data</th>	
	<th>Ativo</th>		
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
		$ativo = $res[$i]['ativo'];
		
		$dataF = implode('/', array_reverse(@explode('-', $data)));

		

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

		$tituloF = rawurlencode($titulo);
		$textoF = rawurlencode($texto);
		

		echo <<<HTML
<tr >
<td style="color:{$classe_ativo}">{$titulo}</td>
<td style="color:{$classe_ativo}">{$dataF}</td>
<td style="">{$ativo}</td>

<td>
<a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}','{$tituloF}','{$textoF}')" title="Editar Dados"><i class="fa fa-edit "></i></a>


<a href="#" class="btn btn-danger-light btn-sm" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a>



<a class="btn btn-success-light btn-sm" href="#" onclick="ativar('{$id}', '{$acao}')" title="{$titulo_link}"><i class="fa {$icone} "></i></a>

</td>
</tr>
HTML;
	}


	echo <<<HTML
</tbody>
<small><div align="center" id="mensagem-excluir"></div></small>
</table>
</small>
HTML;
} else {
	echo 'Nenhum Registro Encontrado!';
}
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
	function editar(id, titulo, texto) {
		$('#mensagem').text('');
		$('#titulo_inserir').text('Editar Registro');

		$('#titulo').val(decodeURIComponent(titulo));
		$('#trumbowyg-editor').trumbowyg('html', decodeURIComponent(texto)); // Atualiza o conteúdo do editor
		
		$('#id').val(id);
		
		$('#modalForm').modal('show');
	}


	
	function limparCampos() {
		$('#id').val('');
		$('#titulo').val('');
		$('#trumbowyg-editor').trumbowyg('html', ''); // Limpa o conteúdo
		
		$('#ids').val('');
		$('#btn-deletar').hide();
	}




	
</script>


