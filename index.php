<?php require_once("cabecalho.php");

$img = 'aberto.png';

if($status_estabelecimento == "Fechado"){		
	$img = 'fechado.png';
}


$data = date('Y-m-d');
//verificar se está aberto hoje
$diasemana = array("Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado");
$diasemana_numero = date('w', strtotime($data));
$dia_procurado = $diasemana[$diasemana_numero];

//percorrer os dias da semana que ele trabalha
$query = $pdo->query("SELECT * FROM dias where dia = '$dia_procurado'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){		
	$img = 'fechado.png';
}

$hora_atual = date('H:i:s');

//nova verificação de horarios
$start = strtotime( date('Y-m-d' .$horario_abertura) );
$end = strtotime( date('Y-m-d' . $horario_fechamento) );
$now = time();

if ( $start <= $now && $now <= $end ) {
   
}else{
	$img = 'fechado.png';
}



 ?>
 <link rel="stylesheet" href="css/font-awesome.css">
        <link rel="stylesheet" href="css/templatemo_style.css">
        <link rel="stylesheet" href="css/templatemo_misc.css">
        <link rel="stylesheet" href="css/flexslider.css">
        <link rel="stylesheet" href="css/testimonails-slider.css">

        <script src="js/vendor/modernizr-2.6.1-respond-1.1.0.min.js"></script>

        <script src="js/vendor/jquery-1.11.0.min.js"></script>
        <script src="js/vendor/jquery.gmap3.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>

 <style type="text/css">

 	
.img-aberto {
  animation-duration: 2s;
  animation-name: slidein;
  opacity:0.9;
  position:fixed;
  bottom:10px;
  left:0;
  z-index:300;
}

 	@keyframes slidein {
  from {
    margin-left: 200%;
    width: 200%
  }

  to {
    margin-left: 0%;
    width: 70px;
  }
}
 </style>

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

<?php 
	if($banner_rotativo == 'Sim'){
	$query = $pdo->query("SELECT * FROM banner_rotativo");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
		if($total_reg > 0){
 ?>

			<div id="slider" style="margin-top: 50px;" class="">
                <div class="flexslider">
                  <ul class="slides">

  	<?php 	
			for($i=0; $i < $total_reg; $i++){
				foreach ($res[$i] as $key => $value){}
					$foto = $res[$i]['foto'];
				$categoria = $res[$i]['categoria'];


				$query2 = $pdo->query("SELECT * FROM categorias where id = '$categoria'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$url = 'categoria-'.$res2[0]['url'];
		}else{
			$url = '#';
		}



  if($i == 0){
    $ativo = 'active';
  }else{
    $ativo = '';
  }
 					?>
 			
    <div class="carousel-item <?php echo $ativo ?>">
    	<a href="<?php echo $url ?>">		
      <img class="d-block w-100" src="sistema/painel/images/banner_rotativo/<?php echo $foto ?>" alt="First slide" width="100%">
      </a>
    </div>

    
    <li>
     <a href="<?php echo $url ?>">	                               
       <img src="sistema/painel/images/banner_rotativo/<?php echo $foto ?>" alt="" />
        </a>
    </li>
                    
	

	<?php }  ?>
		</ul>
    </div>
</div>

<?php } } ?>




	<div class="row ocultar-mobile" style="margin-top: 25px;">
                        <div class="col-md-12">
                            <div class="heading-section">
                                <h2>Nosso Cardápio</h2>
                                <img src="images/under-heading.png" alt="" >
                            </div>
                        </div>
                    </div>
	

	 <div class="row" style="margin-bottom: 100px; margin-top: 15px">
                       

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
			

	<div class="col-md-2 col-sm-6 col-6 <?php echo $mostrar ?> ocultar-mobile" style="margin-bottom: 10px">
                            <div class="timeline-thumb">
                                <div class="thumb">
                                    <img src="sistema/painel/images/categorias/<?php echo $foto ?>" alt="">
                                </div>
                                <div class="overlay">
                                    <div class="timeline-caption">
                                        <a href="categoria-<?php echo $url ?>"><h4><?php echo $nome ?></h4></a>
                                       
                                     </div>
                                 </div>
                             </div>
                        </div>



                        <div class="col-6 <?php echo $mostrar ?> ocultar-banner-web" >
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


<img src="img/<?php echo $img ?>" width="70px" class="img-aberto">

	</body>
	</html>



