<?php
$pag = 'frequencias';

//verificar se ele tem a permissão de estar nessa página
if (@$frequencias == 'ocultar') {
	echo "<script>window.location='index'</script>";
	exit();
}
?>

<style>
html body #modalForm .modal-header{background:#914a29!important;color:#fff!important;border-color:#e5d4c6!important}
html body #modalForm .modal-title,html body #modalForm .modal-title span,html body #modalForm .btn-close{color:#fff!important}
html body #modalForm .modal-content{background:#fff!important;border:1px solid #e5d4c6!important;border-radius:14px!important;overflow:hidden}
html body #modalForm .form-control{background:#fff!important;color:#4e2d20!important;border:1px solid #d7c5b7!important}
html body #modalForm .form-control::placeholder{color:#927d70!important;opacity:1}
html body #modalForm .btn-primary{background:#914a29!important;border-color:#914a29!important;color:#fff!important}
html body #modalForm #dias{text-align:center!important;padding-inline:0!important;appearance:textfield!important}
html body #modalForm #dias::-webkit-inner-spin-button,html body #modalForm #dias::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}
</style>
<div class="card mb-3" style="margin-top:16px;border:1px solid #e5d4c6;border-radius:14px"><div class="card-body d-flex align-items-center flex-wrap gap-3"><h2 class="mb-0" style="font-size:21px;font-weight:600;color:#4e2d20"><i class="fa-solid fa-calendar-days me-2"></i>Frequências</h2><span style="font-size:12px;color:#795038">Gerencie os intervalos de recorrência das contas financeiras.</span></div></div>
<div class="breadcrumb-header justify-content-between">
	<div class="left-content mt-2">
		<a class="btn ripple btn-primary text-white" onclick="inserir()" type="button"><i class="fe fe-plus me-1"></i> Adicionar Frequência</a>



		<a class="btn btn-danger" href="#" onclick="deletarSel()" title="Excluir" id="btn-deletar" style="display:none"><i class="fe fe-trash-2"></i> Deletar</a>


	</div>

</div>


<div class="row row-sm">
	<div class="col-lg-12">
		<div class="card custom-card">
			<div class="card-body" id="listar">

			</div>
		</div>
	</div>
</div>

<input type="hidden" id="ids">

</div>

<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog ">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_inserir"></span></h4>
				<button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			<form id="form">
				<div class="modal-body">


					<div class="row needs-validation was-validated">
						<div class="col-md-6 mb-2">
							<label>Frequência</label>
							<input type="text" class="form-control" id="frequencia" name="frequencia" placeholder="Diária, Mensal, etc" required>
						</div>

						<div class="col-md-3 mb-2">
							<label>Dias</label>
							<input type="number" class="form-control" id="dias" name="dias" placeholder="1, 30" required>
						</div>

						<div class="col-md-3" style="margin-top:25px">
							<button id="btn_salvar" type="submit" class="btn btn-primary">Salvar<i class="fa fa-check ms-2"></i></button>
							<button class="btn btn-primary" type="button" id="btn_carregando" style="display: none">
								<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...
							</button>
						</div>


					</div>



					<input type="hidden" class="form-control" id="id" name="id">

					<br>
					<small>
						<div id="mensagem" align="center"></div>
					</small>
				</div>

			</form>
		</div>
	</div>
</div>







<script type="text/javascript">
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>

<script>
	modalForm.addEventListener('shown.bs.modal', () => {
		frequencia.focus()
	})
</script>