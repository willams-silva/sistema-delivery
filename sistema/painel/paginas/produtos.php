<?php 

if(@$produtos == 'ocultar'){
    echo "<script>window.location='../index.php'</script>";
    exit();
}


$pag = 'produtos';
 ?>
<a class="btn btn-primary" onclick="inserir()" class="btn btn-primary btn-flat btn-pri"><i class="fa fa-plus" aria-hidden="true"></i> Novo Produto</a>


<div class="bs-example widget-shadow" style="padding:15px" id="listar">
	
</div>






<!-- Modal Inserir-->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><span id="titulo_inserir"></span></h4>
				<button id="btn-fechar" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			<form id="form">
			<div class="modal-body">

					<div class="row">
						<div class="col-md-7">
							<div class="form-group">
								<label for="exampleInputEmail1">Nome</label>
								<input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" required>    
							</div> 	
						</div>

						<div class="col-md-5">
							
							<div class="form-group">
								<label for="exampleInputEmail1">Categoria</label>
								<select class="form-control sel2" id="categoria" name="categoria" style="width:100%;" > 

									<?php 
									$query = $pdo->query("SELECT * FROM categorias ORDER BY id asc");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									if($total_reg > 0){
										for($i=0; $i < $total_reg; $i++){
										foreach ($res[$i] as $key => $value){}
										echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
										}
									}else{
											echo '<option value="0">Cadastre uma Categoria</option>';
										}
									 ?>
									

								</select>   
							</div> 	
						</div>
						
					</div>


					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="exampleInputEmail1">Descrição <small>(Até 1000 Caracteres)</small></label>
								<input maxlength="1000" type="text" class="form-control" id="descricao" name="descricao" placeholder="Descrição do Produto" >    
							</div> 	
						</div>
						
					</div>


					<div class="row">

					<div class="col-md-2">

							<div class="form-group">
								<label for="exampleInputEmail1">Valor Compra</label>
								<input type="text" class="form-control" id="valor_compra" name="valor_compra" placeholder="Valor Compra" >    
							</div> 	
						</div>					
						

						<div class="col-md-2">

							<div class="form-group">
								<label for="exampleInputEmail1">Valor Venda</label>
								<input type="text" class="form-control" id="valor_venda" name="valor_venda" placeholder="Valor Venda" >    
							</div> 	
						</div>	


						<div class="col-md-3">

							<div class="form-group">
								<label for="exampleInputEmail1">Alerta Estoque</label>
								<input type="number" class="form-control" id="nivel_estoque" name="nivel_estoque" placeholder="Nível Mínimo" >    
							</div> 	
						</div>	

						<div class="col-md-2">
							<div class="form-group">
								<label for="exampleInputEmail1">Tem Estoque?</label>
								<select class="form-control" id="tem_estoque" name="tem_estoque" style="width:100%;" > 

								<option value="Sim">Sim</option>
								<option value="Não">Não</option>										

								</select>   
							</div> 
							</div>	


							<div class="col-md-3">

							<div class="form-group">
								<label for="exampleInputEmail1">Quantidade Guarnições</label>
								<input type="number" class="form-control" id="guarnicoes" name="guarnicoes" placeholder="Se Houver" >    
							</div> 	
						</div>	

						

					</div>


					<div class="row">
						<div class="col-md-2">
							<div class="form-group">
								<label for="exampleInputEmail1">Promoção?</label>
								<select class="form-control" id="promocao" name="promocao" style="width:100%;" > 

								<option value="Não">Não</option>	
								<option value="Sim">Sim</option>
																	

								</select>   
							</div> 
							</div>	


							<div class="col-md-2">
							<div class="form-group">
								<label for="exampleInputEmail1">Combo?</label>
								<select class="form-control" id="combo" name="combo" style="width:100%;" > 

								<option value="Não">Não</option>	
								<option value="Sim">Sim</option>									

								</select>   
							</div> 
							</div>	
					</div>

					

						<div class="row">
							<div class="col-md-8">						
								<div class="form-group"> 
									<label>Foto</label> 
									<input class="form-control" type="file" name="foto" onChange="carregarImg();" id="foto">
								</div>						
							</div>
							<div class="col-md-4">
								<div id="divImg">
									<img src="images/produtos/sem-foto.jpg"  width="80px" id="target">									
								</div>
							</div>

						</div>


					
						<input type="hidden" name="id" id="id">

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







<!-- Modal Dados-->
<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_dados"></span></h4>
				<button id="btn-fechar-perfil" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">

				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-7">							
						<span><b>Categoria: </b></span>
						<span id="categoria_dados"></span>							
					</div>
					<div class="col-md-5">							
						<span><b>Valor Compra: </b></span>
						<span id="valor_compra_dados"></span>
					</div>					

				</div>


			

				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-7">							
						<span><b>Valor Venda: </b></span>
						<span id="valor_venda_dados"></span>
					</div>

					<div class="col-md-5">							
						<span><b>Estoque: </b></span>
						<span id="estoque_dados"></span>							
					</div>
						

				</div>

				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					
					<div class="col-md-6">							
						<span><b>Alerta Nível Mínimo Estoque: </b></span>
						<span id="nivel_estoque_dados"></span>							
					</div>

					<div class="col-md-6">							
						<span><b>Tem Estoque: </b></span>
						<span id="tem_estoque_dados"></span>							
					</div>

					
						

				</div>



				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					
					<div class="col-md-6">							
						<span><b>Promoção: </b></span>
						<span id="promocao_dados"></span>							
					</div>

					<div class="col-md-6">							
						<span><b>Combo: </b></span>
						<span id="combo_dados"></span>							
					</div>

					
						

				</div>

				

				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-12">							
						<span><b>Descrição: </b></span>
						<span id="descricao_dados"></span>							
					</div>

				
						

				</div>

			<div class="row" style="border-bottom: 1px solid #cac7c7;">
				<div class="col-md-6">							
						<span><b>Guarnições: </b></span>
						<span id="guarnicoes_dados"></span>							
					</div>
				</div>


				<div class="row">
					<div class="col-md-12" align="center">		
						<img width="250px" id="target_mostrar">	
					</div>					
				</div>


			</div>

			
		</div>
	</div>
</div>









<!-- Modal Saida-->
<div class="modal fade" id="modalSaida" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_saida"></span></h4>
				<button id="btn-fechar-saida" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form id="form-saida">

				<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								
								<input type="number" class="form-control" id="quantidade_saida" name="quantidade_saida" placeholder="Quantidade Saída" required>    
							</div> 	
						</div>

						<div class="col-md-5">
							<div class="form-group">								
								<input type="text" class="form-control" id="motivo_saida" name="motivo_saida" placeholder="Motivo Saída" required>    
							</div> 	
						</div>
						<div class="col-md-3">
							<button type="submit" class="btn btn-primary">Salvar</button>
						
						</div>
					</div>	
				
				<input type="hidden" id="id_saida" name="id">
				<input type="hidden" id="estoque_saida" name="estoque">

				</form>

				<br>
					<small><div id="mensagem-saida" align="center"></div></small>
			</div>

			
		</div>
	</div>
</div>





<!-- Modal Entrada-->
<div class="modal fade" id="modalEntrada" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_entrada"></span></h4>
				<button id="btn-fechar-entrada" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form id="form-entrada">

				<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								
								<input type="number" class="form-control" id="quantidade_entrada" name="quantidade_entrada" placeholder="Quantidade Entrada" required>    
							</div> 	
						</div>

						<div class="col-md-5">
							<div class="form-group">								
								<input type="text" class="form-control" id="motivo_entrada" name="motivo_entrada" placeholder="Motivo Entrada" required>    
							</div> 	
						</div>
						<div class="col-md-3">
							<button type="submit" class="btn btn-primary">Salvar</button>
						
						</div>
					</div>	
				
				<input type="hidden" id="id_entrada" name="id">
				<input type="hidden" id="estoque_entrada" name="estoque">

				</form>

				<br>
					<small><div id="mensagem-entrada" align="center"></div></small>
			</div>

			
		</div>
	</div>
</div>







<!-- Modal Variações-->
<div class="modal fade" id="modalVariacoes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_nome_var"></span></h4>
				<button id="btn-fechar-var" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form id="form-var">


					<div class="row">
					<div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Variação</label>
								<div id="listar_var_cat">
									
								</div>
														 
							</div> 	
						</div>	

											
						<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Valor</label>
								<input type="text" class="form-control" id="valor_var" name="valor" placeholder="50,00" required>    
							</div> 	
						</div>

						<div class="col-md-3" style="margin-top: 20px">
							<button type="submit" class="btn btn-primary">Salvar</button>
						
						</div>

					</div>	

			
				
				<input type="hidden" id="id_var" name="id">
				
				</form>

				<br>
					<small><div id="mensagem-var" align="center"></div></small>


					<hr>
					<div id="listar-var"></div>
			</div>

			
		</div>
	</div>
</div>





<!-- Modal Grades-->
<div class="modal fade" id="modalGrades" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_nome_grades"></span></h4>
				<button id="btn-fechar-grades" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form id="form-grades">


					<div class="row">

						<div class="col-md-8">
							<div class="form-group">
								<label for="exampleInputEmail1">Descrição na hora da compra <small>(Até 70 Caracteres)</small></label>
								<input maxlength="70" type="text" class="form-control" id="texto" name="texto" placeholder="Descrição do item" required="">    
							</div> 	
						</div>

					<div class="col-md-4">
							<div class="form-group">
								<label for="exampleInputEmail1">Tipo Item 

										<li class="dropdown head-dpdn2" style="display: inline-block;">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-info-circle text-primary"></i></big></a>

		<ul class="dropdown-menu" style="margin-left:-230px;">
		<li>
		<div class="notification_desc2">
		<p>
			<b>Seletor Único</b><br>
			<span class="text-muted" style="font-size: 12px">Você poderá selecionar apenas uma opção, exemplo, esse produto acompanha uma bebida, selecione a bebida desejada.</span>
		</p><br>

			<p>
			<b>Seletor Múltiplos</b><br>
			<span class="text-muted" style="font-size: 12px">Você poderá selecionar diversos itens dentro desta grade, exemplo de adicionais, 3 adicionais de bacon, 2 de cheddar, etc.</span>
		</p><br>


		<p>
			<b>Apenas 1 Item de cada</b><br>
			<span class="text-muted" style="font-size: 12px">Você pode selecionar várias opções porém só poderá inserir 1 item de cada, exemplo remoção de ingredientes, retirar cebola, retirar tomate, etc, será sempre uma unica seleção por cada item.</span>
		</p><br>

		<p>
			<b>Seletor Variação</b><br>
			<span class="text-muted" style="font-size: 12px">Você poderá selecionar apenas uma opção, exemplo, Tamanho Grande, Médio, etc, será mostrado em locais onde define a variação do produto.</span>
		</p><br>

		</div>
		</li>										
		</ul>
		</li>


								</label>
								<select class="form-control" id="tipo_item" name="tipo_item" style="width:100%;" > 

								<option value="Único">Seletor Único</option>
								<option value="Múltiplo">Seletor Múltiplos</option>	
								<option value="1 de Cada">1 item de Cada</option>	
								<option value="Variação">Variação Produto</option>

								</select>   
														 
							</div> 	
						</div>	

											
						

					</div>	


					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="exampleInputEmail1">Descrição Comprovante <small>(Até 70 Caracteres)</small></label>
								<input maxlength="70" type="text" class="form-control" id="nome_comprovante" name="nome_comprovante" placeholder="Descrição do item no comprovante" required="">    
							</div> 	
						</div>
					</div>

					<div class="row">
						<div class="col-md-5">
							<div class="form-group">
								<label for="exampleInputEmail1">Tipo Valor</label>
								<select class="form-control" id="valor_item" name="valor_item" style="width:100%;" > 
								<option value="Agregado">Valor Agregado</option>	
								<option value="Único">Valor Único Produto</option>
								<option value="Produto">Mesmo Valor do Produto</option>
								<option value="Sem Valor">Sem Valor</option>	

								</select>   
														 
							</div> 	
						</div>	


						<div class="col-md-5">
							<div class="form-group">
								<label for="exampleInputEmail1">Limite de Seleção Itens</label>
								<input type="number" class="form-control" id="limite" name="limite" placeholder="Selecionar até x Itens" >    
							</div> 	
						</div>

						<div class="col-md-2" style="margin-top: 20px">
							<button type="submit" class="btn btn-primary">Salvar</button>
						
						</div>
					</div>

			
				
				<input type="hidden" id="id_grades" name="id">
				
				</form>

				<br>
					<small><div id="mensagem-grades" align="center"></div></small>


					<hr>
					<div id="listar-grades"></div>
			</div>

			
		</div>
	</div>
</div>








<!-- Modal Itens-->
<div class="modal fade" id="modalItens" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_nome_itens"></span></h4>
				<button id="btn-fechar-var" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form id="form-itens">


					<div class="row">

						<div class="col-md-12">
							<div class="form-group">
								<label for="exampleInputEmail1">Nome <small>(Até 70 Caracteres)</small></label>
								<input maxlength="70" type="text" class="form-control" id="texto_item" name="texto" placeholder="Descrição do item" required="">    
							</div> 	
						</div>		
															
						

					</div>	

					<div class="row">

							<div class="col-md-4">

							<div class="form-group">
								<label for="exampleInputEmail1">Valor</label>
								<input type="text" class="form-control" id="valor_do_item" name="valor" placeholder="Valor Se Houver" >    
							</div> 	
						</div>	
						


							<div class="col-md-5">
							<div class="form-group">
								<label for="exampleInputEmail1">Limite de Seleção Itens</label>
								<input type="number" class="form-control" id="limite_itens" name="limite" placeholder="Selecionar até x Itens" >    
							</div> 	
						</div>


						
						<div class="col-md-3" style="margin-top: 20px">
							<button type="submit" class="btn btn-primary">Salvar</button>
						
						</div>
					</div>

			
				
				<input type="hidden" id="id_item" name="id">
				<input type="hidden" id="id_item_produto" name="id_item_produto">
				
				</form>

				<br>
					<small><div id="mensagem-itens" align="center"></div></small>


					<hr>
					<div id="listar-itens"></div>
			</div>

			
		</div>
	</div>
</div>





<script type="text/javascript">var pag = "<?=$pag?>"</script>
<script src="js/ajax.js"></script>



<script type="text/javascript">
	function carregarImg() {
    var target = document.getElementById('target');
    var file = document.querySelector("#foto").files[0];
    
        var reader = new FileReader();

        reader.onloadend = function () {
            target.src = reader.result;
        };

        if (file) {
            reader.readAsDataURL(file);

        } else {
            target.src = "";
        }
    }
</script>





 <script type="text/javascript">
	

$("#form-saida").submit(function () {

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'paginas/' + pag + "/saida.php",
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem-saida').text('');
            $('#mensagem-saida').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {

                $('#btn-fechar-saida').click();
                listar();          

            } else {

                $('#mensagem-saida').addClass('text-danger')
                $('#mensagem-saida').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});
</script>





 <script type="text/javascript">
	

$("#form-entrada").submit(function () {

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'paginas/' + pag + "/entrada.php",
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem-entrada').text('');
            $('#mensagem-entrada').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {

                $('#btn-fechar-entrada').click();
                listar();          

            } else {

                $('#mensagem-entrada').addClass('text-danger')
                $('#mensagem-entrada').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});
</script>






 <script type="text/javascript">
	

$("#form-var").submit(function () {

	var id_var = $('#id_var').val()

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'paginas/' + pag + "/inserir-variacoes.php",
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem-var').text('');
            $('#mensagem-var').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {

                //$('#btn-fechar-var').click();
                listarVariacoes(id_var); 
                limparCamposVar();         

            } else {

                $('#mensagem-var').addClass('text-danger')
                $('#mensagem-var').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});


	function limparCamposVar(){
		
		$('#nome_var').val('');
		$('#valor_var').val('');		
		$('#sigla').val('');	
		$('#descricao_var').val('');		
		
	}




	function listarVariacoes(id){
    $.ajax({
        url: 'paginas/' + pag + "/listar-variacoes.php",
        method: 'POST',
        data: {id},
        dataType: "html",

        success:function(result){
            $("#listar-var").html(result);
            $('#mensagem-excluir-var').text('');
        }
    });
}


function excluirVar(id){
	var id_var = $('#id_var').val()
    $.ajax({
        url: 'paginas/' + pag + "/excluir-variacoes.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Excluído com Sucesso") {                
                listarVariacoes(id_var);                
            } else {
                $('#mensagem-excluir-var').addClass('text-danger')
                $('#mensagem-excluir-var').text(mensagem)
            }

        },      

    });
}


function ativarVar(id, acao){
	var id_var = $('#id_var').val()
    $.ajax({
        url: 'paginas/' + pag + "/mudar-status-var.php",
        method: 'POST',
        data: {id, acao},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Alterado com Sucesso") {                
                listarVariacoes(id_var);                
            } else {
                $('#mensagem-excluir-var').addClass('text-danger')
                $('#mensagem-excluir-var').text(mensagem)
            }

        },      

    });
}
</script>


 <script type="text/javascript">
	

$("#form-grades").submit(function () {

	var id_var = $('#id_grades').val()

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'paginas/' + pag + "/inserir-grades.php",
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem-grades').text('');
            $('#mensagem-grades').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {

                //$('#btn-fechar-var').click();
                listarGrades(id_var); 
                limparCamposGrades();         

            } else {

                $('#mensagem-grades').addClass('text-danger')
                $('#mensagem-grades').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});




</script>


<script type="text/javascript">
	function limparCamposGrades(){
		
		$('#texto').val('');
		$('#limite').val('');	
		$('#nome_comprovante').val('');	
				
		
	}

	

	function listarGrades(id){
    $.ajax({
        url: 'paginas/' + pag + "/listar-grades.php",
        method: 'POST',
        data: {id},
        dataType: "html",

        success:function(result){
            $("#listar-grades").html(result);
            $('#mensagem-excluir-grades').text('');
        }
    });
}







function excluirGrades(id){
	var id_var = $('#id_grades').val()
    $.ajax({
        url: 'paginas/' + pag + "/excluir-grade.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Excluído com Sucesso") {                
                listarGrades(id_var);                
            } else {
                $('#mensagem-excluir-grades').addClass('text-danger')
                $('#mensagem-excluir-grades').text(mensagem)
            }

        },      

    });
}




function ativarGrades(id, acao){
	var id_var = $('#id_grades').val()
    $.ajax({
        url: 'paginas/' + pag + "/mudar-status-grade.php",
        method: 'POST',
        data: {id, acao},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Alterado com Sucesso") {                
                listarGrades(id_var);                
            } else {
                $('#mensagem-excluir-grades').addClass('text-danger')
                $('#mensagem-excluir-grades').text(mensagem)
            }

        },      

    });
}




</script>











 <script type="text/javascript">
	

$("#form-itens").submit(function () {

	var id_var = $('#id_item').val()

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'paginas/' + pag + "/inserir-itens.php",
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem-itens').text('');
            $('#mensagem-itens').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {

                //$('#btn-fechar-var').click();
                listarItens(id_var); 
                limparCamposItens();         

            } else {

                $('#mensagem-itens').addClass('text-danger')
                $('#mensagem-itens').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});




</script>


<script type="text/javascript">
	function limparCamposItens(){
		
		$('#texto_item').val('');
		$('#limite_itens').val('');	
		$('#valor_do_item').val('');			
		
	}

	

	function listarItens(id){
    $.ajax({
        url: 'paginas/' + pag + "/listar-itens.php",
        method: 'POST',
        data: {id},
        dataType: "html",

        success:function(result){
            $("#listar-itens").html(result);
            $('#mensagem-excluir-itens').text('');
        }
    });
}







function excluirItens(id){
	var id_var = $('#id_item').val()
    $.ajax({
        url: 'paginas/' + pag + "/excluir-item.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Excluído com Sucesso") {                
                listarItens(id_var);                
            } else {
                $('#mensagem-excluir-itens').addClass('text-danger')
                $('#mensagem-excluir-itens').text(mensagem)
            }

        },      

    });
}




function ativarItens(id, acao){
	var id_var = $('#id_item').val()
    $.ajax({
        url: 'paginas/' + pag + "/mudar-status-itens.php",
        method: 'POST',
        data: {id, acao},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Alterado com Sucesso") {                
                listarItens(id_var);                
            } else {
                $('#mensagem-excluir-itens').addClass('text-danger')
                $('#mensagem-excluir-itens').text(mensagem)
            }

        },      

    });
}




</script>




<script type="text/javascript">
	$(document).ready(function() {
    $('.sel2').select2({
    	dropdownParent: $('#modalForm')
    });
});
</script>


<script type="text/javascript">
		function listarVarCat(id){
    $.ajax({
        url: 'paginas/' + pag + "/listar_var_cat.php",
        method: 'POST',
        data: {id},
        dataType: "html",

        success:function(result){
            $("#listar_var_cat").html(result);           
        }
    });
}

</script>