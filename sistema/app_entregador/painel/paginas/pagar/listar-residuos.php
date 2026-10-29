<?php 
$tabela = 'pagar';
require_once("../../../../conexao.php");


$id = $_POST['id'];

echo <<<HTML
<table id="" style="width:100%; font-size:13px;">
<thead>
<tr align="center">
<th>Valor</th>
<th>Data</th>								
<th>Usuário</th>
</tr>
</thead>
<tbody>
HTML;


$query = $pdo->query("SELECT * from pagar where id_ref = '$id' and residuo = 'Sim' order by id desc ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
for($i=0; $i < @count($res); $i++){
	foreach ($res[$i] as $key => $value){} 

		$valor = $res[$i]['valor'];
		$data = $res[$i]['data_pgto'];
		$usuario = $res[$i]['usuario_pgto'];

		$query2 = $pdo->query("SELECT * from usuarios where id = '$usuario'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$nome_usuario = $res2[0]['nome'];

		$data = implode('/', array_reverse(@explode('-', $data)));

		$valor = number_format($valor, 2, ',', '.');

	

echo <<<HTML
	<tr align="center" style="border-bottom: 1px solid #000; padding-bottom: 3px">
	<td style="color:green">R$ {$valor}</td>		
	<td>{$data}</td>							
	<td>{$nome_usuario}</td>	
	</tr>
HTML;
} 
echo <<<HTML
</tbody>
</table>
HTML;

?>

