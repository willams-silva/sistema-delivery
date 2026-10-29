<?php 
require_once("../../../conexao.php");
$tabela = 'pagar';

$data_atual = date('Y-m-d');

$id = $_POST['id'];

$total_comissoes = 0;
$total_comissoesF = 0;
$query = $pdo->query("SELECT * FROM $tabela where funcionario = '$id' and pago = 'Não' and referencia = 'Comissão' order by id asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="">
	<thead> 
	<tr> 
		
	<th class="esc">Valor</th>	
	<th class="esc">Data</th>	

		</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];		
	$valor = $res[$i]['valor'];
	$data = $res[$i]['data_lanc'];
	
	
	$valorF = number_format($valor, 2, ',', '.');

	$dataF = implode('/', array_reverse(explode('-', $data)));
	//$horaF = date("H:i", strtotime($hora));	

	$total_comissoes += $valor;

echo <<<HTML
<tr class="">

<td>R$ {$valorF}</td>
<td>{$dataF}</td>

</tr>

HTML;
}

$total_comissoesF = number_format($total_comissoes, 2, ',', '.');

echo <<<HTML
</tbody>
<small><div align="center" id=""></div></small>
</table>

<br>	

<div style="margin-right: 15px; display:inline-block;" align="right">Total de Comissões: <span class="">{$total_reg}</span> </div>

<div style="display:inline-block;" align="right">Total à Pagar: <span class=""> <input id="valor_pago_comissoes" style="width:60px; display:inline-block; font-size: 13px" class="form-control text-danger" type="text" name="total_comissoes" value="{$total_comissoes}"></span> </div>

<div style="display:inline-block; width:140px" align="center">Data PGTO
<input type="date" id="data_pgto_comissoes" class="form-control" value="{$data_atual}">
</div>

<div style="display:inline-block;" align="right">
<button onclick="comissao()" type="button" id="btn_comissoes" class="btn btn-danger">Pagar</button></div>

</small>
HTML;


}else{
	echo '<small>Não possui nenhuma Comissão Pendente!</small>';
}

 ?>


 <script type="text/javascript">
 	$("#total_pagar_comissoes").text("<?=$total_comissoesF?>");

 	function comissao(){
 		var total = $("#valor_pago_comissoes").val();
 		var data = $("#data_pgto_comissoes").val();
 		var id_func = $("#id_comissao").val();

 		if(total <= 0){
 			alert('Insira um total maior que zero!');
 			return;
 		}


 		$.ajax({
        url: 'paginas/' + pag + "/pagar_comissoes.php",
        method: 'POST',
        data: {total, data, id_func},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Salvo com Sucesso") {
                $('#modalComissoes').modal('hide');                
            } else {
               alert(mensagem)
            }

        },      

    });
 	}
 </script>