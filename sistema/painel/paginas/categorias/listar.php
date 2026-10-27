<?php 
require_once("../../../conexao.php");
$tabela = 'categorias';

$query = $pdo->query("SELECT * FROM $tabela order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="tabela">
	<thead> 
	<tr> 
	<th>Nome</th>	
	<th class="esc">Descrição</th> 	
	<th class="esc">Cor</th> 	
	<th class="esc">Mais Sabores</th> 	
	<th class="esc">Foto</th>		
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
		$id = $res[$i]['id'];
		$nome = $res[$i]['nome'];		
		$ativo = $res[$i]['ativo'];		
		$foto = $res[$i]['foto'];
		$descricao = $res[$i]['descricao'];
		$cor = $res[$i]['cor'];
		$mais_sabores = $res[$i]['mais_sabores'];

		$ocultar_ad = 'ocultar';
		if($mais_sabores == 'Sim'){
			$ocultar_ad = '';
		}	

		$descricaoF = mb_strimwidth($descricao, 0, 120, "...");	

		if($ativo == 'Sim'){
			$icone = 'fa-check-square';
			$titulo_link = 'Desativar Item';
			$acao = 'Não';
			$classe_linha = '';
		}else{
			$icone = 'fa-square-o';
			$titulo_link = 'Ativar Item';
			$acao = 'Sim';
			$classe_linha = 'text-muted';
		}

echo <<<HTML
<tr class="{$classe_linha}">
<td>{$nome}</td>
<td class="esc">{$descricaoF}</td>
<td class="esc"><div class="divcor {$cor}"></div></td>
<td class="esc">{$mais_sabores}</td>
<td class="esc"><img src="images/{$tabela}/{$foto}" width="30px"></td>
<td>
	<big><a href="#" onclick="editar('{$id}','{$nome}', '{$descricao}', '{$foto}', '{$cor}', '{$mais_sabores}')" title="Editar Dados"><i class="fa fa-edit text-primary"></i></a></big>

	
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


		<big><a href="#" onclick="ativar('{$id}', '{$acao}')" title="{$titulo_link}"><i class="fa {$icone} text-success"></i></a></big>

		<a href="#" onclick="variacoes('{$id}','{$nome}')" title="Variações do Produto"><i class="fa fa-list text-primary"></i></a>

		<a class="{$ocultar_ad}" href="#" onclick="adicionais('{$id}','{$nome}')" title="Adicionais da Categoria"><i class="fa fa-plus text-verde"></i></a>

		<a class="{$ocultar_ad}" href="#" onclick="bordas('{$id}','{$nome}')" title="Opções de Bordas"><i class="fa fa-plus-circle text-primary"></i></a>


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
	function editar(id, nome, descricao, foto, cor, mais_sabores){
		$('#id').val(id);
		$('#nome').val(nome);
		$('#descricao').val(descricao);
		$('#cor').val(cor).change();
		$('#mais_sabores').val(mais_sabores).change();	
		
		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		$('#foto').val('');
		$('#target').attr('src','images/categorias/' + foto);
	}




	function limparCampos(){
		$('#id').val('');
		$('#nome').val('');
		$('#descricao').val('');		
		$('#foto').val('');
		$('#target').attr('src','images/categorias/sem-foto.jpg');
	}

</script>


<script type="text/javascript">
	function variacoes(id, nome){

		$('#titulo_nome_var').text(nome);		
		$('#id_var').val(id);		
		
		listarVariacoes(id);
		$('#modalVariacoes').modal('show');
		limparCamposVar();
	}
</script>


<script type="text/javascript">
	function adicionais(id, nome){

		$('#titulo_nome_adc').text(nome);		
		$('#id_adc').val(id);		
		
		listarAdicionais(id);
		$('#modalAdicionais').modal('show');
		limparCamposAdc();
	}
</script>


<script type="text/javascript">
	function bordas(id, nome){

		$('#titulo_nome_bordas').text(nome);		
		$('#id_bordas').val(id);		
		
		listarBordas(id);
		$('#modalBordas').modal('show');
		limparCamposBordas();
	}
</script>