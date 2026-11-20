<?php
@session_start();
require_once("verificar.php");
require_once("../conexao.php");

if (@$mesas == 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}


$pag = 'mesas';


$query2 = $pdo->query("SELECT * FROM usuarios where id = '$id_usuario' and empresa = '$id_empresa' ");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if (@count($res2) > 0) {
	$nome_usuario = $res2[0]['nivel'];
}



?>

<div class="breadcrumb-header justify-content-between"><div class="left-content mt-2"><h4 class="mb-1"><i class="fa fa-table me-2"></i>Gestão de Mesas</h4><p class="text-muted small mb-0">Acompanhe disponibilidade, status e QR Codes do salão.</p></div>
	<div class="left-content mt-2">
		<a class="btn ripple btn-primary text-white" onclick="inserir()" type="button"><i class="fe fe-plus me-1"></i>
			Adicionar mesa</a>

		<button type="button" class="btn btn-outline-secondary" onclick="listar()" title="Atualizar mesas"><i class="fa fa-refresh me-1"></i> Atualizar</button>

		<a class="btn btn-danger" href="#" onclick="deletarSel()" title="Excluir" id="btn-deletar" style="display:none"><i class="fe fe-trash-2"></i> Deletar</a>

	</div>
</div>

<style>
.bc-mesa-card{background:#fff;transition:transform .15s ease,box-shadow .15s ease}
.bc-mesa-card:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(0,0,0,.08)}
.bc-mesas-controls{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin:12px 0}
.bc-mesas-controls .btn.active{background:#ff8700;color:#fff;border-color:#ff8700}
</style>
<div class="bc-mesas-controls" aria-label="Visualização e filtros das mesas">
 <div class="btn-group" role="group" aria-label="Modo de visualização">
  <button type="button" class="btn btn-outline-secondary active" id="bc-mesas-btn-cards" onclick="bcDefinirVisaoMesas('cards')"><i class="fa fa-th-large me-1"></i> Cartões</button>
  <button type="button" class="btn btn-outline-secondary" id="bc-mesas-btn-table" onclick="bcDefinirVisaoMesas('table')"><i class="fa fa-list me-1"></i> Tabela</button>
 </div>
 <label for="bc-mesas-filtro" class="small mb-0">Filtrar:</label>
 <select id="bc-mesas-filtro" class="form-select form-select-sm" style="max-width:180px" onchange="bcAplicarVisaoMesas()">
  <option value="todas">Todas</option><option value="livre">Livres</option><option value="ocupada">Ocupadas</option><option value="inativa">Desativadas</option>
 </select>
 <label class="small mb-0 ms-auto"><input type="checkbox" id="bc-mesas-auto" checked> Atualizar a cada 15 segundos</label>
</div>
<div class="bs-example widget-shadow" style="padding:15px" id="listar">

</div>



<input type="hidden" id="ids">


<!-- Modal Inserir-->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title"><span id="titulo_inserir"></span></h4>
				<button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<form id="form">
				<div class="modal-body">

					<div class="row">
						<div class="col-md-9">
							<div class="form-group">

								<label for="nome" class="form-label fw-semibold">Número da mesa</label><input type="number" class="form-control" id="nome" name="nome" placeholder="Ex.: 12" min="1" max="99999" required>
							</div>
						</div>
						<div class="col-md-3">
							<button type="submit" class="btn btn-primary" id="btn_salvar">Salvar<i class="fa fa-check ms-2"></i></button>
							<button class="btn btn-primary" type="button" id="btn_carregando" style="display: none">
								<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
							</button>
						</div>
					</div>


					<input type="hidden" name="id" id="id">

					<br>
					<small>
						<div id="mensagem" align="center"></div>
					</small>
				</div>
			</form>


		</div>
	</div>
</div>




<div class="modal fade" id="modalQRCode" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title">Mesa <span id="titulo_qrcode"></span></h4>
				<button id="btn-fechar" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>
			
				<div class="modal-body" id="gerar_qrcode" align="center">
					
				</div>
			


		</div>
	</div>
</div>


<script type="text/javascript">
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>
<script>
(function(){
 window.bcMesasVisao='cards';
 window.bcDefinirVisaoMesas=function(visao){
  window.bcMesasVisao=visao==='table'?'table':'cards';
  window.bcAplicarVisaoMesas();
 };
 window.bcAplicarVisaoMesas=function(){
  var cards=document.getElementById('bc-mesas-cards');
  var table=document.getElementById('bc-mesas-tabela');
  var filtro=document.getElementById('bc-mesas-filtro');
  var isCards=window.bcMesasVisao!=='table';
  if(cards)cards.style.display=isCards?'':'none';
  if(table)table.style.display=isCards?'none':'';
  var btnCards=document.getElementById('bc-mesas-btn-cards'),btnTable=document.getElementById('bc-mesas-btn-table');
  if(btnCards)btnCards.classList.toggle('active',isCards);
  if(btnTable)btnTable.classList.toggle('active',!isCards);
  if(cards&&filtro){
   Array.prototype.forEach.call(cards.querySelectorAll('.bc-mesa-item'),function(item){
    item.style.display=(filtro.value==='todas'||item.dataset.tipo===filtro.value)?'':'none';
   });
  }
 };
 if(window.bcMesasRefreshTimer)clearInterval(window.bcMesasRefreshTimer);
 window.bcMesasRefreshTimer=setInterval(function(){
  var auto=document.getElementById('bc-mesas-auto');
  if(!auto){clearInterval(window.bcMesasRefreshTimer);return;}
  if(!auto.checked||document.hidden||window.bcMesasVisao==='table')return;
  if(document.querySelector('.modal.show')||document.querySelector('#ids')?.value)return;
  if(typeof listar==='function')listar();
 },15000);
})();
</script>
