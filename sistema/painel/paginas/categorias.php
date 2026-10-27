<?php 
//verificar se ele tem a permissão de estar nessa página
if(@$categorias == 'ocultar'){
    echo "<script>window.location='../index.php'</script>";
    exit();
}
$pag = 'categorias';
 ?>
<a class="btn btn-primary" onclick="inserir()" class="btn btn-primary btn-flat btn-pri"><i class="fa fa-plus" aria-hidden="true"></i> Nova Categoria</a>


<div class="bs-example widget-shadow" style="padding:15px" id="listar">
	
</div>





<!-- Modal Inserir-->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
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
						<div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Nome</label>
								<input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" required>    
							</div> 	
						</div>
						<div class="col-md-6">

							<div class="form-group">
								<label for="exampleInputEmail1">Cor</label>
								<select class="form-control" id="cor" name="cor">
									<option value="azul">Azul</option>
									<option value="rosa">Rosa</option>
									<option value="azul-escuro">Azul Escuro</option>
									<option value="verde">Verde</option>
									<option value="roxo">Roxo</option>
									<option value="vermelho">Vermelho</option>
									<option value="verde-escuro">Verde Escuro</option>
									<option value="laranja">Laranja</option>
									<option value="amarelo">Amarelo</option>
								</select>
							</div> 	
						</div>
					</div>


					<div class="row">
						<div class="col-md-8">
							<div class="form-group">
								<label for="exampleInputEmail1">Descrição</label>
								<input maxlength="255" type="text" class="form-control" id="descricao" name="descricao" placeholder="Pequena Descrição" >    
							</div> 	
						</div>

						<div class="col-md-4">
							<div class="form-group">
								<label for="exampleInputEmail1">Mais Sabores</label>
								<select class="form-control" id="mais_sabores" name="mais_sabores">
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
									<img src="images/<?php echo $pag ?>/sem-foto.jpg"  width="80px" id="target">									
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
					<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Sigla</label>
								<input maxlength="5" type="text" class="form-control" id="sigla" name="sigla" placeholder="P / M / G" >    
							</div> 	
						</div>	

					<div class="col-md-6">
							<div class="form-group">
								<label for="exampleInputEmail1">Nome</label>
								<input maxlength="35" type="text" class="form-control" id="nome_var" name="nome" placeholder="Pequena / Média ..." required>    
							</div> 	
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Máx Sabores</label>
								<input type="number" class="form-control" id="sabores" name="sabores" value="0">    
							</div> 	
						</div>
						
						
					</div>	

				<div class="row">

						<div class="col-md-9">
							<div class="form-group">
								<label for="exampleInputEmail1">Descrição</label>
								<input maxlength="50" type="text" class="form-control" id="descricao_var" name="descricao" placeholder="8 Fatias" >    
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






<!-- Modal Adicionais-->
<div class="modal fade" id="modalAdicionais" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_nome_adc"></span></h4>
				<button id="btn-fechar-adc" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form id="form-adc">



				<div class="row">

						<div class="col-md-5">
							<div class="form-group">								
								<input maxlength="50" type="text" class="form-control" id="nome_adc" name="nome" placeholder="Nome" required>    
							</div> 	
						</div>

						<div class="col-md-4">
							<div class="form-group">								
								<input maxlength="50" type="text" class="form-control" id="valor_adc" name="valor" placeholder="Valor" required>    
							</div> 	
						</div>

						<div class="col-md-3">
							<button type="submit" class="btn btn-primary">Salvar</button>
						
						</div>
					</div>	
				
				<input type="hidden" id="id_adc" name="id">
				
				</form>

				<br>
					<small><div id="mensagem-adc" align="center"></div></small>


					<hr>
					<div id="listar-adc"></div>
			</div>

			
		</div>
	</div>
</div>








<!-- Modal Bordas-->
<div class="modal fade" id="modalBordas" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_nome_bordas"></span></h4>
				<button id="btn-fechar-bordas" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form id="form-bordas">



				<div class="row">

						<div class="col-md-5">
							<div class="form-group">								
								<input maxlength="50" type="text" class="form-control" id="nome_bordas" name="nome" placeholder="Nome" required>    
							</div> 	
						</div>

						<div class="col-md-4">
							<div class="form-group">								
								<input maxlength="50" type="text" class="form-control" id="valor_bordas" name="valor" placeholder="Valor" required>    
							</div> 	
						</div>

						<div class="col-md-3">
							<button type="submit" class="btn btn-primary">Salvar</button>
						
						</div>
					</div>	
				
				<input type="hidden" id="id_bordas" name="id">
				
				</form>

				<br>
					<small><div id="mensagem-bordas" align="center"></div></small>


					<hr>
					<div id="listar-bordas"></div>
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



</script>







 <script type="text/javascript">
	

$("#form-adc").submit(function () {

	var id_adc = $('#id_adc').val()

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'paginas/' + pag + "/inserir-adicionais.php",
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem-adc').text('');
            $('#mensagem-adc').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {

                //$('#btn-fechar-var').click();
                listarAdicionais(id_adc); 
                limparCamposAdc();         

            } else {

                $('#mensagem-adc').addClass('text-danger')
                $('#mensagem-adc').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});
</script>


<script type="text/javascript">
	function limparCamposAdc(){
		
		$('#nome_adc').val('');
		$('#valor_adc').val('');				
		
	}


	function listarAdicionais(id){
    $.ajax({
        url: 'paginas/' + pag + "/listar-adicionais.php",
        method: 'POST',
        data: {id},
        dataType: "html",

        success:function(result){
            $("#listar-adc").html(result);
            $('#mensagem-excluir-adc').text('');
        }
    });
}





function excluirAdc(id){
	var id_adc = $('#id_adc').val()
    $.ajax({
        url: 'paginas/' + pag + "/excluir-adicionais.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Excluído com Sucesso") {                
                listarAdicionais(id_adc);                
            } else {
                $('#mensagem-excluir-adc').addClass('text-danger')
                $('#mensagem-excluir-adc').text(mensagem)
            }

        },      

    });
}




function ativarAdc(id, acao){
	var id_adc = $('#id_adc').val()
    $.ajax({
        url: 'paginas/' + pag + "/mudar-status-adc.php",
        method: 'POST',
        data: {id, acao},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Alterado com Sucesso") {                
                listarAdicionais(id_adc);                
            } else {
                $('#mensagem-excluir-adc').addClass('text-danger')
                $('#mensagem-excluir-adc').text(mensagem)
            }

        },      

    });
}


</script>







 <script type="text/javascript">
	

$("#form-bordas").submit(function () {

	var id_bordas = $('#id_bordas').val()

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'paginas/' + pag + "/inserir-bordas.php",
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem-bordas').text('');
            $('#mensagem-bordas').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {

                //$('#btn-fechar-var').click();
                listarBordas(id_bordas); 
                limparCamposBordas();         

            } else {

                $('#mensagem-bordas').addClass('text-danger')
                $('#mensagem-bordas').text(mensagem)
            }


        },

        cache: false,
        contentType: false,
        processData: false,

    });

});
</script>


<script type="text/javascript">
	function limparCamposBordas(){
		
		$('#nome_bordas').val('');
		$('#valor_bordas').val('');				
		
	}


	function listarBordas(id){
    $.ajax({
        url: 'paginas/' + pag + "/listar-bordas.php",
        method: 'POST',
        data: {id},
        dataType: "html",

        success:function(result){
            $("#listar-bordas").html(result);
            $('#mensagem-excluir-bordas').text('');
        }
    });
}





function excluirBordas(id){
	var id_bordas = $('#id_bordas').val()
    $.ajax({
        url: 'paginas/' + pag + "/excluir-bordas.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Excluído com Sucesso") {                
                listarBordas(id_bordas);                
            } else {
                $('#mensagem-excluir-bordas').addClass('text-danger')
                $('#mensagem-excluir-bordas').text(mensagem)
            }

        },      

    });
}




function ativarBordas(id, acao){
	var id_bordas = $('#id_bordas').val()
    $.ajax({
        url: 'paginas/' + pag + "/mudar-status-bordas.php",
        method: 'POST',
        data: {id, acao},
        dataType: "text",

        success: function (mensagem) {            
            if (mensagem.trim() == "Alterado com Sucesso") {                
                listarBordas(id_bordas);                
            } else {
                $('#mensagem-excluir-bordas').addClass('text-danger')
                $('#mensagem-excluir-bordas').text(mensagem)
            }

        },      

    });
}


</script>
