<?php 
$pag = 'clientes';
$itens_pag = 10;

if(@$clientes == 'ocultar'){
	echo "<script>window.location='index'</script>";
	exit();
}

// pegar a pagina atual
if(@$_POST['pagina'] == ""){
	@$_POST['pagina'] = 0;
}
$pagina = intval(@$_POST['pagina']);
$limite = $pagina * $itens_pag;

$numero_pagina = $pagina + 1;

if($pagina > 0){
	$pag_anterior = $pagina - 1;
	$pag_inativa_ant = '';
}else{
	$pag_anterior = $pagina;
	$pag_inativa_ant = 'desabilitar_botao';
}

$pag_proxima = $pagina + 1;


//totalizar páginas
$query2 = $pdo->query("SELECT * from clientes where nome like '%$buscar%' or telefone like '%$buscar%' or email like '%$buscar%' or cpf like '%$buscar%' order by id desc");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$linhas2 = @count($res2);

$num_paginas = ceil($linhas2/$itens_pag);					
if($pag_proxima == $num_paginas){
	$pag_inativa_prox = 'desabilitar_botao';
	$pag_proxima = $pagina;						
}else{
	$pag_inativa_prox = '';

}


?>
<div id="pages_maincontent" style="background: #FFF; ">

	<div class="loginform" style="margin-top: 10px">
		<form method="post">
			<input  type="text" name="buscar" id="buscar" value="<?php echo $buscar ?>" class="form_input required" placeholder="Buscar Clientes" style="background: transparent !important; width:80%; float:left" />
			<button id="btn_filtrar" class="limpar_botao" type="submit"><img src="images/icons/blue/search.png" width="23px" style="float:left; margin-top: 12px"></button>
		</form>
	</div>
	<div class="page_single layout_fullwidth_padding"> 

		<ul class="posts2" id="listar">
			<?php 
			$query = $pdo->query("SELECT * from clientes where nome like '%$buscar%' or telefone like '%$buscar%' or email like '%$buscar%' or cpf like '%$buscar%' order by id desc LIMIT $limite, $itens_pag");
			$res = $query->fetchAll(PDO::FETCH_ASSOC);
			$linhas = @count($res);
			if($linhas > 0){
				for($i=0; $i<$linhas; $i++){
					$id = $res[$i]['id'];
					$nome = $res[$i]['nome'];
					$telefone = $res[$i]['telefone'];
					$email = $res[$i]['email'];	
					$endereco = $res[$i]['endereco'];
					$tipo_pessoa = $res[$i]['tipo_pessoa'];
					$cpf = $res[$i]['cpf'];

					$data_cad = $res[$i]['data_cad'];
					$data_nasc = $res[$i]['data_nasc'];

					$data_cadF = implode('/', array_reverse(@explode('-', $data_cad)));
					$data_nascF = implode('/', array_reverse(@explode('-', $data_nasc)));

					$tel_whatsF = '55'.preg_replace('/[ ()-]+/' , '' , $telefone);
					

					echo <<<HTML
					<li class="swipeout">
					<div class="swiper-wrapper">		
					<div class="swiper-slide swipeout-content item-content">
					<div class="post_entry" onclick="mostrar('{$nome}','{$email}','{$telefone}','{$endereco}', '{$data_cadF}','{$cpf}','{$tipo_pessoa}','{$data_nascF}')" title="Mostrar Dados">
					
					<div class="post_details">
					<div class="post_category textos_list">{$nome}</div>				
					<p style="font-size:12px">{$telefone} / {$data_cadF}</p>
					<p class="subtitulo_list">{$email} </p>
					</div>
					<div class="post_swipe"><img src="images/swipe_more.png" alt="" title="" /></div>
					</div>
					</div>
					<div class="swiper-slide swipeout-actions-right">
					<a target="_blank" style="width: 20%; background: #1d8c32" href="http://api.whatsapp.com/send?1=pt_BR&phone={$tel_whatsF}" title="Whatsapp"><img src="images/icons/white/whatsapp.png" alt="" title="" /></a>
					<a style="width: 20%; background: #234e9e" href="#" onclick="arquivo('{$id}','{$nome}')" title="Inserir Arquivo"><img src="images/icons/white/arquivo.png" alt="" title="" /></a>
					<a style="width: 20%; background: #37de56" href="#" onclick="mostrarContas('{$nome}','{$id}')" title="Mostrar Contas"><img src="images/icons/white/financeiro2.png" alt="" title="" /></a>
					<a onclick="excluir_reg('{$id}', '{$nome}')" style="width: 20%; background: #d4412a" href="#"><img src="images/icons/white/trash.png" alt="" title="Excluir" /></a>
					<a onclick="editar('{$id}','{$nome}','{$email}','{$telefone}','{$endereco}','{$cpf}','{$tipo_pessoa}','{$data_nasc}')" style="width: 20%; background: #3c6cb5" href="#" class="action1"><img src="images/icons/white/edit.png" alt="" title="Editar" /></a>
					
					
					
					
					</div>
					</div>
					</li>	
					HTML;

				}

			}else{
				echo 'Nenhum Registro Encontrado!';
			}
			?> 

		</ul>

		
		<div class="shop_pagination">
			<a href="#" onclick="paginar('<?php echo $pag_anterior ?>', '<?php echo $buscar ?>')" class="prev_shop <?php echo $pag_inativa_ant ?>">ANTERIOR</a>
			<span class="shop_pagenr"><?php echo @$numero_pagina ?>/<?php echo @$num_paginas ?></span>
			<a href="#" onclick="paginar('<?php echo $pag_proxima ?>', '<?php echo $buscar ?>')" class="next_shop <?php echo $pag_inativa_prox ?>">PRÓXIMA</a>
		</div>	

		<form method="post" style="display:none">
			<input type="text" name="pagina" id="input_pagina">
			<input type="text" name="buscar" id="input_buscar">
			<button type="submit" id="paginacao"></button>
		</form>

	</div>	


</div>


<div class="fab" style="z-index: 100 !important">
	<button class="main open-popup"  data-popup=".popup-form" id="btn_novo">
		+
	</button>			  
</div>



</div>
</div>
</div>

</div>
</div>

<div class="popup popup-form" id="popupForm">
	<div class="content-block" style="padding:5px !important">
		<h2 class="page_title" id="titulo_inserir">INSERIR DADOS</h2>      
		<div class="page_single layout_fullwidth_padding">
			<div class="contactform">
				<form id="form" method="post">

					<div class="form_row">
						<label class="labels_form">Nome</label>
						<input type="text" name="nome" id="nome" value="" class="form_input" />
					</div>

					<div class="form_row">
						<label class="labels_form">Email</label>
						<input type="text" name="email" id="email" value="" class="form_input" />
					</div>

					<div style="width:48%; float:left">
						<label class="labels_form">Telefone</label>
						<input type="text" name="telefone" id="telefone" value="" class="form_input"  style="height:25px;"/>
					</div>

					<div style="width:48%; float:right">
						<label class="labels_form">Nascimento</label>
						<input type="date" name="data_nasc" id="data_nasc" value="" class="form_input"  style="height:25px;  background: #FFF"/>
					</div>

					<div style="width:100%; float:left">
						<label class="labels_form">Tipo Pessoa</label>
						<div class="selector_overlay">
							<select required="" name="tipo_pessoa" id="tipo_pessoa" class="" style="height:40px !important;" onchange="mudarPessoa()">
								<option value="Física">Física</option>
								<option value="Jurídica">Jurídica</option>		
							</select>
						</div>
					</div>

					<div style="width:100%; float:left">
						<label class="labels_form">CPF / CNPJ</label>
						<input type="text" name="cpf" id="cpf" value="" class="form_input" placeholder="Insira CPF" style="height:25px;"/>
					</div>

					<div class="form_row">
						<label class="labels_form">Endereço</label>
						<input type="text" name="endereco" id="endereco" value="" class="form_input" />
					</div>
					

					<input type="submit" name="btn_salvar" class="form_submit botao_salvar" id="btn_salvar" value="SALVAR"/>
					<div align="center" style="display:none" id="img_loader"><img src="images/loader.gif"></div>

					<input type="hidden" name="id" id="id">

				</form> 
			</div>    
		</div>          
	</div>
	<div class="close_popup_button">
		<a id="btn-fechar" href="#" onclick="limparCampos()" class="close-popup" data-popup=".popup-form"><img src="images/icons/black/menu_close.png" alt="" title="" /></a>
	</div>
</div>





<a style="display:block" href="#" class="action1 open-popup" data-popup=".popup-mostrar" id="btn_mostrar"></a>
<!-- Social Icons Popup -->
<div class="popup popup-mostrar" style="width:85%; margin:20px;  background: transparent; margin-top: 100px; ">
	<div class="content-block">

		<h2 class="page_title_dados" id="titulo_dados"></h2>   


		<ul class="responsive_table tabela_dados" style="">

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Telefone</div>
				<div id="telefone_dados" class="table_section" style="width:70%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Email</div>
				<div id="email_dados" class="table_section" style="width:70%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Nascimento</div>
				<div id="data_nasc_dados" class="table_section" style="width:70%"></div>                        
			</li>


			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Cadastro</div>
				<div id="data_dados" class="table_section" style="width:70%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Tipo Pessoa</div>
				<div id="pessoa_dados" class="table_section" style="width:70%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">CPF / CNPJ</div>
				<div id="cpf_dados" class="table_section" style="width:70%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Endereço</div>
				<div id="endereco_dados" class="table_section" style="width:70%"></div>                        
			</li>

		</ul>



		<div class="close_popup_button"><a href="#" class="close-popup" data-popup=".popup-mostrar"><img style="margin-top: 10px" src="images/icons/white/menu_close.png" alt="" title="" width="25px"/></a></div>
	</div>
</div>




<a style="display:block" href="#" class="action1 open-popup" data-popup=".popup-excluir" id="btn_excluir"></a>
<div class="popup popup-excluir" style="width:85%; margin:20px;  background: transparent; margin-top: 100px; overflow: none ">
	<div class="content-block">	

		<h2 class="page_title_excluir" id="nome_excluir"></h2> 

		<div class="botoes_excluir" align="center" style="height: 70px; border:1px solid #bdbbbb">
			<div style="width:49%; float:left"><input onclick="excluir()" type="button" class="form_submit botao_excluir botao_modelo" value="EXCLUIR"/></div>
			<div style="width:49%; float:right"><input data-popup=".popup-excluir" type="button" class="form_submit botao_cancelar botao_modelo close-popup" value="CANCELAR"/></div>
		</div>

		<input type="hidden" id="id_excluir">  

		<div class="close_popup_button"><a href="#" class="close-popup" data-popup=".popup-excluir"><img style="margin-top: 10px" src="images/icons/white/menu_close.png" alt="" title="" width="25px"/></a></div>

	</div>
</div>







<a style="display:block" href="#" class="action1 open-popup" data-popup=".popup-contas" id="btn_contas"></a>
<!-- Social Icons Popup -->
<div class="popup popup-contas" style="width:90%; margin:15px;  background: transparent; margin-top: 50px; ">
	<div class="content-block">

		<h2 class="page_title_dados" id="titulo_contas"></h2>  
		
		<div method="post" style="background:#fff !important; padding-bottom: 17px; padding:10px; border:1px solid #bdbbbb" id="listar_contas"></div> 

		<input type="hidden" id="id_contas">

		<div class="close_popup_button"><a id="btn_fechar_contas" href="#" class="close-popup" data-popup=".popup-contas"><img style="margin-top: 10px" src="images/icons/white/menu_close.png" alt="" title="" width="25px"/></a></div>
	</div>
</div>




<a style="display:block" href="#" class="action1 open-popup" data-popup=".popup-arquivos" id="btn_arquivos"></a>
<!-- Social Icons Popup -->
<div class="popup popup-arquivos" style="width:90%; margin:15px;  background: transparent; margin-top: 50px; ">
	<div class="content-block">

		<h2 class="page_title_dados" id="titulo_arquivo"></h2>  

		<form id="form_arquivos" method="post" style="background:#e9ebf0 !important; padding-bottom: 17px; border:1px solid #bdbbbb">
			
			<div  align="center" onclick="arquivo_conta.click()" style="padding-top: 10px; padding-bottom: 10px">
				<img src="images/sem-foto.png" width="85px" id="target-arquivos"><br>
				<img src="images/icone-arquivo.png" width="85px" style="margin-top: -12px">
			</div>	

			<input onchange="carregarImgArquivos()" type="file" name="arquivo_conta" id="arquivo_conta" hidden="hidden">				

			<div style="width:63%; display:inline-block; margin-right: 10px; margin-left: 5px">
				<input class="form_input"  style="height:25px; width:100%; " type="text" name="nome-arq" id="nome-arq" required="" placeholder="Nome Arquivo">
			</div>

			<div style="width:27%; display:inline-block; ">
				<input type="submit" name="btn_salvar_arquivo" class="botao_modelo_popup" id="btn_salvar_arquivo" value="SALVAR" style="height:32px;"/>
			</div>


			<input type="hidden" name="id-arquivo"  id="id-arquivo">


		</form> 


		<ul class="responsive_table" id="listar-arquivos" style="border:1px solid #bdbbbb; background:#e9ebf0"></ul>


		<div class="close_popup_button"><a href="#" class="close-popup" data-popup=".popup-arquivos"><img style="margin-top: 10px" src="images/icons/white/menu_close.png" alt="" title="" width="25px"/></a></div>
	</div>
</div>



<a style="display:block" href="#" class="action1 open-popup" data-popup=".popup-excluir-arquivo" id="btn_excluir_arquivo"></a>
<div class="popup popup-excluir-arquivo" style="width:85%; margin:20px;  background: transparent; margin-top: 100px; overflow: none ">
	<div class="content-block">	

		<h2 class="page_title_excluir" id="nome_excluir_arquivo"></h2> 

		<div class="botoes_excluir" align="center" style="height: 70px">
			<div style="width:49%; float:left"><input onclick="excluirArquivo()" type="button" class="form_submit botao_excluir botao_modelo" value="EXCLUIR"/></div>
			<div style="width:49%; float:right"><input data-popup=".popup-excluir-arquivo" type="button" class="form_submit botao_cancelar botao_modelo close-popup" value="CANCELAR"/></div>
		</div>

		<input type="hidden" id="id_excluir_arquivo">  

		<div class="close_popup_button"><a href="#" class="close-popup" data-popup=".popup-excluir-arquivo" id="btn_fechar_excluir_arquivos"><img style="margin-top: 10px" src="images/icons/white/menu_close.png" alt="" title="" width="25px"/></a></div>

	</div>
</div>







<a style="display:block" href="#" class="action1 open-popup" data-popup=".popup-baixar" id="btn_baixar"></a>
<!-- Social Icons Popup -->
<div class="popup popup-baixar" style="width:90%; margin:15px;  background: transparent; margin-top: 50px; ">
	<div class="content-block">

		<h2 class="page_title_dados" id="titulo_baixar"></h2>  
		<div class="contactform">
		<form id="form_baixar_cliente" method="post" style="background:#fff !important; padding-bottom: 17px; padding:10px; border:1px solid #bdbbbb">

			<div style="width:48%; float:left">
						<label class="labels_form">Valor <span style="font-size: 11px">(Total / Parcial)</span></label>
						<input onkeyup="totalizar()" type="text" name="valor-baixar" id="valor-baixar" value="" class="form_input"  style="height:25px;" required="" />
					</div>

						<div style="width:48%; float:right">
						<label class="labels_form">Forma PGTO</label>
						<div class="selector_overlay">
							<select name="saida-baixar" id="saida-baixar" required onchange="calcularTaxa()" class="">
								<?php 
										$query = $pdo->query("SELECT * FROM formas_pgto order by id asc");
										$res = $query->fetchAll(PDO::FETCH_ASSOC);
										for($i=0; $i < @count($res); $i++){
											foreach ($res[$i] as $key => $value){}

												?>	
											<option value="<?php echo $res[$i]['id'] ?>"><?php echo $res[$i]['nome'] ?></option>

										<?php } ?>
								
							</select>
						</div>
					</div>



					<div style="width:48%; float:left">
						<label class="labels_form">Multa</label>
						<input type="text" name="valor-multa" id="valor-multa" value="" class="form_input"  style="height:25px;" onkeyup="totalizar()" placeholder="Ex 15.00" value="0"/>
					</div>


					<div style="width:48%; float:right; margin-top: -2px">
						<label class="labels_form">Júros</label>
						<input type="text" name="valor-juros" id="valor-juros" value="" class="form_input"  style="height:25px; " onkeyup="totalizar()" placeholder="Ex 0.15" value="0"/>
					</div>


					<div style="width:48%; float:left">
						<label class="labels_form">Desconto</label>
						<input type="text" name="valor-desconto" id="valor-desconto" value="" class="form_input"  style="height:25px;" onkeyup="totalizar()" placeholder="Ex 15.00" value="0"/>
					</div>


					<div style="width:48%; float:right">
						<label class="labels_form">Taxa PGTO</label>
						<input type="text" name="valor-taxa" id="valor-taxa" value="" class="form_input"  style="height:25px;" onkeyup="totalizar()" placeholder="" value=""/>
					</div>


					<div style="width:48%; float:left">
						<label class="labels_form">Data Baixa</label>
						<input onkeyup="totalizar()" type="date" name="data-baixar" id="data-baixar" class="form_input" value="<?php echo $data_atual ?>" style="height:25px; background: #FFF" />
					</div>


					<div style="width:48%; float:right">
						<label class="labels_form">SubTotal</label>
						<input onkeyup="totalizar()" type="text" name="subtotal" id="subtotal" value="" class="form_input"  style="height:25px;" readonly/>
					</div>

					
			
			
			<div style="width:100%; ">
				<input type="submit" name="btn_salvar_baixar" class="botao_modelo_popup" id="btn_salvar_baixar" value="BAIXAR" style="height:40px;"/>
			</div>


			<input type="hidden" name="id-baixar" id="id-baixar"> 
		


		</form> 
		</div>


		<div class="close_popup_button"><a id="btn_fechar_baixar" href="#" class="close-popup" data-popup=".popup-baixar"><img style="margin-top: 10px" src="images/icons/white/menu_close.png" alt="" title="" width="25px"/></a></div>
	</div>
</div>





<script type="text/javascript">var pag = "<?=$pag?>"</script>

<script type="text/javascript">
	function editar(id, nome, email, telefone, endereco, cpf, tipo_pessoa, data_nasc){
		
		$('#titulo_inserir').text('EDITAR REGISTRO');

		$('#id').val(id);
		$('#nome').val(nome);
		$('#email').val(email);
		$('#telefone').val(telefone);
		$('#endereco').val(endereco);    	
		$('#cpf').val(cpf);
		$('#tipo_pessoa').val(tipo_pessoa).change();
		$('#data_nasc').val(data_nasc);  
		$('#btn_novo').click();
	}
	


	function mostrar(nome, email, telefone, endereco, data, cpf, tipo_pessoa, data_nasc){

		$('#titulo_dados').text(nome);
		$('#email_dados').text(email);
		$('#telefone_dados').text(telefone);
		$('#endereco_dados').text(endereco);
		$('#cpf_dados').text(cpf);
		$('#data_dados').text(data);
		$('#pessoa_dados').text(tipo_pessoa);
		$('#data_nasc_dados').text(data_nasc);   	

		$('#btn_mostrar').click();
	}

	function limparCampos(){
		$('#id').val('');
		$('#nome').val('');
		$('#email').val('');
		$('#telefone').val('');
		$('#endereco').val('');
		$('#cpf').val('');
		$('#tipo_pessoa').val('Física').change();
		$('#data_nasc').val('');	
	}

	

	
	function paginar(pag, busca){
		$('#input_pagina').val(pag);
		$('#input_buscar').val(busca);
		$('#paginacao').click();    
	}




	function mostrarContas(nome, id){
		
    	$('#titulo_contas').text(nome); 
    	$('#id_contas').val(id); 	
    	    	
    	$('#btn_contas').click();
    	listarDebitos(id);
    	
	}


	function listarDebitos(id){

		 $.ajax({
        url: 'paginas/' + pag + "/listar_debitos.php",
        method: 'POST',
        data: {id},
        dataType: "html",

        success:function(result){
            $("#listar_contas").html(result);           
        }
    });
	}




function totalizar(){
			valor = $('#valor-baixar').val();
			desconto = $('#valor-desconto').val();
			juros = $('#valor-juros').val();
			multa = $('#valor-multa').val();
			taxa = $('#valor-taxa').val();

			valor = valor.replace(",", ".");
			desconto = desconto.replace(",", ".");
			juros = juros.replace(",", ".");
			multa = multa.replace(",", ".");
			taxa = taxa.replace(",", ".");

			if(valor == ""){
				valor = 0;
			}

			if(desconto == ""){
				desconto = 0;
			}

			if(juros == ""){
				juros = 0;
			}

			if(multa == ""){
				multa = 0;
			}

			if(taxa == ""){
				taxa = 0;
			}

			subtotal = parseFloat(valor) + parseFloat(juros) + parseFloat(taxa) + parseFloat(multa) - parseFloat(desconto);


			console.log(subtotal)

			$('#subtotal').val(subtotal);

		}

		function calcularTaxa(){
			pgto = $('#saida-baixar').val();
			valor = $('#valor-baixar').val();
			 $.ajax({
		        url: 'paginas/receber/calcular_taxa.php',
		        method: 'POST',
		        data: {valor, pgto},
		        dataType: "html",

		        success:function(result){		           
		            $('#valor-taxa').val(result);
		             totalizar();
		        }
		    });


		}




	
</script>



<script type="text/javascript">
	function mudarPessoa(){
		var pessoa = $('#tipo_pessoa').val();
		if(pessoa == 'Física'){
			$('#cpf').mask('000.000.000-00');
			$('#cpf').attr("placeholder", "Insira CPF");
		}else{
			$('#cpf').mask('00.000.000/0000-00');
			$('#cpf').attr("placeholder", "Insira CNPJ");
		}
	}
</script>




