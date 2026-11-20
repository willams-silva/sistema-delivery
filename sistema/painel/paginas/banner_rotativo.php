<?php
//verificar se ele tem a permissão de estar nessa página
if (@$banner_rotativo == 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}
$pag = 'banner_rotativo';
?>


<div class="justify-content-between breadcrumb-header"><div class="left-content mt-2"><h4><i class="fa-solid fa-images me-2"></i>Banner Rotativo</h4><p class="text-muted mb-2">Gerencie as imagens de destaque do cardápio online.</p></div>
	<div class="left-content mt-2">
		<a class="btn ripple btn-primary text-white" onclick="inserir()" type="button"><i class="fe fe-plus me-1"></i> Adicionar Banner</a>

		<a class="btn btn-danger" href="#" onclick="deletarSel()" title="Excluir" id="btn-deletar" style="display:none"><i class="fe fe-trash-2"></i> Deletar</a>


	</div>
</div>


<div class="bs-example widget-shadow" style="padding:15px" id="listar">

</div>

<input type="hidden" id="ids">



<!-- Modal Inserir-->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header  bg-primary text-white">
				<h4 class="modal-title"><span id="titulo_inserir"></span></h4>
				<button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			<form id="form">
				<div class="modal-body">



					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="categoria" class="form-label fw-semibold">Categoria vinculada</label>
								<select class="form-select sel3" id="categoria" name="categoria" style="width:100%;">
									<option value="0">Nenhuma Categoria</option>
									<?php
									$query = $pdo->query("SELECT * FROM categorias where empresa = '$id_empresa' ORDER BY id asc");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$total_reg = @count($res);
									if ($total_reg > 0) {
										for ($i = 0; $i < $total_reg; $i++) {
											foreach ($res[$i] as $key => $value) {
											}
											echo '<option value="' . (int)$res[$i]['id'] . '">' . htmlspecialchars((string)$res[$i]['nome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</option>';
										}
									}
									?>


								</select>
							</div>
						</div>
					</div>


					<div class="row">
						<div class="col-md-8">
							<div class="form-group">
								<label for="foto" class="form-label fw-semibold">Imagem do banner</label><div class="form-text mb-2">Tamanho recomendado: 1400 × 600 px. JPG, PNG ou GIF, até 5 MB.</div>
								<input class="form-control" type="file" name="foto" accept="image/jpeg,image/png,image/gif" onChange="carregarImg();" id="foto">
							</div>
						</div>
						<div class="col-md-4">
							<div id="divImg">
								<img src="images/<?php echo $pag ?>/sem-foto.jpg" class="img-fluid rounded border" style="width:100%;max-height:180px;object-fit:contain" alt="Prévia do banner" id="target">
							</div>
						</div>

					</div>



					<div class="row g-3 mt-2">
 <div class="col-md-4"><label class="form-label fw-semibold" for="ativo">Status</label><select class="form-select" id="ativo" name="ativo"><option value="Sim">Ativo</option><option value="Não">Desativado</option></select></div>
 <div class="col-md-4"><label class="form-label fw-semibold" for="ordem">Ordem de exibição</label><input class="form-control" id="ordem" name="ordem" type="number" min="0" max="99999" value="0"><div class="form-text">Menor número aparece primeiro.</div></div>
 <div class="col-md-6"><label class="form-label fw-semibold" for="inicio_em">Início da exibição (opcional)</label><input class="form-control" id="inicio_em" name="inicio_em" type="datetime-local"></div>
 <div class="col-md-6"><label class="form-label fw-semibold" for="fim_em">Fim da exibição (opcional)</label><input class="form-control" id="fim_em" name="fim_em" type="datetime-local"></div>
 <div class="col-12"><small class="text-muted">O agendamento e o status só controlarão o cardápio após a integração da consulta pública de banners.</small></div>
</div>
<input type="hidden" name="id" id="id">

					<br>
					<small>
						<div id="mensagem" align="center"></div>
					</small>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" id="btn_salvar" class="btn btn-primary"><i class="fa fa-check me-2"></i>Salvar banner</button>
					<button class="btn btn-primary" type="button" id="btn_carregando" style="display: none">
						<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...
					</button>
				</div>
			</form>


		</div>
	</div>
</div>







<script type="text/javascript">
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>



<script type="text/javascript">
	function carregarImg() {
		var target = document.getElementById('target');
		var file = document.querySelector("#foto").files[0];

		if(file && (file.size > 5*1024*1024 || !['image/jpeg','image/png','image/gif'].includes(file.type))){
 document.querySelector('#foto').value='';
 $('#mensagem').addClass('text-danger').text('Escolha uma imagem JPG, PNG ou GIF de até 5 MB.');
 return;
}
$('#mensagem').removeClass('text-danger').text('');
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