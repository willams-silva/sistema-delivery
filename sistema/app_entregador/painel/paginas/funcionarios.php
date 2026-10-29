<?php 
$pag = 'funcionarios';
$itens_pag = 10;

if(@$funcionarios == 'ocultar'){
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
$query2 = $pdo->query("SELECT * from usuarios where (nivel != 'Cliente' and nivel != 'Administrador') and (nome like '%$buscar%' or telefone like '%$buscar%' or email like '%$buscar%') order by id desc");
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
		<input  type="text" name="buscar" id="buscar" value="<?php echo $buscar ?>" class="form_input required" placeholder="Buscar Funcionários" style="background: transparent !important; width:80%; float:left" />
		<button id="btn_filtrar" class="limpar_botao" type="submit"><img src="images/icons/blue/search.png" width="23px" style="float:left; margin-top: 12px"></button>
		</form>
	</div>
	<div class="page_single layout_fullwidth_padding"> 

		<ul class="posts2" id="listar">
			<?php 
			$query = $pdo->query("SELECT * from usuarios where (nivel != 'Cliente' and nivel != 'Administrador') and (nome like '%$buscar%' or telefone like '%$buscar%' or email like '%$buscar%') order by id desc LIMIT $limite, $itens_pag");
			$res = $query->fetchAll(PDO::FETCH_ASSOC);
			$linhas = @count($res);
			if($linhas > 0){
				for($i=0; $i<$linhas; $i++){
					$id = $res[$i]['id'];
					$nome = $res[$i]['nome'];
					$telefone = $res[$i]['telefone'];
					$email = $res[$i]['email'];
					$senha = $res[$i]['senha'];
					$foto = $res[$i]['foto'];
					$nivel = $res[$i]['nivel'];
					$endereco = $res[$i]['endereco'];
					$ativo = $res[$i]['ativo'];
					$data = $res[$i]['data'];
					$chave_pix = $res[$i]['pix'];

					$tel_whatsF = '55'.preg_replace('/[ ()-]+/' , '' , $telefone);

					$dataF = implode('/', array_reverse(@explode('-', $data)));

					if($ativo == 'Sim'){
						$icone = 'fa-check-square';
						$titulo_link = 'Desativar Usuário';
						$acao = 'Não';
						$classe_ativo = '';
						$foto_ativo = '';
					}else{
						$icone = 'fa-square-o';
						$titulo_link = 'Ativar Usuário';
						$acao = 'Sim';
						$classe_ativo = '#c4c4c4';
						$foto_ativo = '.5';
					}

					$mostrar_adm = '';
					if($nivel == 'Administrador'){
						$senha = '******';
						$mostrar_adm = 'ocultar';

					}

					

					echo <<<HTML
					<li class="swipeout">
					<div class="swiper-wrapper">		
					<div class="swiper-slide swipeout-content item-content" style="color:{$classe_ativo} ">
					<div class="post_entry" onclick="mostrar('{$nome}','{$email}','{$telefone}','{$endereco}','{$ativo}','{$dataF}', '{$senha}', '{$nivel}', '{$foto}', '{$chave_pix}')">
					<div class="post_thumb"><img src="images/perfil/{$foto}" alt="" title="" style="opacity: {$foto_ativo}"/></div>
					<div class="post_details">
					<div class="post_category textos_list">{$nome}</div>				
					<p>{$nivel}</p>
					<p class="subtitulo_list">{$telefone} </p>
					</div>
					<div class="post_swipe"><img src="images/swipe_more.png" alt="" title="" /></div>
					</div>
					</div>
					<div class="swiper-slide swipeout-actions-right">
					<a target="_blank" style="width: 20%; background: #1d8c32" href="http://api.whatsapp.com/send?1=pt_BR&phone={$tel_whatsF}" title="Whatsapp"><img src="images/icons/white/whatsapp.png" alt="" title="" /></a>
					<a style="width: 20%; background: #234e9e" href="#" onclick="arquivo('{$id}','{$nome}')" title="Inserir Arquivo"><img src="images/icons/white/arquivo.png" alt="" title="" /></a>
					<a style="width: 20%; background: #37de56" href="#" onclick="ativar('{$id}', '{$acao}')"><img src="images/icons/white/square-check.png" alt="" title="" /></a>
					<a onclick="excluir_reg('{$id}', '{$nome}')" style="width: 20%; background: #d4412a" href="#"><img src="images/icons/white/trash.png" alt="" title="" /></a>
					<a onclick="editar('{$id}','{$nome}','{$email}','{$telefone}','{$endereco}','{$nivel}','{$foto}','{$chave_pix}')" style="width: 20%; background: #3c6cb5" href="#" class="action1"><img src="images/icons/white/edit.png" alt="" title="" /></a>
					
					
					
					
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
						<label class="labels_form">Cargo</label>
						<div class="selector_overlay">
							<select name="nivel" id="nivel" class="" style="height:40px !important;">
								<option value="0">Selecionar Nível</option>
								<?php 
								$query = $pdo->query("SELECT * from cargos order by id asc");
								$res = $query->fetchAll(PDO::FETCH_ASSOC);
								$linhas = @count($res);
								if($linhas > 0){
									for($i=0; $i<$linhas; $i++){ ?>
										<option value="<?php echo $res[$i]['nome'] ?>"><?php echo $res[$i]['nome'] ?></option>
									<?php } } ?>	
								
							</select>
						</div>
					</div>

					<div class="form_row">
						<label class="labels_form">Chave Pix</label>
						<input type="text" name="chave_pix" id="chave_pix" value="" class="form_input" />
					</div>

					<div class="form_row">
						<label class="labels_form">Endereço</label>
						<input type="text" name="endereco" id="endereco" value="" class="form_input" />
					</div>


					<div class="form_row" align="center" onclick="foto.click()">
						<img src="images/sem-foto-perfil.jpg" width="100px" id="target"><br>
						<img src="images/icone-arquivo.png" width="100px" style="margin-top: -12px">
					</div>

					<input onchange="carregarImg()" type="file" name="foto" id="foto" hidden="hidden">

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


		<ul class="responsive_table tabela_dados">

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Telefone</div>
				<div id="telefone_dados" class="table_section" style="width:70%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Email</div>
				<div id="email_dados" class="table_section" style="width:70%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Ativo</div>
				<div id="ativo_dados" class="table_section" style="width:70%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Cadastro</div>
				<div id="data_dados" class="table_section" style="width:70%"></div>                        
			</li>

				<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Cargo</div>
				<div id="nivel_dados" class="table_section" style="width:70%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Chave Pix</div>
				<div id="pix_dados" class="table_section" style="width:70%"></div>                        
			</li>


			<li class="table_row celula_tabela">
				<div class="table_section" style="width:25%">Endereço</div>
				<div id="endereco_dados" class="table_section" style="width:70%"></div>                        
			</li>

		</ul>

		<div class="form_row celula_tabela" align="center" style="margin-top: -10px">
			<img src="images/sem-foto-perfil.jpg" width="100px" id="foto_dados"><br>
		</div>


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


		<ul class="responsive_table" id="listar-arquivos" style="background:#e9ebf0 !important;  border:1px solid #bdbbbb"></ul>


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





<script type="text/javascript">var pag = "<?=$pag?>"</script>


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
	function editar(id, nome, email, telefone, endereco, nivel, foto, chave_pix){
		$('#mensagem').text('');
    	$('#titulo_inserir').text('EDITAR REGISTRO');

    	$('#id').val(id);
    	$('#nome').val(nome);
    	$('#email').val(email);
    	$('#telefone').val(telefone);
    	$('#endereco').val(endereco);
    	$('#nivel').val(nivel).change();
    	$('#chave_pix').val(chave_pix);
    	$('#target').attr("src", "images/perfil/" + foto);    
    	$('#btn_novo').click();
	}




	function mostrar(nome, email, telefone, endereco, ativo, data, senha, nivel, foto, chave_pix){
		    	
    	$('#titulo_dados').text(nome);
    	$('#email_dados').text(email);
    	$('#telefone_dados').text(telefone);
    	$('#endereco_dados').text(endereco);
    	$('#ativo_dados').text(ativo);
    	$('#data_dados').text(data);
    	$('#pix_dados').text(chave_pix);
    	
    	$('#nivel_dados').text(nivel);
    	$('#foto_dados').attr("src", "images/perfil/" + foto);    	

    	$('#btn_mostrar').click();
	}

	function limparCampos(){
		$('#id').val('');
    	$('#nome').val('');
    	$('#email').val('');
    	$('#telefone').val('');
    	$('#endereco').val('');  
    	$('#chave_pix').val('');    
    	$('#target').attr("src", "images/perfil/sem-foto.jpg");	
	}

	

	
	function paginar(pag, busca){
		$('#input_pagina').val(pag);
		$('#input_buscar').val(busca);
		$('#paginacao').click();    
	}

	
</script>

