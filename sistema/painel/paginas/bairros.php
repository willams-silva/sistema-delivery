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
<style id="bc-frete-bairro-theme">
#bc-frete-bairro{color:#4e2d20!important;background-color:#fffaf5!important;border:1px solid #d7b5a0!important;accent-color:#914a29;color-scheme:light}
#bc-frete-bairro:focus{border-color:#914a29!important;box-shadow:0 0 0 .2rem rgba(145,74,41,.16)!important;outline:none}
#bc-frete-bairro option{color:#4e2d20;background:#fffaf5}
#bc-frete-bairro option:checked{color:#fff;background:#914a29}
</style>
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




<div class="card mt-3" style="border:1px solid #e5d4c6;border-radius:14px"><div class="card-body">
<h5 style="color:#4e2d20"><i class="fa-solid fa-map me-2"></i>Zonas de entrega</h5>
<p class="text-muted">Agrupe ruas do mesmo bairro. A taxa específica por rua terá prioridade quando o cálculo for integrado.</p>
<form id="bc-zona-form" class="row g-2">
<input type="hidden" name="zona_id" value="0">
<div class="col-md-4"><label>Bairro<select name="bairro_id" class="form-control" required></select></label></div>
<div class="col-md-5"><label>Nome da zona<input name="zona_nome" class="form-control" maxlength="150" required></label></div>
<div class="col-md-3"><label>Frete (R$)<input name="zona_valor" class="form-control" inputmode="decimal" required></label></div>
<div class="col-12"><label for="bc-zona-ruas">Ruas da zona (uma por linha)</label><textarea id="bc-zona-ruas" name="zona_ruas" class="form-control" rows="4" placeholder="Rua A&#10;Rua B"></textarea></div>
<div class="col-12"><button type="submit" class="btn btn-primary">Salvar zona</button> <button type="button" id="bc-zona-cancelar" class="btn btn-outline-secondary">Limpar</button></div>
</form><div id="bc-zona-msg" role="status" class="mt-2"></div>
<div class="table-responsive mt-3"><table class="table"><thead><tr><th>Bairro</th><th>Zona</th><th>Frete</th><th>Ruas</th><th>Ações</th></tr></thead><tbody id="bc-zona-list"></tbody></table></div>
</div></div>
<style>#bc-zona-form .bc-zona-option{display:block;width:100%;text-align:left;padding:9px 12px;border:0;background:#fffaf5!important;color:#4e2d20!important}#bc-zona-form .bc-zona-option:hover,#bc-zona-form .bc-zona-option:focus,#bc-zona-form .bc-zona-option[aria-pressed=true]{background:#914a29!important;color:#fff!important}#bc-zona-form .select2-results__option--highlighted{background:#914a29!important;color:#fff!important}#bc-zona-form .select2-results__option[aria-selected=true]:not(.select2-results__option--highlighted),#bc-zona-form .select2-results__option--selected:not(.select2-results__option--highlighted){background:#ead5c2!important;color:#4e2d20!important}#bc-zona-form .select2-selection,#bc-zona-form .select2-dropdown{border-color:#d7b5a0!important}#bc-zona-form select option:checked{background:#914a29;color:white}#bc-zona-form label{display:block;width:100%}#bc-zona-form select{background:#fffaf5;color:#4e2d20;border-color:#d7b5a0}#bc-zona-form .form-control:focus{border-color:#914a29;box-shadow:0 0 0 .2rem rgba(145,74,41,.16)}</style>
<script>
(function(){
const f=document.getElementById('bc-zona-form'), e=f.elements, msg=$('#bc-zona-msg'), list=$('#bc-zona-list');
$(e.bairro_id).html($('#bc-frete-bairro').html());
const native=e.bairro_id;
native.style.display='none';native.required=false;
const wrap=$('<div>').css({position:'relative'}).insertAfter(native);
const trigger=$('<button type="button" aria-expanded="false">').addClass('form-control text-start').css({background:'#fffaf5',color:'#4e2d20',border:'1px solid #d7b5a0'}).appendTo(wrap);
const menu=$('<div>').css({position:'absolute',top:'100%',left:0,right:0,zIndex:1050,background:'#fffaf5',border:'1px solid #d7b5a0',borderRadius:'8px',boxShadow:'0 5px 15px #0002',maxHeight:'240px',overflowY:'auto'}).hide().appendTo(wrap);
function syncZona(){trigger.text(native.options[native.selectedIndex]?.text || 'Selecione').prop('disabled',native.disabled);menu.empty();Array.from(native.options).forEach(o=>{const b=$('<button type="button">').addClass('bc-zona-option').text(o.text).attr('aria-pressed',String(o.selected)).appendTo(menu);b.on('click',()=>{native.value=o.value;$(native).trigger('change');syncZona();closeZona();trigger.focus();});});}
function closeZona(){menu.hide();trigger.attr('aria-expanded','false');}
trigger.on('click',()=>{menu.toggle();trigger.attr('aria-expanded',String(menu.is(':visible')));});
wrap.on('keydown',ev=>{if(ev.key==='Escape'){closeZona();trigger.focus();}});
$(document).on('click',ev=>{if(!wrap[0].contains(ev.target))closeZona();});
$(native).on('change',syncZona);syncZona();


function req(data){return $.ajax({url:'paginas/bairros/frete_zonas.php',method:'POST',dataType:'json',data:Object.assign({csrf:<?= json_encode($_SESSION['bc_frete_csrf']) ?>},data)});}
function fail(x){msg.text(x.responseJSON?.erro || 'Não foi possível concluir a operação.');}
function reset(){f.reset();e.zona_id.value='0';e.bairro_id.disabled=false;syncZona();closeZona();}
function load(){req({acao:'listar'}).done(rows=>{list.empty();if(!rows.length)list.append($('<tr>').append($('<td colspan="5">').text('Nenhuma zona cadastrada.')));rows.forEach(r=>{const tr=$('<tr>');[r.bairro,r.nome,Number(r.valor).toLocaleString('pt-BR',{style:'currency',currency:'BRL'}),r.ruas.join(', ')].forEach(v=>tr.append($('<td>').text(v)));const actions=$('<td>');actions.append($('<button type="button" class="btn btn-outline-secondary btn-sm me-1">').text('Editar').on('click',()=>{e.zona_id.value=r.id;e.bairro_id.value=r.bairro_id;e.bairro_id.disabled=true;syncZona();closeZona();e.zona_nome.value=r.nome;e.zona_valor.value=r.valor;e.zona_ruas.value=r.ruas.join('\n');f.scrollIntoView({block:'center'});}));actions.append($('<button type="button" class="btn btn-outline-danger btn-sm">').text('Excluir').on('click',()=>{if(confirm('Excluir zona e vínculos de ruas?'))req({acao:'excluir',id:r.id}).done(()=>{reset();load();}).fail(fail);}));tr.append(actions);list.append(tr);});}).fail(fail);}
$(f).on('submit',function(ev){ev.preventDefault();if(!e.bairro_id.value){msg.text('Selecione um bairro.');trigger.focus();return;}const btn=$(f).find('[type=submit]').prop('disabled',true);req({acao:'salvar',id:e.zona_id.value,bairro_id:e.bairro_id.value,nome:e.zona_nome.value,valor:e.zona_valor.value,ruas:e.zona_ruas.value}).done(()=>{msg.text('Zona salva com sucesso.');reset();load();}).fail(fail).always(()=>btn.prop('disabled',false));});
$('#bc-zona-cancelar').on('click',reset);load();
})();
</script>

<div class="card mb-3" style="border:1px solid #e5d4c6;border-radius:14px">
<div class="card-body">
<h4 style="color:#4e2d20"><i class="fa-solid fa-calculator me-2"></i>Frete sob consulta</h4>
<p style="color:#795038">Permita solicitar uma cotação quando não houver taxa automática válida.</p>
<label><input type="checkbox" id="bc-consulta-ativo" disabled> Ativar frete sob consulta</label>
<button type="button" id="bc-consulta-refresh" class="btn btn-sm ms-2" style="background:#914a29;color:white"><i class="fa-solid fa-rotate"></i> Atualizar</button>
<div id="bc-consulta-msg" role="status" class="mt-2"></div>
<div id="bc-consulta-list" class="mt-3"></div>
<h5 class="mt-3" style="color:#4e2d20"><i class="fa-solid fa-map-pin"></i> Fretes memorizados</h5>
<div id="bc-frete-memorias"></div>
</div></div>
<script>
$(function(){
const ativo=$('#bc-consulta-ativo'), lista=$('#bc-consulta-list'), msg=$('#bc-consulta-msg');
function req(data){return $.ajax({url:'paginas/bairros/frete_consulta.php',method:'POST',dataType:'json',data:Object.assign({csrf:<?= json_encode($_SESSION['bc_frete_csrf']) ?>},data)});}
function endereco(v){try{const e=JSON.parse(v);return [e.rua,e.numero,e.bairro,e.cidade,e.cep].filter(Boolean).join(', ');}catch(e){return v;}}
function memorias(){const el=$('#bc-frete-memorias');req({acao:'memorias'}).done(rows=>{el.empty();if(!rows.length)el.text('Nenhum endereço memorizado.');rows.forEach(v=>{const card=$('<div>').addClass('border rounded p-2 mb-2').appendTo(el);$('<span>').text(endereco(v.endereco)+' — R$ '+Number(v.valor).toFixed(2).replace('.',',')).appendTo(card);$('<button type="button">').addClass('btn btn-outline-danger btn-sm ms-2').text('Excluir').appendTo(card).on('click',()=>{if(confirm('Remover taxa memorizada?'))req({acao:'excluir_memoria',hash:v.endereco_hash}).done(memorias).fail(()=>msg.text('Não foi possível excluir.'));});});}).fail(()=>el.text('Memorização indisponível. Verifique a atualização do banco.'));}
let busy=false;
function load(){memorias();if(busy||lista.find('input:focus').length)return;busy=true;req({acao:'listar'}).done(function(r){
ativo.prop('checked',Number(r.ativo)===1).prop('disabled',false);lista.empty();
if(!r.consultas.length){lista.text('Nenhuma solicitação em aberto.');return;}
r.consultas.forEach(function(v){
const card=$('<div>').addClass('border rounded p-3 mb-2').appendTo(lista);
$('<strong>').text('Consulta #'+v.id+' — '+(v.status==='pendente'?'Aguardando cotação':v.status==='aceito'?'Aceito':'Cotado')).appendTo(card);
$('<p>').text(endereco(v.endereco)).appendTo(card);
if(v.status==='aceito'){$('<span>').text('R$ '+Number(v.valor).toFixed(2).replace('.',',')).appendTo(card);$('<button type="button">').addClass('btn btn-sm ms-2').css({background:'#914a29',color:'white'}).text('Memorizar frete').appendTo(card).on('click',()=>{if(confirm('Usar este valor em futuras entregas neste endereço?'))req({acao:'memorizar',id:v.id}).done(()=>{msg.text('Frete memorizado.');memorias();}).fail(()=>msg.text('Não foi possível memorizar.'));});return;}
if(v.status==='cotado'){$('<span>').text('R$ '+Number(v.valor).toFixed(2).replace('.',',')).appendTo(card);return;}
const input=$('<input type="text" inputmode="decimal" placeholder="Valor do frete (R$)" aria-label="Valor do frete">').addClass('form-control mb-2').appendTo(card);
const btn=$('<button type="button">').addClass('btn btn-sm').css({background:'#914a29',color:'white'}).text('Enviar cotação').appendTo(card);
btn.on('click',function(){btn.prop('disabled',true);req({acao:'cotar',id:v.id,valor:input.val()}).done(function(){msg.text('Cotação enviada.');load();}).fail(function(){msg.text('Não foi possível enviar a cotação.');btn.prop('disabled',false);});});
});
}).fail(function(){msg.text('Não foi possível carregar as consultas.');}).always(function(){busy=false;});}
ativo.on('change',function(){ativo.prop('disabled',true);req({acao:'config',ativo:ativo.prop('checked')?'1':'0'}).done(function(){msg.text('Configuração salva.');}).fail(function(){msg.text('Erro ao salvar configuração.');}).always(load);});
$('#bc-consulta-refresh').on('click',load);load();
});
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