<?php 
require_once("../../../conexao.php");
$tabela = 'grades';

$id = $_POST['id'];

$query = $pdo->query("SELECT * FROM $tabela where produto = '$id' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover">
	<thead> 
	<tr> 
	<th>Texto</th>	
	<th class="esc">Tipo Item</th> 	
	<th class="esc">Valor Item</th> 
	<th class="esc">Limite</th> 		
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
		$id = $res[$i]['id'];
		$texto = $res[$i]['texto'];
		$tipo_item = $res[$i]['tipo_item'];
		$valor_item = $res[$i]['valor_item'];	
		$limite = $res[$i]['limite'];		
	$ativo = $res[$i]['ativo'];
	$produto = $res[$i]['produto'];

		

		if($ativo == 'Sim'){
			$icone = 'fa-check-square';
			$titulo_link = 'Desativar Item';
			$acao = 'Não';
			$classe_linha = '';
		}else{
			$icone = 'fa-square-o';
			$titulo_link = 'Ativar Item';
			$acao = 'Sim';
			$classe_linha = 'text-muted';
		}

		if($limite == 0){
			$limite = 'ilimitado';
		}


		
echo <<<HTML
<tr class="{$classe_linha}">
<td>
{$texto}
</td>
<td class="esc">{$tipo_item}</td>
<td class="esc">{$valor_item}</td>
<td class="esc">{$limite}</td>
<td>
	
	<li class="dropdown head-dpdn2" style="display: inline-block;">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-trash-o text-danger"></i></big></a>

		<ul class="dropdown-menu" style="margin-left:-230px;">
		<li>
		<div class="notification_desc2">
		<p>Confirmar Exclusão? <a href="#" onclick="excluirGrades('{$id}')"><span class="text-danger">Sim</span></a></p>
		</div>
		</li>										
		</ul>
		</li>


		<big><a href="#" onclick="ativarGrades('{$id}', '{$acao}')" title="{$titulo_link}"><i class="fa {$icone} text-success"></i></a></big>


		<big><a href="#" onclick="addItensGrade('{$id}', '{$produto}', '$texto')" title="Adicionar Itens"><i class="fa fa-plus text-verde"></i></a></big>



</td>
</tr>
HTML;

}

echo <<<HTML
	</tbody>
	<small><div align="center" id="mensagem-excluir-grades"></div></small>
	</table>
	</small>
HTML;


}else{
	echo '<small>Não possui nenhuma variação cadastrada!</small>';
}






 ?>



<script type="text/javascript">
	function addItensGrade(id, produto, texto){

		$('#titulo_nome_itens').text(texto);		
		$('#id_item').val(id);	
		$('#id_item_produto').val(produto);		
		
		listarItens(id);
		
		$('#modalItens').modal('show');
		limparCamposItens();
	}
</script>