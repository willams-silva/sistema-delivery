<?php

if (@$produtos === 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}


$pag = 'produtos';
?>
<div class="breadcrumb-header justify-content-between ux-catalog-toolbar">
	<div class="left-content mt-2">
		<a class="btn ripple btn-primary text-white" onclick="inserir()" type="button"><i class="fe fe-plus me-1"></i>
			Adicionar <?php echo ucfirst($pag); ?></a>

		<a class="btn btn-outline-primary" href="paginas/produtos/combos.php" title="Configurar combos" style="margin-left:8px"><i class="fe fe-layers me-1"></i> Configurar Combos</a>

		<!-- EXCLUIR MULTIPLO -->
		<a class="btn btn-danger" href="#" onclick="deletarSelProdutos()" title="Excluir" id="btn-deletar" style="display:none"><i class="fe fe-trash-2"></i> Deletar</a>

		<select class="form-select sel7" name="categoria" id="busca" style="width:350px" onchange="buscar()">
			<option value="">Buscar por categoria</option>
			<?php
			$query = $pdo->query("SELECT * from categorias where empresa = '$id_empresa' order by id desc");
			$res = $query->fetchAll(PDO::FETCH_ASSOC);
			$linhas = @count($res);
			if ($linhas > 0) {
				for ($i = 0; $i < $linhas; $i++) { ?>
					<option value="<?php echo $res[$i]['id'] ?>"><?php echo $res[$i]['nome'] ?></option>
			<?php }
			} ?>
		</select>


	</div>


</div>


<div class="ux-products-view-toolbar" role="group" aria-label="Visualização dos produtos">
  <span>Visualização:</span>
  <button type="button" class="ux-view-btn" data-product-view="cards" aria-pressed="true"><i class="fe fe-grid"></i> Cartões</button>
  <button type="button" class="ux-view-btn" data-product-view="table" aria-pressed="false"><i class="fe fe-list"></i> Tabela</button>
</div>
<div class="bs-example widget-shadow" style="padding:15px" id="listar">

</div>



<input type="hidden" id="ids">


<!-- Modal Inserir-->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title"><span id="titulo_inserir"></span></h4>
				<button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			<form id="form_produtos">
				<div class="modal-body">

					<div class="row">
						<div class="col-md-4 mb-2">							
								<label>Código</label>
								<input type="text" class="form-control" id="codigo" name="codigo" placeholder="Código de Barras" onkeyup="gerarCodigo()">							
							</div>
						<div class="col-md-5 needs-validation was-validated">
							<div class="form-group">
								<label for="exampleInputEmail1">Nome do Produto</label>
								<input type="text" class="form-control" id="nome" name="nome" placeholder="Digite o Produto" required>
							</div>
						</div>

						<div class="col-md-3">

							<div class="form-group">
								<label for="exampleInputEmail1">Categoria</label>
								<select class="form-select sel3" id="categoria" name="categoria" style="width:100%;">

									<?php
									$query = $pdo->query("SELECT * FROM categorias where empresa = '$id_empresa' ORDER BY id desc");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									if ($total_reg > 0) {
										for ($i = 0; $i < $total_reg; $i++) {
											foreach ($res[$i] as $key => $value) {
											}
											echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
										}
									} else {
										echo '<option value="0">Cadastre uma Categoria</option>';
									}
									?>


								</select>
							</div>
						</div>

					</div>



					<div class="row">
						<div class="col-md-12 mb-2">
							<label>Descrição <small>(Até 1000 Caracteres)</small></label>
							<textarea type="text" class="form-control" id="descricao" name="descricao"
								placeholder="Descrição do Produto">	</textarea>
						</div>
					</div>



					<div class="row">

						<div class="col-md-2">
							<div class="form-group">
								<label for="exampleInputEmail1">Valor Custo</label>
								<input type="text" class="form-control" id="valor_compra" name="valor_compra" placeholder="R$ 0,00">
							</div>
						</div>


						<div class="col-md-2">
							<div class="form-group">
								<label for="exampleInputEmail1">Valor Venda</label>
								<input type="text" class="form-control" id="valor_venda" name="valor_venda" placeholder="R$ 0,00">
							</div>
						</div>

						<div class="col-md-2">
							<div class="form-group">
								<label for="val_promocional">Valor Promoção</label>
								<input type="text" class="form-control" id="val_promocional" name="val_promocional" placeholder="R$ 0,00">
							</div>
						</div>


						<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Promoção?</label>
								<select class="form-select" id="promocao" name="promocao" style="width:100%;">
									<option value="Não">Não</option>
									<option value="Sim">Sim</option>
								</select>
							</div>
						</div>


						<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Alerta Estoque</label>
								<input type="number" class="form-control" id="nivel_estoque" name="nivel_estoque"
									placeholder="Nível Min.">
							</div>
						</div>




					</div>

					<div class="row">




						<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Combo?</label>
								<select class="form-select" id="combo" name="combo" style="width:100%;">

									<option value="Não">Não</option>
									<option value="Sim">Sim</option>

								</select>
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Delivery?</label>
								<select class="form-select" id="delivery" name="delivery" style="width:100%;">
									<option value="Sim">Sim</option>
									<option value="Não">Não</option>
								</select>
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">Preparado Cozinha?</label>
								<select class="form-select" id="preparado" name="preparado" style="width:100%;">
									<option value="Cozinha">Cozinha</option>
									<option value="Bar">Bar</option>
									<option value="Outro">Outro Local (Local 3)</option>
									<option value="Não">Não</option>
								</select>
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<label for="tem_estoque">Tem Estoque?</label>
								<select class="form-select" id="tem_estoque" name="tem_estoque" style="width:100%;">

									<option value="Sim">Sim</option>
									<option value="Não">Não</option>

								</select>
							</div>
						</div>


					</div>





					<div class="row">

						<div class="col-md-3 mb-2 col-6">							
							<label>Unidade de Medida</label>
							<select class="form-select" name="unidade" id="unidade">
								<option value="0">Escolher Unidade</option>
								<?php 
								$query3 = $pdo->query("SELECT * from unidade_medida where empresa = '$id_empresa' order by id asc");
								$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
								$linhas = @count($res3);
								if($linhas > 0){
									for($i=0; $i<$linhas; $i++){ ?>
										<option value="<?php echo $res3[$i]['id'] ?>"><?php echo $res3[$i]['nome'] ?></option>
									<?php } } ?>
								</select>			
							</div>

						<div class="col-md-7">
							<div class="form-group">
								<label>Foto</label>
								<input class="form-control" type="file" name="foto" onChange="carregarImg();" id="foto">
							</div>
						</div>
						<div class="col-md-2">
							<div id="divImg">
								<img src="images/produtos/sem-foto.jpg" width="80px" id="target">
							</div>
						</div>
					</div>

                    <div class="row mt-2" id="config-tapioca-mista" style="display:none">
                        <div class="col-md-6"><label for="limite_proteinas_mista">Proteínas inclusas (tapioca mista)</label>
                            <select class="form-select" name="limite_proteinas_mista" id="limite_proteinas_mista">
                                <option value="0">Não é tapioca mista</option>
                                <option value="2">2 proteínas inclusas</option>
                                <option value="3">3 proteínas inclusas</option>
                            </select>
                            <small>As proteínas extras são cobradas pelo preço do cadastro de adicionais.</small>
                        </div>
                    </div>
					<div id="listar-codigo"></div>

					<input type="hidden" name="id" id="id">

					<br>
					<small>
						<div id="mensagem" align="center"></div>
					</small>
				</div>

				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" id="btn_salvar">Salvar<i class="fa fa-check ms-2"></i></button>
					<button class="btn btn-primary" type="button" id="btn_carregando" style="display: none">
						<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...
					</button>
				</div>
			</form>


		</div>
	</div>
</div>







<!-- Modal Dados -->
<div class="modal fade" id="modalDados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_dados"></span><span id="titulo_dados"></span></h4>
				<button id="btn-fechar-dados" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">

				<div class="row">


					<div class="col-md-8">
						<div class="tile">
							<div class="table-responsive">
								<table id="" class="text-left table table-bordered">

									<tr>
										<td width="30%" class="bg-primary text-white">Código</td>
										<td><span id="codigo_dados"></span></td>
									</tr>

									<tr>
										<td width="30%" class="bg-primary text-white">Categoria</td>
										<td><span id="categoria_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white">Valor Compra</td>
										<td>R$ <span id="valor_compra_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white">Valor Venda</td>
										<td>R$ <span id="valor_venda_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white">Valor Promocional</td>
										<td>R$ <span id="val_promocional_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white">Estoque</td>
										<td><span id="estoque_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white">Alerta Nível Estoque</td>
										<td><span id="nivel_estoque_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white">Tem Estoque</td>
										<td><span id="tem_estoque_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Promoção</td>
										<td><span id="promocao_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Combo</td>
										<td><span id="combo_dados"></span></td>
									</tr>




									<tr>
										<td class="bg-primary text-white w_150">Preparar Cozinha</td>
										<td><span id="preparado_dados"></span></td>
									</tr>

									<tr>
										<td class="bg-primary text-white w_150">Delivery</td>
										<td><span id="delivery_dados"></span></td>
									</tr>



								</table>
							</div>
						</div>
					</div>


					<div class="col-md-4">
						<div class="tile">
							<div class="table-responsive">
								<table id="" class="text-left table table-bordered">

									<tr>
										<td align="center"><img src="" id="target_mostrar" width="200px"></td>
									</tr>

								</table>
							</div>
						</div>
					</div>


				</div>

				<div class="row">

					<div class="col-md-12">
						<div class="tile">
							<div class="table-responsive">
								<table id="" class="text-left table table-bordered">

									<tr>
										<td width="15%" class="bg-primary text-white">Descrição</td>
										<td><span id="descricao_dados"></span></td>
									</tr>

								</table>
							</div>
						</div>
					</div>
				</div>

			</div>

		</div>
	</div>
</div>









<!-- Modal Saida-->
<div class="modal fade" id="modalSaida" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_saida"></span></h4>
				<button id="btn-fechar-saida" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">
				<form id="form-saida">

					<div class="row">
						<div class="col-md-4">
							<div class="form-group">

								<input type="number" class="form-control" id="quantidade_saida" name="quantidade_saida"
									placeholder="Quantidade" required>
							</div>
						</div>

						<div class="col-md-5">
							<div class="form-group">
								<input type="text" class="form-control" id="motivo_saida" name="motivo_saida" placeholder="Motivo Saída"
									required>
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
				<small>
					<div id="mensagem-saida" align="center"></div>
				</small>
			</div>


		</div>
	</div>
</div>





<!-- Modal Entrada-->
<div class="modal fade" id="modalEntrada" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_entrada"></span></h4>
				<button id="btn-fechar-entrada" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">
				<form id="form-entrada">

					<div class="row">
						<div class="col-md-4">
							<div class="form-group">

								<input type="number" class="form-control" id="quantidade_entrada" name="quantidade_entrada"
									placeholder="Quantidade" required>
							</div>
						</div>

						<div class="col-md-5">
							<div class="form-group">
								<input type="text" class="form-control" id="motivo_entrada" name="motivo_entrada"
									placeholder="Motivo Entrada" required>
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
				<small>
					<div id="mensagem-entrada" align="center"></div>
				</small>
			</div>


		</div>
	</div>
</div>







<!-- Modal Variações-->
<div class="modal fade" id="modalVariacoes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_nome_var"></span></h4>
				<button id="btn-fechar-var" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
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
								<input type="text" class="form-control" id="valor_var" name="valor" placeholder="R$ 50,00" required>
							</div>
						</div>

						<div class="col-md-3" style="margin-top: 25px">
							<button id="btn_var" type="submit" class="btn btn-primary">Salvar</button>

						</div>

					</div>

					<input type="hidden" id="id_var" name="id">
					<input type="hidden" id="id_it_var" name="id_item">

				</form>

				<br>
				<small>
					<div id="mensagem-var" align="center"></div>
				</small>


				<div id="listar-var"></div>
			</div>


		</div>
	</div>
</div>





<!-- Modal Grades-->
<div class="modal fade" id="modalGrades" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_nome_grades"></span></h4>
				<button id="btn-fechar-grades" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">
				<form id="form-grades">


					<div class="row">

						<div class="col-md-8">
							<div class="form-group">
								<label for="exampleInputEmail1">Descrição na hora da compra <small>(Até 70 Caracteres)</small></label>
								<input maxlength="70" type="text" class="form-control" id="texto" name="texto"
									placeholder="Descrição do item" required="">
							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								<label for="exampleInputEmail1">Tipo Item

									<div class="icones_mobile" class="dropdown" style="display: inline-block; ">
										<a title="Clique para ver as Informações" href="#" aria-expanded="false" aria-haspopup="true"
											data-bs-toggle="dropdown" class="dropdown"><i class="fa fa-info-circle text-primary"></i> </a>
										<div class="dropdown-menu tx-13" style="width:500px">
											<div class="dropdown-item-text botao_excluir" style="width:500px">
												<div class="notification_desc2">
													<p>
														<b>Seletor Único</b><br>
														<span class="text-muted" style="font-size: 12px">Você poderá selecionar apenas uma opção,
															exemplo, esse produto acompanha uma bebida, selecione a bebida desejada.</span>
													</p><br>

													<p>
														<b>Seletor Múltiplos</b><br>
														<span class="text-muted" style="font-size: 12px">Você poderá selecionar diversos itens
															dentro desta grade, exemplo de adicionais, 3 adicionais de bacon, 2 de cheddar,
															etc.</span>
													</p><br>


													<p>
														<b>Apenas 1 Item de cada</b><br>
														<span class="text-muted" style="font-size: 12px">Você pode selecionar várias opções porém só
															poderá inserir 1 item de cada, exemplo remoção de ingredientes, retirar cebola, retirar
															tomate, etc, será sempre uma unica seleção por cada item.</span>
													</p><br>

													<p>
														<b>Seletor Variação</b><br>
														<span class="text-muted" style="font-size: 12px">Você poderá selecionar apenas uma opção,
															exemplo, Tamanho Grande, Médio, etc, será mostrado em locais onde define a variação do
															produto.</span>
													</p><br>


													</p>

												</div>

											</div>
										</div>
									</div>



								</label>
								<select class="form-select" id="tipo_item" name="tipo_item" style="width:100%;">

									<option value="Único">Seletor Único</option>
									<option value="Múltiplo">Seletor Múltiplos</option>
									<option value="1 de Cada">1 item de Cada</option>
									<option value="Variação">Variação Produto</option>

								</select>

							</div>
						</div>




					</div>


					<div class="row">
						<div class="col-md-9">
							<div class="form-group">
								<label for="exampleInputEmail1">Descrição Comprovante <small>(Até 70 Caracteres)</small></label>
								<input maxlength="70" type="text" class="form-control" id="nome_comprovante" name="nome_comprovante"
									placeholder="Descrição do item no comprovante" required="">
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<label for="exampleInputEmail1">É Adicional?</label>
								<select class="form-select" id="adicional" name="adicional" style="width:100%;">

									<option value="Não">Não</option>
									<option value="Sim">Sim</option>

								</select>
							</div>
						</div>

					</div>

					<div class="row">
						<div class="col-md-5">
							<div class="form-group">
								<label for="exampleInputEmail1">Tipo Valor</label>
								<select class="form-select" id="valor_item" name="valor_item" style="width:100%;">
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
								<input type="number" class="form-control" id="limite" name="limite"
									placeholder="Selecionar até x Itens">
							</div>
						</div>

						<div class="col-md-2" style="margin-top: 24px">
							<button id="btn_grade" type="submit" class="btn btn-primary">Salvar</button>

						</div>
					</div>



					<input type="hidden" id="id_grades" name="id">
					<input type="hidden" id="id_grade_editar" name="id_grade_editar">

				</form>

				<br>
				<small>
					<div id="mensagem-grades" align="center"></div>
				</small>


				<hr>
				<div id="listar-grades"></div>
			</div>


		</div>
	</div>
</div>








<!-- Modal Itens-->
<div class="modal fade" id="modalItens" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_nome_itens"></span></h4>
				<button id="btn-fechar-itens" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">
				<form id="form-itens">


					<div class="row">

						<div class="col-md-12" id="div_nome">
							<div class="form-group">
								<label for="exampleInputEmail1">Nome <small>(Até 70 Caracteres)</small></label>
								<input maxlength="70" type="text" class="form-control" id="texto_item" name="texto"
									placeholder="Descrição do item">
							</div>
						</div>


						<div class="col-md-12" id="div_adicional">
							<div class="form-group">
								<label for="exampleInputEmail1">Escolher Adicional </label>

								<select class="form-select sel5" id="adicional_grade" name="adicional" style="width:100%;"
									onchange="alterarValor()">
									<option value="">Selecione um Adicional</option>
									<?php
									$query = $pdo->query("SELECT * FROM adicionais ORDER BY nome asc");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									if ($total_reg > 0) {
										for ($i = 0; $i < $total_reg; $i++) {
											foreach ($res[$i] as $key => $value) {
											}
											echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
										}
									} else {
										echo '<option value="">Cadastre os Adicionais</option>';
									}
									?>


								</select>
							</div>
						</div>



					</div>

					<div class="row">

						<div class="col-md-4">

							<div class="form-group">
								<label for="exampleInputEmail1">Valor</label>
								<input type="text" class="form-control" id="valor_do_item" name="valor" placeholder="Valor Se Houver">
							</div>
						</div>



						<div class="col-md-5">
							<div class="form-group">
								<label for="exampleInputEmail1">Limite de Seleção Itens</label>
								<input type="number" class="form-control" id="limite_itens" name="limite"
									placeholder="Selecionar até x Itens">
							</div>
						</div>



						<div class="col-md-3" style="margin-top: 24px">
							<button id="btn_itens" type="submit" class="btn btn-primary">Salvar</button>

						</div>
					</div>



					<input type="hidden" id="id_item" name="id">
					<input type="hidden" id="id_item_produto" name="id_item_produto">
					<input type="hidden" id="e_adicional" name="e_adicional">
					<input type="hidden" id="id_item_editar" name="id_item_editar">

				</form>

				<br>
				<small>
					<div id="mensagem-itens" align="center"></div>
				</small>


				<hr>
				<div id="listar-itens"></div>
			</div>


		</div>
	</div>
</div>





<script type="text/javascript">
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>

<script>
	$(document).ready(function() {

		$('.sel7').select2({
		
		});

	});
</script>

<script type="text/javascript">
	function carregarImg() {
		var target = document.getElementById('target');
		var file = document.querySelector("#foto").files[0];

		var reader = new FileReader();

		reader.onloadend = function() {
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
	$("#form-saida").submit(function() {

		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: 'paginas/' + pag + "/saida.php",
			type: 'POST',
			data: formData,

			success: function(mensagem) {
				$('#mensagem-saida').text('');
				$('#mensagem-saida').removeClass()
				if (mensagem.trim() == "Salvo com Sucesso") {

					$('#btn-fechar-saida').click();
					buscar();

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
	$("#form-entrada").submit(function() {

		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: 'paginas/' + pag + "/entrada.php",
			type: 'POST',
			data: formData,

			success: function(mensagem) {
				$('#mensagem-entrada').text('');
				$('#mensagem-entrada').removeClass()
				if (mensagem.trim() == "Salvo com Sucesso") {

					$('#btn-fechar-entrada').click();
					buscar();

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
	$("#form-var").submit(function() {

		var id_var = $('#id_var').val()

		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: 'paginas/' + pag + "/inserir-variacoes.php",
			type: 'POST',
			data: formData,

			success: function(mensagem) {
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


	function limparCamposVar() {

		$('#nome_var').val('');
		$('#valor_var').val('');
		$('#sigla').val('');
		$('#descricao_var').val('');
		$('#id_it_var').val('');

	}




	function listarVariacoes(id) {
		$.ajax({
			url: 'paginas/' + pag + "/listar-variacoes.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "html",

			success: function(result) {
				$("#listar-var").html(result);
				$('#mensagem-excluir-var').text('');
			}
		});
	}


	function excluirVar(id) {
		var id_var = $('#id_var').val()
		$.ajax({
			url: 'paginas/' + pag + "/excluir-variacoes.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "text",

			success: function(mensagem) {
				if (mensagem.trim() == "Excluído com Sucesso") {
					listarVariacoes(id_var);
				} else {
					$('#mensagem-excluir-var').addClass('text-danger')
					$('#mensagem-excluir-var').text(mensagem)
				}

			},

		});
	}


	function ativarVar(id, acao) {
		var id_var = $('#id_var').val()
		$.ajax({
			url: 'paginas/' + pag + "/mudar-status-var.php",
			method: 'POST',
			data: {
				id,
				acao
			},
			dataType: "text",

			success: function(mensagem) {
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
	$("#form-grades").submit(function() {

		var id_var = $('#id_grades').val()

		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: 'paginas/' + pag + "/inserir-grades.php",
			type: 'POST',
			data: formData,

			success: function(mensagem) {
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
	function limparCamposGrades() {

		$('#texto').val('');
		$('#limite').val('');
		$('#nome_comprovante').val('');
		$('#id_grade_editar').val('');
		$('#btn_grade').text('Salvar');


	}



	function listarGrades(id) {
		$.ajax({
			url: 'paginas/' + pag + "/listar-grades.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "html",

			success: function(result) {
				$("#listar-grades").html(result);
				$('#mensagem-excluir-grades').text('');
			}
		});
	}







	function excluirGrades(id) {
		var id_var = $('#id_grades').val()
		$.ajax({
			url: 'paginas/' + pag + "/excluir-grade.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "text",

			success: function(mensagem) {
				if (mensagem.trim() == "Excluído com Sucesso") {
					listarGrades(id_var);
				} else {
					$('#mensagem-excluir-grades').addClass('text-danger')
					$('#mensagem-excluir-grades').text(mensagem)
				}

			},

		});
	}




	function ativarGrades(id, acao) {
		var id_var = $('#id_grades').val()
		$.ajax({
			url: 'paginas/' + pag + "/mudar-status-grade.php",
			method: 'POST',
			data: {
				id,
				acao
			},
			dataType: "text",

			success: function(mensagem) {
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
	$("#form-itens").submit(function() {

		var id_var = $('#id_item').val()

		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: 'paginas/' + pag + "/inserir-itens.php",
			type: 'POST',
			data: formData,

			success: function(mensagem) {
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
	function limparCamposItens() {

		$('#texto_item').val('');
		$('#limite_itens').val('');
		$('#valor_do_item').val('');


		$('#id_item_editar').val('');
		$('#btn_itens').text('Salvar');

	}



	function listarItens(id) {
		$.ajax({
			url: 'paginas/' + pag + "/listar-itens.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "html",

			success: function(result) {
				$("#listar-itens").html(result);
				$('#mensagem-excluir-itens').text('');
			}
		});
	}







	function excluirItens(id) {
		var id_var = $('#id_item').val()
		$.ajax({
			url: 'paginas/' + pag + "/excluir-item.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "text",

			success: function(mensagem) {
				if (mensagem.trim() == "Excluído com Sucesso") {
					listarItens(id_var);
				} else {
					$('#mensagem-excluir-itens').addClass('text-danger')
					$('#mensagem-excluir-itens').text(mensagem)
				}

			},

		});
	}




	function ativarItens(id, acao) {
		var id_var = $('#id_item').val()
		$.ajax({
			url: 'paginas/' + pag + "/mudar-status-itens.php",
			method: 'POST',
			data: {
				id,
				acao
			},
			dataType: "text",

			success: function(mensagem) {
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

		$('.sel5').select2({
			dropdownParent: $('#modalItens')
		});
	});
</script>


<script type="text/javascript">
	function listarVarCat(id) {
		$.ajax({
			url: 'paginas/' + pag + "/listar_var_cat.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "html",

			success: function(result) {
				$("#listar_var_cat").html(result);
			}
		});
	}
</script>


<script type="text/javascript">
	function alterarValor() {
		var adicional = $('#adicional_grade').val();
		var e_adicional = $('#e_adicional').val();

		if (e_adicional == 'Não') {
			return;
		}

		$.ajax({
			url: 'paginas/' + pag + "/listar_valor_adicional.php",
			method: 'POST',
			data: {
				adicional
			},
			dataType: "html",

			success: function(result) {
				//alert(result)
				$("#valor_do_item").val(result);
			}
		});

	}


	function buscar() {
		var busca = $('#busca').val();
		listar(busca)
	}
</script>



<script type="text/javascript">
	
$("#form_produtos").submit(function () {

    event.preventDefault();
    var formData = new FormData(this);

    $('#mensagem').text('Salvando...')
    $('#btn_salvar').hide();
    $('#btn_carregando').show();

    $.ajax({
        url: 'paginas/' + pag + "/salvar.php",
        type: 'POST',
        data: formData,

        success: function (mensagem) {
            $('#mensagem').text('');
            $('#mensagem').removeClass()
            if (mensagem.trim() == "Salvo com Sucesso") {

                $('#btn-fechar').click();
                sucesso();
                buscar();

                $('#mensagem').text('')          

            } else {

                $('#mensagem').addClass('text-danger')
                $('#mensagem').text(mensagem)
            }

            $('#btn_salvar').show();
            $('#btn_carregando').hide();

        },

        cache: false,
        contentType: false,
        processData: false,

    });

});


</script>



<script type="text/javascript">
	

function excluirProduto(id) {
    //$('#mensagem-excluir').text('Excluindo...')

    $('body').removeClass('timer-alert');
    Swal.fire({
        title: "Deseja Excluir?",
        text: "Você não conseguirá recuperá-lo novamente!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33', // Cor do botão de confirmação (vermelho)
        cancelButtonColor: '#3085d6', // Cor do botão de cancelamento (azul)
        confirmButtonText: "Sim, Excluir!",
        cancelButtonText: "Cancelar",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {


            $.ajax({
                url: 'paginas/' + pag + "/excluir.php",
                method: 'POST',
                data: { id },
                dataType: "html",

                success: function (mensagem) {
                    if (mensagem.trim() == "Excluído com Sucesso") {

                        // Ação de exclusão aqui
                        Swal.fire({
                            title: 'Excluido com Sucesso!',
                            text: 'Fecharei em 1 segundo.',
                            icon: "success",
                            timer: 1000
                        })
                        //excluido();
                        buscar();
                        limparCampos();


                    } else {
                        $('#mensagem-excluir').addClass('text-danger')
                        $('#mensagem-excluir').text(mensagem)
                    }
                }
            });

        }
    });


};
</script>


<script type="text/javascript">
			function gerarCodigo(){
				var codigo = $('#codigo').val();


				$.ajax({
					url: 'paginas/' + pag + "/gerar-codigo.php",
					method: 'POST',
					data: {codigo},
					dataType: "html",

					success:function(result){
						$("#listar-codigo").html(result);

					}
				});

			}
		</script>
<script>
(function(){
 function atualizarCampoMista(){
   var categoria = ($('#categoria option:selected').text() || '').trim().toLowerCase();
   var mista = categoria === 'tapiocas salgadas';
   $('#config-tapioca-mista').toggle(mista);
   if (!mista) $('#limite_proteinas_mista').val('0');
 }
 $(document).on('change', '#categoria', atualizarCampoMista);
 $(document).on('click', '[onclick^="inserir("]', function(){ $('#limite_proteinas_mista').val('0'); setTimeout(atualizarCampoMista, 0); });
 $(document).on('click', '[onclick^="editar("]', function(){
   var id = $('#id').val();
   $('#limite_proteinas_mista').val('0');
   atualizarCampoMista();
   if (id && $('#config-tapioca-mista').is(':visible')) {
     $.getJSON('paginas/produtos/consultar-mista.php', {id:id}, function(r){
       if ($('#id').val() == id) $('#limite_proteinas_mista').val(String(r.limite || 0));
     });
   }
 });
})();
</script>

<style>
.ux-products-view-toolbar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:14px 0 10px}.ux-products-view-toolbar>span{font-weight:600;margin-right:4px}.ux-view-btn{border:1px solid #d7c5b7;border-radius:9px;background:#fff;color:#6c4631;padding:9px 14px;font-weight:600}.ux-view-btn[aria-pressed=true]{background:#914a29;color:white;border-color:#914a29}.ux-product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px;margin:14px 0}.ux-product-card{border:1px solid #ebdfd6;border-radius:14px;overflow:hidden;background:#fff;box-shadow:0 3px 14px #0000000b;display:flex;flex-direction:column}.ux-product-card-photo{height:150px;background:#fff;display:flex;align-items:center;justify-content:center}.ux-product-card-photo img{width:100%;height:100%;object-fit:contain}.ux-product-card-body{padding:14px;display:flex;flex-direction:column;gap:8px;flex:1}.ux-product-card-name{font-weight:750;line-height:1.3;overflow-wrap:anywhere}.ux-product-card-price{font-size:1.2rem;font-weight:800;color:#8e4526}.ux-product-card-meta{font-size:.85rem;color:#6c625e}.ux-product-card-actions{display:flex;gap:6px;flex-wrap:wrap;margin-top:auto;padding-top:9px;border-top:1px solid #f1e8e2}.ux-product-card-actions a{margin:0!important}
html body #listar .ux-product-card-actions{display:flex!important;flex-wrap:wrap!important;justify-content:center!important;gap:7px!important;align-items:center;justify-items:center;padding-top:12px!important}
html body #listar .ux-product-card-actions .btn{--bc-fg:#795038;--bc-bg:#f4e7dc;min-width:36px!important;min-height:36px!important;padding:7px!important;border-radius:9px!important;border:1px solid var(--bc-fg)!important;background:var(--bc-bg)!important;color:var(--bc-fg)!important;transition:background .2s,color .2s,transform .2s,box-shadow .2s!important}
html body #listar .ux-product-card-actions .btn-info-light{--bc-fg:#17658a;--bc-bg:#e1f2fa}
html body #listar .ux-product-card-actions .btn-primary-light,html body #listar .ux-product-card-actions .btn-warning-light{--bc-fg:#89500a;--bc-bg:#fff0d7}
html body #listar .ux-product-card-actions .btn-danger-light{--bc-fg:#b42332;--bc-bg:#fde5e7}
html body #listar .ux-product-card-actions .btn-success-light{--bc-fg:#13745a;--bc-bg:#def3e9}
html body #listar .ux-product-card-actions .btn i{color:inherit!important;font-size:16px!important}
html body #listar .ux-product-card-actions .btn:not(.disabled):not([aria-disabled=true]):hover{background:var(--bc-fg)!important;color:#fff!important;transform:translateY(-2px);box-shadow:0 4px 9px #63351e30!important}
html body #listar .ux-product-card-actions .btn:focus-visible{outline:2px solid var(--bc-fg)!important;outline-offset:2px}
html body #listar .ux-product-card-actions .btn.ocultar,html body #listar .ux-product-card-actions .btn.d-none{display:none!important}
@media(prefers-reduced-motion:reduce){html body #listar .ux-product-card-actions .btn{transition:none!important;transform:none!important}}
.ux-product-card-stock{font-size:.85rem}.ux-product-card-disabled{opacity:.72}.ux-product-grid-empty{padding:24px;text-align:center;color:#6c625e}.ux-products-cards-mode #tabela_wrapper .dataTables_scroll,.ux-products-cards-mode #tabela_wrapper table#tabela,.ux-products-cards-mode #tabela_wrapper .dataTables_paginate{display:none!important}.ux-card-pagination{display:flex;justify-content:center;align-items:center;flex-wrap:wrap;gap:6px;padding:16px 0}.ux-card-pagination button{border:1px solid #d7c5b7;border-radius:8px;background:#fff;color:#6c4631;padding:7px 12px;min-width:38px}.ux-card-pagination button[aria-current=page]{background:#914a29;color:#fff;border-color:#914a29}.ux-card-pagination button:disabled{opacity:.45;cursor:not-allowed}@media(max-width:600px){.ux-product-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.ux-product-card-photo{height:115px}.ux-product-card-body{padding:10px}.ux-product-card-actions{gap:4px}.ux-product-card-actions .btn{padding:5px 7px}.ux-product-card-price{font-size:1rem}}
</style>
<script>
(function(){
  var key='bora-produtos-visualizacao';
  var view='cards';
  try { if(localStorage.getItem(key)==='table') view='table'; } catch(e){}
  function escapeText(value){return $('<div>').text(value||'').html();}
  function applyView(){
    $('.ux-view-btn').each(function(){var active=$(this).attr('data-product-view')===view;$(this).attr('aria-pressed',String(active));});
    $('#listar').toggleClass('ux-products-cards-mode',view==='cards');
    if(view==='cards') setTimeout(renderCards,0);
    else $('#ux-product-grid,#ux-card-pagination').hide();
  }
  function renderCards(){
    var $table=$('#listar #tabela');
    if(!$table.length)return;
    var $grid=$('#ux-product-grid');
    if(!$grid.length){$grid=$('<div id="ux-product-grid" class="ux-product-grid" aria-label="Produtos em cartões"></div>');$('#tabela_wrapper').prepend($grid);}
    $grid.empty().show();

    var api=($.fn.DataTable && $.fn.DataTable.isDataTable($table[0])) ? $table.DataTable() : null;
    var $pager=$('#ux-card-pagination');
    if(!$pager.length){$pager=$('<nav id="ux-card-pagination" class="ux-card-pagination" aria-label="Páginas de produtos"></nav>');$('#tabela_wrapper').append($pager);}
    $pager.empty().show();
    if(api){
      var info=api.page.info();
      if(info.pages>1){
        var makeButton=function(label,page,disabled,current){var $b=$('<button type="button"></button>').text(label).prop('disabled',disabled).attr('data-page',page);if(current)$b.attr('aria-current','page');$pager.append($b);};
        makeButton('Anterior',Math.max(0,info.page-1),info.page===0,false);
        for(var n=0;n<info.pages;n++){if(info.pages>9 && Math.abs(n-info.page)>2 && n!==0 && n!==info.pages-1)continue;makeButton(String(n+1),n,false,n===info.page);}
        makeButton('Próxima',Math.min(info.pages-1,info.page+1),info.page>=info.pages-1,false);
      }
    }
    // Usa somente as linhas visíveis da página atual do DataTables, mantendo busca e paginação existentes.
    var rows=($.fn.DataTable && $.fn.DataTable.isDataTable($table[0])) ? $table.DataTable().rows({page:'current',search:'applied'}).nodes() : $table.find('tbody tr').toArray();
    $(rows).each(function(){
      var $cells=$(this).children('td');
      if($cells.length<8)return;
      var $img=$cells.eq(1).find('img').first().clone();
      var $name=$cells.eq(1).clone();$name.find('img,span').remove();
      var name=$name.text().trim();
      var $card=$('<article class="ux-product-card"></article>');
      if($cells.eq(1).css('color')==='rgb(196, 196, 196)')$card.addClass('ux-product-card-disabled');
      var $photo=$('<div class="ux-product-card-photo"></div>').append($img);
      var $body=$('<div class="ux-product-card-body"></div>');
      $body.append($('<div class="ux-product-card-name"></div>').text(name));
      $body.append($('<div class="ux-product-card-meta"></div>').text($cells.eq(2).text().trim()));
      $body.append($('<div class="ux-product-card-price"></div>').text($cells.eq(3).text().trim()));
      $body.append($('<div class="ux-product-card-stock"></div>').text('Estoque: '+$cells.eq(4).text().trim()));
      $body.append($('<div class="ux-product-card-meta"></div>').text('Delivery: '+$cells.eq(5).text().trim()+' · Preparado: '+$cells.eq(6).text().trim()));
      // Clona as ações originais para preservar os mesmos handlers de edição, estoque e exclusão.
      $body.append($('<div class="ux-product-card-actions"></div>').append($cells.eq(7).children().clone()));
      $card.append($photo,$body);$grid.append($card);
    });
    if(!$grid.children().length)$grid.append('<p class="ux-product-grid-empty">Nenhum produto encontrado nesta página.</p>');
  }
  $(document).on('click','#ux-card-pagination button[data-page]',function(){var $table=$('#listar #tabela');if(!$.fn.DataTable || !$.fn.DataTable.isDataTable($table[0]))return;var api=$table.DataTable();var target=Number($(this).attr('data-page'));if(Number.isInteger(target)&&target>=0&&target<api.page.info().pages){api.page(target).draw('page');renderCards();}});
  $(document).on('click','.ux-view-btn',function(){view=$(this).attr('data-product-view');try{localStorage.setItem(key,view);}catch(e){}applyView();});
  var cardSearchTimer;
  $(document).on('input keyup search','#listar #tabela_wrapper .dataTables_filter input',function(){
    if(view!=='cards')return;
    var value=this.value;
    clearTimeout(cardSearchTimer);
    cardSearchTimer=setTimeout(function(){
      var table=$('#listar #tabela')[0];
      if(!table || !$.fn.DataTable || !$.fn.DataTable.isDataTable(table))return;
      var api=$(table).DataTable();
      api.search(value).draw();
      renderCards();
    },120);
  });
  $(document).on('draw.dt search.dt length.dt','#tabela',function(){if(view==='cards')setTimeout(renderCards,0);});
  // A listagem é substituída via AJAX ao filtrar/salvar: reconstruir os cartões após a atualização.
  var root=document.getElementById('listar');
  if(root&&window.MutationObserver){
    var scheduled=false;
    new MutationObserver(function(){if(scheduled)return;scheduled=true;setTimeout(function(){scheduled=false;applyView();},60);}).observe(root,{childList:true,subtree:false});
  }
  $(function(){applyView();});
})();
</script>
