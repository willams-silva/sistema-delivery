<?php
@session_start();
$id_empresa = (int)($_SESSION['empresa']??0);
if($id_empresa<=0||empty($_SESSION['id'])){http_response_code(403);exit('Sessão inválida');}
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$tabela = 'abertura_mesa';
$data_hoje = date('Y-m-d');

$dataInicial = @$_POST['dataInicial'];
$dataFinal = @$_POST['dataFinal'];
$status = '%'.@$_POST['status'].'%';

$nome_forma_pgto = '';

$total_vendas = 0;

$mesa=filter_var($_POST['mesa']??'',FILTER_VALIDATE_INT);
$dataInicial=(string)($_POST['dataInicial']??'');
$dataFinal=(string)($_POST['dataFinal']??'');
if(!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/',$dataInicial)||!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/',$dataFinal)||$dataInicial>$dataFinal){
 http_response_code(400);exit('Período inválido');
}
$sql="SELECT * FROM abertura_mesa WHERE data>=:inicio AND data<=:fim AND status='Fechada' AND empresa=:empresa";
$params=[':inicio'=>$dataInicial,':fim'=>$dataFinal,':empresa'=>$id_empresa];
if($mesa && $mesa>0){$sql.=' AND mesa=:mesa';$params[':mesa']=$mesa;}
$sql.=' ORDER BY data ASC, horario_abertura DESC';
$query=$pdo->prepare($sql);
$query->execute($params);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr> 
	<th>Mesa</th>	
	<th>Garçon</th> 
	<th>Fehamento</th>		
	<th>Consumo</th> 
	<th>Comissão</th> 
	<th>Couvert</th>	
	<th>Adiantado</th>	
	<th>Subtotal</th>
	<th>Forma Pgto</th>	 	
	<th>Data</th>		
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
		$valor_adiantado = $res[$i]['valor_adiantado'];


		$query2=$pdo->prepare('SELECT nome FROM formas_pgto WHERE id=:id AND empresa=:empresa LIMIT 1');
        $query2->execute([':id'=>(int)$forma_pgto,':empresa'=>$id_empresa]);
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count($res2) > 0) {
			$nome_forma_pgto = htmlspecialchars((string)$res2[0]['nome'],ENT_QUOTES,'UTF-8');
		}

		$totalF = @number_format($total, 2, ',', '.');
		$comissaoF = @number_format($comissao, 2, ',', '.');
		$couvertF = @number_format($couvert, 2, ',', '.');
		$subtotalF = @number_format($subtotal, 2, ',', '.');
		$valor_adiantadoF = @number_format($valor_adiantado, 2, ',', '.');
	
		$dataF = implode('/', array_reverse(@explode('-', $data)));

		$query2=$pdo->prepare('SELECT nome FROM mesas WHERE id=:id AND empresa=:empresa LIMIT 1');
        $query2->execute([':id'=>(int)$mesa,':empresa'=>$id_empresa]);
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$nome_mesa = htmlspecialchars((string)($res2[0]['nome']??'Mesa removida'),ENT_QUOTES,'UTF-8');

		$query2=$pdo->prepare('SELECT nome FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1');
        $query2->execute([':id'=>(int)$garcon,':empresa'=>$id_empresa]);
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$nome_garcon = htmlspecialchars((string)($res2[0]['nome']??'Não informado'),ENT_QUOTES,'UTF-8');

		$horario_aberturaF = date('H:i', @strtotime($horario_abertura));
		$horario_fechamentoF = date('H:i', @strtotime($horario_fechamento));

		$total_vendas += $subtotal;

echo <<<HTML
<tr>
<td>{$nome_mesa}</td>
<td>{$nome_garcon}</td>
<td>{$horario_fechamentoF}</td>
<td>R$ {$totalF}</td>
<td>R$ {$comissaoF}</td>
<td>R$ {$couvertF}</td>
<td>R$ {$valor_adiantadoF}</td>
<td>R$ {$subtotalF}</td>
<td>{$nome_forma_pgto}</td>
<td>{$dataF}</td>
<td>	
		
	<a class="btn btn-dark-light btn-sm" href="#" onclick="gerarComprovante('{$id}')" title="Gerar Comprovante"><i class="fa fa-file-pdf-o"></i></a>
	
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