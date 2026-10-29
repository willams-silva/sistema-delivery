<?php 
@session_start();
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'novo_pedido';
if(@$novo_pedido == 'ocultar'){
    echo "<script>window.location='../index.php'</script>";
    exit();
}
 ?>

<div  style="padding:5px" id="listar">
	
</div>






<!-- Modal modalAbertura-->
<div class="modal fade" id="modalAbertura" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Abrir Mesa <span id="nome_da_mesa"></span></h4>
				<button id="btn-fechar" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			<form id="form">
			<div class="modal-body">

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Nome Cliente</label>
								<input type="text" class="form-control" id="nome" name="nome" placeholder="Nome do Cliente" required>    
							</div> 	
						</div>
						<div class="col-md-6">							
							
							<div class="form-group">
								<label for="exampleInputEmail1">Garçon</label>
								<select class="form-control sel2" id="garcon" name="garcon" style="width:100%;" > 

									<?php 
									$query = $pdo->query("SELECT * FROM usuarios where nivel = 'Garçon' or nivel = 'Atendente' ORDER BY nome asc");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									if($total_reg > 0){
										for($i=0; $i < $total_reg; $i++){
										if($res[$i]['id'] == $id_usuario){
											echo '<option selected value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
										}else{
											echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
										}
										
										}
									}else{
											echo '<option value="0">Cadastre um Garçon</option>';
										}
									 ?>
									

								</select>   
							</div> 	
						
						</div>
					</div>


			

					

						<div class="row">

							<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Pessoas</label>
								<input type="number" class="form-control" id="pessoas" name="pessoas" placeholder="Número de Pessoas" >    
							</div> 	
						</div>

							<div class="col-md-9">
							<div class="form-group">
								<label for="exampleInputEmail1">Observações</label>
								<input type="text" class="form-control" id="obs" name="obs" placeholder="Observações" >    
							</div> 	
						</div>

							

						</div>


					
						<input type="hidden" name="id" id="id_abertura">
						<input type="hidden" name="id_ab" id="id">

					<br>
					<small><div id="mensagem" align="center"></div></small>
				</div>

				<div class="modal-footer">      
					<button type="submit" class="btn btn-primary">Salvar</button>
				</div>
			</form>

			
		</div>
	</div>
</div>






<!-- Modal Itens-->
<div class="modal fade" id="modalItens" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Mesa <span id="nome_da_mesa_itens"></span> <small><span id="obs_da_mesa_itens"></span></small></h4>
				<button id="btn-fechar-itens" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">										

					<div id="listar_carrinho">
						
					</div>							
						

					<br>
					<small><div id="mensagem_itens" align="center"></div></small>
				</div>

			
				<div class="modal-footer"> 
				<form method="POST" action="../../index" target="_blank">
							<input type="hidden" name="id_mesa" id="id_itens">
							<input type="hidden" name="id_abertura_mesa" id="id_abertura_mesa">     
					<button type="submit" class="btn btn-primary">Adicionar Itens</button>

					</form>
				</div>
			
		</div>
	</div>
</div>








<!-- Modal Fechamento-->
<div class="modal fade" id="modalFechamento" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Fechar Mesa <span id="nome_da_mesa_fechamento"></span></h4>
				<button id="btn-fechar-fechamento" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			<form id="form_fechamento">
			<div class="modal-body">

					<div class="row">
						<div class="col-md-6">							
							
							<div class="form-group">
								<label for="exampleInputEmail1">Garçon</label>
								<select class="form-control sel3" id="garcon_fechamento" name="garcon" style="width:100%;" > 

									<?php 
									$query = $pdo->query("SELECT * FROM usuarios where nivel = 'Garçon' or nivel = 'Atendente' ORDER BY nome asc");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									if($total_reg > 0){
										for($i=0; $i < $total_reg; $i++){
										
											echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';										
										
										}
									}else{
											echo '<option value="0">Cadastre um Garçon</option>';
										}
									 ?>
									

								</select>   
							</div> 	
						
						</div>


							<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Total Itens</label>
								<input readonly="true" type="text" class="form-control" id="total_itens_fechamento" name="total_itens" placeholder="Total Itens" >    
							</div> 	
								</div>

							<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Comissão R$</label>
								<input onkeyup="calcular()" type="text" class="form-control" id="comissao_fechamento" name="comissao" placeholder="" >    
							</div> 
						</div>

					</div>


				

						<div class="row">	
							<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Couvert</label>
								<input onkeyup="calcular()" type="text" class="form-control" id="couvert_fechamento" name="couvert" placeholder="" >    
							</div> 
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Subtotal</label>
								<input type="text" class="form-control" id="subtotal_fechamento" name="subtotal" placeholder="" >    
							</div> 
						</div>



						<div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Forma de Pagamento</label>
									<select class="form-control" id="forma_pgto" name="forma_pgto" > 

									<?php 
									$query = $pdo->query("SELECT * FROM formas_pgto ORDER BY id asc");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									if($total_reg > 0){
										for($i=0; $i < $total_reg; $i++){
										
											echo '<option value="'.$res[$i]['nome'].'">'.$res[$i]['nome'].'</option>';										
										
										}
									}else{
											echo '<option value="0">Cadastre uma Forma de Pagamento</option>';
										}
									 ?>
									

								</select> 
							</div> 
						</div>


						</div>

			

					

						<div class="row">							

							<div class="col-md-12">
							<div class="form-group">
								<label for="exampleInputEmail1">Observações</label>
								<input type="text" class="form-control" id="obs_fechamento" name="obs" placeholder="Observações" >    
							</div> 	
						</div>

							

						</div>


					
						<input type="hidden" name="id" id="id_fechamento">
						<input type="hidden" name="id_ab" id="id_ab_fechamento">

					<br>
					<small><div id="mensagem_fechamento" align="center"></div></small>
				</div>

				<div class="modal-footer">      
					<button type="submit" class="btn btn-primary">Salvar</button>
				</div>
			</form>

			
		</div>
	</div>
</div>








<script type="text/javascript">var pag = "<?=$pag?>"</script>
<script src="js/ajax.js"></script>


<script type="text/javascript">
	$(document).ready(function() {
		setInterval(listar, 120000);
		setInterval(listarCarrinho, 30000);

    $('.sel2').select2({
    	dropdownParent: $('#modalAbertura')
    });

     $('.sel3').select2({
    	dropdownParent: $('#modalFechamento')
    });
});

function listarCarrinho(){
	 var id = $('#id_abertura_mesa').val();
	    $.ajax({

        url: 'paginas/' + pag + "/listar_carrinho.php",
        method: 'POST',
        data: {id},
        dataType: "html",

        success:function(result){
            $("#listar_carrinho").html(result);
            $('#mensagem_itens').text('');
        }
    });
}

function calcular(){
	var comissao = $('#comissao_fechamento').val();
	var couvert = $('#couvert_fechamento').val();
	var total_itens = $('#total_itens_fechamento').val();

	if(comissao == ""){
		comissao = 0;
	}

	if(couvert == ""){
		couvert = 0;
	}

	if(total_itens == ""){
		total_itens = 0;
	}

	var subtotal = parseFloat(comissao) + parseFloat(couvert) + parseFloat(total_itens);
	$('#subtotal_fechamento').val(subtotal.toFixed(2));
}





$("#form_fechamento").submit(function () {

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'paginas/' + pag + "/fechamento.php",
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem_fechamento').text('');
            $('#mensagem_fechamento').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {

                $('#btn-fechar-fechamento').click();
                listar();          

            } else {

                $('#mensagem_fechamento').addClass('text-danger')
                $('#mensagem_fechamento').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});





</script>


