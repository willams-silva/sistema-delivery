<?php require_once("cabecalho.php") ?>


<style type="text/css">
	body{
		background:#f2f2f2;
	}
</style>

<div class="main-container">

	<nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
		<div class="container-fluid">
			<div class="navbar-brand" >
				<a href="index.php"><big><i class="bi bi-arrow-left"></i></big></a>
				<span style="margin-left: 15px">RESUMO DO PEDIDO</span>
			</div>

			<?php require_once("icone-carrinho.php") ?>

		</div>
	</nav>




	<ol class="list-group" style="margin-top: 65px">

		
		<li class="list-group-item">		    	
				<span class="nome-produto"><b>Pizza Calabresa</b></span>				
				<a href="#popup-excluir" class="link-neutro"><i class="bi bi-x-lg direita"></i></a>	


					
				<div class="carrinho-qtd">
					<a href="finalizar.php" class="link-neutro">
					<div class="menos-mais">
						-
					</div>
					</a>

					
					<div class="qtd-item-carrinho">
						1
					</div>
					

					<a href="finalizar.php" class="link-neutro">
					<div class="menos-mais">
						+
					</div>
					</a>

					
					<div class="valor-carrinho-it">
						<small><b>R$ 25,00</b></small>
					</div>
					
				</div>
		</li>
		


		<li class="list-group-item">		    	
				<span class="nome-produto"><b>Refrigerante 2L Coca Cola</b></span>				
				<a href="#popup-excluir" class="link-neutro"><i class="bi bi-x-lg direita"></i></a>
					
				<div class="carrinho-qtd">
					<a href="finalizar.php" class="link-neutro">
					<div class="menos-mais">
						-
					</div>
					</a>

					
					<div class="qtd-item-carrinho">
						1
					</div>
					

					<a href="finalizar.php" class="link-neutro">
					<div class="menos-mais">
						+
					</div>
					</a>

					
					<div class="valor-carrinho-it">
						<small><b>R$ 9,00</b></small>
					</div>
					
				</div>
		</li>




		<li class="list-group-item">		    	
				<span class="nome-produto"><b>Sanduíche Bacon Cheddar</b></span>				
				<a href="#popup-excluir" class="link-neutro"><i class="bi bi-x-lg direita"></i></a>
					
				<div class="carrinho-qtd">
					<a href="finalizar.php" class="link-neutro">
					<div class="menos-mais">
						-
					</div>
					</a>

					
					<div class="qtd-item-carrinho">
						1
					</div>
					

					<a href="finalizar.php" class="link-neutro">
					<div class="menos-mais">
						+
					</div>
					</a>

					
					<div class="valor-carrinho-it">
						<small><b>R$ 22,00</b></small>
					</div>
					
				</div>
		</li>

	</ol>


</div>


<div class="total-pedido">
	<big>
	<span><b>SUB TOTAL</b></span>
	<span class="direita">	<b>R$ 56,00</b></span>
	</big>
</div>


<div class="d-grid gap-2 mt-4 abaixo">
	<a href='#popup2' class="btn btn-primary botao-carrinho">Finalizar Pedido</a>
</div>


</body>
</html>





<div id="popup-excluir" class="overlay-excluir">
	<div class="popup">
		<div class="row">
			<div class="col-12">
				Confirmar Exclusão? <a href="" class="text-danger link-neutro">Sim</a>
			</div>
			<div class="col-3">
				<a class="close" href="#" >&times;</a>
			</div>
		</div>
		
	</div>
</div>