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
$cargos_em_uso = count(array_filter($res, static function ($cargo) { return (int) $cargo['total_funcionarios'] > 0; }));
echo '<script>$("#bc-total-cargos").text(' . $linhas . ');$("#bc-cargos-em-uso").text(' . $cargos_em_uso . ');</script>';
if ($linhas > 0) {
	echo <<<HTML
<small>
	<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
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
<small><div align="center" id="mensagem-excluir"></div></small>
</table>
</small>
HTML;
} else {
	echo '<div class="text-center py-5"><i class="fa-solid fa-id-badge fa-2x text-muted mb-3"></i><h5>Nenhum cargo cadastrado</h5><p class="text-muted">Clique em Novo Cargo para cadastrar a primeira função da equipe.</p></div>';
}

?>



<script type="text/javascript">
	$(document).ready(function() {
		$('#tabela').DataTable({
			"ordering": true,
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

