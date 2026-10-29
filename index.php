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

  if($end < $start){

    if($now > $start){
     
    }else{
       if($now < $end){
        
       }else{
         $img = 'fechado.png';
       }
     
    }
  }else{
    $img = 'fechado.png';
  }
	
}



 ?>
 <link rel="stylesheet" href="css/font-awesome.css">
        <link rel="stylesheet" href="css/templatemo_style.css">
        <link rel="stylesheet" href="css/templatemo_misc.css">
        <link rel="stylesheet" href="css/flexslider.css">
        <link rel="stylesheet" href="css/testimonails-slider.css">

        <link rel="stylesheet" href="css/style_cards_index.css">

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

    <div id="slider" style="margin-top: 50px;" class="ocultar-banner-web">

<?php 
	if($banner_rotativo == 'Sim'){
	$query = $pdo->query("SELECT * FROM banner_rotativo");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
		if($total_reg > 0){
 ?>

		
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


<?php } } ?>

</div>

	 <!-- Menu Start -->
        <div class="container-xxl py-5 margin_top_web" style="margin-bottom: 15px">
            <div class="margem_container">
                <div class="text-center wow fadeInUp ocultar-mobile" data-wow-delay="0.1s"  style="margin-bottom: 15px">
                    <h5 class="section-title ff-secondary text-center text-primary fw-normal"><span style="color:#FEA116">Nosso Cardápio</span></h5>
                   
                </div>
                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">

                	 <ul class="nav nav-pills d-inline-flex justify-content-center border-bottom mb-5" style="width:100%;">
                        <li class="nav-item" style="width:34%;">
                            <a class="d-flex align-items-center text-start mx-3 ms-0 pb-3 active" data-bs-toggle="pill" href="#tab-1">
                                <img class="icone_mobile" src="img/acai.png" width="50px" height="50px">
                                <div class="ps-1">
                                    <small class="text-body titulo_icones">Categorias</small>
                                    <h6 class="mt-n1 mb-0 subtitulo_icones" style="font-size: 12px">Produtos</h6>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item" style="width:34%;">
                            <a class="d-flex align-items-center text-start mx-3 pb-3" data-bs-toggle="pill" href="#tab-2">
                                 <img class="icone_mobile" src="img/acai2.png" width="50px" height="50px">
                                <div class="ps-1">
                                    <small class="text-body titulo_icones">Combos</small>
                                    <h6 class="mt-n1 mb-0 subtitulo_icones" style="font-size: 12px">Diversos</h6>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item" style="width:32%;">
                            <a  class="d-flex align-items-center text-start mx-3 me-0 pb-3" data-bs-toggle="pill" href="#tab-3">
                                <img class="icone_mobile" src="img/icone3.png" width="50px" height="50px">
                                <div class="ps-1">
                                    <small class="text-body titulo_icones">Promoções</small>
                                    <h6 class="mt-n1 mb-0 subtitulo_icones" style="font-size: 12px">Ofertas</h6>
                                </div>
                            </a>
                        </li>
                    </ul>
                  
                    <div class="tab-content">
                      <div id="tab-1" class="tab-pane fade show p-0 active">
                            <div class="row g-4">

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
				$descricao = $res[$i]['descricao'];
				$url = $res[$i]['url'];
				$id = $res[$i]['id'];
        $mais_sabores = $res[$i]['mais_sabores'];

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
			
								
                                <div class="col-md-6">
                                  <?php if($mais_sabores == 'Sim'){ ?>
                                	<a href="categoria-sabores-<?php echo $url ?>">
                                  <?php }else{ ?>
                                    <a href="categoria-<?php echo $url ?>">
                                  <?php } ?>
                                    <div class="d-flex align-items-center">
                                        <img class="flex-shrink-0 img-fluid " src="sistema/painel/images/categorias/<?php echo $foto ?>" alt="" style="width: 80px;">
                                        <div class="w-100 d-flex flex-column text-start ps-4">
                                            <h5 class="d-flex justify-content-between border-bottom pb-2">
                                                <span class="titulo_itens" style="color:#2d2e2e; "><?php echo $nome ?></span>
                                                <span class="text-primary"></span>
                                            </h5>
                                            <small class="fst-italic subtitulo_itens" style="color:#474747"><?php echo $descricao ?></small>
                                        </div>
                                    </div>
                                     </a>
                                </div>
                          		 

                                <?php } } ?>		
                                
                            </div>
                        </div>


                         <div id="tab-2" class="tab-pane fade show p-0" >
                         	   <div class="row g-4" >

                              <?php 
    $query = $pdo->query("SELECT * FROM produtos where ativo = 'Sim' and combo = 'Sim'");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $tem_produto = @count($res);
    $mostrar = 'ocultar';
    if($tem_produto > 0){
      for($i=0; $i < $tem_produto; $i++){
        foreach ($res[$i] as $key => $value){}     
        $id_prod = $res[$i]['id']; 
        $foto = $res[$i]['foto'];
        $nome = $res[$i]['nome'];
        $descricao = $res[$i]['descricao'];
        $url = $res[$i]['url'];
        $estoque = $res[$i]['estoque'];
        $tem_estoque = $res[$i]['tem_estoque'];
        $valor = $res[$i]['valor_venda'];
        $valorF = number_format($valor, 2, ',', '.');

        $descricaoF = mb_strimwidth($descricao, 0, 70, "...");

         if($tem_estoque == 'Sim' and $estoque <= 0){
         continue;         
        }else{
          
          $url_produto = 'produto-'.$url;
        }


      

      //verificar se o produto tem adicionais
$query3 = $pdo->query("SELECT * FROM grades where produto = '$id_prod'");
$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
$total_adc = @count($res3);
      if($total_adc > 0){
        if($tem_estoque == 'Sim' and $estoque <= 0){
          $url_produto = '#';
        }else{
          $url_produto = 'adicionais-'.$url;
        }
        
      }else{
        if($tem_estoque == 'Sim' and $estoque <= 0){
          $url_produto = '#';
        }else{
          $url_produto = 'observacoes-'.$url;
        }
        
      }
      
    
         
   
        ?>
      
                
                                <div class="col-md-6">
                                  <a href="<?php echo $url_produto ?>">
                                    <div class="d-flex align-items-center">
                                        <img class="flex-shrink-0 img-fluid " src="sistema/painel/images/produtos/<?php echo $foto ?>" alt="" style="width: 70px;">
                                        <div class="w-100 d-flex flex-column text-start ps-4">
                                            <span class="d-flex justify-content-between border-bottom pb-2">
                                                <span class="titulo_itens" style="color:#2d2e2e; "><?php echo $nome ?></span>
                                                <span class="text-primary valor_itens"><span class="ocultar-mobile">R$ </span><?php echo $valorF ?></span>
                                            </span>
                                            <small class="fst-italic subtitulo_itens" style="color:#474747"><?php echo $descricaoF ?></small>
                                        </div>
                                    </div>
                                     </a>
                                </div>
                               

                                <?php } } ?>    
                                
                            </div>
                         </div>


                          <div id="tab-3" class="tab-pane fade show p-0">
                         	     <div class="row g-4" >

                              <?php 
    $query = $pdo->query("SELECT * FROM produtos where ativo = 'Sim' and promocao = 'Sim'");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $tem_produto = @count($res);
    $mostrar = 'ocultar';
    if($tem_produto > 0){
      for($i=0; $i < $tem_produto; $i++){
        foreach ($res[$i] as $key => $value){}     
        $id_prod = $res[$i]['id']; 
        $foto = $res[$i]['foto'];
        $nome = $res[$i]['nome'];
        $descricao = $res[$i]['descricao'];
        $url = $res[$i]['url'];
        $estoque = $res[$i]['estoque'];
        $tem_estoque = $res[$i]['tem_estoque'];
        $valor = $res[$i]['valor_venda'];
        $valorF = number_format($valor, 2, ',', '.');

        $descricaoF = mb_strimwidth($descricao, 0, 70, "...");

       
        if($tem_estoque == 'Sim' and $estoque <= 0){
         continue;         
        }else{
          
          $url_produto = 'produto-'.$url;
        }

 

      //verificar se o produto tem adicionais
$query3 = $pdo->query("SELECT * FROM grades where produto = '$id_prod'");
$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
$total_adc = @count($res3);
      if($total_adc > 0){
        if($tem_estoque == 'Sim' and $estoque <= 0){
          $url_produto = '#';
        }else{
          $url_produto = 'adicionais-'.$url;
        }
        
      }else{
        if($tem_estoque == 'Sim' and $estoque <= 0){
          $url_produto = '#';
        }else{
          $url_produto = 'observacoes-'.$url;
        }
        
      }
      
    
         
   
        ?>
      
                
                                <div class="col-md-6">
                                  <a href="<?php echo $url_produto ?>">
                                    <div class="d-flex align-items-center">
                                        <img class="flex-shrink-0 img-fluid " src="sistema/painel/images/produtos/<?php echo $foto ?>" alt="" style="width: 70px;">
                                        <div class="w-100 d-flex flex-column text-start ps-4">
                                            <span class="d-flex justify-content-between border-bottom pb-2">
                                                <span class="titulo_itens" style="color:#2d2e2e; "><?php echo $nome ?></span>
                                                <span class="text-primary valor_itens"><span class="ocultar-mobile">R$ </span><?php echo $valorF ?></span>
                                            </span>
                                            <small class="fst-italic subtitulo_itens" style="color:#474747"><?php echo $descricaoF ?></small>
                                        </div>
                                    </div>
                                     </a>
                                </div>
                               

                                <?php } } ?>    
                                
                            </div>
                         </div>
                     
                    </div>
                </div>
            </div>
        </div>
        <!-- Menu End -->


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

        
        <span class="ocultar-mobile" style="margin-left: 15px"> / <a href="sistema" class="link-neutro "><i class="bi bi-lock" style="color:red"></i> Painel Sistema</a></span>
			</div>


		</footer>

<?php if($img == "aberto.png" and $mostrar_aberto != "Sim"){ 

}else{ ?>
  <img src="img/<?php echo $img ?>" width="70px" class="img-aberto">
<?php } ?>


	</body>
	</html>



