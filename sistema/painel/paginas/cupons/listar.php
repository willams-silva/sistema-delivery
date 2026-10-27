<?php 
require_once("../../../conexao.php");
$tabela = 'cupons';

$query = $pdo->query("SELECT * FROM $tabela order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="tabela">
	<thead> 
	<tr> 
	<th>Código</th>	
	<th>Valor</th>	
	<th>Data</th>	
	<th>Quantidade</th>	
	<th>Valor Mínimo</th>	
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
		$id = $res[$i]['id'];
		$codigo = $res[$i]['codigo'];
		$valor = $res[$i]['valor'];
		$data = $res[$i]['data'];
		$quantidade = $res[$i]['quantidade'];
		$valor_minimo = $res[$i]['valor_minimo'];

		$dataF = implode('/', array_reverse(explode('-', $data)));
		$valorF = number_format($valor, 2, ',', '.');
		$valor_minimoF = number_format($valor_minimo, 2, ',', '.');

echo <<<HTML
<tr>
<td>{$codigo}</td>
<td>{$valorF}</td>
<td>{$dataF}</td>
<td>{$quantidade}</td>
<td>{$valor_minimoF}</td>
<td>
	<big><a href="#" onclick="editar('{$id}','{$codigo}','{$valor}','{$data}','{$quantidade}','{$valor_minimo}')" title="Editar Dados"><i class="fa fa-edit text-primary"></i></a></big>

	

	<li class="dropdown head-dpdn2" style="display: inline-block;">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-trash-o text-danger"></i></big></a>

		<ul class="dropdown-menu" style="margin-left:-230px;">
		<li>
		<div class="notification_desc2">
		<p>Confirmar Exclusão? <a href="#" onclick="excluir('{$id}')"><span class="text-danger">Sim</span></a></p>
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
	<small><div align="center" id="mensagem-excluir"></div></small>
	</table>
	</small>
HTML;


}else{
	echo 'Não possui registros cadastrados!';
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
	function editar(id, codigo, valor, data, quantidade, valor_minimo){
		$('#id').val(id);
		$('#codigo').val(codigo);
		$('#valor').val(valor);
		$('#data').val(data);
		$('#quantidade').val(quantidade);
		$('#valor_minimo').val(valor_minimo);
		
		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		
	}






	function limparCampos(){
		$('#id').val('');
		$('#codigo').val('');	
		$('#valor').val('');
		$('#data').val('');
		$('#quantidade').val('1');	
		$('#valor_minimo').val('');	
	}

</script>