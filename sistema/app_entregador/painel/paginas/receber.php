<?php 
$pag = 'receber';
$itens_pag = 10;

if(@$receber == 'ocultar'){
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

$checked_vencidas = '';
$checked_pagas = '';
$checked_pendentes = '';
$checked_todas = '';
if(@$_POST['pago'] == "Vencidas"){
	$checked_vencidas = 'checked';
}else if(@$_POST['pago'] == "Sim"){
	$checked_pagas = 'checked';
}else if(@$_POST['pago'] == "Não"){
	$checked_pendentes = 'checked';
}else{
	$checked_todas = 'checked';
}

if($dataInicial == ""){
	$dataInicial = $data_inicio_mes;
}

if($dataFinal == ""){
	$dataFinal = $data_final_mes;
}

$total_pago = 0;
$total_pendentes = 0;

$total_pagoF = 0;
$total_pendentesF = 0;

//totalizar páginas
if($pago == 'Vencidas'){
	$query2 = $pdo->query("SELECT * from receber where vencimento < curDate() and pago != 'Sim' order by id desc");
}else{
	$query2 = $pdo->query("SELECT * from receber where vencimento >= '$dataInicial' and vencimento <= '$dataFinal' and pago LIKE '%$pago%' order by id desc");
}

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
		<input class="form_input" type="date" name="dataInicial" id="dataInicial" style="width:37%; float:left" value="<?php echo $dataInicial ?>" onchange="buscar('')"/>
		<img src="images/icons/black/icone_datas4.png" style="float:left; width:13%">
			<input class="form_input" type="date" name="dataFinal" id="dataFinal" style="width:37%; float:right" value="<?php echo $dataFinal ?>" onchange="buscar('')"/>		

			<div class="tabs tabs--photos" style="margin:5px !important; width:100%">
					
			<input type="radio" name="tabs" class="tabradio" id="tabone" <?php echo $checked_todas ?> onchange="buscar('')">
			<label style="width:25%" class="tablabel tablabel--13" for="tabone" >Todas</label>	
                          <div class="tab ocultar">
                             1  
                          </div>
    
			<input type="radio" name="tabs" class="tabradio" id="tabtwo" <?php echo $checked_pagas ?> onchange="buscar('Sim')">
			<label style="width:25%" class="tablabel tablabel--13" for="tabtwo">Pagas</label>
                          <div class="tab ocultar">
                            2
                          </div> 
			<input type="radio" name="tabs" class="tabradio" id="tabthree" <?php echo $checked_pendentes ?> onchange="buscar('Não')">
			<label style="width:25%" class="tablabel tablabel--13" for="tabthree">Pendentes</label>
                          <div class="tab ocultar">
                             3
                          </div> 

            <input type="radio" name="tabs" class="tabradio" id="tab4" <?php echo $checked_vencidas ?> onchange="buscar('Vencidas')">
			<label style="width:25%" class="tablabel tablabel--13" for="tab4">Vencidas</label>
                          <div class="tab ocultar">
                             4
                          </div>          
                           
                    </div>

                    <input type="hidden" name="pago" id="pago">
                    <button id="btn_filtrar" class="limpar_botao ocultar" type="submit"></button>
		</form>
	</div>
	<div class="page_single layout_fullwidth_padding"> 

		<ul class="posts2" id="listar">
<?php 
if($pago == 'Vencidas'){
	$query = $pdo->query("SELECT * from receber where vencimento < curDate() and pago != 'Sim' order by id desc");
}else{
	$query = $pdo->query("SELECT * from receber where vencimento >= '$dataInicial' and vencimento <= '$dataFinal' and pago LIKE '%$pago%' order by id desc LIMIT $limite, $itens_pag");
}
			
			$res = $query->fetchAll(PDO::FETCH_ASSOC);
			$linhas = @count($res);
			if($linhas > 0){
				for($i=0; $i<$linhas; $i++){
				$id = $res[$i]['id'];
	$descricao = $res[$i]['descricao'];
	$cliente = $res[$i]['cliente'];
	$valor = $res[$i]['valor'];
	$vencimento = $res[$i]['vencimento'];
	$data_pgto = $res[$i]['data_pgto'];
	$data_lanc = $res[$i]['data_lanc'];
	$forma_pgto = $res[$i]['forma_pgto'];
	$frequencia = $res[$i]['frequencia'];
	$obs = $res[$i]['obs'];
	$arquivo = $res[$i]['arquivo'];
	$referencia = $res[$i]['referencia'];
	$id_ref = $res[$i]['id_ref'];
	$multa = $res[$i]['multa'];
	$juros = $res[$i]['juros'];
	$desconto = $res[$i]['desconto'];
	$taxa = $res[$i]['taxa'];
	$subtotal = $res[$i]['subtotal'];
	$usuario_lanc = $res[$i]['usuario_lanc'];
	$usuario_pgto = $res[$i]['usuario_pgto'];
	$pago = $res[$i]['pago'];

	$vencimentoF = implode('/', array_reverse(@explode('-', $vencimento)));
	$data_pgtoF = implode('/', array_reverse(@explode('-', $data_pgto)));
	$data_lancF = implode('/', array_reverse(@explode('-', $data_lanc)));



	$valorF = @number_format($valor, 2, ',', '.');
	$multaF = @number_format($multa, 2, ',', '.');
	$jurosF = @number_format($juros, 2, ',', '.');
	$descontoF = @number_format($desconto, 2, ',', '.');
	$taxaF = @number_format($taxa, 2, ',', '.');
	$subtotalF = @number_format($subtotal, 2, ',', '.');

	if($pago == "Sim"){
		$valor_finalF = @number_format($subtotal, 2, ',', '.');
	}else{
		$valor_finalF = @number_format($valor, 2, ',', '.');
	}



	//extensão do arquivo
$ext = pathinfo($arquivo, PATHINFO_EXTENSION);
if($ext == 'pdf' || $ext == 'PDF'){
	$tumb_arquivo = 'pdf.png';
}else if($ext == 'rar' || $ext == 'zip' || $ext == 'RAR' || $ext == 'ZIP'){
	$tumb_arquivo = 'rar.png';
}else if($ext == 'doc' || $ext == 'docx' || $ext == 'DOC' || $ext == 'DOCX'){
	$tumb_arquivo = 'word.png';
}else if($ext == 'xlsx' || $ext == 'xlsm' || $ext == 'xls'){
	$tumb_arquivo = 'excel.png';
}else if($ext == 'xml'){
	$tumb_arquivo = 'xml.png';
}else{
	$tumb_arquivo = $arquivo;
}
	
	

$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_lanc'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_usu_lanc = $res2[0]['nome'];
}else{
	$nome_usu_lanc = 'Sem Usuário';
}


$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_pgto'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_usu_pgto = $res2[0]['nome'];
}else{
	$nome_usu_pgto = 'Sem Usuário';
}


$query2 = $pdo->query("SELECT * FROM frequencias where dias = '$frequencia'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_frequencia = $res2[0]['frequencia'];
}else{
	$nome_frequencia = 'Sem Registro';
}

$query2 = $pdo->query("SELECT * FROM formas_pgto where id = '$forma_pgto'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_pgto = $res2[0]['nome'];
	$taxa_pgto = $res2[0]['taxa'];
}else{
	$nome_pgto = 'Sem Registro';
	$taxa_pgto = 0;
}


$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_cliente = $res2[0]['nome'];
	$ocultar_cliente = '';
}else{
	$nome_cliente = 'Sem Registro';
	$ocultar_cliente = 'ocultar';
}


if($pago == 'Sim'){
	$classe_pago = 'verde.jpg';
	$ocultar = 'ocultar';
	$ocultar_pendentes = '';
	$total_pago += $subtotal;
}else{
	$classe_pago = 'vermelho.jpg';
	$ocultar_pendentes = 'ocultar';
	$ocultar = '';
	$total_pendentes += $valor;
}	

$valor_multa = 0;
$valor_juros = 0;
$classe_venc = '';
if(strtotime($vencimento) < strtotime($data_hoje)){
	$classe_venc = 'text-danger';
	$valor_multa = $multa_atraso;

	//pegar a quantidade de dias que o pagamento está atrasado
	$dif = strtotime($data_hoje) - strtotime($vencimento);
	$dias_vencidos = floor($dif / (60*60*24));

	$valor_juros = ($valor * $juros_atraso / 100) * $dias_vencidos;
}

$total_pendentesF = @number_format($total_pendentes, 2, ',', '.');
$total_pagoF = @number_format($total_pago, 2, ',', '.');

$taxa_conta = $taxa_pgto * $valor / 100;




//PEGAR RESIDUOS DA CONTA
	$total_resid = 0;
	$valor_com_residuos = 0;
	$query2 = $pdo->query("SELECT * FROM receber WHERE id_ref = '$id' and residuo = 'Sim'");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res2) > 0){

		$descricao = '(Resíduo) - ' .$descricao;

		for($i2=0; $i2 < @count($res2); $i2++){
			foreach ($res2[$i2] as $key => $value){} 
				$id_res = $res2[$i2]['id'];
			$valor_resid = $res2[$i2]['valor'];
			$total_resid += $valor_resid - $res2[$i2]['desconto'];
		}


		$valor_com_residuos = $valor + $total_resid;
	}
	if($valor_com_residuos > 0){
		$vlr_antigo_conta = '('.$valor_com_residuos.')';
		$descricao_link = '';
		$descricao_texto = 'd-none';
		$classe_residuo = '';
	}else{
		$vlr_antigo_conta = '';
		$descricao_link = 'd-none';
		$descricao_texto = '';
		$classe_residuo = 'ocultar';
	}


					

					echo <<<HTML
					<li class="swipeout">
					<div class="swiper-wrapper">		
					<div class="swiper-slide swipeout-content item-content">
					<div class="post_entry" onclick="mostrar('{$descricao}','{$valorF}','{$nome_cliente}','{$vencimentoF}','{$data_pgtoF}','{$nome_pgto}','{$nome_frequencia}','{$obs}','{$tumb_arquivo}','{$multaF}','{$jurosF}','{$descontoF}','{$taxaF}','{$subtotalF}','{$nome_usu_lanc}','{$nome_usu_pgto}', '{$pago}', '{$arquivo}')">
					<div class="post_thumb"><img src="images/contas/{$tumb_arquivo}" alt="" title=""/></div>					
					<div class="post_details">
					<div class="post_category textos_list"><img src="images/{$classe_pago}" width="12px" style="float:left; margin-right: 2px; margin-top: 3px"> {$descricao}</div>				
					<p>R$ {$valor_finalF} <small><span style="color:red;">{$vlr_antigo_conta}</span></small></p>
					<p class="subtitulo_list"><span class="{$ocultar_cliente}">{$nome_cliente} / </span>Venc: {$vencimentoF} </p>
					</div>
					<div class="post_swipe"><img src="images/swipe_more.png" alt="" title="" /></div>
					</div>
					</div>
					<div class="swiper-slide swipeout-actions-right">
					<a class="{$ocultar}" onclick="baixar('{$id}', '{$valor}', '{$descricao}', '{$forma_pgto}', '{$taxa_conta}', '{$valor_multa}', '{$valor_juros}')" style="width: 16.66%; background: #37de56" href="#"><img src="images/icons/white/square-check.png" alt="" title="" /></a>

					<a class="{$classe_residuo}" style="width: 16.66%; background: #fa4646" href="#" onclick="mostrarResiduos('{$id}')" title="Inserir Arquivo"><img src="images/icons/white/financeiro2.png" alt="" title="" /></a>

					<a style="width: 16.66%; background: #234e9e" href="#" onclick="arquivo('{$id}','{$descricao}')" title="Inserir Arquivo"><img src="images/icons/white/arquivo.png" alt="" title="" /></a>
					<a class="{$ocultar}" style="width: 16.66%; background: #5e5e5e" href="#" onclick="parcelar('{$id}', '{$valor}', '{$descricao}')"><img src="images/icons/white/blog.png" alt="" title="" /></a>
					<a onclick="excluir_reg('{$id}', '{$descricao}')" style="width: 16.66%; background: #d4412a" href="#"><img src="images/icons/white/trash.png" alt="" title="" /></a>
					<a onclick="editar('{$id}','{$descricao}','{$valor}','{$cliente}','{$vencimento}','{$data_pgto}','{$forma_pgto}','{$frequencia}','{$obs}','{$tumb_arquivo}')" style="width: 16.66%; background: #3c6cb5" href="#" class="action1"><img src="images/icons/white/edit.png" alt="" title="" /></a>
					
					
					
					
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


		<div align="right" style="font-size:13px; margin-top: 10px">
				<span style="margin-right: 10px">Total Pendentes  <span style="color:red">R$ <?php echo $total_pendentesF ?> </span></span>
				<span>Total Pago  <span style="color:green">R$ <?php echo $total_pagoF ?> </span></span>
			</div>

		
	<div class="shop_pagination">
          <a href="#" onclick="paginar('<?php echo $pag_anterior ?>', '<?php echo $buscar ?>')" class="prev_shop <?php echo $pag_inativa_ant ?>">ANTERIOR</a>
          <span class="shop_pagenr"><?php echo @$numero_pagina ?>/<?php echo @$num_paginas ?></span>
          <a href="#" onclick="paginar('<?php echo $pag_proxima ?>', '<?php echo $buscar ?>')" class="next_shop <?php echo $pag_inativa_prox ?>">PRÓXIMA</a>
     </div>	

     <form method="post" style="display:none">
     	<input type="text" name="pagina" id="input_pagina">
     	<input type="text" name="buscar" id="input_buscar">
     	<input type="text" name="dataInicial" id="dataInicialPag">
     	<input type="text" name="dataFinal" id="dataFinalPag">
     	<input type="text" name="pago" value="<?php echo $pago ?>">
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
						<label class="labels_form">Descrição</label>
						<input type="text" name="descricao" id="descricao" value="" class="form_input" />
					</div>		

				

					<div style="">
						<label class="labels_form">Cliente</label>
						<div class="selector_overlay">
							<select name="cliente" id="cliente" class="sel2" style="height:40px !important;">
								<option value="0">Selecionar Cliente</option>
								<?php 
								$query = $pdo->query("SELECT * from clientes order by id asc");
								$res = $query->fetchAll(PDO::FETCH_ASSOC);
								$linhas = @count($res);
								if($linhas > 0){
									for($i=0; $i<$linhas; $i++){
										echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
									}
								}
								?>	
								
							</select>
						</div>
					</div>


					<div style="width:48%; float:left">
						<label class="labels_form">Valor</label>
						<input type="text" name="valor" id="valor" value="" class="form_input"  style="height:25px;" required="" />
					</div>

						<div style="width:48%; float:right">
						<label class="labels_form">Vencimento</label>
						<input type="date" name="vencimento" id="vencimento" value="<?php echo $data_hoje ?>" class="form_input"  style="height:25px; background: #FFF"/>
					</div>



						<div style="width:48%; float:left">
						<label class="labels_form">Pago em</label>
						<input type="date" name="data_pgto" id="forma_pgto" value="" class="form_input"  style="height:25px; background: #FFF"/>
					</div>


					<div style="width:48%; float:right">
						<label class="labels_form">Forma PGTO</label>
						<div class="selector_overlay">
						<select name="forma_pgto" id="forma_pgto" class="" style="height:40px !important;">								
								<?php 
								$query = $pdo->query("SELECT * from formas_pgto order by id asc");
								$res = $query->fetchAll(PDO::FETCH_ASSOC);
								$linhas = @count($res);
								if($linhas > 0){
									for($i=0; $i<$linhas; $i++){
										echo '<option value="'.$res[$i]['id'].'">'.$res[$i]['nome'].'</option>';
									}
								}else{
									echo '<option value="0">Cadastre uma Forma de Pagamento</option>';
								}
								?>	
								
							</select>
						</div>
					</div>


					<div style="">
						<label class="labels_form">Frequência</label>
						<div class="selector_overlay">
							<select name="frequencia" id="frequencia" class="" style="height:40px !important;">
								<?php 
								$query = $pdo->query("SELECT * from frequencias order by id asc");
								$res = $query->fetchAll(PDO::FETCH_ASSOC);
								$linhas = @count($res);
								if($linhas > 0){
									for($i=0; $i<$linhas; $i++){
										echo '<option value="'.$res[$i]['dias'].'">'.$res[$i]['frequencia'].'</option>';
									}
								}else{
									echo '<option value="0">Cadastre uma Forma de Pagamento</option>';
								}
								?>	
								
							</select>
						</div>
					</div>

					
					<div class="form_row">
						<label class="labels_form">Observações</label>
						<input type="text" name="obs" id="obs" value="" class="form_input" />
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
<div class="popup popup-mostrar" style="width:85%; margin:20px;  background: transparent; margin-top: 60px; ">
	<div class="content-block">

		<h2 class="page_title_dados" id="titulo_dados" style="background: #3e4f9c"></h2>   


		<ul class="responsive_table tabela_dados" style="height:450px !important; overflow: scroll !important; height:100%; scrollbar-width: thin;">

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Valor</div>
				<div id="valor_dados" class="table_section" style="width:60%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Cliente</div>
				<div id="cliente_dados" class="table_section" style="width:60%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Vencimento</div>
				<div id="vencimento_dados" class="table_section" style="width:60%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Data PGTO</div>
				<div id="data_pgto_dados" class="table_section" style="width:60%"></div>                        
			</li>

				<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Tipo PGTO</div>
				<div id="nome_pgto_dados" class="table_section" style="width:60%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Frequência</div>
				<div id="frequencia_dados" class="table_section" style="width:60%"></div>                        
			</li>


			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Multa</div>
				<div id="multa_dados" class="table_section" style="width:60%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Júros</div>
				<div id="juros_dados" class="table_section" style="width:60%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Desconto</div>
				<div id="desconto_dados" class="table_section" style="width:60%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Taxa</div>
				<div id="taxa_dados" class="table_section" style="width:60%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Total</div>
				<div id="total_dados" class="table_section" style="width:60%"></div>                        
			</li>


			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Lançador Por</div>
				<div id="usu_lanc_dados" class="table_section" style="width:60%"></div>                        
			</li>

			<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Pago Por</div>
				<div id="usu_pgto_dados" class="table_section" style="width:60%"></div>                        
			</li>

				<li class="table_row celula_tabela">
				<div class="table_section" style="width:35%">Pago</div>
				<div id="pago_dados" class="table_section" style="width:60%"></div>                        
			</li>

			<div class="form_row celula_tabela" align="center" style="">
			<a href="" id="target_link_dados" target="_blank"><img src="images/sem-foto-perfil.jpg" width="100px" id="target_dados"></a><br>
		</div>


		</ul>

		


		<div class="close_popup_button"><a href="#" class="close-popup" data-popup=".popup-mostrar"><img style="margin-top: 10px" src="images/icons/white/menu_close.png" alt="" title="" width="25px"/></a></div>
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

		<div class="botoes_excluir" align="center" style="height: 70px; border:1px solid #bdbbbb">
			<div style="width:49%; float:left"><input onclick="excluirArquivo()" type="button" class="form_submit botao_excluir botao_modelo" value="EXCLUIR"/></div>
			<div style="width:49%; float:right"><input data-popup=".popup-excluir-arquivo" type="button" class="form_submit botao_cancelar botao_modelo close-popup" value="CANCELAR"/></div>
		</div>

		<input type="hidden" id="id_excluir_arquivo">  

		<div class="close_popup_button"><a href="#" class="close-popup" data-popup=".popup-excluir-arquivo" id="btn_fechar_excluir_arquivos"><img style="margin-top: 10px" src="images/icons/white/menu_close.png" alt="" title="" width="25px"/></a></div>

	</div>
</div>








<a style="display:block" href="#" class="action1 open-popup" data-popup=".popup-parcelar" id="btn_parcelar"></a>
<!-- Social Icons Popup -->
<div class="popup popup-parcelar" style="width:90%; margin:15px;  background: transparent; margin-top: 50px; ">
	<div class="content-block">

		<h2 class="page_title_dados" id="titulo_parcelar"></h2>  
		<div class="contactform">
		<form id="form_parcelar" method="post" style="background:#fff !important; padding-bottom: 17px; padding:10px; border:1px solid #bdbbbb">

			<div style="width:48%; float:left">
						<label class="labels_form">Valor</label>
						<input type="text" name="valor-parcelar" id="valor-parcelar" value="" class="form_input"  style="height:25px;" required="" />
					</div>

						<div style="width:48%; float:right">
						<label class="labels_form">Parcelas</label>
						<input type="number" name="qtd-parcelar" id="qtd-parcelar" value="" class="form_input"  style="height:25px; " required/>
					</div>

					<div style="">
						<label class="labels_form">Frequência</label>
						<div class="selector_overlay">
							<select name="frequencia" id="frequencia-parcelar" class="" style="height:40px !important;">
								<?php 
								$query = $pdo->query("SELECT * from frequencias order by id asc");
								$res = $query->fetchAll(PDO::FETCH_ASSOC);
								$linhas = @count($res);
								if($linhas > 0){
									for($i=0; $i<$linhas; $i++){
										echo '<option value="'.$res[$i]['dias'].'">'.$res[$i]['frequencia'].'</option>';
									}
								}else{
									echo '<option value="0">Cadastre uma Forma de Pagamento</option>';
								}
								?>	
								
							</select>
						</div>
					</div>
			
			
			<div style="width:100%; ">
				<input type="submit" name="btn_salvar_parcelar" class="botao_modelo_popup" id="btn_salvar_parcelar" value="PARCELAR" style="height:40px;"/>
			</div>


			<input type="hidden" name="id-parcelar" id="id-parcelar"> 
			<input type="hidden" name="nome-parcelar" id="nome-input-parcelar"> 


		</form> 
		</div>



		<div class="close_popup_button"><a id="btn_fechar_parcelar" href="#" class="close-popup" data-popup=".popup-parcelar"><img style="margin-top: 10px" src="images/icons/white/menu_close.png" alt="" title="" width="25px"/></a></div>
	</div>
</div>









<a style="display:block" href="#" class="action1 open-popup" data-popup=".popup-baixar" id="btn_baixar"></a>
<!-- Social Icons Popup -->
<div class="popup popup-baixar" style="width:90%; margin:15px;  background: transparent; margin-top: 100px; ">
	<div class="content-block">

		<h2 class="page_title_dados" id="titulo_baixar"></h2>  
		<div class="contactform">
		<form id="form_baixar" method="post" style="background:#fff !important; padding-bottom: 17px; padding:10px; border:1px solid #bdbbbb">

			<div style="width:48%; float:left">
						<label class="labels_form">Valor <span style="font-size: 11px">(Total / Parcial)</span></label>
						<input onkeyup="totalizar()" type="text" name="valor-baixar" id="valor-baixar" value="" class="form_input"  style="height:25px;" required="" />
					</div>

						<div style="width:48%; float:right">
						<label class="labels_form">Forma PGTO</label>
						<div class="selector_overlay">
							<select name="saida-baixar" id="saida-baixar" required onchange="calcularTaxa()" class="form_input">
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






<a style="display:block" href="#" class="action1 open-popup" data-popup=".popup-residuos" id="btn_residuos"></a>
<!-- Social Icons Popup -->
<div class="popup popup-residuos" style="width:90%; margin:15px;  background: transparent; margin-top: 50px; ">
	<div class="content-block">

		<h2 class="page_title_dados" id="">Resíduos da Conta</h2>  
		
		<div method="post" style="background:#fff !important; padding-bottom: 17px; padding:10px; border:1px solid #bdbbbb" id="listar-residuos">

			
		</div> 
		<div class="close_popup_button"><a id="btn_fechar_residuos" href="#" class="close-popup" data-popup=".popup-residuos"><img style="margin-top: 10px" src="images/icons/white/menu_close.png" alt="" title="" width="25px"/></a></div>
	</div>
</div>


<script type="text/javascript">var pag = "<?=$pag?>"</script>


	<script type="text/javascript">
		function carregarImg() {
			var target = document.getElementById('target');
			var file = document.querySelector("#foto").files[0];

			var arquivo = file['name'];
			resultado = arquivo.split(".", 2);

			if(resultado[1] === 'pdf'){
				$('#target').attr('src', "images/pdf.png");
				return;
			}

			if(resultado[1] === 'rar' || resultado[1] === 'zip'){
				$('#target').attr('src', "images/rar.png");
				return;
			}

			if(resultado[1] === 'doc' || resultado[1] === 'docx' || resultado[1] === 'txt'){
				$('#target').attr('src', "images/word.png");
				return;
			}


			if(resultado[1] === 'xlsx' || resultado[1] === 'xlsm' || resultado[1] === 'xls'){
				$('#target').attr('src', "images/excel.png");
				return;
			}


			if(resultado[1] === 'xml'){
				$('#target').attr('src', "images/xml.png");
				return;
			}



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
	function editar(id, descricao, valor, cliente, vencimento, data_pgto, forma_pgto, frequencia, obs, arquivo){
		
    	$('#titulo_inserir').text('Editar Registro');

    	$('#id').val(id);
    	$('#descricao').val(descricao);
    	$('#valor').val(valor);
    	$('#cliente').val(cliente).change();
    	$('#vencimento').val(vencimento);
    	$('#data_pgto').val(data_pgto);
    	$('#forma_pgto').val(forma_pgto).change();
    	$('#frequencia').val(frequencia).change();
    	$('#obs').val(obs);

    	$('#foto').val('');
    	$('#target').attr('src','images/contas/' + arquivo);		

    	$('#btn_novo').click();
	}




	function mostrar(descricao, valor, cliente, vencimento, data_pgto, nome_pgto, frequencia, obs, arquivo, multa, juros, desconto, taxa, total, usu_lanc, usu_pgto, pago, arq){
		    	
    	$('#titulo_dados').text(descricao);
    	$('#valor_dados').text(valor);
    	$('#cliente_dados').text(cliente);
    	$('#vencimento_dados').text(vencimento);
    	$('#data_pgto_dados').text(data_pgto);
    	$('#nome_pgto_dados').text(nome_pgto);
    	$('#frequencia_dados').text(frequencia);
    	$('#obs_dados').text(obs);
    	
    	$('#multa_dados').text(multa);
    	$('#juros_dados').text(juros);
    	$('#desconto_dados').text(desconto);    	
    	$('#taxa_dados').text(taxa);
    	$('#total_dados').text(total);
    	$('#usu_lanc_dados').text(usu_lanc);
    	$('#usu_pgto_dados').text(usu_pgto);
    	
    	$('#pago_dados').text(pago);
    	$('#target_dados').attr("src", "images/contas/" + arquivo);
    	$('#target_link_dados').attr("href", "images/contas/" + arq);

    	$('#btn_mostrar').click();
	}

	function limparCampos(){
		$('#id').val('');
    	$('#descricao').val('');
    	$('#valor').val('');    	
    	$('#vencimento').val("<?=$data_atual?>");
    	$('#data_pgto').val('');    	
    	$('#obs').val('');
    	$('#foto').val('');

    	$('#target').attr("src", "images/contas/sem-foto.png");
	}

	

	
	function paginar(pag, busca){
		$('#dataInicialPag').val($('#dataInicial').val());
		$('#dataFinalPag').val($('#dataFinal').val());

		$('#input_pagina').val(pag);
		$('#input_buscar').val(busca);
		$('#paginacao').click();    
	}


	function buscar(filtro){
		$('#pago').val(filtro);
		$('#btn_filtrar').click();
	}	


	
	function parcelar(id, valor, nome){
    $('#id-parcelar').val(id);
    $('#valor-parcelar').val(valor);
    $('#qtd-parcelar').val('');
    $('#titulo_parcelar').text(nome);
    $('#nome-input-parcelar').val(nome);
	$('#btn_parcelar').click();

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
		        url: 'paginas/' + pag + "/calcular_taxa.php",
		        method: 'POST',
		        data: {valor, pgto},
		        dataType: "html",

		        success:function(result){		           
		            $('#valor-taxa').val(result);
		             totalizar();
		        }
		    });


		}


		function valorBaixar(){
				var ids = $('#ids').val();
				
				 $.ajax({
			        url: 'paginas/' + pag + "/valor_baixar.php",
			        method: 'POST',
			        data: {ids},
			        dataType: "html",

			        success:function(result){
			            $("#total_contas").html(result);
			           
			        }
			    });
			}
	
</script>






	<script type="text/javascript">
		function baixar(id, valor, descricao, pgto, taxa, multa, juros){
	$('#id-baixar').val(id);
	$('#titulo_baixar').text(descricao);
	$('#valor-baixar').val(valor);
	$('#saida-baixar').val(pgto).change();
	$('#subtotal').val(valor);

	
	$('#valor-juros').val(juros);
	$('#valor-desconto').val('');
	$('#valor-multa').val(multa);
	$('#valor-taxa').val(taxa);

	totalizar()

	$('#btn_baixar').click();
	
}


function mostrarResiduos(id){

	$.ajax({
		url: 'paginas/' + pag + "/listar-residuos.php",
		method: 'POST',
		data: {id},
		dataType: "html",

		success:function(result){
			$("#listar-residuos").html(result);
		}
	});
	$('#btn_residuos').click();
	
	
}

	</script>