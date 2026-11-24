<?php 

if(@$acessos == 'ocultar'){
	echo "<script>window.location='index'</script>";
    exit();
}

$pag = 'acessos';
 ?>

<div class="card mb-3" style="margin-top:16px;border:1px solid #e2e8f0;border-radius:14px"><div class="card-body d-flex align-items-center flex-wrap gap-3"><h2 class="mb-0" style="font-size:21px;font-weight:600;color:#4e2d20"><i class="fa-solid fa-shield-halved me-2"></i>Acessos e Permissões</h2><span style="font-size:12px;color:#795038">Gerencie com cuidado: essas definições podem afetar o acesso de outros usuários.</span></div></div>
<div class="breadcrumb-header justify-content-between">
    
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
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="titulo_inserir"></span></h4>
				 <button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			<form id="form">
			<div class="modal-body">
				

					<div class="row">
						<div class="col-md-6 mb-3">						
								<label for="nome" class="form-label fw-semibold">Nome do acesso <span class="text-danger">*</span></label>
								<input type="text" class="form-control" id="nome" name="nome" placeholder="Ex.: Pedidos locais" maxlength="100" required><div class="form-text">Nome exibido na administração das permissões.</div>	
						</div>

						<div class="col-md-6 mb-3">						
								<label for="chave" class="form-label fw-semibold">Chave técnica <span class="text-danger">*</span></label>
								<input type="text" class="form-control" id="chave" name="chave" placeholder="Ex.: pedidos" maxlength="100" pattern="[A-Za-z0-9_-]+" title="Use letras, números, hífen ou sublinhado" required><div class="form-text">Identificador usado internamente. Não pode ser alterado depois do cadastro.</div>	
						</div>
					</div>

					<div class="row">
						<div class="col-md-6 mb-3">						
								<label for="grupo" class="form-label fw-semibold">Grupo de acesso</label>
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

						<div class="col-md-6 mb-3">	
							<label for="pagina" class="form-label fw-semibold">Exibir como página no menu?</label>
								<select class="form-select" name="pagina" id="pagina">
								<option value="Sim">Sim</option>
								<option value="Não">Não</option>
								</select>	
						</div>

						<div class="col-12 d-flex justify-content-end gap-2 mt-2">							
								<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button id="btn_salvar" type="submit" class="btn btn-primary"><i class="fa fa-check me-1"></i>Salvar acesso</button>					
						</div>

						
					</div>

			
					<input type="hidden" class="form-control" id="id" name="id">					

				<br>
				<div id="mensagem" class="small text-center" role="status"></div>
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



