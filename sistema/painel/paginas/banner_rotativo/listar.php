<?php
@session_start();
$id_empresa = (int)($_SESSION['empresa'] ?? 0);
if ($id_empresa <= 0) { http_response_code(403); exit('Sessão inválida'); }
$tabela = 'banner_rotativo';
require_once("../../../conexao.php");
require_once("../../buscar_config.php");


$query = $pdo->prepare("SELECT * FROM banner_rotativo WHERE empresa = :empresa ORDER BY ordem ASC, id DESC");
$query->execute([':empresa'=>$id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {

	echo <<<HTML
	<small>
	<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr>
		<th align="center" width="5%" class="text-center">Selecionar</th>
	<th class="width70">Categoria</th>		
	<th>Foto</th><th>Status</th><th>Ordem</th><th>Período</th>		
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$id = $res[$i]['id'];
		$categoria = $res[$i]['categoria'];
		$foto = basename((string)$res[$i]['foto']);
        if(!preg_match('/^[a-zA-Z0-9_.-]+$/D',$foto)) $foto='sem-foto.jpg';
        $ativo=$res[$i]['ativo']==='Sim'?'Sim':'Não';
        $ordem=(int)$res[$i]['ordem'];
        $inicio=(string)($res[$i]['inicio_em']??'');
        $fim=(string)($res[$i]['fim_em']??'');
        $inicioForm=$inicio!==''?str_replace(' ','T',substr($inicio,0,16)):'';
        $fimForm=$fim!==''?str_replace(' ','T',substr($fim,0,16)):'';
        $periodo=htmlspecialchars(($inicio?:'Sem início').' até '.($fim?:'Sem fim'),ENT_QUOTES,'UTF-8');
        $badge=$ativo==='Sim'?'<span class="badge bg-success">Ativo</span>':'<span class="badge bg-secondary">Desativado</span>';

		$query2 = $pdo->prepare("SELECT nome FROM categorias WHERE id = :id AND empresa = :empresa");
        $query2->execute([':id'=>$categoria, ':empresa'=>$id_empresa]);
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$nome_cat = htmlspecialchars((string)$res2[0]['nome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		} else {
			$nome_cat = 'Sem Categoria!';
		}


		echo <<<HTML
<tr>
<td align="center">
<div class="custom-checkbox custom-control">
<input type="checkbox" class="custom-control-input" id="seletor-{$id}" onchange="selecionar('{$id}')">
<label for="seletor-{$id}" class="custom-control-label mt-1 text-dark"></label>
</div>
</td>
<td>{$nome_cat}</td>
<td class="esc"><img src="images/{$tabela}/{$foto}" alt="Miniatura do banner" style="width:140px;max-height:75px;object-fit:cover;border-radius:8px"></td><td>{$badge}</td><td>{$ordem}</td><td><small>{$periodo}</small></td>
<td>
	<a class="btn btn-info-light btn-sm" href="#" onclick="editar('{$id}','{$categoria}', '{$foto}', '{$ativo}', '{$ordem}', '{$inicioForm}', '{$fimForm}')" title="Editar Dados"><i class="fa fa-edit"></i></a>

	
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
	echo 'Não possui registros cadastrados!';
}


?>


<script type="text/javascript">
	$(document).ready(function() {
		$('#tabela').DataTable({
			"ordering": false,
			"stateSave": true
		});
		$('#tabela_filter label input').focus();
	});
</script>


<script type="text/javascript">
	function editar(id, cat, foto, ativo, ordem, inicio, fim) {
		$('#id').val(id);
		$('#categoria').val(cat).change();
        $('#ativo').val(ativo);$('#ordem').val(ordem);$('#inicio_em').val(inicio);$('#fim_em').val(fim);

		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		$('#foto').val('');
		$('#target').attr('src', 'images/banner_rotativo/' + foto);
	}




	function limparCampos() {
		$('#id').val('');
        $('#categoria').val('0').change();
        $('#ativo').val('Sim');$('#ordem').val('0');$('#inicio_em,#fim_em').val('');
        $('#mensagem').removeClass('text-danger').text('');
		$('#foto').val('');
		$('#target').attr('src', 'images/banner_rotativo/sem-foto.jpg');
	}
</script>

