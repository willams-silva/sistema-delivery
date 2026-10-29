<?php 
require_once("../../../conexao.php");
$tabela = 'abertura_mesa';
$data_hoje = date('Y-m-d');

$dataInicial = @$_POST['dataInicial'];
$dataFinal = @$_POST['dataFinal'];
$status = '%'.@$_POST['status'].'%';


$total_vendas = 0;

$query = $pdo->query("SELECT * FROM $tabela where data >= '$dataInicial' and data <= '$dataFinal' and status = 'Fechada'  order by data asc, horario_abertura desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="tabela">
	<thead> 
	<tr> 
	<th>Mesa</th>	
	<th class="esc">Garçon</th> 
	<th class="esc">Fehamento</th>		
	<th class="esc">Consumo</th> 
	<th class="esc">Comissão</th> 
	<th class="esc">Couvert</th> 	
	<th class="esc">Subtotal</th> 
	<th class="esc">Forma Pgto</th>	 	
	<th class="esc">Data</th>		
	<th>Detalhamento</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];	
	$cliente = $res[$i]['cliente'];
		$horario_abertura = $res[$i]['horario_abertura'];
		$horario_fechamento = $res[$i]['horario_fechamento'];
		$garcon = $res[$i]['garcon'];
		$obs = $res[$i]['obs'];
		$id_abertura_mesa = $res[$i]['id'];
		$pessoas = $res[$i]['pessoas'];
		$mesa = $res[$i]['mesa'];
		$data = $res[$i]['data'];
		$total = $res[$i]['total'];
		$comissao = $res[$i]['comissao_garcon'];
		$couvert = $res[$i]['couvert'];
		$subtotal = $res[$i]['subtotal'];
		$forma_pgto = $res[$i]['forma_pgto'];

		$totalF = @number_format($total, 2, ',', '.');
		$comissaoF = @number_format($comissao, 2, ',', '.');
		$couvertF = @number_format($couvert, 2, ',', '.');
		$subtotalF = @number_format($subtotal, 2, ',', '.');
	
		$dataF = implode('/', array_reverse(@explode('-', $data)));

		$query2 = $pdo->query("SELECT * FROM mesas where id = '$mesa' ");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$nome_mesa = $res2[0]['nome'];

		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$garcon' ");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$nome_garcon = $res2[0]['nome'];

		$horario_aberturaF = date('H:i', @strtotime($horario_abertura));
		$horario_fechamentoF = date('H:i', @strtotime($horario_fechamento));

		$total_vendas += $subtotal;

echo <<<HTML
<tr class="">
<td class="esc">{$nome_mesa}</td>
<td class="esc">{$nome_garcon}</td>
<td class="esc">{$horario_fechamentoF}</td>
<td class="esc">R$ {$totalF}</td>
<td class="esc">R$ {$comissaoF}</td>
<td class="esc">R$ {$couvertF}</td>
<td class="esc">R$ {$subtotalF}</td>
<td class="esc">{$forma_pgto}</td>
<td class="esc">{$dataF}</td>
<td>	
		
		<big><a  href="#" onclick="gerarComprovante('{$id}')" title="Gerar Comprovante"><i class="fa fa-file-pdf-o text-primary"></i></a></big>
	
		</td>
</tr>
HTML;

}

$total_vendasF = number_format($total_vendas, 2, ',', '.');

echo <<<HTML
</tbody>
<small><div align="center" id="mensagem-excluir"></div></small>
</table>

<br>	
<div align="right">Total Vendas: <span class="text-verde">R$ {$total_vendasF}</span> </div>

</small>
HTML;


}else{
	echo '<small>Não possui nenhum registro Cadastrado!</small>';
}

?>

<script type="text/javascript">
	$(document).ready( function () {
    $('#tabela').DataTable({
    		"ordering": false,
			"stateSave": true
    	});
    $('#tabela_filter label input').focus();
} );
</script>


<script type="text/javascript">
	function gerarComprovante(id){
		let a= document.createElement('a');
		                a.target= '_blank';
		                a.href= 'rel/comprovante_mesa.php?id='+ id + '&imp=Não';
		                a.click();
	}
</script>