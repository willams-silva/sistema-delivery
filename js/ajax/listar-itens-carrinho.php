<?php 
@session_start();
require_once('../../sistema/conexao.php');

$sessao = @$_SESSION['sessao_usuario'];


$query = $pdo->query("SELECT * FROM carrinho where sessao = '$sessao'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$total_carrinho = 0;
if($total_reg > 0){
	for($i=0; $i < $total_reg; $i++){
		foreach ($res[$i] as $key => $value){}	

			$id = $res[$i]['id'];
			$total_item = $res[$i]['total_item'];
			$produto = $res[$i]['produto'];
			$quantidade = $res[$i]['quantidade'];
			$valor_unit = $total_item / $quantidade;

			$total_carrinho += $total_item;


			$total_itemF = number_format($total_item, 2, ',', '.');
			$valor_unitF = number_format($valor_unit, 2, ',', '.');
			$total_carrinhoF = number_format($total_carrinho, 2, ',', '.');

				$query2 = $pdo->query("SELECT * FROM produtos where id = '$produto'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$nome_produto = $res2[0]['nome'];

echo <<<HTML

		
		<li class="list-group-item">		    	
				<span class="nome-produto"><b>{$nome_produto}</b></span>				
				<a href="#" onclick="excluir('{$id}')" class="link-neutro"><i class="bi bi-x-lg direita"></i></a>

				<div id="popup-excluir{$id}" class="overlay-excluir">
					<div class="popup">
						<div class="row">
							<div class="col-12">
								Confirmar Exclusão? <a href="#" onclick="excluirCarrinho('{$id}')" class="text-danger link-neutro">Sim</a>
							</div>
							<div class="col-3">
								<a class="close" href="#" onclick="fecharExcluir('{$id}')">&times;</a>
							</div>
						</div>
						
					</div>
				</div>	


					
				<div class="carrinho-qtd">
					<a href="#" onclick="mudarQuant('{$id}', '{$quantidade}', 'menos')" class="link-neutro">
					<div class="menos-mais">
						-
					</div>
					</a>

					
					<div class="qtd-item-carrinho">
						<span id="quant">{$quantidade}</span>
					</div>
					

					<a href="#" onclick="mudarQuant('{$id}', '{$quantidade}', 'mais')" class="link-neutro">
					<div class="menos-mais">
						+
					</div>
					</a>

					
					<div class="valor-carrinho-it">
						<small><b>R$ {$total_itemF}</b></small>
					</div>
					
				</div>


		</li>

HTML;
		
 } 

}else{
	echo "<script>window.location='index'</script>";
}


?>

<script type="text/javascript">
	 $("#total-do-pedido").text("<?=$total_carrinhoF?>");

	function mudarQuant(id, quantidade, acao){
		 $.ajax({
        url: 'js/ajax/mudar-quant-carrinho.php',
        method: 'POST',
        data: {id, quantidade, acao},
        dataType: "text",

        success: function (mensagem) {  
                  
            if (mensagem.trim() == "Alterado com Sucesso") {                
                 listarCarrinho();         
            } 

        },      

    });
	}


	function excluirCarrinho(id){
		
		 $.ajax({
        url: 'js/ajax/excluir-carrinho.php',
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {  
                  
            if (mensagem.trim() == "Excluido com Sucesso") {                
                 listarCarrinho();         
            } 

        },      

    });
	}

	function excluir(id){
		var popup = 'popup-excluir' + id;
		document.getElementById(popup).style.display = 'block';
	}

	function fecharExcluir(id){
		var popup = 'popup-excluir' + id;
		document.getElementById(popup).style.display = 'none';
	}
</script>