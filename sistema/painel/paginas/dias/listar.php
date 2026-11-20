<?php
@session_start();
$id_empresa = (int) ($_SESSION['empresa'] ?? 0);
if ($id_empresa <= 0 || (int) ($_SESSION['id'] ?? 0) <= 0) { http_response_code(403); exit('Sessão inválida'); }
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'dias';

$query = $pdo->prepare("SELECT * FROM dias WHERE empresa = :empresa ORDER BY id DESC");
$query->execute([':empresa'=>$id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = count($res);
echo '<script>$("#bc-total-dias").text(' . $total_reg . ');</script>';
if ($total_reg > 0) {

	echo <<<HTML
	<small>
	<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr>
		<th align="center" width="5%" class="text-center">Selecionar</th>
	<th width="80%">Dia</th>
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$id = $res[$i]['id'];
		$dia = $res[$i]['dia'];
        $dia_html = htmlspecialchars((string) $dia, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $dia_js = htmlspecialchars(json_encode((string) $dia, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');


		echo <<<HTML
<tr>
	<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$id}" onchange="selecionar('{$id}')">
<label for="seletor-{$id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>
<td>{$dia_html}</td>
<td>
	<a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}',{$dia_js})" title="Editar Dados"><i class="fa fa-edit"></i></a>

	

<big><a href="#" class="btn btn-danger-light btn-sm" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>




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
	echo '<div class="text-center py-5"><i class="fa-solid fa-calendar-xmark fa-2x text-muted mb-3"></i><h5>Nenhum dia fechado cadastrado</h5><p class="text-muted">Cadastre apenas os dias da semana em que a loja não funciona.</p></div>';
}


?>


<script type="text/javascript">
	$(document).ready(function() {
		$('#tabela').DataTable({
			"ordering": true,
            "pageLength": 10,
            "language": {"search": "Buscar:", "lengthMenu": "Mostrar _MENU_", "info": "Exibindo _START_ a _END_ de _TOTAL_", "infoEmpty": "Nenhum registro", "zeroRecords": "Nenhum resultado", "paginate": {"previous": "Anterior", "next": "Próximo"}},
			"stateSave": true
		});
		$('#tabela_filter label input').focus();
	});
</script>


<script type="text/javascript">
	function editar(id, dia) {
		$('#id').val(id);
		$('#dia').val(dia).change();


		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');

	}


	function limparCampos() {
		$('#id').val('');

	}
</script>

