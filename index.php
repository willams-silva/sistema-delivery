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

		<?php 
		$query = $pdo->query("SELECT * FROM categorias where ativo = 'Sim'");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
		if($total_reg > 0){
			for($i=0; $i < $total_reg; $i++){
				foreach ($res[$i] as $key => $value){}
					$cor = $res[$i]['cor'];
				$foto = $res[$i]['foto'];
				$nome = $res[$i]['nome'];
				$url = $res[$i]['url'];
				$id = $res[$i]['id'];

				$query2 = $pdo->query("SELECT * FROM produtos where categoria = '$id' and ativo = 'Sim'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$tem_produto = @count($res2);
		$mostrar = 'ocultar';
		if($tem_produto > 0){
			for($i2=0; $i2 < $tem_produto; $i2++){
				foreach ($res2[$i2] as $key => $value){}
				
					$estoque = $res2[$i2]['estoque'];
					$tem_estoque = $res2[$i2]['tem_estoque'];	

					if(($tem_estoque == 'Sim' and $estoque > 0) or ($tem_estoque == 'Não')){
						$mostrar = '';
					}

					}
		
				}else{
					$mostrar = 'ocultar';
				}

			

				


				?>

				<div class="col-md-4 col-6 <?php echo $mostrar ?>" >
					<a class="link-card" href="categoria-<?php echo $url ?>">
						<div class="card <?php echo $cor ?> " <?php if($tipo_miniatura == 'Foto'){ ?> style="background-image: url('sistema/painel/images/categorias/<?php echo $foto ?>'); background-size: cover; border:none" <?php } ?> >
							<?php if($tipo_miniatura == 'Foto'){ ?>
								<div class="badge2"><?php echo $nome ?></div>
							<?php }else{ ?>
								<h3 class="card-title"><?php echo $nome ?></h3>
							<?php } ?>
						</div>
					</a>
				</div>

			<?php } } ?>		




			





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
				<span style="margin-left: 15px"><a target="_blank" href="<?php echo $instagram_sistema ?> " class="link-neutro"><i class="bi bi-instagram" style="color:#d11144"></i> @Instagram</a></span> 
			</div>


		</footer>




	</body>
	</html>






