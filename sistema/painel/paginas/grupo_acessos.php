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



<button type="button" class="btn btn-outline-danger" id="btn-deletar" style="display:none" onclick="bcExcluirGruposSelecionados()"><i class="fa-solid fa-trash-can me-1"></i>Excluir selecionados</button>

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




<script>
function bcExcluirGruposSelecionados(){
 var selecionados=$('#bc-grupos-tabela .bc-grupo-check:checked').map(function(){return this.value;}).get();
 if(!selecionados.length)return;
 Swal.fire({icon:'warning',title:'Excluir '+selecionados.length+' grupo(s)?',text:'A operação será cancelada se qualquer grupo tiver acessos vinculados.',showCancelButton:true,confirmButtonText:'Excluir grupos',cancelButtonText:'Cancelar',confirmButtonColor:'#dc3545'}).then(function(res){
  if(!res.isConfirmed)return;
  var botao=$('#btn-deletar');botao.prop('disabled',true);
  $.ajax({url:'paginas/grupo_acessos/excluir_lote.php',method:'POST',data:{ids:selecionados},dataType:'json'})
   .done(function(r){Swal.fire({icon:'success',title:r.excluidos+' grupo(s) excluído(s)',timer:1600,showConfirmButton:false});$('#ids').val('');botao.hide();listar();})
   .fail(function(xhr){Swal.fire({icon:'error',title:'Exclusão não realizada',text:xhr.responseJSON&&xhr.responseJSON.erro||'Não foi possível concluir a operação.'});})
   .always(function(){botao.prop('disabled',false);});
 });
}
</script>
