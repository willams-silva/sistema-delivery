<?php 
@session_start();
require_once('../../sistema/conexao.php');

$sessao = @$_SESSION['sessao_usuario'];

$nome_produto2 = '';
$query = $pdo->query("SELECT * FROM carrinho where sessao = '$sessao'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$total_carrinho = 0;
if($total_reg > 0){
	for($i=0; $i < $total_reg; $i++){
		foreach ($res[$i] as $key => $value){}	

			$id = $res[$i]['id'];
		$total_item = $res[$i]['total_item'];
		$produto = $res[$i]['produto'];
		$quantidade = $res[$i]['quantidade'];
		$obs = $res[$i]['obs'];
		$item = $res[$i]['item'];
		$variacao = $res[$i]['variacao'];
		$nome_produto_tab = $res[$i]['nome_produto'];
		$sabores = $res[$i]['sabores'];
		$borda = $res[$i]['borda'];
		$categoria = $res[$i]['categoria'];
		if($total_item > 0 and $quantidade > 0){
			$valor_unit = $total_item / $quantidade;
		}else{
			$valor_unit = 0;
		}
		
		$id_sabor = $res[$i]['id_sabor'];

		$total_carrinho += $total_item;


		$total_itemF = number_format($total_item, 2, ',', '.');
		$valor_unitF = number_format($valor_unit, 2, ',', '.');
		$total_carrinhoF = number_format($total_carrinho, 2, ',', '.');

		
		$query2 = $pdo->query("SELECT * FROM variacoes where id = '$variacao'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if(@count(@$res2) > 0){
			$sigla_variacao = '('.$res2[0]['sigla'].')';			
		}else{
			$sigla_variacao = '';
		}

		$query2 = $pdo->query("SELECT * FROM produtos where id = '$produto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if(@count(@$res2) > 0){
			$nome_produto = $res2[0]['nome'];
			$foto_produto = $res2[0]['foto'];
		}else{
			$nome_produto = $nome_produto_tab;
			$foto_produto = "";
		}		

		if($obs == ''){
			$classe_obs = 'text-warning';
		}else{
			$classe_obs = 'text-danger';
		}

		$classe_borda = 'ocultar';
		if($sabores > 0){			
			$nome_produto = $nome_produto_tab;
			$classe_borda = '';
		}


		$query8 =$pdo->query("SELECT * FROM bordas where id = '$borda'");
		$res8 = $query8->fetchAll(PDO::FETCH_ASSOC);
		$total_reg8 = @count($res8);
		if($total_reg8 > 0){
		$nome_borda = ' - '.$res8[0]['nome'];
		}else{
			$nome_borda = '';
		}




echo <<<HTML

		
		<li class="list-group-item">
		<img class="" src="sistema/painel/images/produtos/{$foto_produto}" width="30px">		    	
		<span class="nome-produto"><b>{$nome_produto} {$sigla_variacao}</b></span>
		<span class="{$classe_borda}" style="font-size:11px; color:#450703"><b> {$nome_borda}</b></span>

HTML;

$query2 = $pdo->query("SELECT * FROM temp where carrinho = '$id' and tabela = 'ingredientes'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){

		for($i2=0; $i2 < $total_reg2; $i2++){
			foreach ($res2[$i2] as $key => $value){}
				$id_item = $res2[$i2]['id_item'];

			$query3 =$pdo->query("SELECT * FROM ingredientes where id = '$id_item'");
			$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
			$nome_ingrediente = 'Sem '.$res3[0]['nome'];
			if($i2 < ($total_reg2 - 1) ){
				$nome_ingrediente = $nome_ingrediente. ', ';
			}

echo '<span class="text-danger ingredientes">'.$nome_ingrediente.'</span>';

}
}


$query5 = $pdo->query("SELECT * FROM temp where carrinho = '$id' and tabela = 'adicionais'");
		$res5 = $query5->fetchAll(PDO::FETCH_ASSOC);
		$total_reg5 = @count($res5);
		if($total_reg5 > 0){
			$classe_adc = '';
		}else{
			$classe_adc = 'ocultar';
		}

echo <<<HTML


			
			<a href="#" onclick="excluir('{$id}')" class="link-neutro"><i class="bi bi-x-lg direita"></i></a>

			<div id="popup-excluir{$id}" class="overlay-excluir">
			<div class="popup">
			<div class="row">
			<div class="col-12">
			Confirmar Exclusão? <a href="#" onclick="excluirCarrinho('{$id}', '{$id_sabor}')" class="text-danger link-neutro">Sim</a>
			</div>
			<div class="col-3">
			<a class="close" href="#" onclick="fecharExcluir('{$id}')">&times;</a>
			</div>
			</div>

			</div>
			</div>	


			
			<div class="carrinho-qtd">

			<div class="itens-carrinho-qtd">
				<a title="Observações do item" class="link-neutro" href="#" onclick="obs('{$nome_produto}', '{$obs}', '{$id}')"><i class="bi bi-card-text {$classe_obs}"></i></a>
			</div>

			<div class="itens-carrinho-qtd-adc {$classe_adc}">
				<a title="Ver Adicionais" class="link-neutro" href="#" onclick="adicionais('{$nome_produto}', '{$id}', '{$id_sabor}', '{$categoria}')"><i class="bi  bi-plus text-primary"></i><small><small>Adicionais</small></small></a>
			</div>

			<a href="#" onclick="mudarQuant('{$id}', '{$quantidade}', 'menos')" class="link-neutro">
			<div class="menos-mais">
			-
			</div>
			</a>


			<div class="qtd-item-carrinho">
			<span id="quant">{$quantidade}</span>
			</div>


			<a href="#" onclick="mudarQuant('{$id}', '{$quantidade}', 'mais')" class="link-neutro">
			<div class="menos-mais">
			+
			</div>
			</a>


			<div class="valor-carrinho-it">
			<small><b>R$ {$total_itemF}</b></small>
			</div>

			</div>


			</li>

HTML;

		} 

	}else{
		echo "<script>window.location='index'</script>";
	}


	?>

	<script type="text/javascript">
		$("#total-do-pedido").text("<?=$total_carrinhoF?>");

		function mudarQuant(id, quantidade, acao){
			if(acao == 'menos' && quantidade == 1){
				excluirCarrinho(id);
			}
			$.ajax({
				url: 'js/ajax/mudar-quant-carrinho.php',
				method: 'POST',
				data: {id, quantidade, acao},
				dataType: "text",

				success: function (mensagem) {  

					if (mensagem.trim() == "Alterado com Sucesso") {                
						listarCarrinho();         
					}else{
						alert(mensagem);
						listarCarrinho();
					} 

				},      

			});
		}


		function excluirCarrinho(id, id_sabor){

			$.ajax({
				url: 'js/ajax/excluir-carrinho.php',
				method: 'POST',
				data: {id, id_sabor},
				dataType: "text",

				success: function (mensagem) {  
					
					if (mensagem.trim() == "Excluido com Sucesso") {                
						listarCarrinho();         
					} 

				},      

			});
		}

		function excluir(id){
			var popup = 'popup-excluir' + id;
			document.getElementById(popup).style.display = 'block';
		}

		function fecharExcluir(id){
			var popup = 'popup-excluir' + id;
			document.getElementById(popup).style.display = 'none';
		}

		function obs(nome, obs, id){
			$('#obs').val('');
			$("#nome_item").text(nome)
			$("#obs").val(obs)
			$("#id_obs").val(id)
			 var myModal = new bootstrap.Modal(document.getElementById('modalObs'), {
        		//backdrop: 'static',
    		});
   			 myModal.show();

		}


		function adicionais(nome, id, id_sabor, cat){			
			$("#nome_item_adc").text(nome)			
			listarAdicionais(id, id_sabor, cat);

			var myModal = new bootstrap.Modal(document.getElementById('modalAdc'), {
        		//backdrop: 'static',
    		});
   			 myModal.show();			 

		}

		function listarAdicionais(id, id_sabor, cat){

			$.ajax({
         url: 'js/ajax/listar-adc-carrinho.php',
        method: 'POST',
        data: {id, id_sabor, cat},
        dataType: "html",

        success:function(result){
            $("#listar-adc-carrinho").html(result);
           
        }
    });

			

		}



	</script>