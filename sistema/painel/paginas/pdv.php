<?php 
$_SESSION['sessao_usuario'] = $id_usuario;
$pag = 'pdv';

$id_mesa = @$_POST['id_mesa'];

$query2 = $pdo->query("SELECT * FROM abertura_mesa where mesa = '$id_mesa' and status = 'Aberta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$id_ab_mesa = @$res2[0]['id'];
	$_SESSION['id_ab_mesa'] = $id_ab_mesa;
}else{
	$id_ab_mesa = "";
	$_SESSION['id_ab_mesa'] = "";
}

//buscar os dados da mesa
$query2 = $pdo->query("SELECT * FROM mesas where id = '$id_mesa' ");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_mesa = 'Mesa: ' . @$res2[0]['nome'];
	$_SESSION['nome_mesa'] = $nome_mesa;
}else{
	$nome_mesa = "";
	 $_SESSION['nome_mesa'] = '';
}


//verificar se ele tem a permissão de estar nessa página
if(@$vendas == 'ocultar'){
	echo "<script>window.location='index.php'</script>";
	exit();
}

//verificar se o caixa está aberto
$query = $pdo->query("SELECT * from caixas where operador = '$id_usuario' and data_fechamento is null");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	
}else{
	//echo '<script>alert("Não possui caixa Aberto, abra o caixa!")</script>';
	if($abertura_caixa == 'Sim' and $nivel_usuario != 'Administrador'){
		//echo '<script>window.location="caixas"</script>';  
	}	
}


$data_atual = date('Y-m-d');

?>

<!-- PRELOADER (Bootstrap) -->
<div id="preloader-bs" class="position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-90 d-flex align-items-center justify-content-center" style="z-index:9999;">
  <div class="text-center">
    <div class="spinner-border" role="status" aria-label="Carregando"></div>
    <div class="small text-muted mt-2">Carregando...</div>
  </div>
</div>

<script>
  function hidePreloaderBS(){ var el=document.getElementById('preloader-bs'); if(el){ el.style.opacity='0'; el.style.transition='opacity .25s ease'; setTimeout(()=>el.remove(), 250); } }
  window.addEventListener('load', hidePreloaderBS);
  setTimeout(hidePreloaderBS, 8000); // fallback
  window.PDVPreloaderBS = {
    show: ()=>{ if(!document.getElementById('preloader-bs')){ const d=document.createElement('div'); d.id='preloader-bs'; d.className='position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-90 d-flex align-items-center justify-content-center'; d.style.zIndex='9999'; d.innerHTML='<div class="text-center"><div class="spinner-border" role="status" aria-label="Processando"></div><div class="small text-muted mt-2">Processando...</div></div>'; document.body.appendChild(d);} },
    hide: hidePreloaderBS
  };
</script>


<link rel="stylesheet" type="text/css" href="css/pdv.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="pdv_body">
 <!-- Main Content -->
  <main class="">
    <div class="flex flex-col lg:flex-row gap-6 pdv-container bg-white">
      <!-- Produtos Section -->
      <div class="w-full lg:w-2/3 products-section">
        <!-- Barra de Busca e Categorias -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
          <div class="search-container mb-4">
            <i class="fas fa-search search-icon"></i>
            <input style="font-weight: normal" onkeyup="buscar()" type="text" placeholder="Buscar produtos..." class="form-input" name="txt_buscar" id="txt_buscar">
           
          </div>
          
          <div class="flex overflow-x-auto pb-2 gap-3">
           
          	
		<?php 
		$query = $pdo->query("SELECT * from categorias where ativo = 'Sim' order by nome asc");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$linhas = @count($res);
		if($linhas > 0){
			for($i=0; $i<$linhas; $i++){
				$id = $res[$i]['id'];
				$nome = $res[$i]['nome'];	
				$foto = $res[$i]['foto'];	
				$ativo = $res[$i]['ativo'];
				$icone = $res[$i]['foto'];
				$mais_sabores = $res[$i]['mais_sabores'];

				$nomeF = mb_strimwidth($nome, 0, 20, "..."); 
				
		//totalizar produtos
				$query2 = $pdo->query("SELECT * from produtos where categoria = '$id' and ativo = 'Sim' and (estoque > 0 or tem_estoque = 'Não')  ");
				$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
				$produtos = @count($res2);

				if($produtos > 0){
					?>
	            <button type="button" onclick="<?php echo (strcasecmp($mais_sabores,'Sim')===0) ? "pizza('".htmlspecialchars($id,ENT_QUOTES)."', '".htmlspecialchars($nome,ENT_QUOTES)."')" : "listar('".htmlspecialchars($id,ENT_QUOTES)."')"; ?>" class="category-pill flex flex-col items-center px-3 py-2 rounded-xl bg-white text-secondary text-sm font-medium whitespace-nowrap shadow-sm">
				  <div class="w-12 h-12 rounded-full bg-neutral-100 flex items-center justify-center mb-1 overflow-hidden">
				    <img src="images/categorias/<?php echo $foto ?>" class="w-full h-full object-cover rounded-full">
				  </div>
				  <span class="text-dark"><small><?php echo $nomeF ?></small></span>
				</button>


        <?php } } } ?>

          
          </div>
        </div>
        
        <!-- Grid de Produtos -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 bg-white" id="listar">


         
          
          
         
        </div>
      </div>
      
      <!-- Carrinho Section -->
      <div class="w-full lg:w-1/3 cart-section">
        <div class="bg-white rounded-xl shadow-sm p-4">
                   <!-- Lista de Itens no Carrinho -->
         <div id="listar_vendas" style="margin-top: -10px"></div>
          
          
          <form id="form_venda">     

          <?php if($id_ab_mesa == ""){ ?>    
          <!-- Cliente e Pagamento -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-dark mb-1">Cliente</label>
            <div class="flex gap-2">
              
              <div class="col-md-10" style="padding:0">
              <div id="listar_clientes">
										
			  </div>
			  </div>
            	
            	<div class="col-md-2" style="">
              <button type="button" class="btn-search bg-success" data-bs-toggle="modal" data-bs-target="#modalCliente">
                <i class="fas fa-plus"></i>
              </button>
          </div>
            </div>
          </div>
          
         <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
            <label class="block text-sm font-medium text-dark mb-1">Forma de Pagamento</label>
            <select class="form-select" id="saida" name="saida" required="">
              <option value="">Forma de Pgto</option>
												<?php 
												$query = $pdo->query("SELECT * FROM formas_pgto order by id asc");
												$res = $query->fetchAll(PDO::FETCH_ASSOC);
												for($i=0; $i < @count($res); $i++){
													foreach ($res[$i] as $key => $value){}

														?>	
													<option value="<?php echo $res[$i]['nome'] ?>"><?php echo $res[$i]['nome'] ?></option>

												<?php } ?>
            </select>
          </div>  


          <div>
              <label class="block text-sm font-medium text-dark mb-1">Data de Pagamento</label>
            <input type="date" class="form-control" id="data2" name="data2" value="<?php echo $data_atual; ?>">
            </div> 


          </div>    
       

           <div class="grid grid-cols-2 gap-4 mb-4">            
            <div>
              <label class="block text-sm font-medium text-dark mb-1">Valor Pago</label>
              <input type="text" class="form-control" id="valor_pago" name="valor_pago" value="0" onkeyup="FormaPg()" readonly="">
            </div>

             <div>
            	<label class="block text-sm font-medium text-dark mb-1">Desconto <a id="desc_reais" class="desconto_link_ativo" href="#" onclick="tipoDesc('reais')">R$</a> / <a id="desc_p" class="desconto_link_inativo" href="#" onclick="tipoDesc('%')">%</a></label>
             
              <input type="number" class="form-control" id="desconto" name="desconto" placeholder="R$" onkeyup="listarVendas()">
            </div>
          </div>
         
          
          <div class="grid grid-cols-2 gap-4 mb-4">          
            
             <div>
              <label class="block text-sm font-medium text-dark mb-1">Frete</label>
              <input type="text" class="form-control" name="frete" id="frete" placeholder="Valor Frete"  onkeyup="listarVendas()">
            </div>

            <div>
              <label class="block text-sm font-medium text-dark mb-1">Troco Para</label>
              <input type="number" class="form-control" id="troco" placeholder="R$" name="troco" onkeyup="listarVendas()">
            </div>


          </div>
          
        


          <div id="div_pgto2">
									<span><b>Total Restante: <span class="text-danger">R$ <span id="total_restante"></span></span></b></span>
								

									<div class="grid grid-cols-2 gap-4 mb-4">

									<div>						
										<label class="block text-sm font-medium text-dark mb-1">Data Pagamento 2</label> 
										<input type="date" class="form-control" id="data_restante" name="data_restante" value="<?php echo $data_atual ?>">
										
									</div>

									<div>					
											<label class="block text-sm font-medium text-dark mb-1">Pgto Restante</label>
											<select class="form-select" name="forma_pgto2" id="forma_pgto2"> 
												<option value="">Forma de Pgto</option>
												<?php 
												$query = $pdo->query("SELECT * FROM formas_pgto  order by id asc");
												$res = $query->fetchAll(PDO::FETCH_ASSOC);
												for($i=0; $i < @count($res); $i++){
													foreach ($res[$i] as $key => $value){}

														?>	
													<option value="<?php echo $res[$i]['nome'] ?>"><?php echo $res[$i]['nome'] ?></option>

												<?php } ?>

											</select>
									</div>

								</div>

								</div>

			<?php } ?>
          
          <div class="flex gap-3 mt-6">

          	<?php if($id_ab_mesa == ""){ ?>
            <button type="button" class="btn-cancel flex-1" onclick="limparVenda()">
              <i class="fas fa-times mr-2"></i> Limpar Venda
            </button>
        <?php } ?>

            <?php if($id_ab_mesa > 0){ ?>
            	<a href="#" onclick="window.close()" class="btn-finalize flex-1">
              <i class="fas fa-check mr-2"></i> Adiconar a Mesa
            </a>
            <?php }else{ ?>
            	<button id="btn_venda" type="submit" class="btn-finalize flex-1">
              <i class="fas fa-check mr-2"></i> Finalizar Venda
            </button>
            <?php } ?>
            
            <img id="img_loading" src="../img/loading.gif" width="40px" style="display:none">
          </div>




							<input type="hidden" name="cliente" id="cliente_input">

							<input type="hidden" name="tipo_desconto" id="tipo_desconto" value="reais">

							<input type="hidden" name="subtotal_venda" id="subtotal_venda">

							<input type="hidden" name="valor_restante" id="valor_restante">



      </form>


        </div>
      </div>
    </div>
  </main>

 </div>





<!-- Modal Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel">Adicionar Cliente</h4>
				<button id="btn-fechar-cliente" aria-label="Close" class="btn-close" data-bs-toggle="modal" data-bs-target="#modalForm" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			<form id="form-cliente">
			<div class="modal-body">
				

					<div class="row">
						<div class="col-md-6 mb-2 col-6 needs-validation was-validated">
							<label>Nome <span class="text-danger" style="font-size: 9px">(Obrigatório)</span></label>
							<input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" required>
						</div>

						<div class="col-md-3 col-6">
							<label>Telefone</label>
							<input type="text" class="form-control" id="telefone" name="telefone" placeholder="Telefone">
						</div>

						<div class="col-md-3 mb-2">
							<label>Nascimento</label>
							<input type="date" class="form-control" id="data_nasc" name="data_nasc" placeholder="">
						</div>


					</div>


					<div class="row">

						<div class="col-md-3 mb-2 col-6">
							<label>CPF</label>
							<input type="text" class="form-control" id="cpf" name="cpf" placeholder="CPF">
						</div>

						<div class="col-md-6">
							<label>Email</label>
							<input type="email" class="form-control" id="email" name="email" placeholder="Email">
						</div>

						<div class="col-md-3 mb-2">
							<label>CEP</label>
							<input type="text" class="form-control" id="cep" name="cep" placeholder="CEP"
								onblur="pesquisacep(this.value);">
						</div>


					</div>
					<div class="row">



						<div class="col-md-7 mb-2">
							<label>Rua</label>
							<input type="text" class="form-control" id="endereco" name="endereco" placeholder="Rua">
						</div>

						<div class="col-md-2 mb-2">
							<label>Número</label>
							<input type="text" class="form-control" id="numero" name="numero" placeholder="Número">
						</div>

						<div class="col-md-3 mb-2">
							<label>Complemento</label>
							<input type="text" class="form-control" id="complemento" name="complemento" placeholder="AP 100">
						</div>


					</div>



					<div class="row">

						<div class="col-md-4 mb-2">
							<label>Bairro</label>
							<input type="text" class="form-control" id="bairro" name="bairro" placeholder="Bairro">
						</div>

						<div class="col-md-5 mb-2">
							<label>Cidade</label>
							<input type="text" class="form-control" id="cidade" name="cidade" placeholder="Cidade">
						</div>

						<div class="col-md-3 mb-2">
							<label>Estado</label>
							<select class="form-select" id="estado" name="estado">
								<option value="">Selecionar</option>
								<option value="AC">Acre</option>
								<option value="AL">Alagoas</option>
								<option value="AP">Amapá</option>
								<option value="AM">Amazonas</option>
								<option value="BA">Bahia</option>
								<option value="CE">Ceará</option>
								<option value="DF">Distrito Federal</option>
								<option value="ES">Espírito Santo</option>
								<option value="GO">Goiás</option>
								<option value="MA">Maranhão</option>
								<option value="MT">Mato Grosso</option>
								<option value="MS">Mato Grosso do Sul</option>
								<option value="MG">Minas Gerais</option>
								<option value="PA">Pará</option>
								<option value="PB">Paraíba</option>
								<option value="PR">Paraná</option>
								<option value="PE">Pernambuco</option>
								<option value="PI">Piauí</option>
								<option value="RJ">Rio de Janeiro</option>
								<option value="RN">Rio Grande do Norte</option>
								<option value="RS">Rio Grande do Sul</option>
								<option value="RO">Rondônia</option>
								<option value="RR">Roraima</option>
								<option value="SC">Santa Catarina</option>
								<option value="SP">São Paulo</option>
								<option value="SE">Sergipe</option>
								<option value="TO">Tocantins</option>
								<option value="EX">Estrangeiro</option>
							</select>
						</div>


					</div>


					<input type="hidden" class="form-control" id="id" name="id">					

				<br>
				<small><div id="mensagem_cliente" align="center"></div></small>
			</div>
			<div class="modal-footer">       
				<button type="submit" id="btn_salvar_cliente" class="btn btn-primary">Salvar</button>
			</div>
			</form>
		</div>
	</div>
</div>








<!-- Modal Quantidade -->
<div class="modal fade" id="modalQuantidade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog ">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel">Quantidade: <span id="nome_do_prod"></span></h4>
				<button id="btn-fechar-quant" aria-label="Close" class="btn-close" data-bs-toggle="modal" data-bs-target="#modalForm" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			
			<div class="modal-body">
				
				<div class="row">
						<div class="col-md-8 mb-2">							
								<label>Quantidade em <span id="uni_do_prod"></span></label>
								<input type="text" class="form-control" id="quantidade_prod" placeholder="0.5" onkeyup="mascara_decimal('quantidade_prod')">							
						</div>

						<div class="col-md-4" style="margin-top: 22px">							
								<a onclick="addVenda('', '', '', '')" href="#"  class="btn btn-primary">Adicionar</a>					
						</div>

						<input type="hidden" id="id_do_p">

						
					</div>

			
			</div>		
			
		</div>
	</div>
</div>






<!-- Modal Grade -->
<div class="modal fade" id="modalGrade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog ">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_do_produto_grade"></span></h4>
				<button id="btn-fechar-grade" aria-label="Close" class="btn-close" data-bs-toggle="modal" data-bs-target="#modalForm" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			
			<div class="modal-body">
				
			<div id="listar_grades">
				
			</div>
			
			</div>		
			
		</div>
	</div>
</div>





<!-- Modal Sabores -->
<div class="modal fade" id="modalSabores" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog ">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_do_produto_sabores"></span></h4>
				<button id="btn-fechar-sabores" aria-label="Close" class="btn-close" data-bs-toggle="modal" data-bs-target="#modalForm" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			
			<div class="modal-body">
				
			<div id="listar_sabores">
				
			</div>
			
			</div>		
			
		</div>
	</div>
</div>




		<script type="text/javascript">var pag = "<?=$pag?>"</script>
		<script src="js/ajax.js"></script>

		<script type="text/javascript">
			$(document).ready(function() {

				setTimeout(function() {
			        $('#txt_buscar').focus();
			    }, 100);

				if ($(window).width() > 768) {
				    $('#btn_ocultar_menu').click();
				}

				$('#div_pgto2').hide();
			listarVendas()		

				$('.sel2').select2({
					dropdownParent: $('#modalForm')
				});

				$(document).on('select2:open', () => {
					document.querySelector('.select2-search__field').focus();
				});
				listarClientes();
			});
		</script>

		<script type="text/javascript">	
			

			let envioEmAndamento = false;

$("#form_venda").submit(function (event) {
	event.preventDefault();

	if (envioEmAndamento) return; // impede múltiplos envios

	envioEmAndamento = true;

	// Força o blur nos inputs de quantidade
	$("[id^='input_quant_']").each(function () {
		$(this).trigger('blur');
	});

	// Aguarda 200ms e executa o restante do envio
	setTimeout(() => {
		$("#btn_venda").hide();
		$("#btn_limpar").hide();
		$("#img_loading").show();

		var data = $("#data2").val();
		var cliente = $("#cliente").val();
		var data_atual = "<?=$data_atual?>";

		if (data > data_atual && cliente == "") {
			alert('Você precisa selecionar um cliente para essa venda!');
			$("#img_loading").hide();
			$("#btn_venda").show();
			envioEmAndamento = false;
			return;
		}

		var formData = new FormData($("#form_venda")[0]);

		$.ajax({
			url: 'paginas/' + pag + "/salvar.php",
			type: 'POST',
			data: formData,
			success: function (mensagem) {				
				var msg = mensagem.split("-");

				$('#mensagem').text('');
				$('#mensagem').removeClass();

				if (msg[0].trim() == "Salvo com Sucesso") {
					$("#img_loading").hide();

					$('#desconto').val('');
					$('#troco').val('');
					$('#frete').val('');
					$('#cliente').val('').change();
					$('#cliente_input').val('');
					$('#data').val('<?=$data_atual?>');

					listar(); 
					listarVendas();

					var imp_auto = "<?=$impressao_automatica?>";

					if (imp_auto != 'Não') {						
						window.open('rel/comprovante_balcao.php?id=' + msg[1]);
						$('#div_pgto2').hide();
					} else {
						alertSucesso('Venda Efetuada!');
						$('#div_pgto2').hide();
					}
				} else {
					alertWarning(msg[0]);
					$("#btn_venda").show();
					$("#img_loading").hide();
					$("#btn_limpar").show();
				}

				$("#btn_venda").show();
				$("#btn_limpar").show();
				envioEmAndamento = false;
			},

			cache: false,
			contentType: false,
			processData: false,
		});
	}, 500); // Espera 200ms
});





			function buscar(){
				var busca = $('#txt_buscar').val();
				listar('', busca)
			}

			

			function listarVendas(){
				var desconto = $("#desconto").val();
				var frete = $("#frete").val();	
				var troco = $("#troco").val();
				var tipo_desconto = $("#tipo_desconto").val();	
				$.ajax({
					url: 'paginas/' + pag + "/listar_vendas.php",
					method: 'POST',
					data: {desconto, troco, tipo_desconto, frete},
					dataType: "html",

					success:function(result){
						$("#listar_vendas").html(result);            
					}
				});

				FormaPg()
			}

			function limparVenda(){
				$("#cliente").val('').change();
				$("#quantidade").val('1');
				$("#desconto").val('');
				$("#troco").val('');
				$("#frete").val('');
				$("#data").val('<?=$data_atual?>');
				$("#cliente_input").val('');
				$('#div_pgto2').hide();
				listarVendas()

				$("#btn_limpar").hide();		
				$.ajax({
					url: 'paginas/' + pag + "/limpar_venda.php",
					method: 'POST',
					data: {},
					dataType: "html",

					success:function(result){        	
						listarVendas(); 
						limparCampos();     
					}
				});
				
			}
		</script>

		<script type="text/javascript">
	$("#form-cliente").submit(function () { 

    $('#mensagem_cliente').text('Salvando!!');
    $('#btn_salvar_cliente').hide();

    event.preventDefault();
    var formData = new FormData(this);
    var nova_pag = 'clientes';

    $.ajax({
        url: 'paginas/' + nova_pag + "/salvar.php",
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem_cliente').text('');
            $('#mensagem_cliente').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {

                $('#btn-fechar-cliente').click();
                listar();
                listarClientes('1'); 


            } else {

                $('#mensagem_cliente').addClass('text-danger')
                $('#mensagem_cliente').text(mensagem)
            }

             $('#btn_salvar_cliente').show();

        },

        cache: false,
        contentType: false,
        processData: false,

    });

});


	function listarClientes(valor){
	$.ajax({
        url: 'paginas/' + pag + "/listar_clientes.php",
        method: 'POST',
        data: {valor},
        dataType: "html",

        success:function(result){
            $("#listar_clientes").html(result);           
        }
    });
}


function tipoDesc(p){
	$('#desc_reais').removeClass()
	$('#desc_p').removeClass()

	if(p == '%'){
		$('#desconto').attr('placeholder', '%');
		$('#desc_reais').addClass('desconto_link_inativo')
		$('#desc_p').addClass('desconto_link_ativo')
	}else{
		$('#desconto').attr('placeholder', 'R$');
		$('#desc_reais').addClass('desconto_link_ativo')
		$('#desc_p').addClass('desconto_link_inativo')
	}

	 $("#tipo_desconto").val(p);
	 listarVendas();  
}



function FormaPg(){
	var valor_pago = $('#valor_pago').val();
	var subtotal_venda = $('#subtotal_venda').val();

	if(parseFloat(valor_pago) < parseFloat(subtotal_venda)){
		$('#div_pgto2').show();
	}else{
		$('#div_pgto2').hide();
	}

	if(valor_pago == ""){
		valor_pago = 0;
	}

	if(subtotal_venda == ""){
		subtotal_venda = 0;
	}

	var total_restante = parseFloat(subtotal_venda) - parseFloat(valor_pago);
	$('#total_restante').text(total_restante.toFixed(2));
	$('#valor_restante').val(total_restante);
}
		</script>






<script>
    
    function limpa_formulário_cep() {
            //Limpa valores do formulário de cep.
            document.getElementById('endereco').value=("");
            document.getElementById('bairro').value=("");
            document.getElementById('cidade').value=("");
            document.getElementById('estado').value=("");
            //document.getElementById('ibge').value=("");
    }

    function meu_callback(conteudo) {
        if (!("erro" in conteudo)) {
            //Atualiza os campos com os valores.
            document.getElementById('endereco').value=(conteudo.logradouro);
            document.getElementById('bairro').value=(conteudo.bairro);
            document.getElementById('cidade').value=(conteudo.localidade);
            document.getElementById('estado').value=(conteudo.uf);
            //document.getElementById('ibge').value=(conteudo.ibge);
        } //end if.
        else {
            //CEP não Encontrado.
            limpa_formulário_cep();
            alertWarning("CEP não encontrado.");
        }
    }
        
    function pesquisacep(valor) {

        //Nova variável "cep" somente com dígitos.
        var cep = valor.replace(/\D/g, '');

        //Verifica se campo cep possui valor informado.
        if (cep != "") {

            //Expressão regular para validar o CEP.
            var validacep = /^[0-9]{8}$/;

            //Valida o formato do CEP.
            if(validacep.test(cep)) {

                //Preenche os campos com "..." enquanto consulta webservice.
                document.getElementById('endereco').value="...";
                document.getElementById('bairro').value="...";
                document.getElementById('cidade').value="...";
                document.getElementById('estado').value="...";
                //document.getElementById('ibge').value="...";

                //Cria um elemento javascript.
                var script = document.createElement('script');

                //Sincroniza com o callback.
                script.src = 'https://viacep.com.br/ws/'+ cep + '/json/?callback=meu_callback';

                //Insere script no documento e carrega o conteúdo.
                document.body.appendChild(script);

            } //end if.
            else {
                //cep é inválido.
                limpa_formulário_cep();
                alertWarning("Formato de CEP inválido.");
            }
        } //end if.
        else {
            //cep sem valor, limpa formulário.
            limpa_formulário_cep();
        }
    };

    </script>





<!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              DEFAULT: '#0F4C81',
              dark: '#0A3A62',
              light: '#1A6DB3'
            },
            secondary: {
              DEFAULT: '#1E293B',
              dark: '#0F172A',
              light: '#334155'
            },
            accent: {
              DEFAULT: '#38BDF8',
              dark: '#0284C7',
              light: '#7DD3FC'
            },
            success: {
              DEFAULT: '#10B981',
              dark: '#059669',
              light: '#34D399'
            },
            warning: {
              DEFAULT: '#F59E0B',
              dark: '#D97706',
              light: '#FBBF24'
            },
            danger: {
              DEFAULT: '#EF4444',
              dark: '#DC2626',
              light: '#F87171'
            },
            neutral: {
              50: '#F8FAFC',
              100: '#F1F5F9',
              200: '#E2E8F0',
              300: '#CBD5E1',
              400: '#94A3B8',
              500: '#64748B',
              600: '#475569',
              700: '#334155',
              800: '#1E293B',
              900: '#0F172A',
            }
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
          boxShadow: {
            'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
            'card-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
          }
        }
      }
    }
  </script>


  <script type="text/javascript">
  $(document).keyup(function(e) {
  		if(e.keyCode === 13){

  			var codigo = $("#txt_buscar").val(); 
  			if(codigo == ""){
  				return;
  			}  
            
            $.ajax({
		        url: 'paginas/' + pag + "/buscar_produto.php",
		        method: 'POST',
		        data: {codigo},
		        dataType: "html",

		        success:function(result){

		            if(result.trim() === 'Código do Produto não encontrado!'){
		            	limparCampos();
		            	alertCodigo(result)

		            }else{
		            	// Faz o split separando por vírgula
						var partes = result.split(',');

						// Agora cada valor está separado:
						var id = partes[0];
						var estoque_unit = partes[1];
						var nome = partes[2];
						var nome_unidade = partes[3];
		            	addVenda(id, estoque_unit, nome, nome_unidade);

		            	if(estoque_unit != 'Não'){
		            		var audio = new Audio('../../img/barCode.wav');
					         audio.addEventListener('canplaythrough', function() {
					          audio.play();
					        });

					         //limparCampos();
		            	}
		            	
		            }        
		        }
		    });

        }
  });
  </script>


  <script type="text/javascript">
  	$(document).ready(function() {
    $('#quantidade_prod').on('keyup', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addVenda('', '', '', '');

            var audio = new Audio('../../img/barCode.wav');
				         audio.addEventListener('canplaythrough', function() {
				          audio.play();
				        });

				         //limparCampos();
        }
    });
});
  </script>



  <script type="text/javascript">
  	function alertCodigo(){
        $('body').removeClass('timer-alert');
        Swal.fire({
            title: 'Produto não Encontrado!',
            text: 'Código do produto não Cadastrado',
            icon: "error",
            timer: 1500,
            customClass: {
            container: 'swal-whatsapp-container'
        }
        })?.then(
            function () {
            },
            // lidando com a rejeição da promessa
            function (dismiss) {
                if (dismiss === 'timer') {
                    console.log('Eu estava fechado pelo cronômetro')
                }
            }
        )
    }
  </script>




<script type="text/javascript">
	function addVenda(id_produto, estoque, produto, unidade, tem_grade){

				if(tem_grade == 'Sim'){
					gradeProduto(id_produto, estoque, produto, unidade);
					return;
				}

				if(id_produto == ""){
					var id_produto = $('#id_do_p').val();
					var quantidade = $('#quantidade_prod').val();
					$('#btn-fechar-quant').click();
					$('#quantidade_prod').val('');
				}else{
					var quantidade = 1;
					$('#id_do_p').val(id_produto);
				}
				
				$('#nome_do_prod').text(produto);
				$('#uni_do_prod').text(unidade);

				if(estoque == 'Não'){
					setTimeout(function() {						
					$('#quantidade_prod').val('');
			        $('#quantidade_prod').focus();

			    }, 500);
					$('#modalQuantidade').modal('show');

					return;
				}


				if(quantidade <= 0){
					alertWarning('A quantidade deve ser maior que zero')
					return;
				}
				
				$.ajax({
					url: 'paginas/' + pag + "/inserir_item.php",
					method: 'POST',
					data: {quantidade, id_produto},
					dataType: "html",

					success:function(mensagem){
						var msg = mensagem.split('*');
						var qtd = msg[1];
						var tot = msg[2];
						var vlr_prd = msg[3];
						var id_pdr = msg[4];

						if (msg[0].trim() == "Inserido com Sucesso") {  

							//chamar add carrinho
							addCarrinhoDireto(qtd, id_pdr, tot, vlr_prd);
							
							inserido();

							setTimeout(function() {
						       listarVendas();
						       limparCampos();
						       buscar();
						    }, 1000);

						}else{
							alertWarning(mensagem)
						}
					}
				});


				
			}
</script>


  <script type="text/javascript">
  	function gradeProduto(id_produto, estoque, produto, unidade){
  		$('#nome_do_produto_grade').text('Grade: '+produto);  	
    	    	
    	$('#modalGrade').modal('show');
    	listarGrades(id_produto);
  	}

  	function listarGrades(id){
  		$.ajax({
        url: 'paginas/' + pag + "/listar_grades.php",
        method: 'POST',
        data: {id},
        dataType: "html",

        success:function(result){
            $("#listar_grades").html(result);
            
        }
    });
  	}
  </script>



<script type="text/javascript">
    function addCarrinhoDireto(quantidade, produto, total_item, valor_produto) {

       
        var obs = "";
        var tem_var = "";
        var mesa = "<?= $id_ab_mesa ?>";        

        if (total_item <= 0 && valor_produto <= 0) {
            alert("O valor do Pedido é zero, selecione as opções!");
            return;
        }

        if (valor_produto <= 0 && tem_var == 'Sim') {
            alert("Selecione a Variação do Item");
            return;
        }



        $.ajax({
            url: '../../js/ajax/add-carrinho.php',
            method: 'POST',
            data: {
                quantidade,
                total_item,
                produto,
                obs,
                valor_produto,
                mesa
            },
            dataType: "text",

            success: function(mensagem) {
                if (mensagem.trim() == "Inserido com Sucesso") {
                    $('#btn-fechar-grade').click();
                    listarVendas();
                } else {
                    alertWarning(mensagem)
                }

            },

        });
    }
</script>

 

<script type="text/javascript">
	function pizza(id, nome){
		$('#nome_do_produto_sabores').text('Categoria: '+nome);  	
    	    	
    	$('#modalSabores').modal('show');
    	listarSabores(id);
	}


	function listarSabores(id){
  		$.ajax({
        url: 'paginas/' + pag + "/listar_sabores.php",
        method: 'POST',
        data: {id},
        dataType: "html",

        success:function(result){
            $("#listar_sabores").html(result);
            
        }
    });
  	}
</script>
