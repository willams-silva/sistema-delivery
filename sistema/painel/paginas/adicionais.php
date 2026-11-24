<?php
//verificar se ele tem a permissão de estar nessa página
if (@$adicionais == 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}
$pag = 'adicionais';
?>
<div class="card mb-3" style="margin-top:16px;border:1px solid #e2e8f0;border-radius:14px"><div class="card-body d-flex align-items-center flex-wrap gap-3"><h2 class="mb-0" style="font-size:21px;font-weight:600;color:#4e2d20"><i class="fa-solid fa-circle-plus me-2"></i>Gestão de Adicionais</h2><span style="font-size:12px;color:#795038">Cadastre e gerencie os adicionais e seus valores.</span></div></div>
<div class="justify-content-between breadcrumb-header">
	<div class="left-content mt-2">
		<a class="btn ripple btn-primary text-white" onclick="inserir()" type="button"><i class="fe fe-plus me-1"></i> Novo Adicional</a>

		<!-- EXCLUIR MULTIPLO -->
		<a class="btn btn-danger" href="#" onclick="deletarSel()" title="Excluir"
			id="btn-deletar" style="display:none"><i class="fe fe-trash-2"></i> Deletar</a>

	</div>
</div>


<div class="bs-example widget-shadow" style="padding:15px" id="listar">

</div>


<input type="hidden" id="ids">


<!-- Modal Inserir-->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title"><span id="titulo_inserir"></span></h4>
				<button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			<form id="form">
				<div class="modal-body">

					<div class="row needs-validation was-validated">
						<div class="col-md-7">
							<div class="form-group">
								<label for="exampleInputEmail1">Adicional</label>
								<input type="text" class="form-control" id="nome" name="nome" placeholder="Adicional" required>
							</div>
						</div>

						<div class="col-md-5">
							<div class="form-group">
								<label for="exampleInputEmail1">Valor</label>
								<input type="text" class="form-control" id="valor" name="valor" placeholder="Val do Adicional" required>
							</div>
						</div>

					</div>



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





<script type="text/javascript">
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>