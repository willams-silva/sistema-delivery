<?php 
$pag = 'usuarios';
$itens_pag = 10;

if(@$usuarios == 'ocultar'){
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
$query2 = $pdo->query("SELECT * from usuarios where nome like '%$buscar%' or telefone like '%$buscar%' or email like '%$buscar%' order by id desc");
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
		<input  type="text" name="buscar" id="buscar" value="<?php echo $buscar ?>" class="form_input required" placeholder="Buscar Usuários" style="background: transparent !important; width:80%; float:left" />
		<button id="btn_filtrar" class="limpar_botao" type="submit"><img src="images/icons/blue/search.png" width="23px" style="float:left; margin-top: 12px"></button>
		</form>
	</div>
	<div class="page_single layout_fullwidth_padding"> 

		<ul class="posts2" id="listar">
			<?php 
			$query = $pdo->query("SELECT * from usuarios where nome like '%$buscar%' or telefone like '%$buscar%' or email like '%$buscar%' order by id desc LIMIT $limite, $itens_pag");
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
					<div class="post_entry" onclick="mostrar('{$nome}','{$email}','{$telefone}','{$endereco}','{$ativo}','{$dataF}', '{$senha}', '{$nivel}', '{$foto}')">
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

					<a style="width: 25%; background: #dba81a" href="#" onclick="permissoes('{$id}', '{$nome}')"><img src="images/icons/white/lock.png" alt="" title="" /></a>
					<a style="width: 25%; background: #37de56" href="#" onclick="ativar('{$id}', '{$acao}')"><img src="images/icons/white/square-check.png" alt="" title="" /></a>
					<a onclick="excluir_reg('{$id}', '{$nome}')" style="width: 25%; background: #d4412a" href="#"><img src="images/icons/white/trash.png" alt="" title="" /></a>
					<a onclick="editar('{$id}','{$nome}','{$email}','{$telefone}','{$endereco}','{$nivel}','{$foto}')" style="width: 25%; background: #3c6cb5" href="#" class="action1"><img src="images/icons/white/edit.png" alt="" title="" /></a>
					
					
					
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
						<label class="labels_form">Nível</label>
						<div class="selector_overlay">
							<select name="nivel" id="nivel" class="" style="height:40px !important;">
								<option value="0" >Selecionar Nível</option>
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






<a style="display:block" href="#" class="action1 open-popup" data-popup=".popup-acessos" id="btn_acessos"></a>
<div class="popup popup-acessos" id="popupAcessos">
	<div class="content-block" style="padding:5px !important">
		<h2 class="page_title" id="">Definir Permissões</h2>      
		<div class="page_single layout_fullwidth_padding">
			<div style="border-bottom: 1px solid #000">
			<span class="page_title" id="nome_permissoes"></span> 
			<span class="page_title" style="float:right">Marcar Todos <input class="form-check-input" type="checkbox" id="input-todos" onchange="marcarTodos()"></span>
			</div>
			<div id="listar_permissoes" style="margin-top: 15px">

			</div>
			<input type="hidden" name="id" id="id_permissoes">
		</div>          
	</div>
	<div class="close_popup_button">
		<a style="margin-top: -8px" id="btn-fechar-acessos" href="#" onclick="limparCampos()" class="close-popup" data-popup=".popup-acessos"><img src="images/icons/black/menu_close.png" alt="" title="" /></a>
	</div>
</div>



<script type="text/javascript">var pag = "<?=$pag?>"</script>



<script type="text/javascript">
	

	
</script>

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
	function editar(id, nome, email, telefone, endereco, nivel, foto){
		$('#mensagem').text('');
    	$('#titulo_inserir').text('EDITAR REGISTRO');

    	$('#id').val(id);
    	$('#nome').val(nome);
    	$('#email').val(email);
    	$('#telefone').val(telefone);
    	$('#endereco').val(endereco);
    	$('#nivel').val(nivel).change();
    	$('#target').attr("src", "images/perfil/" + foto);    
    	$('#btn_novo').click();
	}




	function mostrar(nome, email, telefone, endereco, ativo, data, senha, nivel, foto){
		    	
    	$('#titulo_dados').text(nome);
    	$('#email_dados').text(email);
    	$('#telefone_dados').text(telefone);
    	$('#endereco_dados').text(endereco);
    	$('#ativo_dados').text(ativo);
    	$('#data_dados').text(data);
    	
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
    	$('#target').attr("src", "images/perfil/sem-foto.jpg");	
	}

	

	function permissoes(id, nome){
		    	
    	$('#id_permissoes').val(id);
    	$('#nome_permissoes').text(nome);    	

    	$('#btn_acessos').click();
    	listarPermissoes(id);
	}

	
	function paginar(pag, busca){
		$('#input_pagina').val(pag);
		$('#input_buscar').val(busca);
		$('#paginacao').click();    
	}

	
</script>



	<script type="text/javascript">

		function listarPermissoes(id){
			$.ajax({
				url: 'paginas/' + pag + "/listar_permissoes.php",
				method: 'POST',
				data: {id},
				dataType: "html",

				success:function(result){        	
					$("#listar_permissoes").html(result);
					$('#mensagem_permissao').text('');
				}
			});
		}

		function adicionarPermissao(id, usuario){
			$.ajax({
				url: 'paginas/' + pag + "/add_permissao.php",
				method: 'POST',
				data: {id, usuario},
				dataType: "html",

				success:function(result){        	
					listarPermissoes(usuario);
				}
			});
		}


		function marcarTodos(){
			let checkbox = document.getElementById('input-todos');
			var usuario = $('#id_permissoes').val();

			if(checkbox.checked) {
				adicionarPermissoes(usuario);		    
			} else {
				limparPermissoes(usuario);
			}
		}


		function adicionarPermissoes(id_usuario){

			$.ajax({
				url: 'paginas/' + pag + "/add_permissoes.php",
				method: 'POST',
				data: {id_usuario},
				dataType: "html",

				success:function(result){        	
					listarPermissoes(id_usuario);
				}
			});
		}


		function limparPermissoes(id_usuario){

			$.ajax({
				url: 'paginas/' + pag + "/limpar_permissoes.php",
				method: 'POST',
				data: {id_usuario},
				dataType: "html",

				success:function(result){        	
					listarPermissoes(id_usuario);
				}
			});
		}
	</script>