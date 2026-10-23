<?php require_once("cabecalho.php") ?>

	<div class="main-container">

		<nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
			<div class="container-fluid">
				<a class="navbar-brand" href="index.php">
					<img src="img/<?php echo $logo_sistema ?>" alt="" width="30" height="30" class="d-inline-block align-text-top">
					<?php echo $nome_sistema ?>
				</a>

				<?php require_once("icone-carrinho.php") ?>

			</div>
		</nav>


		<div class="row cards">
			<div class="col-md-4 col-6">
			<a class="link-card" href="itens.php">
				<div class="card azul ">
					<h3 class="card-title">PIZZAS</h3>
				</div>
			</a>
			</div>

			<div class="col-md-4 col-6">
			<a class="link-card" href="#">
				<div class="card rosa ">
					<h3 class="card-title">PASTÉIS</h3>
				</div>
			</a>
			</div>

			<div class="col-md-4 col-6">
			<a class="link-card" href="#">
				<div class="card azul-escuro ">
					<h3 class="card-title">SANDUÍCHES</h3>
				</div>
			</a>
			</div>

			<div class="col-md-4 col-6">
			<a class="link-card" href="#">
				<div class="card verde ">
					<h3 class="card-title">HOT DOG</h3>
				</div>
			</a>
			</div>

			<div class="col-md-4 col-6">
			<a class="link-card" href="#">
				<div class="card roxo ">
					<h3 class="card-title">BEBIDAS</h3>
				</div>
			</a>
			</div>

			<div class="col-md-4 col-6">
			<a class="link-card" href="#">
				<div class="card vermelho ">
					<h3 class="card-title">SOBREMESAS</h3>
				</div>
			</a>
			</div>
		</div>


	</div>


	<footer class="rodape">	
		<div class="row">
		<div class="col-md-6">	
		<?php if($endereco_sistema == ""){ ?>	
			<span class="ocultar-mobile"><?php echo $nome_sistema ?></span> 
		<?php }else{ ?>
		<span><?php echo $endereco_sistema ?></span> 
		<?php } ?>
		</div>
		<div class="col-md-6">	
		<span style="margin-left: 15px"><a target="_blank" href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $whatsapp_sistema ?>" class="link-neutro"><i class="bi bi-whatsapp text-success"></i> <?php echo $telefone_sistema ?></a></span> 
		/
		<span style="margin-left: 15px"><a target="_blank" href="<?php echo $instagram_sistema ?> " class="link-neutro"><i class="bi bi-instagram text-primary"></i> @Instagram</a></span> 
	</div>
		

	</footer>




</body>
</html>






