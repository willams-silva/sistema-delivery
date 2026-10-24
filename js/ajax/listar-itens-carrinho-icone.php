<?php 
@session_start();
require_once('../../sistema/conexao.php');

$sessao = @$_SESSION['sessao_usuario'];


$query = $pdo->query("SELECT * FROM carrinho where sessao = '$sessao'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$total_carrinho = 0;
$total_carrinhoF = 0;
if($total_reg > 0){
	for($i=0; $i < $total_reg; $i++){
		foreach ($res[$i] as $key => $value){}	

			$id = $res[$i]['id'];
		$total_item = $res[$i]['total_item'];
		$produto = $res[$i]['produto'];
		$quantidade = $res[$i]['quantidade'];
		$obs = $res[$i]['obs'];
		$valor_unit = $total_item / $quantidade;

		$total_carrinho += $total_item;


		$total_itemF = number_format($total_item, 2, ',', '.');
		$valor_unitF = number_format($valor_unit, 2, ',', '.');
		$total_carrinhoF = number_format($total_carrinho, 2, ',', '.');

		$query2 = $pdo->query("SELECT * FROM produtos where id = '$produto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$nome_produto = $res2[0]['nome'];
		$foto_produto = $res2[0]['foto'];

		if($obs == ''){
			$classe_obs = 'text-warning';
		}else{
			$classe_obs = 'text-danger';
		}

echo <<<HTML

		
		<li class="list-group-item" style="padding:5px">
		<img src="sistema/painel/images/produtos/{$foto_produto}" width="30px">		    	
		<span class="nome-produto"><b><small>{$quantidade} {$nome_produto} <span class="text-success">R$ ($valor_unitF)</span></small></b></span>

			
		<a href="#popup1" onclick="excluirCarrinhoIcone('{$id}')" class="link-neutro"><i class="bi bi-x-lg direita"></i></a>		


			</li>

HTML;

		} 

	}else{
		echo 'Nenhum item adicionado!';
	}


	?>

	<script type="text/javascript">
		$("#total-carrinho-icone").text("<?=$total_carrinhoF?>");
		$("#total-itens-carrinho").text("<?=$total_reg?>");
		$("#total-carrinho-finalizar").text("<?=$total_carrinhoF?>");
		
		
		function excluirCarrinhoIcone(id){

			$.ajax({
				url: 'js/ajax/excluir-carrinho.php',
				method: 'POST',
				data: {id},
				dataType: "text",

				success: function (mensagem) {  

					if (mensagem.trim() == "Excluido com Sucesso") {                
						listarCarrinhoIcone();         
					} 

				},      

			});
		}

		

		
	</script>