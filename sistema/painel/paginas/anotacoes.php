<?php
require_once("verificar.php");
$pag = 'anotacoes';

//verificar se ele tem a permissão de estar nessa página
if (@$anotacoes == 'ocultar') {
	echo "<script>window.location='index'</script>";
	exit();
}
?>

<style>
html body .bc-note-page .nav-tabs{border-color:#e5d4c6!important;gap:8px}
html body .bc-note-page .nav-link{background:#fff!important;color:#63351e!important;border:1px solid #d7c5b7!important;border-radius:9px!important;padding:10px 16px}
html body .bc-note-page .nav-link.active{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body .bc-note-content .custom-card,html body .bc-note-content #form_contrato{background:#fff!important;border:1px solid #e5d4c6!important;border-radius:14px!important}
html body .bc-note-content #form_contrato{padding:20px}
html body .bc-note-content :is(.form-control,.form-select){background:#fff!important;color:#4e2d20!important;border:1px solid #d7c5b7!important;border-radius:9px!important;color-scheme:light}
html body .bc-note-content .btn-primary{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body .bc-note-content .nicEdit-panelContain{background:#f4e9df!important;border-color:#d7c5b7!important}
html body .bc-note-content .nicEdit-main{color:#4e2d20!important;background:#fff!important}
html body #modalDados .modal-header{background:#914a29!important;color:#fff!important}
html body #modalDados .modal-title{color:#fff!important}
html body #modalDados .modal-content{background:#fff!important;border:1px solid #e5d4c6!important;border-radius:14px!important}
html body #modalDados #titulo_dados{color:#63351e;font-weight:700}
</style>
<div class="card mb-3" style="margin-top:16px;border:1px solid #e5d4c6;border-radius:14px"><div class="card-body d-flex align-items-center flex-wrap gap-3"><h2 class="mb-0" style="font-size:21px;font-weight:600;color:#4e2d20"><i class="fa-solid fa-note-sticky me-2"></i>Anotações</h2><span style="font-size:12px;color:#795038">Organize informações, lembretes e anotações da equipe.</span></div></div>
<div class="justify-content-between bc-note-page" style="margin-top: 20px">
	<nav style="margin-bottom: 20px">
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
			<button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Listar Anotações</button>
			<button onclick="limparCampos()" class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">
				<i class="fa fa-plus "></i>
				Nova Anotação</button>

		</div>
	</nav>

</div>


<div class="tab-content bc-note-content" id="nav-tabContent">

	<div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
		<div class="row row-sm">
			<div class="col-lg-12">
				<div class="card custom-card">
					<div class="card-body" id="listar">

					</div>
				</div>
			</div>
		</div>
	</div>


	<div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">

		<form id="form_contrato">
			<div>


				<div class="row">
					<div class="col-md-4 mb-2">
						<label>Título</label>
						<input type="text" class="form-control" id="titulo" name="titulo" placeholder="Título" required>

					</div>

					<div class="col-md-2">
						<div class="form-group">
							<label>Mostrar no Dashboard</label>
							<select class="form-select" name="mostrar_home" id="mostrar_home">
								<option value="Não">Não</option>
								<option value="Sim">Sim</option>
							</select>
						</div>
					</div>

					<div class="col-md-2">
						<div class="form-group">
							<label>Privado?</label>
							<select class="form-select" name="privado" id="privado">
								<option value="Não">Não</option>
								<option value="Sim">Sim</option>
							</select>
						</div>
					</div>

					<div class="col-md-4 mb-2" style="margin-top: 26px">
						<button type="submit" id="btn_salvar" class="btn btn-primary">Salvar<i class="fa fa-check ms-2"></i></button>
						<button class="btn btn-primary" type="button" id="btn_carregando" style="display: none">
							<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...
						</button>
						<small>
							<div id="mensagem" align="center"></div>
						</small>
					</div>

				</div>


				<div class="row" style="z-index: 1000">
					<div class="col-md-12 mb-2">
						<label>Texto</label>
						<textarea class="textarea textarea_menor2" id="area" name="msg">

								</textarea>
					</div>

				</div>
				<input type="hidden" class="form-control" id="id" name="id">

			</div>


		</form>

	</div>

</div>




<!-- Modal Dados -->
<div class="modal fade" id="modalDados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel">Detalhes da Anotação</h4>
				<button id="btn-fechar-dados" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">
				<small>

					<div class="row">




						<div class="col-md-12">
							<div class="tile">
								<div class="table-responsive">
									<table id="" class="text-left table table-bordered">

										<span align="center">
											<h5 id="titulo_dados"></h5>
										</span>
										<tr>

											<td><span id="mensagem_dados"></span></td>
										</tr>


									</table>
								</div>
							</div>
						</div>

					</div>



				</small>
				<div id="listar_servicos" style="margin-top: 15px"></div>
			</div>

		</div>
	</div>
</div>







<script type="text/javascript">
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>


<script src="//js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
<script type="text/javascript">
	bkLib.onDomLoaded(nicEditors.allTextAreas);
</script>


<script type="text/javascript">
	$("#form_contrato").submit(function() {

		event.preventDefault();
		nicEditors.findEditor('area').saveContent();
		var formData = new FormData(this);


		$('#btn_salvar').hide();
		$('#btn_carregando').show();

		$.ajax({
			url: 'paginas/' + pag + "/salvar.php",
			type: 'POST',
			data: formData,

			success: function(mensagem) {
				$('#mensagem').text('');
				$('#mensagem').removeClass()
				if (mensagem.trim() == "Salvo com Sucesso") {

					$('#nav-home-tab').click();
					listar();

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