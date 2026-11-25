<?php
//verificar se ele tem a permissão de estar nessa página
if (@$bairros == 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}
$pag = 'bairros';
?>
<div class="card mb-3" style="margin-top:16px;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 3px 12px rgba(65,33,22,.04)"><div class="card-body d-flex align-items-center flex-wrap gap-3"><h2 class="mb-0" style="font-size:21px;font-weight:600;color:#4e2d20"><i class="fa-solid fa-map-location-dot me-2"></i>Bairros e Taxas de Entrega</h2><span style="font-size:12px;color:#795038">Cadastre as regiões atendidas e defina a taxa de entrega de cada local.</span></div></div>
<div class="breadcrumb-header justify-content-between align-items-center">
 
 <div class="mt-2 d-flex gap-2 flex-wrap">
 <button class="btn btn-primary" type="button" onclick="inserir()"><i class="fa-solid fa-plus me-1"></i>Nova localidade</button>
 <button class="btn btn-outline-danger" type="button" onclick="deletarSel()" id="btn-deletar" style="display:none"><i class="fa-solid fa-trash-can me-1"></i>Excluir selecionados</button>
 </div>
</div>
<div class="row row-sm"><div class="col-md-4 mb-3"><div class="card custom-card"><div class="card-body"><small class="text-muted">Localidades cadastradas</small><h3 class="mb-0" id="bc-total-bairros">—</h3></div></div></div></div>
<div class="row row-sm"><div class="col-lg-12"><div class="card custom-card"><div class="card-body" id="listar"></div></div></div></div>
<input type="hidden" id="ids">
<?php
if (empty($_SESSION['bc_frete_csrf'])) $_SESSION['bc_frete_csrf'] = bin2hex(random_bytes(32));
$bc_bairros = $pdo->prepare("SELECT id,nome FROM bairros WHERE empresa=? ORDER BY nome");
$bc_bairros->execute([(int)$_SESSION['empresa']]);
?>
<div class="card mt-3" style="border:1px solid #e5d4c6;border-radius:14px">
<div class="card-body">
<h5 style="color:#4e2d20"><i class="fa-solid fa-road me-2"></i>Taxas específicas por rua</h5>
<p class="text-muted">Cadastre uma exceção para uma rua do bairro. Para alterar o valor, salve novamente a mesma rua.</p>
<form id="bc-frete-form" class="row g-2 align-items-end">
<div class="col-md-4"><label for="bc-frete-bairro">Bairro</label><select id="bc-frete-bairro" class="form-control" name="bairro_id" required><option value="">Selecione</option><?php foreach ($bc_bairros as $b): ?><option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['nome'],ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label for="bc-frete-rua">Rua</label><input id="bc-frete-rua" name="rua" class="form-control" maxlength="150" required></div>
<div class="col-md-2"><label for="bc-frete-valor">Frete (R$)</label><input id="bc-frete-valor" name="valor" class="form-control" inputmode="decimal" required></div>
<div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Salvar regra</button></div>
</form>
<div id="bc-frete-msg" role="status" class="mt-2"></div>
<div class="table-responsive mt-3"><table class="table"><thead><tr><th>Bairro</th><th>Rua</th><th>Frete</th><th>Ações</th></tr></thead><tbody id="bc-frete-list"></tbody></table></div>
</div></div>
<script>
(function(){
const url='paginas/bairros/frete_ruas.php', csrf=<?= json_encode($_SESSION['bc_frete_csrf']) ?>;
const msg=$('#bc-frete-msg'), list=$('#bc-frete-list');
function request(data){return $.ajax({url:url,method:'POST',dataType:'json',data:Object.assign({csrf:csrf},data)});}
function error(){msg.text('Não foi possível concluir a operação. Verifique a sessão e a tabela de fretes.');}
function load(){request({acao:'listar'}).done(function(rows){list.empty();if(!rows.length){list.append($('<tr>').append($('<td colspan="4">').text('Nenhuma regra cadastrada.')));return;}rows.forEach(function(r){const tr=$('<tr>');[r.bairro,r.rua,Number(r.valor).toLocaleString('pt-BR',{style:'currency',currency:'BRL'})].forEach(v=>tr.append($('<td>').text(v)));tr.append($('<td>').append($('<button type="button" class="btn btn-outline-danger btn-sm">').text('Excluir').on('click',function(){if(confirm('Excluir esta regra?'))request({acao:'excluir',id:r.id}).done(load).fail(error);})));list.append(tr);});}).fail(error);}
$('#bc-frete-form').on('submit',function(e){e.preventDefault();const form=this, btn=$(form).find('button');btn.prop('disabled',true);request({acao:'salvar',bairro_id:form.bairro_id.value,rua:form.rua.value,valor:form.valor.value}).done(function(){msg.text('Regra salva com sucesso.');form.reset();load();}).fail(error).always(()=>btn.prop('disabled',false));});
load();
})();
</script>



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