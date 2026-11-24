<?php
@session_start();
require_once("verificar.php");
require_once("../conexao.php");

if (@$pedidos_outros == 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}

$query2 = $pdo->query("SELECT * FROM usuarios where id = '$id_usuario'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if (@count($res2) > 0) {
	$nome_usuario = $res2[0]['nivel'];
}



//verificar se o caixa está aberto
$query = $pdo->query("SELECT * from caixas where operador = '$id_usuario' and data_fechamento is null");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
} else {
	if ($abertura_caixa == 'Sim' and $nome_usuario != 'Administrador') {
		echo '<script>alert("Não possui caixa Aberto, abra o caixa!")</script>';
		echo '<script>window.location="caixas"</script>';
	}
}

$pag = 'pedidos';

$segundos = $tempo_atualizar * 1000;

?>

<style>
html body .card.bc-local-heading{margin-top:16px!important;border:1px solid #e2e8f0!important;border-radius:14px!important;box-shadow:0 3px 12px rgba(65,33,22,.04)!important}
</style>
<div class="card mb-3 bc-local-heading">
 <div class="card-body d-flex align-items-center flex-wrap gap-3">
  <h2 class="mb-0" style="font-size:21px;font-weight:600;color:#4e2d20"><i class="fa-solid fa-location-dot me-2" aria-hidden="true"></i>Outros Locais</h2>
  <span style="font-size:12px;color:#795038">Acompanhe e organize os pedidos destinados aos demais setores do estabelecimento.</span>
 </div>
</div>


<div class="" style="padding:15px; margin-top: -5px">

	<div class="row">
		<div class="col-md-12" align="center">


			<button style="font-size:11px;" type="button" class="btn btn-dark mt-1 mb-1" title="Todas os Pedidos" onclick="buscarContas('')">
				<span>TODAS</span>
				<span class="badge bg-light rounded-pill ms-1" id="todos_pedidos">0</span>
			</button>


			<button style="font-size:11px;" type="button" class="btn btn-danger mt-1 mb-1" title="Aguardando Preparo" onclick="buscarContas('Aguardando')">
				<span>AGUARDANDO</span>
				<span class="badge bg-light rounded-pill ms-1" id="ini_pedidos">0</span>
			</button>


			<button style="font-size:11px;" type="button" class="btn btn-info mt-1 mb-1" title="Pedidos em Preparo" onclick="buscarContas('Preparando')">
				<span>PREPARANDO</span>
				<span class="badge bg-light rounded-pill ms-1" id="prep_pedidos">0</span>
			</button>

			<button style="font-size:11px;" type="button" class="btn btn-success mt-1 mb-1" title="Pedidos Prontos" onclick="buscarContas('Pronto')">
				<span>PRONTO</span>
				<span class="badge bg-light rounded-pill ms-1" id="ent_pedidos">0</span>
			</button>

			<button type="button" class="btn btn-outline-secondary btn-sm mt-1 mb-1" id="bc-fila-toggle" aria-expanded="false"><i class="fa fa-print"></i> Fila de impressão</button>
			<button type="button" id="bc-kanban-toggle" class="btn btn-outline-primary btn-sm mt-1 mb-1" aria-pressed="false"><i class="fa fa-columns"></i> Visualização Kanban</button>
			<input type="hidden" id="buscar-contas">
			<input type="hidden" id="id_pedido">

		</div>
	</div>


	<hr>
	<section id="bc-fila-painel" style="display:none" class="card p-3 mb-3" aria-label="Fila de impressao">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <strong>Fila de impressão — Outros Locais</strong>
    <button type="button" class="btn btn-outline-primary btn-sm" id="bc-fila-atualizar">Atualizar</button>
  </div>
  <p class="text-muted small mb-2">Somente acompanhamento. O envio automático para impressoras permanece desativado.</p>
  <div id="bc-fila-mensagem" role="status" class="small mb-2"></div>
  <div class="table-responsive"><table class="table table-sm table-striped">
    <thead><tr><th>Fila</th><th>Item</th><th>Estado</th><th>Tentativas</th><th>Criado em</th><th>Detalhes</th></tr></thead>
    <tbody id="bc-fila-linhas"><tr><td colspan="6">Clique em Atualizar para consultar.</td></tr></tbody>
  </table></div>
</section>
<div id="bc-kanban" style="display:none" class="row g-2 mb-3"></div>
	<div id="listar">

	</div>

</div>







<!-- Modal Dados-->
<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_dados"></span></h4>
				<button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body" id="listar-pedido">


			</div>


		</div>
	</div>
</div>





<!-- Modal Baixar-->
<div class="modal fade" id="modalBaixar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_baixar"></span></h4>
				<button id="btn-fechar-baixar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">
				<form id="form-baixar">
					<div class="row">
						<div class="col-md-8">
							<select class="form-select " id="pgto" name="pgto" style="width:100%;">
								<?php
								$query = $pdo->query("SELECT * FROM formas_pgto where empresa = '$id_empresa' order by id asc");
								$res = $query->fetchAll(PDO::FETCH_ASSOC);
								for ($i = 0; $i < @count($res); $i++) {
									foreach ($res[$i] as $key => $value) {
									}

								?>
									<option value="<?php echo $res[$i]['id'] ?>"><?php echo $res[$i]['nome'] ?></option>

								<?php } ?>
							</select>

							<input type="hidden" name="id" id="id_baixar">
						</div>
						<div class="col-md-4">
							<button type="submit" class="btn btn-primary">Confirmar</button>
						</div>


					</div>
				</form>

				<br><small>
					<div align="center" id="mensagem-baixar"></div>
				</small>

			</div>
		</div>
	</div>
</div>




<!-- Modal Entregador-->
<div class="modal fade" id="modalEntregador" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog" role="document">
		<div class="modal-content">

			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel">Selecionar Entregador</h4>
				<button id="btn-fechar-entregador" aria-label="Close" class="btn-close" data-bs-dismiss="modal"
					type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">
				<form id="form-entregador">
					<div class="row">
						<div class="col-md-8">
							<select class="form-select " id="entregador" name="entregador" style="width:100%;">
								<?php
								$query = $pdo->query("SELECT * FROM usuarios WHERE (nivel = 'Entregador' or nivel = 'Motoboy' or nivel = 'Motoboys' or nivel = 'Entregadores') and empresa = '$id_empresa' ORDER BY id asc");
								$res = $query->fetchAll(PDO::FETCH_ASSOC);
								$total_reg = @count($res);
								if ($total_reg > 0) {
									for ($i = 0; $i < $total_reg; $i++) {
										foreach ($res[$i] as $key => $value) {
										}
										echo '<option value="' . $res[$i]['id'] . '">' . $res[$i]['nome'] . '</option>';
									}
								} else {
									echo '<option value="0">Cadastre uma Categoria</option>';
								}
								?>
							</select>

							<input type="hidden" name="id" id="id_entregador">
						</div>
						<div class="col-md-4">
							<button type="submit" id="btn_entregador" class="btn btn-primary">Selecionar</button>
						</div>


					</div>
				</form>

				<br><small>
					<div align="center" id="mensagem-entregador"></div>
				</small>

			</div>
		</div>
	</div>
</div>



<script type="text/javascript">
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>

<script type="text/javascript">
	var seg = Math.max(5000, Number('<?= $segundos ?>') || 10000);
	$(document).ready(function() {
		setInterval(() => {
			if (!document.hidden && !$('#modalDados').hasClass('show') && !$('#modalBaixar').hasClass('show') && !$('#modalEntregador').hasClass('show')) listar();
		}, seg);
	});
</script>




<script type="text/javascript">
	var atualizacaoPedidosEmAndamento = false;
	function listar() {
		if (atualizacaoPedidosEmAndamento) return;
		atualizacaoPedidosEmAndamento = true;

		var status = $('#buscar-contas').val();
		var ult_pedido = $('#id_pedido').val();

		$.ajax({
			url: 'paginas/' + pag + "/listar_outros.php",
			method: 'POST',
			data: {
				status,
				ult_pedido
			},
			dataType: "html",

			success: function(result) {
				$("#listar").html(result);
				if (window.bcAtualizarKanban) window.bcAtualizarKanban();
				$('#mensagem-excluir').text('');
			},
			complete: function() { atualizacaoPedidosEmAndamento = false; }
		});
	}
</script>



<script type="text/javascript">
	function listarPedido(id) {

		$.ajax({
			url: 'paginas/' + pag + "/listar-pedido.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "html",

			success: function(result) {
				$("#listar-pedido").html(result);
			}
		});
	}
</script>



<script type="text/javascript">
	function buscarContas(status) {
		$('#buscar-contas').val(status);
		listar();
	}
</script>




<script type="text/javascript">
	$("#form-baixar").submit(function() {

		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: 'paginas/' + pag + "/baixar.php",
			type: 'POST',
			data: formData,

			success: function(mensagem) {
				$('#mensagem-baixar').text('');
				$('#mensagem-baixar').removeClass()
				if (mensagem.trim() == "Baixado com Sucesso") {
					baixado()
					$('#btn-fechar-baixar').click();
					listar();

				} else {

					$('#mensagem-baixar').addClass('text-danger')
					$('#mensagem-baixar').text(mensagem)
				}


			},

			cache: false,
			contentType: false,
			processData: false,

		});

	});



	$("#form-entregador").submit(function() {

		$('#mensagem-entregador').text('Carregando!!');
		$('#btn_entregador').hide();

		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: 'paginas/' + pag + "/entregador.php",
			type: 'POST',
			data: formData,

			success: function(mensagem) {
				$('#mensagem-entregador').text('');
				$('#mensagem-entregador').removeClass()
				if (mensagem.trim() == "Salvo com Sucesso") {
					$('#btn_entregador').show();

					$('#btn-fechar-entregador').click();
					listar();

				} else {

					$('#mensagem-entregador').addClass('text-danger')
					$('#mensagem-entregador').text(mensagem)
				}

				$('#btn_entregador').show();


			},

			cache: false,
			contentType: false,
			processData: false,

		});

	});
</script>
<style>
#bc-kanban .bc-kanban-col { background:#f3f4f6; border-radius:12px; padding:12px; min-height:160px; }
#bc-kanban .bc-kanban-col h5 { font-size:14px; font-weight:700; color:#242b3a; }
#bc-kanban .bc-kanban-items { display:flex; flex-direction:column; gap:10px; }
#bc-kanban .bc-kanban-items > .widget { width:100% !important; max-width:100%; margin:0 !important; }
#bc-kanban .bc-kanban-items > .widget > div { height:auto !important; min-height:150px; max-height:400px; overflow:auto !important; }
</style>
<script>
(function(){
  var ativo=false;
  var estados=['Aguardando','Preparando','Pronto'];
  window.bcAtualizarKanban=function(){
    var origem=document.getElementById('listar'), destino=document.getElementById('bc-kanban');
    if(!origem || !destino) return;
    if(!ativo){destino.style.display='none';origem.style.display='';return;}
    destino.style.display='flex';origem.style.display='none';
    destino.innerHTML='';
    estados.forEach(function(status){
      var coluna=document.createElement('div');coluna.className='col-12 col-lg-4';
      var painel=document.createElement('div');painel.className='bc-kanban-col';
      var titulo=document.createElement('h5');titulo.textContent=status;
      var itens=document.createElement('div');itens.className='bc-kanban-items';
      var cards=origem.querySelectorAll('.widget');
      cards.forEach(function(card){
        var botao=card.querySelector('[id^="area_status_"] a');
        if(botao && botao.textContent.trim().indexOf(status)===0) itens.appendChild(card.cloneNode(true));
      });
      if(!itens.children.length){var vazio=document.createElement('small');vazio.className='text-muted';vazio.textContent='Nenhum pedido';itens.appendChild(vazio);}
      painel.appendChild(titulo);painel.appendChild(itens);coluna.appendChild(painel);destino.appendChild(coluna);
    });
  };
  var botao=document.getElementById('bc-kanban-toggle');
  if(botao)botao.addEventListener('click',function(){
    ativo=!ativo;botao.setAttribute('aria-pressed',String(ativo));
    botao.classList.toggle('btn-primary',ativo);
    botao.classList.toggle('btn-outline-primary',!ativo);
    window.bcAtualizarKanban();
  });
})();
</script>

<script>
(function(){
  var setor="Outro";
  var painel=document.getElementById('bc-fila-painel');
  var botao=document.getElementById('bc-fila-toggle');
  var atualizar=document.getElementById('bc-fila-atualizar');
  var linhas=document.getElementById('bc-fila-linhas');
  var mensagem=document.getElementById('bc-fila-mensagem');
  var carregando=false;
  function celula(tr,valor){
    var td=document.createElement('td');td.textContent=valor==null?'—':String(valor);tr.appendChild(td);
  }
  function carregar(){
    if(carregando || !painel || painel.style.display==='none')return;
    carregando=true;atualizar.disabled=true;mensagem.textContent='Consultando fila...';
    fetch('paginas/pedidos/fila_impressao_local.php?setor='+encodeURIComponent(setor),{credentials:'same-origin',cache:'no-store'})
      .then(function(r){return r.json().then(function(d){if(!r.ok)throw new Error(d.erro||'Erro na consulta');return d;});})
      .then(function(d){
        linhas.replaceChildren();
        if(!d.fila || !d.fila.length){
          var tr=document.createElement('tr'),td=document.createElement('td');td.colSpan=6;td.textContent='Nenhum item registrado na fila.';tr.appendChild(td);linhas.appendChild(tr);
        } else d.fila.forEach(function(item){
          var tr=document.createElement('tr');
          [item.id,item.carrinho_id,item.estado,item.tentativas,item.criado_em,item.erro||'—'].forEach(function(v){celula(tr,v);});
          linhas.appendChild(tr);
        });
        mensagem.textContent=d.fila.length+' registro(s). Impressão automática desativada.';
      })
      .catch(function(e){mensagem.textContent='Não foi possível carregar: '+e.message;})
      .finally(function(){carregando=false;atualizar.disabled=false;});
  }
  if(botao)botao.addEventListener('click',function(){
    var aberto=painel.style.display==='none';
    painel.style.display=aberto?'block':'none';
    botao.setAttribute('aria-expanded',String(aberto));
    if(aberto)carregar();
  });
  if(atualizar)atualizar.addEventListener('click',carregar);
})();
</script>
