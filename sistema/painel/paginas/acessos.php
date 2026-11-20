<?php 

if(@$acessos == 'ocultar'){
	echo "<script>window.location='index'</script>";
    exit();
}

$pag = 'acessos';
 ?>

<div class="breadcrumb-header justify-content-between">
    <div class="left-content mt-2"><h4><i class="fa-solid fa-shield-halved me-2"></i>Acessos e Permissões</h4><p class="text-muted mb-2">Gerencie com cuidado: essas definições podem afetar o acesso de outros usuários.</p></div>
    <div class="left-content mt-2">
<a class="btn ripple btn-primary text-white" onclick="inserir()" type="button"><i class="fe fe-plus me-1"></i> Adicionar <?php echo ucfirst($pag); ?></a>






<button type="button" class="btn btn-outline-danger" id="btn-deletar" style="display:none" onclick="bcExcluirAcessosSelecionados()"><i class="fe fe-trash-2 me-1"></i>Excluir selecionados</button>

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
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_inserir"></span></h4>
				 <button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			<form id="form">
			<div class="modal-body">
				

					<div class="row">
						<div class="col-md-6 mb-2 col-6">						
								<label>Nome</label>
								<input type="text" class="form-control" id="nome" name="nome" placeholder="Nome do Acesso" maxlength="100" required>	
						</div>

						<div class="col-md-6 col-6">						
								<label>Chave</label>
								<input type="text" class="form-control" id="chave" name="chave" placeholder="Ex.: pedidos" maxlength="100" pattern="[A-Za-z0-9_-]+" title="Use letras, números, hífen ou sublinhado" required>	
						</div>
					</div>

					<div class="row">
						<div class="col-md-5 mb-2 col-6">						
								<label>Grupo</label>
								<select class="form-select" name="grupo" id="grupo">
								<option value="0">Sem Grupo</option>
								<?php 
									$query = $pdo->query("SELECT * from grupo_acessos order by id asc");
									$res = $query->fetchAll(PDO::FETCH_ASSOC);
									$linhas = @count($res);
									if($linhas > 0){
									for($i=0; $i<$linhas; $i++){
								 ?>
								  <option value="<?php echo $res[$i]['id'] ?>"><?php echo htmlspecialchars((string)$res[$i]['nome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>

								<?php } } ?>
									
								</select>	
						</div>

						<div class="col-md-4 mb-2 col-6">	
							<label>Página</label>
								<select class="form-select" name="pagina" id="pagina">
								<option value="Sim">Sim</option>
								<option value="Não">Não</option>
								</select>	
						</div>

						<div class="col-md-3" style="margin-top: 22px">							
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
function bcExcluirAcessosSelecionados(){
 var ids=($('#ids').val()||'').split('-').filter(function(v){return /^[1-9][0-9]*$/.test(v);});
 ids=Array.from(new Set(ids));
 if(!ids.length){Swal.fire('Nenhum acesso selecionado','','info');return;}
 if(ids.length>100){Swal.fire('Limite excedido','Selecione no máximo 100 acessos por vez.','warning');return;}
 Swal.fire({title:'Excluir '+ids.length+' acesso(s)?',text:'Todos serão verificados antes da exclusão. Se houver algum vínculo com usuários ou cargos, nenhum será excluído.',icon:'warning',showCancelButton:true,confirmButtonText:'Excluir selecionados',cancelButtonText:'Cancelar',confirmButtonColor:'#dc3545'}).then(function(result){
  if(!result.isConfirmed)return;
  $('#btn-deletar').prop('disabled',true);
  $.ajax({url:'paginas/acessos/excluir_lote.php',method:'POST',dataType:'json',data:{ids:ids}})
  .done(function(data){$('#ids').val('');$('#btn-deletar').hide();listar();Swal.fire('Exclusão concluída',data.excluidos+' acesso(s) excluído(s).','success');})
  .fail(function(xhr){Swal.fire('Exclusão bloqueada',xhr.responseJSON&&xhr.responseJSON.erro||'Não foi possível excluir os acessos.','warning');})
  .always(function(){$('#btn-deletar').prop('disabled',false);});
 });
}
$(document).ajaxError(function (_event, xhr, settings) {
    if (!settings.url || settings.url.indexOf('paginas/acessos/') === -1 || settings.url.indexOf('/excluir_lote.php') !== -1) return;
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



