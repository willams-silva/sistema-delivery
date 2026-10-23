<?php require_once("cabecalho.php") ?>

<div class="main-container">

	<nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
		<div class="container-fluid">
			<div class="navbar-brand" >
				<a href="itens.php"><big><i class="bi bi-arrow-left"></i></big></a>
				<span style="margin-left: 15px">Pizza Calabresa - Tamanho P</span>
			</div>

			<?php require_once("icone-carrinho.php") ?>

		</div>
	</nav>

	<div class="titulo-itens">
		Inserir Adicionais?
	</div>




	<ol class="list-group">

		<a href="adicionais.php" class="link-neutro">
		<li class="list-group-item">		    	
				Bacon <span class="valor-item">(R$ 5,00)</span>
				<i class="bi bi-square direita"></i>			
		</li>
		</a>


		<a href="adicionais.php" class="link-neutro">
		<li class="list-group-item">		    	
				Cheddar <span class="valor-item">(R$ 5,00)</span>
				<i class="bi bi-square direita"></i>			
		</li>
		</a>


		
		<a href="adicionais.php" class="link-neutro">
		<li class="list-group-item">		    	
				Palmito <span class="valor-item">(R$ 4,00)</span>
				<i class="bi bi-square direita"></i>			
		</li>
		</a>

	</ol>


	<div class="total">
		R$ <b>25,00</b>
	</div>



	<div class="titulo-itens-2">
		Remover Ingredientes?
	</div>


	<ol class="list-group">

		<a href="adicionais.php" class="link-neutro">
		<li class="list-group-item">		    	
				Cebola
				<i class="bi bi-check-square-fill direita"></i>			
		</li>
		</a>


		<a href="adicionais.php" class="link-neutro">
		<li class="list-group-item">		    	
				Tomate 
				<i class="bi bi-check-square-fill direita"></i>			
		</li>
		</a>


		
		<a href="adicionais.php" class="link-neutro">
		<li class="list-group-item">		    	
				Palmito 
				<i class="bi bi-check-square-fill direita"></i>			
		</li>
		</a>

	</ol>

	<div class="d-grid gap-2 mt-4">
		<a href='observacoes.php' class="btn btn-primary">Avançar</a>
	</div>



</div>

</body>
</html>
