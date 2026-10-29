<?php 
require_once("../../../conexao.php");
$tabela = 'valor_adiantamento';

$id = @$_POST['id'];


$query = $pdo->query("SELECT * FROM $tabela where abertura = '$id' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="">
	<thead> 
	<tr> 
	<th>Nome</th>	
	<th>Valor</th>		
	<th>Excluir</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;
$total_valor = 0;
$total_valorF = 0;
for($i=0; $i < $total_reg; $i++){
	$id = $res[$i]['id'];		
		$nome = $res[$i]['nome'];
		$valor = $res[$i]['valor'];

		$valorF = number_format($valor, 2, ',', '.');

		$total_valor += $valor;
		$total_valorF = number_format($total_valor, 2, ',', '.');

echo <<<HTML
<tr>
<td>{$nome}</td>
<td>{$valorF}</td>
<td>

	<li class="dropdown head-dpdn2" style="display: inline-block;">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-trash-o text-danger"></i></big></a>

		<ul class="dropdown-menu" style="margin-left:-230px;">
		<li>
		<div class="notification_desc2">
		<p>Confirmar Exclusão? <a title="Excluir Item" href="#" onclick="excluirValor('{$id}')"><span class="text-danger">Sim</span></a></p>
		</div>
		</li>										
		</ul>
		</li>




</td>
</tr>
HTML;

}

echo <<<HTML
	</tbody>
	<small><div align="center" id="mensagem-excluir"></div>	</small>
	</table>
	<div align="right"><b>Total R$ </b><span style="color:green">{$total_valorF}</span></div>
	</small>
HTML;


}else{
	echo 'Não possui valores lançados!';
}


 ?>


<script type="text/javascript">
	
function excluirValor(id){
    $.ajax({
        url: 'paginas/' + pag + "/excluir_valor.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Excluído com Sucesso") {               
                  
                listarValores();                 
            } else {
                $('#mensagem-valor').addClass('text-danger')
                $('#mensagem-valor').text(mensagem)
            }

        },      

    });
}
</script>