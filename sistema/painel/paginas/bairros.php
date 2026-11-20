<?php
//verificar se ele tem a permissão de estar nessa página
if (@$bairros == 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}
$pag = 'bairros';
?>
<div class="breadcrumb-header justify-content-between align-items-center">
 <div class="left-content mt-2"><h4 class="mb-1"><i class="fa-solid fa-map-location-dot me-2"></i>Bairros e Taxas de Entrega</h4><p class="text-muted mb-0">Cadastre as regiões atendidas e defina a taxa de entrega de cada local.</p></div>
 <div class="mt-2 d-flex gap-2 flex-wrap">
 <button class="btn btn-primary" type="button" onclick="inserir()"><i class="fa-solid fa-plus me-1"></i>Nova localidade</button>
 <button class="btn btn-outline-danger" type="button" onclick="deletarSel()" id="btn-deletar" style="display:none"><i class="fa-solid fa-trash-can me-1"></i>Excluir selecionados</button>
 </div>
</div>
<div class="row row-sm"><div class="col-lg-12"><div class="card custom-card"><div class="card-body" id="listar"></div></div></div></div>
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

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="nome">Bairro ou localidade</label>
								<input type="text" class="form-control" id="nome" name="nome" placeholder="Ex.: Centro" maxlength="150" required>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<label for="valor">Taxa de entrega (R$)</label>
								<input type="text" class="form-control" id="valor" name="valor" placeholder="Ex.: 5,00" inputmode="decimal" required>
							</div>
						</div>

					</div>



					<input type="hidden" name="id" id="id">

					<br>
					<small>
						<div id="mensagem" role="status" aria-live="polite"></div>
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
<script>
$(document).ajaxError(function (_event, xhr, settings) {
 if (!settings.url || settings.url.indexOf('paginas/bairros/') === -1) return;
 var msg = (xhr.responseText || '').replace(/<[^>]*>/g, '').trim();
 if (!msg || msg.length > 250) msg = 'Não foi possível concluir a operação.';
 if (settings.url.indexOf('/salvar.php') !== -1) {
   $('#mensagem').addClass('text-danger').text(msg);
   $('#btn_salvar').show(); $('#btn_carregando').hide();
 } else if (settings.url.indexOf('/excluir.php') !== -1) {
   Swal.fire({title:'Operação não concluída',text:msg,icon:'warning'});
 } else { $('#listar').text(msg); }
});
</script>