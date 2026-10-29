<?php 
$pag = 'acessos';
$itens_pag = 10;

if(@$acessos == 'ocultar'){
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
$query2 = $pdo->query("SELECT * from acessos where nome like '%$buscar%' order by id desc");
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
		<input  type="text" name="buscar" id="buscar" value="<?php echo $buscar ?>" class="form_input required" placeholder="Buscar Acessos" style="background: transparent !important; width:80%; float:left" />
		<button id="btn_filtrar" class="limpar_botao" type="submit"><img src="images/icons/blue/search.png" width="23px" style="float:left; margin-top: 12px"></button>
		</form>
	</div>
	<div class="page_single layout_fullwidth_padding"> 

		<ul class="posts2" id="listar">
			<?php 
			$query = $pdo->query("SELECT * from acessos where nome like '%$buscar%'  order by id desc LIMIT $limite, $itens_pag");
			$res = $query->fetchAll(PDO::FETCH_ASSOC);
			$linhas = @count($res);
			if($linhas > 0){
				for($i=0; $i<$linhas; $i++){
					$id = $res[$i]['id'];
	$nome = $res[$i]['nome'];
	$grupo = $res[$i]['grupo'];
	$chave = $res[$i]['chave'];
	$pg = $res[$i]['pagina'];

$query2 = $pdo->query("SELECT * from grupo_acessos where id = '$grupo' ");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_grupo = $res2[0]['nome'];
}else{
	$nome_grupo = 'Sem Grupo';
}
					

					echo <<<HTML
					<li class="swipeout">
					<div class="swiper-wrapper">		
					<div class="swiper-slide swipeout-content item-content" >
					<div class="post_entry">
					
					<div class="post_details">
					<div class="post_category textos_list">{$nome}</div>				
					<p>Grupo: {$nome_grupo}</p>
					<p class="subtitulo_list">Chave: {$chave} </p>					
					</div>
					<div class="post_swipe"><img src="images/swipe_more.png" alt="" title="" /></div>
					</div>
					</div>
					<div class="swiper-slide swipeout-actions-right" >
					
					<a onclick="excluir_reg('{$id}', '{$nome}')" style="width: 65px; height:65px; background: #d4412a" href="#"><img src="images/icons/white/trash.png" alt="" title="" style="margin-top:22px"/></a>
					<a onclick="editar('{$id}','{$nome}','{$chave}','{$grupo}','{$pg}')" style="width: 65px; height:65px; background: #3c6cb5" href="#" class="action1"><img src="images/icons/white/edit.png" alt="" title="" style="margin-top:22px"/></a>					
					
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

					

					<div style="width:48%; float:left">
						<label class="labels_form">Nome</label>
						<input type="text" name="nome" id="nome" value="" class="form_input"  style="height:25px;"/>
					</div>

					<div style="width:48%; float:right">
						<label class="labels_form">Chave</label>
						<input type="text" name="chave" id="chave" value="" class="form_input" style="height:25px;"/>
					</div>

					<div style="width:48%; float:left">
						<label class="labels_form">Grupo</label>
						<div class="selector_overlay">
						<select required="" name="grupo" id="grupo" class="" style="height:40px !important;">
								<option value="" disabled selected>Selecionar Grupo</option>
								<?php 
								$query = $pdo->query("SELECT * from grupo_acessos order by id asc");
								$res = $query->fetchAll(PDO::FETCH_ASSOC);
								$linhas = @count($res);
								if($linhas > 0){
									for($i=0; $i<$linhas; $i++){ ?>
										<option value="<?php echo $res[$i]['id'] ?>"><?php echo $res[$i]['nome'] ?></option>
									<?php } } ?>	
								
							</select>
						</div>
					</div>

					<div style="width:48%; float:right">
						<label class="labels_form">Página</label>
						<div class="selector_overlay">
						<select name="pagina" id="pagina" style="height:40px !important;">			<option value="Sim">Sim</option>
									<option value="Não">Não</option>								
							</select>
						</div>
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





<a style="display:block" href="#" class="action1 open-popup" data-popup=".popup-excluir" id="btn_excluir"></a>
<div class="popup popup-excluir" style="width:85%; margin:20px;  background: transparent; margin-top: 100px; overflow: none ">
	<div class="content-block">	

		<h2 class="page_title_excluir" id="nome_excluir"></h2> 

		<div class="botoes_excluir" align="center" style="height: 70px">
			<div style="width:49%; float:left"><input onclick="excluir()" type="button" class="form_submit botao_excluir botao_modelo" value="EXCLUIR"/></div>
			<div style="width:49%; float:right"><input data-popup=".popup-excluir" type="button" class="form_submit botao_cancelar botao_modelo close-popup" value="CANCELAR"/></div>
		</div>

		<input type="hidden" id="id_excluir">  

		<div class="close_popup_button"><a href="#" class="close-popup" data-popup=".popup-excluir"><img style="margin-top: 10px" src="images/icons/white/menu_close.png" alt="" title="" width="25px"/></a></div>
	
</div>
</div>






<script type="text/javascript">var pag = "<?=$pag?>"</script>


<script type="text/javascript">
	function editar(id, nome, chave, grupo, pagina){
		$('#mensagem').text('');
    	$('#titulo_inserir').text('EDITAR REGISTRO');

    	$('#id').val(id);
    	$('#nome').val(nome);
    	$('#chave').val(chave);
    	$('#grupo').val(grupo).change();
    	$('#pagina').val(pagina).change();
        	 
    	$('#btn_novo').click();
	}


	function limparCampos(){
		$('#id').val('');
    	$('#nome').val('');
    	$('#chave').val('');
    	$('#grupo').val('').change();  
    	
	}

	

	
	function paginar(pag, busca){
		$('#input_pagina').val(pag);
		$('#input_buscar').val(busca);
		$('#paginacao').click();    
	}

	
</script>

