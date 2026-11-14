<?php 
@session_start();
require_once('../../sistema/conexao.php');

// Ícone automático para os adicionais exibidos nesta lista carregada via AJAX.
function iconeAdicionalAjax($nome) {
    $nome_normalizado = mb_strtolower(trim((string) $nome), 'UTF-8');
    $nome_normalizado = strtr($nome_normalizado, [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c'
    ]);

    if (strpos($nome_normalizado, 'mel') !== false) return '🍯';
    if (strpos($nome_normalizado, 'banana') !== false) return '🍌';
    if (strpos($nome_normalizado, 'farinha lactea') !== false) return '🥛';
    if (strpos($nome_normalizado, 'acucar') !== false) return '🧂';
    if (strpos($nome_normalizado, 'canela') !== false) return '🍂';
    if (strpos($nome_normalizado, 'chocolate') !== false) return '🍫';
    if (strpos($nome_normalizado, 'morango') !== false) return '🍓';
    if (strpos($nome_normalizado, 'coco') !== false) return '🥥';
    if (strpos($nome_normalizado, 'leite') !== false) return '🥛';
    if (strpos($nome_normalizado, 'doce') !== false) return '🍬';
    return '🥄';
}
$id = $_POST['id'];
$valor_item = $_POST['valor'];
$quant = $_POST['quant'];
$sessao = @$_SESSION['sessao_usuario'];



$query =$pdo->query("SELECT * FROM adicionais where produto = '$id' and ativo = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML

	<div class="titulo-itens">
	Inserir Adicionais?
	</div>
	<ol class="list-group" id="listar-adicionais">
HTML;

	for($i=0; $i < $total_reg; $i++){
		foreach ($res[$i] as $key => $value){}
			$id = $res[$i]['id'];				
		$nome_adc = $res[$i]['nome'];
		$valoradc = $res[$i]['valor'];
		$valoradcF = number_format($valoradc, 2, ',', '.');
        $icone_adc = iconeAdicionalAjax($nome_adc);

		$query2 =$pdo->query("SELECT * FROM temp where sessao = '$sessao' and id_item = '$id' and tabela = 'adicionais' and carrinho = '0'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);

		if($total_reg2 > 0){
			$icone = 'bi-check-square-fill';
			$titulo_link = 'Remover Adicional';
			$acao = 'Não';
			$valor_item += $valoradc;

		}else{
			$icone = 'bi-square';
			$titulo_link = 'Escolher Adicional';
			$acao = 'Sim';

		}

echo <<<HTML

		<a href="#" onclick="adicionar('{$id}', '{$acao}')" class="link-neutro" title="{$titulo_link}">
		<li class="list-group-item">		    	
		<span style="display:inline-block;width:27px;margin-right:5px;font-size:18px;vertical-align:middle">{$icone_adc}</span>{$nome_adc} <span class="valor-item">(R$ {$valoradcF})</span>
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

<div class="total">
	R$ <b><span id="valor_item_quantF">{$valor_item_quantF}</span></b>
</div>

<input type="hidden" id="total_item_input" value="{$valor_item_quant}">
<input type="hidden" id="total_item_input_adc" value="{$valor_itemF}">

HTML;

}