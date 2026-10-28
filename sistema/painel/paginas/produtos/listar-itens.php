<?php 
require_once("../../../conexao.php");
$tabela = 'itens_grade';

$id = $_POST['id'];

$query = $pdo->query("SELECT * FROM $tabela where grade = '$id' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover">
	<thead> 
	<tr> 
	<th>Nome</th>	
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
		$valor = $res[$i]['valor'];	
		$limite = $res[$i]['limite'];		
	$ativo = $res[$i]['ativo'];
	$produto = $res[$i]['produto'];

	$valorF = number_format($valor, 2, ',', '.');

		

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
<td class="esc">R$ {$valorF}</td>
<td class="esc">{$limite}</td>
<td>
	
	<li class="dropdown head-dpdn2" style="display: inline-block;">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-trash-o text-danger"></i></big></a>

		<ul class="dropdown-menu" style="margin-left:-230px;">
		<li>
		<div class="notification_desc2">
		<p>Confirmar Exclusão? <a href="#" onclick="excluirItens('{$id}')"><span class="text-danger">Sim</span></a></p>
		</div>
		</li>										
		</ul>
		</li>


		<big><a href="#" onclick="ativarItens('{$id}', '{$acao}')" title="{$titulo_link}"><i class="fa {$icone} text-success"></i></a></big>


		


</td>
</tr>
HTML;

}

echo <<<HTML
	</tbody>
	<small><div align="center" id="mensagem-excluir-itens"></div></small>
	</table>
	</small>
HTML;


}else{
	echo '<small>Não possui nenhuma variação cadastrada!</small>';
}






 ?>

