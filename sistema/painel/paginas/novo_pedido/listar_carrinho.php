<?php 
require_once("../../../conexao.php");
$tabela = 'carrinho';

$id = @$_POST['id'];
$id_post = @$_POST['id'];

$total_carrinho = 0;
$query = $pdo->query("SELECT * FROM $tabela where mesa = '$id' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="">
	<thead> 
	<tr> 
	<th>Foto</th>	
	<th>Produto(s)</th>	
	<th>Total</th>	
	<th>Excluir</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
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
		$valor_unit = $res[$i]['valor_unitario'];
		$status = $res[$i]['status'];

		if($status == 'Aguardando'){
			$classe_alerta = 'text-danger';
			$opacidade = '1';				
		}else if($status == 'Preparando'){
			$classe_alerta = 'text-primary';
			$opacidade = '1';
		}else if($status == 'Pronto'){
			$classe_alerta = 'text-verde';	
			$opacidade = '1';
		}else{
			$classe_alerta = 'text-secondary';	
			$opacidade = '0';		
						
		}

		if($valor_unit == ""){
			if($total_item > 0 and $quantidade > 0){
			$valor_unit = $total_item / $quantidade;
			}else{
				$valor_unit = 0;
			}	
		}

		$total_item = $total_item * $quantidade;
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

		if($sabores > 0){			
			$nome_produto = $nome_produto_tab;
		}


		//totalizar valores pagos
		$total_valor = 0;
		$total_valorF = 0;
$query2 = $pdo->query("SELECT * FROM valor_adiantamento where abertura = '$id_post' order by id desc");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$total_reg2 = @count($res2);
	if($total_reg2 > 0){
		
		for($i2=0; $i2 < $total_reg2; $i2++){
			$valor2 = $res2[$i2]['valor'];

		$valorF2 = number_format($valor2, 2, ',', '.');

		$total_valor += $valor2;
		$total_valorF = number_format($total_valor, 2, ',', '.');
		}


}
$ocultar_pago = 'ocultar';
if($total_valor > 0){
			$ocultar_pago = '';
		}	

echo <<<HTML
<tr>
<td><img class="" src="../../sistema/painel/images/produtos/{$foto_produto}" width="25px"></td>
<td><i class="fa fa-square {$classe_alerta}" style="opacity: {$opacidade}"></i> {$quantidade} / {$nome_produto}</td>
<td>{$total_itemF}</td>
<td>

	

		<a class="" href="#" onclick="excluirModal('{$id}', '{$nome_produto}')" title="Excluir Item" class=""><big><i class="fa fa-trash-o text-danger"></i></big></a>




</td>
</tr>
HTML;

}

echo <<<HTML
	</tbody>
	<small><div align="center" id="mensagem-excluir"></div>	</small>
	</table>
	<div align="right">
	<span class="{$ocultar_pago}"><b>Pago Adiantado</b> <span style="color:red">(R$ {$total_valorF})</span></span>
	 <span style="margin-left:30px"><b>Itens</b> ({$total_reg}) / <b>Total R$ </b><span style="color:green">{$total_carrinhoF}</span></span>
	 </div>
	</small>
HTML;


}else{
	echo 'Não possui itens lançados!';
}


 ?>


<script type="text/javascript">
	
function excluirCarrinho(){

	var id = $('#id_car_excluir').val();
	var senha = $('#senha').val();
	var gerente = $('#gerente').val();

	if(senha == "" || gerente == ""){
		alert('Selecione um Gerente e preencha a senha!');
		return;
	}

    $.ajax({
        url: 'paginas/' + pag + "/excluir_carrinho.php",
        method: 'POST',
        data: {id, gerente, senha},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Excluído com Sucesso") {                
                listar();   
                listarCarrinho();  
                $('#mensagem-excluir-modal').text('')
                $('#btn-fechar-excluir').click();               
            } else {
                $('#mensagem-excluir-modal').addClass('text-danger')
                $('#mensagem-excluir-modal').text(mensagem)
            }

        },      

    });
}



	function excluirModal(id, nome){	
			
			$('#nome_modal').text(nome);			
			$('#id_car_excluir').val(id);	
			$('#senha').val('');	

			$('#modalExcluir').modal('show');	
			
	}
</script>