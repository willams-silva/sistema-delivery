<!DOCTYPE html>
<html>
<head>
	<title></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>


<div class="main-container">
 
 <nav class="navbar bg-light fixed-top">
  <div class="container-fluid">
    <span class="navbar-brand">
      <a class="link-neutro" href="index.php">
      <img src="img/logo.png" alt="" width="30" height="30" class="d-inline-block align-text-top">
    </a>
      Finalizar Compra
    </span>

    <a class="text-dark" href="#popup1">
    <div class="d-flex">
       <big><span class="bi bi-cart-fill">
         <small><small><span class="position-absolute top-3 start-90 translate-middle badge rounded-pill bg-danger">
    0
    
  </span></small></small>
  </span></big> 
      </div>
      </a>

  </div>
</nav>

<div class="accordion" id="accordionExample" style="margin-top: 55px">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingOne">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        1 - IDENTIFICAÇÃO
      </button>
    </h2>
    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
      <div class="accordion-body" align="center">
       <img src="img/user.png" width="50px" height="50px" >
       <div class="nome_user"> Hugo Vasconcelos </div>
       <div class="telefone_user"> (31) 9 7527-5084 </div>
       <hr>
       <div><b>Seus Dados estão corretos?</b></div>
       <hr>
       <div class="row">
       	<div class="col-6">
       <a class="btn btn-danger botao_nao">NÃO</a>
       
    </div>

    	<div class="col-6">
       
        <a class="btn btn-success botao_sim" data-bs-toggle="collapse" data-bs-target="#collapseTwo">SIM</a>
    </div>
</div>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingTwo">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
       2 - MODO DE ENTREGA
      </button>
    </h2>
    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
      <div class="accordion-body">
        <ul class="list-group form-check">
		  <li class="list-group-item d-flex justify-content-between align-items-center">
		    Retirar no Local
		    <input class="form-check-input" type="radio" name="radio1" id="radio_retirar">
		  </li>
		  <li class="list-group-item d-flex justify-content-between align-items-center">
		  	Consumir no Local
		    <input class="form-check-input" type="radio" name="radio1" id="radio_local">
		  </li>
		  <li class="list-group-item d-flex justify-content-between align-items-center">
		  	Entrega Delivery
		    <input class="form-check-input" type="radio" name="radio1" id="radio_entrega">
		  </li>
		</ul>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingThree">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
        3 - ENDEREÇO OU UNIDADE DE RETIRADA
      </button>
    </h2>
    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
      <div class="accordion-body">

      	<a href="" class="" data-bs-toggle="collapse" data-bs-target="#collapse4" style="text-decoration: none; color:#000">
        <div>
        	<b>Unidade de Retirada: </b><br>
        	Rua X Número 20 Bairro Santa Monica
        	<i class="bi bi-check-lg"></i>
        </div>
        </a>


        <div>

          <div class="row">
        <div class="col-8"> 
                  <div class="group">
                  <input type="text" class="input" name="rua" required>
                  <span class="highlight"></span>
                  <span class="bar"></span>
                  <label class="label">Rua</label>
                </div>
              </div>
       

        <div class="col-4"> 
                  <div class="group">
                  <input type="text" class="input" name="numero" required>
                  <span class="highlight"></span>
                  <span class="bar"></span>
                  <label class="label">Número</label>
                </div>
              </div>
        </div>

      </div>

      </div>
    </div>
  </div>
   <div class="accordion-item">
    <h2 class="accordion-header" id="heading4">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
        4 - ENDEREÇO OU UNIDADE DE RETIRADA
      </button>
    </h2>
    <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="heading4" data-bs-parent="#accordionExample">
      <div class="accordion-body">
      	<a href="" class="" data-bs-toggle="collapse" data-bs-target="#collapseTwo" style="text-decoration: none; color:#000">
        <div>
        	<b>Unidade de Retirada: </b><br>
        	Rua X Número 20 Bairro Santa Monica
        	<i class="bi bi-check-lg"></i>
        </div>
        </a>
      </div>
    </div>
  </div>
</div>


</div>

</body>
</html>