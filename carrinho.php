<?php
@session_start(); 
require_once("cabecalho.php");

$sessao = $_SESSION['sessao_usuario'];

?>

<style type="text/css">
	body{
		background:#f2f2f2;
	}
</style>

<div class="main-container">

	<nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
		<div class="container-fluid">
			<div class="navbar-brand" >
				<a href="index"><big><i class="bi bi-arrow-left"></i></big></a>
				<span style="margin-left: 15px">RESUMO DO PEDIDO</span>
			</div>

			<?php require_once("icone-carrinho.php") ?>

		</div>
	</nav>




	<ol class="list-group" style="margin-top: 65px; margin-bottom: 95px; overflow: scroll; height:100%; scrollbar-width: thin;" id="listar-itens-carrinho">

		

	</ol>



</div>

<div class="area-pedidos">
	<div class="total-pedido">
		<big>
		<span><b>SUB TOTAL</b></span>
		<span class="direita">	<b>R$ <span id="total-do-pedido"></span></b></span>
		</big>
	</div>


	<div class="d-grid gap-2 mt-4 abaixo">
		<a href='#popup2' class="btn btn-primary botao-carrinho">Finalizar Pedido</a>
	</div>
</div>


</body>
</html>




<script type="text/javascript">

	$(document).ready(function() {    		
    listarCarrinho()    
} );
	
function listarCarrinho(){
	
    $.ajax({
         url: 'js/ajax/listar-itens-carrinho.php',
        method: 'POST',
        data: {},
        dataType: "html",

        success:function(result){
            $("#listar-itens-carrinho").html(result);
           
        }
    });
}

</script>