<?php 
require_once("../../../conexao.php");
$tabela = 'carrinho';

$id = @$_POST['id'];

$total_carrinho = 0;
$query = $pdo->query("SELECT * FROM $tabela where mesa = '$id' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="">
	<thead> 
	<tr> 
	<th>Foto</th>	
	<th>Produto(s)</th>	
	<th>Total</th>	
	<th>Excluir</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	$id = $res[$i]['id'];
		$total_item = $res[$i]['total_item'];
		$produto = $res[$i]['produto'];
		$quantidade = $res[$i]['quantidade'];
		$obs = $res[$i]['obs'];
		$item = $res[$i]['item'];
		$variacao = $res[$i]['variacao'];
		$nome_produto_tab = $res[$i]['nome_produto'];
		$sabores = $res[$i]['sabores'];
		$borda = $res[$i]['borda'];
		$categoria = $res[$i]['categoria'];
		$valor_unit = $res[$i]['valor_unitario'];

		if($valor_unit == ""){
			if($total_item > 0 and $quantidade > 0){
			$valor_unit = $total_item / $quantidade;
			}else{
				$valor_unit = 0;
			}	
		}

		$total_item = $total_item * $quantidade;
		$total_carrinho += $total_item;


		$total_itemF = number_format($total_item, 2, ',', '.');
		$valor_unitF = number_format($valor_unit, 2, ',', '.');
		$total_carrinhoF = number_format($total_carrinho, 2, ',', '.');

		$query2 = $pdo->query("SELECT * FROM variacoes where id = '$variacao'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if(@count(@$res2) > 0){
			$sigla_variacao = '('.$res2[0]['sigla'].')';			
		}else{
			$sigla_variacao = '';
		}


		$query2 = $pdo->query("SELECT * FROM produtos where id = '$produto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if(@count(@$res2) > 0){
			$nome_produto = $res2[0]['nome'];
			$foto_produto = $res2[0]['foto'];
		}else{
			$nome_produto = $nome_produto_tab;
			$foto_produto = "";
		}	
		

		if($obs == ''){
			$classe_obs = 'text-warning';
		}else{
			$classe_obs = 'text-danger';
		}

		if($sabores > 0){			
			$nome_produto = $nome_produto_tab;
		}

	

echo <<<HTML
<tr>
<td><img class="" src="../../sistema/painel/images/produtos/{$foto_produto}" width="25px"></td>
<td>{$quantidade} / {$nome_produto}</td>
<td>{$total_itemF}</td>
<td>

	<li class="dropdown head-dpdn2" style="display: inline-block;">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-trash-o text-danger"></i></big></a>

		<ul class="dropdown-menu" style="margin-left:-230px;">
		<li>
		<div class="notification_desc2">
		<p>Confirmar Exclusão? <a title="Excluir Item" href="#" onclick="excluirCarrinho('{$id}')"><span class="text-danger">Sim</span></a></p>
		</div>
		</li>										
		</ul>
		</li>




</td>
</tr>
HTML;

}

echo <<<HTML
	</tbody>
	<small><div align="center" id="mensagem-excluir"></div>	</small>
	</table>
	<div align="right"><b>Itens</b> ({$total_reg}) / <b>Total R$ </b><span style="color:green">{$total_carrinhoF}</span></div>
	</small>
HTML;


}else{
	echo 'Não possui itens lançados!';
}


 ?>


<script type="text/javascript">
	
function excluirCarrinho(id){
    $.ajax({
        url: 'paginas/' + pag + "/excluir_carrinho.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Excluído com Sucesso") {                
                listar();   
                listarCarrinho();                 
            } else {
                $('#mensagem-excluir').addClass('text-danger')
                $('#mensagem-excluir').text(mensagem)
            }

        },      

    });
}
</script>