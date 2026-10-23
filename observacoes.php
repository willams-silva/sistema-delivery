<?php
@session_start(); 
require_once("cabecalho.php");
$url_completa = $_GET['url'];

$sessao = $_SESSION['sessao_usuario'];
$query = $pdo->query("SELECT * FROM carrinho where sessao = '$sessao'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	$id_cliente = $res[0]['cliente'];

	$query = $pdo->query("SELECT * FROM clientes where id = '$id_cliente'");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$nome_cliente = $res[0]['nome'];
	$telefone_cliente = $res[0]['telefone'];
}

$separar_url = explode("_", $url_completa);
$url = $separar_url[0]; 
$item = @$separar_url[1];


$query = $pdo->query("SELECT * FROM produtos where url = '$url'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	$nome = $res[0]['nome'];
	$descricao = $res[0]['descricao'];
	$foto = $res[0]['foto'];
	$id_prod = $res[0]['id'];
	$valor = $res[0]['valor_venda'];
	$valorF = number_format($valor, 2, ',', '.');
	$categoria = $res[0]['categoria'];
}

if($item == ""){
	$valor_item = $valor;
}else{
	$query = $pdo->query("SELECT * FROM variacoes where produto = '$id_prod' and nome = '$item'");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$valor_item = $res[0]['valor'];

}

$query =$pdo->query("SELECT * FROM temp where sessao = '$sessao' and tabela = 'adicionais' and carrinho = '0'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	for($i=0; $i < $total_reg; $i++){
		foreach ($res[$i] as $key => $value){}
		$id_adc = $res[$i]['id_item'];

		$query2 =$pdo->query("SELECT * FROM adicionais where id = '$id_adc'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$valor_adc = $res2[0]['valor'];
		$valor_item += $valor_adc;	

}
}

$valor_itemF = number_format($valor_item, 2, ',', '.');
?>

<style type="text/css">
	body{
		background:#f2f2f2;
	}
</style>


<div class="main-container" style="background:#fff;">

	<nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
		<div class="container-fluid">
			<div class="navbar-brand" >
				<a href="adicionais-<?php echo $url_completa ?>"><big><i class="bi bi-arrow-left"></i></big></a>
				<span style="margin-left: 15px">Resumo do Item</span>
			</div>

			<?php require_once("icone-carrinho.php") ?>

		</div>
	</nav>



	<div class="destaque">
		<b><?php echo mb_strtoupper($nome); ?></b>
	</div>


	<div class="destaque-qtd">
		<b>QUANTIDADE</b>
		<span class="direita">
			<big>
				<span><a href="#" onclick="diminuirQuant()"><i class="bi bi-dash-circle-fill text-danger"></i></a></span>
				<span> <b><span id="quant"></span></b> </span>
				<span><a href="#" onclick="aumentarQuant()"><i class="bi bi-plus-circle-fill text-success"></i></a></span>
			</big>
		</span>
	</div>

	<input type="hidden" id="quantidade" value="1">
	<input type="hidden" id="total_item_input" value="<?php echo $valor_item ?>">


	<div class="destaque-qtd">
		<b>OBSERVAÇÕES</b>
		<div class="form-group mt-3">
			<textarea maxlength="255" class="form-control" type="text" name="obs" id="obs"></textarea>
		</div>
	</div>

</div>


<div class="total-pedido">
	<span><b>TOTAL</b></span>
	<span class="direita">	<b>R$ <span id="total_item"><?php echo $valor_itemF ?></span></b></span>
</div>


<div class="d-grid gap-2 mt-4 abaixo">
	<a href='#popup2' class="btn btn-primary botao-carrinho">Adicionar ao Carrinho</a>
</div>


</body>
</html>



<div id="popup2" class="overlay2">
	<div class="popup2">
		<div class="row">
			<div class="col-12">
				<div class="card-add-carrinho">
					<a class="close" href="#">&times;</a>


					<form action="carrinho.php" method="post">						     
						<div class="row">								
							<div class="col-6"> 
								<div class="group">
									<input onkeyup="buscarNome()" type="text" class="input" name="telefone" id="telefone" required value="<?php echo @$telefone_cliente ?>">
									<span class="highlight"></span>
									<span class="bar"></span>
									<label class="label">Telefone</label>
								</div>
							</div>


							<div class="col-6"> 
								<div class="group">
									<input onclick="buscarNome()" type="text" class="input" name="nome" id="nome" required value="<?php echo @$nome_cliente ?>">
									<span class="highlight"></span>
									<span class="bar"></span>
									<label class="label">Nome</label>
								</div>
							</div>
						</div>

						<div class="row" align="center">								
							<div class="col-6"> 
								<a href="#" onclick="comprarMais()" class="btn btn-primary" style="width:100%">Comprar Mais</a>
							</div>

							<div class="col-6"> 
								<a href="#" onclick="finalizarPedido()" class="btn btn-success" style="width:100%">Finalizar Pedido</a>
							</div>

						</div>

						<br>
						<div id="mensagem" align="center"></div>

						
					</form>
				</div>
			</div>

		</div>
		
	</div>
</div>


<!-- jQery -->
<script src="js/jquery-3.4.1.min.js"></script>

<!-- Mascaras JS -->
<script type="text/javascript" src="js/mascaras.js"></script>

<!-- Ajax para funcionar Mascaras JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script> 


<script type="text/javascript">
	$(document).ready(function() {    		
		var quant = $("#quantidade").val();		
		$("#quant").text(quant);
		
	} );

	function aumentarQuant(){
		var quant = $("#quantidade").val();
		var novo_valor = parseInt(quant) + parseInt(1);
		$("#quant").text(novo_valor)
		$("#quantidade").val(novo_valor);

		
		var total_quant = parseInt(quant) + parseInt(1);
		var total_inicial = '<?=$valor_item?>';		
		var total_it = parseFloat(total_inicial) * parseFloat(total_quant);
		$("#total_item").text(total_it.toFixed(2));
		$("#total_item_input").val(total_it);
	}

	function diminuirQuant(){
		var quant = $("#quantidade").val();
		if(quant > 1){
			var novo_valor = parseInt(quant) - parseInt(1);
			$("#quant").text(novo_valor)
			$("#quantidade").val(novo_valor)

		var total_quant = parseInt(quant) - parseInt(1);
		var total_inicial = '<?=$valor_item?>';		
		var total_it = parseFloat(total_inicial) * parseFloat(total_quant);
		$("#total_item").text(total_it.toFixed(2));
		$("#total_item_input").val(total_it);
		}

	}
	
</script>





<script type="text/javascript">
	
	function buscarNome(){

		var tel = $('#telefone').val();	
				
		$.ajax({
			url: 'js/ajax/listar-nome.php',
			method: 'POST',
			data: {tel},
			dataType: "text",

			success:function(result){	

				$('#nome').val(result);	
			}
		});	
	}




	function comprarMais(){
		var telefone = $('#telefone').val();
		var nome = $('#nome').val();
		if(telefone == ''){
			alert('Preencha o Telefone!');
			return;
		}
		if(nome == ''){
			alert('Preencha o Nome!');
			return;
		}
		addCarrinho();
		setTimeout(redirecionar, 1000);
	}


	function finalizarPedido(){
		var telefone = $('#telefone').val();
		var nome = $('#nome').val();
		if(telefone == ''){
			alert('Preencha o Telefone!');
			return;
		}
		if(nome == ''){
			alert('Preencha o Nome!');
			return;
		}
		addCarrinho();
		setTimeout(redirecionarCarrinho, 1000);
	}

	function redirecionar(){
		 window.location='index';
	}


	function redirecionarCarrinho(){
		 window.location='carrinho';
	}


	function addCarrinho(){
		var telefone = $('#telefone').val();
		var nome = $('#nome').val();
		var quantidade = $('#quantidade').val();
		var total_item = $('#total_item_input').val();
		var produto = "<?=$id_prod?>";
		var obs = $('#obs').val();

		 $.ajax({
        url: 'js/ajax/add-carrinho.php',
        method: 'POST',
        data: {telefone, nome, quantidade, total_item, produto, obs},
        dataType: "text",

        success: function (mensagem) {  
                  
            if (mensagem.trim() == "Inserido com Sucesso") {                
                          
            } 

        },      

    });
	}
</script>