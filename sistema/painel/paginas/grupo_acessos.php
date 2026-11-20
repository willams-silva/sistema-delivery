<?php 
$pag = 'grupo_acessos';

if(@$grupo_acessos == 'ocultar'){
	echo "<script>window.location='index'</script>";
    exit();
}

 ?>

 <div class="breadcrumb-header justify-content-between">
    <div class="left-content mt-2"><h4><i class="fa-solid fa-shield-halved me-2"></i>Grupos de Acessos</h4><p class="text-muted mb-2">Gerencie com cuidado: essas definições podem afetar o acesso de outros usuários.</p></div>
    <div class="left-content mt-2">
<a class="btn ripple btn-primary text-white" onclick="inserir()" type="button"><i class="fe fe-plus me-1"></i> Adicionar Grupos</a>



<div class="dropdown" style="display: inline-block;">                      
                        <a href="#" aria-expanded="false" aria-haspopup="true" data-bs-toggle="dropdown" class="btn btn-danger dropdown" id="btn-deletar" style="display:none"><i class="fe fe-trash-2"></i> Deletar</a>
                        <div  class="dropdown-menu tx-13">
                        <div style="width: 240px; padding:15px 5px 0 10px;" class="dropdown-item-text">
                        <p>Excluir Selecionados? <a href="#" onclick="deletarSel()"><span class="text-danger">Sim</span></a></p>
                        </div>
                        </div>
                        </div>

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

<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog ">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_inserir"></span></h4>
				 <button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			<form id="form">
			<div class="modal-body">
				
				

					<div class="row">
						<div class="col-md-8 mb-2">							
								<label>Nome</label>
								<input type="text" class="form-control" id="nome" name="nome" placeholder="Nome do Grupo" maxlength="100" required>							
						</div>

						<div class="col-md-4 mb-2" style="margin-top: 25px">							
								<button id="btn_salvar" type="submit" class="btn btn-primary">Salvar<i class="fa fa-check ms-2"></i></button>					
						</div>

						
					</div>

			
					<input type="hidden" class="form-control" id="id" name="id">					

				<br>
				<small><div id="mensagem" align="center"></div></small>
			</div>
			
			</form>
		</div>
	</div>
</div>





<script type="text/javascript">var pag = "<?=$pag?>"</script>
<script src="js/ajax.js"></script>
<script>
$(document).ajaxError(function (_event, xhr, settings) {
    if (!settings.url || settings.url.indexOf('paginas/grupo_acessos/') === -1) return;
    var msg = (xhr.responseText || '').replace(/<[^>]*>/g, '').trim();
    if (!msg || msg.length > 250) msg = 'Operação não concluída. Verifique suas permissões.';
    if (settings.url.indexOf('/salvar.php') !== -1) {
        $('#mensagem').addClass('text-danger').text(msg);
        $('#btn_salvar').show();
        $('#btn_carregando').hide();
    } else if (settings.url.indexOf('/excluir.php') !== -1) {
        Swal.fire({title:'Operação bloqueada',text:msg,icon:'warning'});
    } else { $('#listar').text(msg); }
});
</script>



