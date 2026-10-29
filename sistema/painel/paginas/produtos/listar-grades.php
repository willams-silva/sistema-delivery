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
	$nome_comprovante = $res[$i]['nome_comprovante'];
	$adicional = $res[$i]['adicional'];
		

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


		<big><a href="#" onclick="addItensGrade('{$id}', '{$produto}', '$texto', '$adicional')" title="Adicionar Itens"><i class="fa fa-plus text-verde"></i></a></big>

		<big><a href="#" onclick="editarGrade('{$id}', '{$tipo_item}', '$valor_item', '$texto', '$limite', '$nome_comprovante', '$adicional')" title="Editar Grade"><i class="fa fa-edit text-primary"></i></a></big>



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
	function addItensGrade(id, produto, texto, adicional){

		$('#titulo_nome_itens').text(texto);		
		$('#id_item').val(id);	
		$('#id_item_produto').val(produto);		
		$('#e_adicional').val(adicional);		
		
		listarItens(id);

		if(adicional == 'Sim'){
			$('#div_adicional').show();
			$('#div_nome').hide();
		}else{
			$('#div_adicional').hide();
			$('#div_nome').show();
		}

		$('#btn_itens').text('Salvar');
		$('#adicional_grade').val('');
		$('#id_item_editar').val('');	
		
		$('#modalItens').modal('show');
		limparCamposItens();

		$('#mensagem-itens').text('');	
	}


		function editarGrade(id, tipo_item, valor_item, texto, limite, nome_comprovante, adicional){

				
		$('#tipo_item').val(tipo_item);	
		$('#valor_item').val(valor_item);
		$('#texto').val(texto);
		$('#limite').val(limite);
		$('#nome_comprovante').val(nome_comprovante);	
		$('#adicional').val(adicional);	
		$('#id_grade_editar').val(id);	
		
		$('#btn_grade').text('Editar');	
		
	}
</script>