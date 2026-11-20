<?php


if (@$cargos == 'ocultar') {
	echo "<script>window.location='index'</script>";
	exit();
}
$pag = 'cargos';
?>

<div class="breadcrumb-header justify-content-between align-items-center">
    <div class="left-content mt-2">
        <h4 class="mb-1"><i class="fa-solid fa-id-badge me-2" aria-hidden="true"></i>Gestão de Cargos</h4>
        <p class="text-muted mb-0">Organize as funções da equipe e consulte quantos funcionários utilizam cada cargo.</p>
    </div>
    <div class="mt-2 d-flex gap-2 flex-wrap">
        <button class="btn btn-primary" type="button" onclick="inserir()"><i class="fa-solid fa-plus me-1"></i>Novo Cargo</button>
        <button class="btn btn-outline-danger" type="button" onclick="deletarSel()" id="btn-deletar" style="display:none"><i class="fa-solid fa-trash-can me-1"></i>Excluir selecionados</button>
    </div>
</div>
<div class="row row-sm">
    <div class="col-sm-6 mb-3"><div class="card custom-card h-100"><div class="card-body"><small class="text-muted">Cargos cadastrados</small><h3 id="bc-total-cargos" class="mb-0">—</h3></div></div></div>
    <div class="col-sm-6 mb-3"><div class="card custom-card h-100"><div class="card-body"><small class="text-muted">Cargos em uso</small><h3 id="bc-cargos-em-uso" class="mb-0">—</h3></div></div></div>
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

<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog ">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_inserir"></span></h4>
				<button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			<form id="form">
				<div class="modal-body">



					<div class="row needs-validation was-validated">
						<div class="col-md-8 mb-2">
							<label for="nome">Nome do cargo <span class="text-danger">*</span></label>
							<input type="text" class="form-control" id="nome" name="nome" placeholder="Ex.: Atendente, Gerente, Cozinheiro" maxlength="100" autocomplete="off" required>
						</div>

						<div class="col-md-4 d-flex align-items-end mb-2">
							<button id="btn_salvar" type="submit" class="btn btn-primary w-100">Salvar<i
									class="fa fa-check ms-2"></i></button>
						</div>


					</div>


					<input type="hidden" class="form-control" id="id" name="id">

					<br>
					<small>
						<div id="mensagem" role="status" aria-live="polite"></div>
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
// Tratamento de erros dos endpoints de Cargos, sem alterar o AJAX compartilhado.
$(document).ajaxError(function (_event, xhr, settings) {
    if (!settings.url || settings.url.indexOf('paginas/cargos/') === -1) return;
    var mensagem = (xhr.responseText || '').replace(/<[^>]*>/g, '').trim();
    if (!mensagem || mensagem.length > 250) mensagem = 'Não foi possível concluir a operação. Tente novamente.';
    if (settings.url.indexOf('/salvar.php') !== -1) {
        $('#mensagem').addClass('text-danger').text(mensagem);
        $('#btn_salvar').show();
        $('#btn_carregando').hide();
    } else if (settings.url.indexOf('/excluir.php') !== -1) {
        Swal.fire({title: 'Não foi possível excluir', text: mensagem, icon: 'warning'});
    } else {
        $('#listar').text(mensagem);
    }
});
</script>