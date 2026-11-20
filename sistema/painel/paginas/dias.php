<?php
//verificar se ele tem a permissão de estar nessa página
if (@$dias == 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}
$pag = 'dias';
?>
<div class="breadcrumb-header justify-content-between align-items-center">
 <div class="left-content mt-2"><h4 class="mb-1"><i class="fa-solid fa-calendar-xmark me-2"></i>Dias de Fechamento</h4><p class="text-muted mb-0">Configure os dias da semana em que o estabelecimento não funciona.</p></div>
 <div class="mt-2 d-flex gap-2 flex-wrap">
 <button class="btn btn-primary" type="button" onclick="inserir()"><i class="fa-solid fa-plus me-1"></i>Adicionar dia fechado</button>
 <button class="btn btn-outline-danger" type="button" onclick="deletarSel()" id="btn-deletar" style="display:none"><i class="fa-solid fa-trash-can me-1"></i>Excluir selecionados</button>
 </div>
</div>
<div class="row row-sm"><div class="col-md-4 mb-3"><div class="card custom-card"><div class="card-body"><small class="text-muted">Dias fechados na semana</small><h3 class="mb-0" id="bc-total-dias">—</h3></div></div></div></div>
<div class="row row-sm"><div class="col-lg-12"><div class="card custom-card"><div class="card-body" id="listar"></div></div></div></div>
<div class="row row-sm"><div class="col-lg-12"><div class="card custom-card"><div class="card-body">
 <h5><i class="fa-solid fa-calendar-day me-2"></i>Feriados e fechamentos excepcionais</h5>
 <p class="text-muted">Cadastre datas específicas sem alterar os dias semanais. As datas cadastradas são consideradas no status visual do cardápio público. O sistema também verifica a data ao receber pedidos online; valide o funcionamento antes de usar em produção.</p>
 <form id="bc-excecoes-form" class="row g-2 align-items-end">
  <div class="col-md-4"><label for="bc-excecao-data" class="form-label">Data</label><input type="date" id="bc-excecao-data" class="form-control" required></div>
  <div class="col-md-5"><label for="bc-excecao-motivo" class="form-label">Motivo (opcional)</label><input type="text" id="bc-excecao-motivo" maxlength="150" class="form-control" placeholder="Ex.: Feriado municipal"></div>
  <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-plus me-1"></i>Salvar data</button></div>
 </form>
 <div id="bc-excecoes-msg" class="small mt-2" role="status" aria-live="polite"></div>
 <div class="table-responsive mt-3"><table class="table table-hover"><thead><tr><th>Data</th><th>Motivo</th><th>Ação</th></tr></thead><tbody id="bc-excecoes-lista"><tr><td colspan="3" class="text-muted">Carregando datas...</td></tr></tbody></table></div>
 </div></div></div></div>
<div class="row row-sm"><div class="col-lg-12"><div class="card custom-card"><div class="card-body">
<h5><i class="fa-solid fa-clock me-2"></i>Horários especiais por data</h5>
<p class="text-muted">Configure uma abertura e fechamento diferentes em uma data específica. O cadastro não altera o horário normal. O cardápio e a finalização online consideram os horários especiais; valide o comportamento antes de usar em produção. Horários especiais podem atravessar a meia-noite (ex.: 20h às 3h do dia seguinte). O fechamento integral na data seguinte interrompe o atendimento à meia-noite.</p>
<form id="bc-horario-form" class="row g-2 align-items-end">
<div class="col-md-3"><label for="bc-horario-data" class="form-label">Data</label><input type="date" id="bc-horario-data" class="form-control" required></div>
<div class="col-md-2"><label for="bc-horario-inicio" class="form-label">Abertura</label><input type="time" id="bc-horario-inicio" class="form-control" required></div>
<div class="col-md-2"><label for="bc-horario-fim" class="form-label">Fechamento</label><input type="time" id="bc-horario-fim" class="form-control" required></div>
<div class="col-md-3"><label for="bc-horario-descricao" class="form-label">Motivo (opcional)</label><input type="text" id="bc-horario-descricao" maxlength="150" class="form-control" placeholder="Ex.: Véspera de feriado"></div>
<div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Salvar horário</button></div>
</form>
<div id="bc-horario-msg" class="small mt-2" role="status" aria-live="polite"></div>
<div class="table-responsive mt-3"><table class="table table-hover"><thead><tr><th>Data</th><th>Horário</th><th>Motivo</th><th>Ação</th></tr></thead><tbody id="bc-horario-lista"><tr><td colspan="4" class="text-muted">Carregando...</td></tr></tbody></table></div>
</div></div></div></div>
<div class="row row-sm"><div class="col-lg-12"><div class="card custom-card"><div class="card-body">
<h5><i class="fa-solid fa-calendar-check me-2"></i>Simulador de funcionamento</h5>
<p class="text-muted">Consulte o status do delivery para uma data e horário, com base nas regras atualmente salvas. A simulação não modifica configurações. Pausas e fechamentos manuais futuros não podem ser previstos.</p>
<form id="bc-previsao-form" class="row g-2 align-items-end">
<div class="col-md-4"><label for="bc-previsao-data" class="form-label">Data</label><input type="date" id="bc-previsao-data" class="form-control" required></div>
<div class="col-md-4"><label for="bc-previsao-hora" class="form-label">Horário</label><input type="time" id="bc-previsao-hora" class="form-control" required></div>
<div class="col-md-4"><button class="btn btn-primary w-100" type="submit" id="bc-previsao-btn"><i class="fa-solid fa-magnifying-glass me-1"></i>Consultar funcionamento</button></div>
</form>
<div id="bc-previsao-resultado" class="mt-3" role="status" aria-live="polite"></div>
</div></div></div></div>
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

					<div class="row ">
						<div class="col-md-9">
							<div class="form-group">
								<label for="dia">Dia da semana sem funcionamento</label>
								<select class="form-select" name="dia" id="dia" style="width:100%;">
									<option value="Segunda-Feira">Segunda-Feira</option>
									<option value="Terça-Feira">Terça-Feira</option>
									<option value="Quarta-Feira">Quarta-Feira</option>
									<option value="Quinta-Feira">Quinta-Feira</option>
									<option value="Sexta-Feira">Sexta-Feira</option>
									<option value="Sábado">Sábado</option>
									<option value="Domingo">Domingo</option>

								</select>
							</div>
						</div>


						<div class="col-md-3" style="margin-top: 24px">
							<div class="form-group">
								<button type="submit" class="btn btn-primary" id="btn_salvar">Salvar<i class="fa fa-check ms-2"></i></button>
								<button class="btn btn-primary" type="button" id="btn_carregando" style="display: none">
									<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
								</button>
							</div>
						</div>

					</div>



					<input type="hidden" name="id" id="id">

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
$(document).ajaxError(function (_event, xhr, settings) {
 if (!settings.url || settings.url.indexOf('paginas/dias/') === -1) return;
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
<script>
(function(){
 var endpoint = 'paginas/dias/fechamentos_api.php';
 var $msg = $('#bc-excecoes-msg'), $lista = $('#bc-excecoes-lista');
 function mensagem(t, erro){ $msg.toggleClass('text-danger',!!erro).toggleClass('text-success',!erro).text(t); }
 function carregar(){
  $.post(endpoint,{acao:'listar'},function(r){
   $lista.empty();
   if(!r.registros || !r.registros.length){$lista.append($('<tr>').append($('<td colspan="3" class="text-muted">').text('Nenhuma data cadastrada.')));return;}
   r.registros.forEach(function(x){
    var tr=$('<tr>');tr.append($('<td>').text(x.data_fechamento.split('-').reverse().join('/')));
    tr.append($('<td>').text(x.motivo||'—'));
    var b=$('<button type="button" class="btn btn-sm btn-outline-danger">').text('Excluir');
    b.on('click',function(){
     if(!confirm('Excluir esta data?'))return;
     $.post(endpoint,{acao:'excluir',id:x.id},function(){mensagem('Data excluída.');carregar();},'json').fail(falha);
    });
    tr.append($('<td>').append(b));$lista.append(tr);
   });
  },'json').fail(function(xhr){$lista.empty().append($('<tr>').append($('<td colspan="3" class="text-muted">').text(xhr.responseJSON?.erro||'Cadastro indisponível. Execute o SQL de instalação.')));});
 }
 function falha(xhr){mensagem(xhr.responseJSON?.erro||'Não foi possível concluir a operação.',true);}
 $('#bc-excecoes-form').on('submit',function(e){
  e.preventDefault();
  $.post(endpoint,{acao:'salvar',data_fechamento:$('#bc-excecao-data').val(),motivo:$('#bc-excecao-motivo').val()},function(){
   mensagem('Data cadastrada. Confira o status no cardápio e o bloqueio de novos pedidos na data selecionada.');
   $('#bc-excecao-motivo').val('');carregar();
  },'json').fail(falha);
 });
 carregar();
})();
</script>

<script>
(function(){
var endpoint='paginas/dias/horarios_especiais_api.php';
var lista=$('#bc-horario-lista'), msg=$('#bc-horario-msg');
function aviso(t,erro){msg.toggleClass('text-danger',!!erro).toggleClass('text-success',!erro).text(t);}
function falha(xhr){aviso(xhr.responseJSON && xhr.responseJSON.erro || 'Operação não concluída.',true);}
function carregar(){
 $.post(endpoint,{acao:'listar'},function(r){
 lista.empty();
 if(!r.registros || !r.registros.length){lista.append($('<tr>').append($('<td colspan="4" class="text-muted">').text('Nenhum horário especial cadastrado.')));return;}
 r.registros.forEach(function(x){
 var tr=$('<tr>');tr.append($('<td>').text(x.data_especial.split('-').reverse().join('/')));
 tr.append($('<td>').text(x.hora_inicio+' às '+x.hora_fim+(x.hora_fim < x.hora_inicio ? ' (dia seguinte)' : '')));
 tr.append($('<td>').text(x.descricao||'—'));
 var b=$('<button type="button" class="btn btn-sm btn-outline-danger">').text('Excluir');
 b.on('click',function(){if(!confirm('Excluir este horário especial?'))return;$.post(endpoint,{acao:'excluir',id:x.id},function(){aviso('Horário excluído.');carregar();},'json').fail(falha);});
 tr.append($('<td>').append(b));lista.append(tr);
 });
 },'json').fail(function(xhr){lista.empty().append($('<tr>').append($('<td colspan="4" class="text-muted">').text(xhr.responseJSON && xhr.responseJSON.erro || 'Cadastro indisponível. Execute o SQL de horários especiais.')));});
}
$('#bc-horario-form').on('submit',function(e){
 e.preventDefault();
 $.post(endpoint,{acao:'salvar',data_especial:$('#bc-horario-data').val(),hora_inicio:$('#bc-horario-inicio').val(),hora_fim:$('#bc-horario-fim').val(),descricao:$('#bc-horario-descricao').val()},function(){
 aviso('Horário salvo. Confira o status no cardápio e a finalização de pedidos na data cadastrada.');
 carregar();
 },'json').fail(falha);
});
carregar();
})();
</script>

<script>
(function(){
 var form=$('#bc-previsao-form'), result=$('#bc-previsao-resultado'), btn=$('#bc-previsao-btn');
 var agora=new Date();
 var dataLocal=agora.getFullYear()+'-'+String(agora.getMonth()+1).padStart(2,'0')+'-'+String(agora.getDate()).padStart(2,'0');
 $('#bc-previsao-data').val(dataLocal);
 $('#bc-previsao-hora').val(String(agora.getHours()).padStart(2,'0')+':'+String(agora.getMinutes()).padStart(2,'0'));
 form.on('submit',function(e){
  e.preventDefault();
  result.empty().append($('<span class="text-muted">').text('Consultando regras...'));
  btn.prop('disabled',true);
  $.post('paginas/dias/previsao_funcionamento.php',{
   data:$('#bc-previsao-data').val(),hora:$('#bc-previsao-hora').val()
  },function(r){
   result.empty();
   var cor=r.status==='ABERTO'?'text-success':r.status==='PAUSADO'?'text-warning':'text-danger';
   result.append($('<div class="fw-bold">').addClass(cor).text('Resultado: '+r.status));
   result.append($('<div>').text(r.motivo));
   result.append($('<small class="text-muted">').text(r.observacao));
  },'json').fail(function(xhr){
   result.empty().append($('<div class="text-danger">').text(xhr.responseJSON && xhr.responseJSON.erro || 'Não foi possível consultar o funcionamento.'));
  }).always(function(){btn.prop('disabled',false);});
 });
})();
</script>
