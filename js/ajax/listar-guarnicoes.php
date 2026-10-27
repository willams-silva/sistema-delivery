<?php 
@session_start();
require_once('../../sistema/conexao.php');
$id = $_POST['id'];
$valor_item = $_POST['valor'];
$quant = $_POST['quant'];

$query =$pdo->query("SELECT * FROM produtos where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$valor_produto = $res[0]['valor_venda'];
$guarnicoes = $res[0]['guarnicoes'];

$query =$pdo->query("SELECT * FROM adicionais where produto = '$id' and ativo = 'Sim' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_adicionais = @count($res);


$sessao = @$_SESSION['sessao_usuario'];

$query =$pdo->query("SELECT * FROM guarnicoes where produto = '$id' and ativo = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML

	<div class="titulo-itens-2">
	Escolha até {$guarnicoes} Guarnições!
	</div>
	<ol class="list-group" id="listar-adicionais">
HTML;

	for($i=0; $i < $total_reg; $i++){
		foreach ($res[$i] as $key => $value){}
			$id = $res[$i]['id'];				
				$nome_ing = $res[$i]['nome'];

		$query2 =$pdo->query("SELECT * FROM temp where sessao = '$sessao' and id_item = '$id' and tabela = 'guarnicoes' and carrinho = '0'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);

		if($total_reg2 > 0){
			$icone = 'bi-check-square-fill';
			$titulo_link = 'Remover Guarnição';
			$acao = 'Não';			

		}else{
			$icone = 'bi-square';
			$titulo_link = 'Adicionar Guarnição';
			$acao = 'Sim';

		}

echo <<<HTML

		<a href="#" onclick="adicionarGuar('{$id}', '{$acao}')" class="link-neutro" title="{$titulo_link}">
		<li class="list-group-item">		    	
		{$nome_ing}
		<i class="bi {$icone} direita"></i>			
		</li>
		</a>

HTML;


	}

$valor_itemF = number_format($valor_item, 2, ',', '.');

$valor_item_quant = $valor_item * $quant;
$valor_item_quantF = number_format($valor_item_quant, 2, ',', '.');

echo <<<HTML

</ol>



HTML;

if($total_adicionais == 0){
echo <<<HTML
<div class="total">
	R$ <b><span id="valor_item_quantF">{$valor_item_quantF}</span></b>
</div>

<input type="hidden" id="total_item_input" value="{$valor_item_quant}">
<input type="hidden" id="total_item_input_adc" value="{$valor_itemF}">
HTML;

}

}