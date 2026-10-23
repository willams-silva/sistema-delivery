<?php require_once("cabecalho.php") ?>

<div class="main-container">

	<nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
		<div class="container-fluid">
			<div class="navbar-brand" >
				<a href="index.php"><big><i class="bi bi-arrow-left"></i></big></a>
				<span style="margin-left: 15px">PIZZA</span>
			</div>

			<?php require_once("icone-carrinho.php") ?>

		</div>
	</nav>




	<ol class="list-group " style="margin-top: 65px">

		<a href="variacoes.php" class="link-neutro">
		<li class="list-group-item d-flex justify-content-between align-items-start"> 
			<div class="me-auto">
				<div class="fw-bold titulo-item">Pizza Calabresa</div>
				<span class="valor-item">(P) R$ 25,00 / (M) R$ 30,00 / (G) R$ 35,00</span>
			</div>
			
		</li>
		</a>


		<a href="#" class="link-neutro">
		<li class="list-group-item d-flex justify-content-between align-items-start"> 
			<div class="me-auto">
				<div class="fw-bold titulo-item">Pizza Frango Catupury</div>
				<span class="valor-item">(P) R$ 25,00 / (M) R$ 30,00 / (G) R$ 35,00</span>
			</div>
			
		</li>
		</a>


		
		<a href="#" class="link-neutro">
		<li class="list-group-item d-flex justify-content-between align-items-start"> 
			<div class="me-auto">
				<div class="fw-bold titulo-item">Pizza Cheddar com Bacon</div>
				<span class="valor-item">(P) R$ 29,00 / (M) R$ 36,00 / (G) R$ 42,00</span>
			</div>
			
		</li>
		</a>

	</ol>


</div>

</body>
</html>
