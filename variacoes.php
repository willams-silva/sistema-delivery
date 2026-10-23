<?php require_once("cabecalho.php") ?>

<div class="main-container">

	<nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
		<div class="container-fluid">
			<div class="navbar-brand" >
				<a href="itens.php"><big><i class="bi bi-arrow-left"></i></big></a>
				<span style="margin-left: 15px">PIZZA CALABRESA</span>
			</div>

			<?php require_once("icone-carrinho.php") ?>

		</div>
	</nav>




	<ol class="list-group " style="margin-top: 65px">

		<a href="adicionais.php" class="link-neutro">
		<li class="list-group-item d-flex justify-content-between align-items-start"> 
			<div class="me-auto">
				<div class="fw-bold titulo-item">Pequena</div>
				<span class="valor-item">R$ 25,00</span>
			</div>
			
		</li>
		</a>


		<a href="#" class="link-neutro">
		<li class="list-group-item d-flex justify-content-between align-items-start"> 
			<div class="me-auto">
				<div class="fw-bold titulo-item">Média</div>
				<span class="valor-item">R$ 30,00</span>
			</div>
			
		</li>
		</a>


		
		<a href="#" class="link-neutro">
		<li class="list-group-item d-flex justify-content-between align-items-start"> 
			<div class="me-auto">
				<div class="fw-bold titulo-item">Grande</div>
				<span class="valor-item">R$ 35,00</span>
			</div>
			
		</li>
		</a>

	</ol>

	<hr>
	<div class="conteudo-descricao-item">
	<div class="titulo-descricao-item"><b>Descrição Pizza Calabresa</b></div>
	<p class="descricao-item">Pizza de Calabresa com borda recheada com cheddar, massa fina, bem recheada, tendo como ingredientes tomate, cebola, palmito, calabresa defumada e queijo.</p>
	</div>
	<div >
		<img class="imagem-produto" src="img/produtos/calabresa.jpg">
	</div>

</div>

</body>
</html>
