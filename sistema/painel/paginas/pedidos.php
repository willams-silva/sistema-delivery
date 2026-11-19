<?php
@session_start();
require_once("verificar.php");
require_once("../conexao.php");

if (@$pedidos == 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}

$query2 = $pdo->query("SELECT * FROM usuarios where id = '$id_usuario'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if (@count($res2) > 0) {
	$nome_usuario = $res2[0]['nivel'];
}



//verificar se o caixa está aberto
$query = $pdo->query("SELECT * from caixas where operador = '$id_usuario' and data_fechamento is null and empresa = '$id_empresa'");
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

<input type="hidden" name="pagina" id="pagina">
<?php if (isset($nome_usuario) && $nome_usuario === 'Administrador'): ?>
<div class="card mb-3" style="border-radius:14px;border:1px solid #dce4ee">
 <div class="card-body">
  <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#automacao-pedidos-painel" aria-expanded="false"><i class="fa fa-clock-o"></i> Automação de pedidos / tempos</button>
  <div class="collapse mt-3" id="automacao-pedidos-painel">
   <form id="form-automacao-pedidos">
    <div class="row g-3 align-items-end">
     <div class="col-md-3"><label class="form-label">Automação</label><select class="form-select" name="ativo"><option value="1">Ativada</option><option value="0">Desativada</option></select></div>
     <div class="col-md-3"><label class="form-label">Iniciado → Aceito (minutos)</label><input class="form-control" type="number" min="1" max="60" step="1" name="aceitar_minutos" value="1" required></div>
     <div class="col-md-3"><label class="form-label">Aceito → Preparando (minutos)</label><input class="form-control" type="number" min="1" max="60" step="1" name="preparar_minutos" value="2" required></div>
     <div class="col-md-3"><label class="form-label">Fila de impressão</label><select class="form-select" name="imprimir_ao_aceitar"><option value="1">Enfileirar cupom</option><option value="0">Não enfileirar</option></select></div>
    </div>
    <p class="text-muted small mt-2">Os tempos são exclusivos desta empresa. O cron verifica a cada minuto. Enfileirar não imprime sem agente local configurado. Em rota continua manual.</p>
    <button type="submit" class="btn btn-primary btn-sm">Salvar automação</button> <span id="mensagem-automacao-pedidos" role="status"></span>
   </form>
  </div>
 </div>
</div>
<script>
$(function(){
 const form=$('#form-automacao-pedidos');
 const msg=$('#mensagem-automacao-pedidos');
 const url='paginas/pedidos/configurar-automacao.php';
 $.getJSON(url,function(d){if(!d.ok)return; const c=d.config;form.find('[name=ativo]').val(String(c.ativo));form.find('[name=aceitar_minutos]').val(Number(c.aceitar_segundos)/60);form.find('[name=preparar_minutos]').val(Number(c.preparar_segundos)/60);form.find('[name=imprimir_ao_aceitar]').val(String(c.imprimir_ao_aceitar));}).fail(function(){msg.text('Não foi possível carregar as configurações.');});
 form.on('submit',function(e){e.preventDefault();msg.text('Salvando...');
 const a=Number(form.find('[name=aceitar_minutos]').val()),p=Number(form.find('[name=preparar_minutos]').val());
 if(!Number.isInteger(a)||!Number.isInteger(p)||a<1||p<1||a>60||p>60){msg.text('Informe de 1 a 60 minutos.');return;}
 $.ajax({url,type:'POST',dataType:'json',data:{ativo:form.find('[name=ativo]').val(),aceitar_segundos:a*60,preparar_segundos:p*60,imprimir_ao_aceitar:form.find('[name=imprimir_ao_aceitar]').val()}}).done(function(d){msg.text(d.ok?'Configurações salvas!':(d.erro||'Erro ao salvar'));}).fail(function(xhr){msg.text(xhr.responseJSON?.erro||'Erro ao salvar configurações');});
 });
});
</script>
<?php endif; ?>

<div class="breadcrumb-header justify-content-between">
	<div class="left-content mt-2">


	</div>
</div>



<div class="bs-example widget-shadow" style="padding:15px; margin-top: -5px">


	<?php
// STATUS DO DELIVERY
$status_delivery = 'Aberto';
$classe_status_delivery = 'success';
$texto_botao_delivery = 'Pausar Delivery';
$classe_botao_delivery = 'warning';
$acao_delivery = 'pausar';

if (@$pausar_delivery == 'Sim') {
	$status_delivery = 'Pausado';
	$classe_status_delivery = 'warning';
	$texto_botao_delivery = 'Ativar Delivery';
	$classe_botao_delivery = 'success';
	$acao_delivery = 'ativar';
} else {

	if (@$status_estabelecimento == "Fechado") {
		$status_delivery = 'Fechado';
		$classe_status_delivery = 'danger';
	}

	$data = date('Y-m-d');
	$diasemana = array("Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado");
	$diasemana_numero = date('w', strtotime($data));
	$dia_procurado = $diasemana[$diasemana_numero];

	$query_dia = $pdo->query("SELECT * FROM dias where dia = '$dia_procurado' and empresa = '$id_empresa'");
	$res_dia = $query_dia->fetchAll(PDO::FETCH_ASSOC);

	if (@count($res_dia) > 0) {
		$status_delivery = 'Fechado';
		$classe_status_delivery = 'danger';
	}

	$start = strtotime(date('Y-m-d') . ' ' . $horario_abertura);
	$end = strtotime(date('Y-m-d') . ' ' . $horario_fechamento);
	$now = time();

	if ($end < $start) {
		if (!($now > $start || $now < $end)) {
			$status_delivery = 'Fechado';
			$classe_status_delivery = 'danger';
		}
	} else {
		if (!($start <= $now && $now <= $end)) {
			$status_delivery = 'Fechado';
			$classe_status_delivery = 'danger';
		}
	}
}
?>


<div class="row mb-3 ux-orders-status">
	<div class="col-md-12">
		<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 ux-orders-statusbar">

			<div>				
				<span class="badge bg-<?php echo $classe_status_delivery ?>" style="font-size:14px; padding:8px 12px;">
					<?php echo $status_delivery ?>
				</span>
			</div>

			<div class="<?php echo $pausar_delivery ?>">
				<button type="button" 
					class="btn btn-<?php echo $classe_botao_delivery ?> btn-sm"
					onclick="alterarStatusDelivery('<?php echo $acao_delivery ?>')">
					<i class="fa fa-power-off"></i>
					<?php echo $texto_botao_delivery ?>
				</button>
			</div>

		</div>
	</div>
</div>

	<div class="row">
		<div class="col-md-12 ux-orders-filters" role="group" aria-label="Filtrar pedidos por situação">

			<button style="font-size:11px;" type="button" class="btn btn-dark mt-1 mb-1" title="Todas os Pedidos" onclick="buscarContas('')">
				<span>TODAS</span>
				<span class="badge bg-light rounded-pill ms-1" id="todos_pedidos">0</span>
			</button>


			<button style="font-size:11px;" type="button" class="btn btn_iniciado mt-1 mb-1" title="Iniciado" onclick="buscarContas('Iniciado')">
				<span>INICIADOS</span>
				<span class="badge bg-light rounded-pill ms-1" id="ini_pedidos">0</span>
			</button>

			<button style="font-size:11px;" type="button" class="btn btn_aceito mt-1 mb-1" title="Aceito" onclick="buscarContas('Aceito')">
				<span>ACEITOS</span>
				<span class="badge bg-light rounded-pill ms-1" id="ace_pedidos">0</span>
			</button>

			<button style="font-size:11px;" type="button" class="btn btn-warning mt-1 mb-1" title="Preparando"
				onclick="buscarContas('Preparando')">
				<span>PREPARANDO</span>
				<span class="badge bg-light rounded-pill ms-1" id="prep_pedidos">0</span>
			</button>

			<button style="font-size:11px;" type="button" class="btn btn-success mt-1 mb-1" title="Em Rota de Entrega"
				onclick="buscarContas('Entrega')">
				<span>EM ROTA</span>
				<span class="badge bg-light rounded-pill ms-1" id="ent_pedidos">0</span>
			</button>


			<input type="hidden" id="buscar-contas">
			<input type="hidden" id="id_pedido">


		</div>

	</div>


        <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
            <label for="tipo-pedido-filtro" class="form-label mb-0">Tipo de pedido
                <select id="tipo-pedido-filtro" class="form-select" onchange="$('#pagina').val(0); listar(undefined,0)">
                    <option value="">Todos os tipos</option>
                    <?php if ($pedidos_pdv_delivery == 'Sim'): ?>
                    <option value="Balcão">Balcão</option>
                    <?php endif; ?>
                    <option value="Delivery">Delivery</option>
                    <option value="Retirar">Retirar</option>
                </select>
            </label>
        </div>
		<div class="d-flex justify-content-end me-2" style="max-width: 300px; margin-left: auto; margin-bottom: 10px">
    <div class="input-group">
        <span class="input-group-text bg-light border border-primary">
            <i class="fas fa-search text-secondary"></i>
        </span>
        <input type="text" class="form-control border border-primary" id="busca" placeholder="Buscar pedidos..." aria-label="Buscar pedidos" autocomplete="off">
    </div>
</div>

	<hr>
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
									<option value="<?php echo $res[$i]['nome'] ?>"><?php echo $res[$i]['nome'] ?></option>

								<?php } ?>

							</select>

							<input type="hidden" name="id" id="id_baixar">
                        <div class="mt-2" id="aviso-pix-manual" style="display:none">
                            <label class="form-label text-warning">PIX sem integração bancária</label>
                            <p class="small mb-2">Confira o valor recebido no extrato do Nubank ou do banco da loja. Comprovante enviado pelo cliente não confirma crédito.</p>
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="pix_conferido" id="pix_conferido" value="1">
                                Confirmo que o valor entrou na conta da empresa
                            </label>
                        </div>
						</div>
						<div class="col-md-4">
							<button id="btn_confirmar" type="submit" class="btn btn-primary">Confirmar</button>
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
							<button type="submit" class="btn btn-primary">Selecionar</button>
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





<!-- Modal Desconto-->
<div class="modal fade" id="modalDesconto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
	aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel">Desconto</h4>
				<button id="btn-fechar-desconto" aria-label="Close" class="btn-close" data-bs-dismiss="modal"
					type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">
				<form id="form-desconto">
					<div class="row">
						<div class="col-md-8">
							<input class="form-control" type="text" name="valor" id="desconto_valor">

							<input type="hidden" name="id" id="id_desconto">
						</div>
						<div class="col-md-4">
							<button id="btn_desconto" type="submit" class="btn btn-primary">Confirmar</button>
						</div>


					</div>
				</form>

				<br><small>
					<div align="center" id="mensagem-desconto"></div>
				</small>

			</div>
		</div>
	</div>
</div>




<!-- Modal Whats -->
<div class="modal fade" id="modalWhats" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel">Enviar Mensagem no WhatsApp</h4>
				<button id="btn-fechar-whats" aria-label="Close" class="btn-close" data-bs-dismiss="modal"
					type="button"><span class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">
				<label class="form-label mt-2">Mensagem <i class="fa-brands fa-whatsapp"></i></label>

				<!-- Botões de mensagens rápidas -->
				<div class="btn-group mb-2 w-100" role="group">
					<button type="button" class="btn btn-outline-success btn-sm" onclick="cancelarPedido()">
						<i class="fa fa-ban"></i> Cancelar Pedido
					</button>
				
					<button type="button" class="btn btn-outline-success btn-sm" onclick="avaliarPedido()">
						<i class="fa fa-star"></i> Avaliar
					</button>
					<button type="button" class="btn btn-outline-success btn-sm" onclick="confirmarPedido()">
 					 <i class="fa fa-check-circle"></i> Confirmar Pedido
					</button>
				</div>

				<textarea id="mensagem_whatsapp" name="mensagem_whatsapp" class="form-control" rows="4"
					placeholder="Digite sua mensagem..." style="background-image: url('images/whatsapp.jpg'); 
							   background-size: cover;
							   background-position: center;
							   background-repeat: no-repeat;
							   opacity: 0.9;"></textarea>
			</div>


			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ Fechar</button>
				<button type="button" class="btn btn-success" id="btn_enviar_whats" onclick="enviarWhats()">Enviar<i
						class="fa fa-paper-plane ms-2"></i></button>
				<button type="button" class="btn btn-success" id="btn_carregando_whats" style="display: none">
					<span class="spinner-border spinner-border-sm" role="status"
						aria-hidden="true"></span>Enviando...</button>
			</div>
			<input type="hidden" class="form-control" id="id_whats" name="id_whats">
			<input type="hidden" class="form-control" id="tipo_whats" name="tipo_whats">			
			<input type="hidden" class="form-control" id="email_whats" name="email_whats">
		</div>
	</div>
</div>





<script type="text/javascript">
	document.addEventListener("visibilitychange", function() {
    if (!document.hidden) {
        listar(); // Chama a função AJAX ao voltar para a aba
    }
});



	
</script>

<script>
$(document).on('change', '#pgto', function(){
 const pix = /\bpix\b/i.test(String($(this).val() || ''));
 $('#aviso-pix-manual').toggle(pix);
 if (!pix) $('#pix_conferido').prop('checked', false);
});
$(document).on('show.bs.modal', '#modalBaixar', function(){
 $('#pix_conferido').prop('checked', false);
 setTimeout(function(){ $('#pgto').trigger('change'); },0);
});
</script>
<script type="text/javascript">
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>

<script type="text/javascript">
	// O intervalo configurado é em milissegundos; evita polling excessivo.
    var seg = Math.max(3000, Number(<?= json_encode($segundos ?? 10000) ?>) || 10000);
	$(document).ready(function() {
		setInterval(() => {
            if (document.hidden || $('.modal.show').length ||
                $('#listar').find(':focus').length) return;
            if (requisicaoPedidosAtual && requisicaoPedidosAtual.readyState !== 4) return;
			listar();
		}, seg);
	});
</script>




<script type="text/javascript">
    // Debounce da pesquisa: não modifica a automação do cron.
    var temporizadorBuscaPedidos = null;
    $(document).off('input.boraPedidosBusca','#busca').on('input.boraPedidosBusca','#busca',function(){
        clearTimeout(temporizadorBuscaPedidos);
        temporizadorBuscaPedidos=setTimeout(function(){
            $('#pagina').val(0);
            listar(undefined,0);
        },350);
    });
	var resultadoPedidosPendente = null;
    $(document).off('focusout.boraPedidos hidden.bs.modal.boraPedidos')
      .on('focusout.boraPedidos','#listar',function(){
          setTimeout(aplicarPedidosPendentes,0);
      })
      .on('hidden.bs.modal.boraPedidos','.modal',aplicarPedidosPendentes);
    function aplicarPedidosPendentes(){
        if (resultadoPedidosPendente === null ||
            $('#listar').find(':focus').length || $('.modal.show').length) return;
        $('#listar').html(resultadoPedidosPendente);
        resultadoPedidosPendente = null;
    }
    var requisicaoPedidosAtual = null;
    var sequenciaPedidos = 0;
	function listar(p1, p2) {

		if(p1 == "" || p1 == "undefined" || p1 == undefined){
			var p1 = $('#busca').val();
		}
		
		if(p2 == "" || p2 == "undefined" || p2 == undefined){
			var p2 = $('#pagina').val();
		}
		

		var status = $('#buscar-contas').val();
		var ult_pedido = $('#id_pedido').val();
        var tipo_pedido = $('#tipo-pedido-filtro').val() || '';

		const sequencia = ++sequenciaPedidos;
        if (requisicaoPedidosAtual && requisicaoPedidosAtual.readyState !== 4) {
            requisicaoPedidosAtual.abort();
        }
        requisicaoPedidosAtual = $.ajax({
			url: 'paginas/' + pag + "/listar.php",
			method: 'POST',
			data: {
				p1,
				p2,
				status,
				ult_pedido,
                tipo_pedido
			},
			dataType: "html",

			success: function(result) {
                if (sequencia !== sequenciaPedidos) return;
                // Não substituir botões ou formulários enquanto o operador os utiliza.
                if ($('#listar').find(':focus').length || $('.modal.show').length) {
                    // Guarda o resultado mais recente para aplicá-lo após a interação.
                    resultadoPedidosPendente = result;
                    return;
                }
                resultadoPedidosPendente = null;
				$("#listar").html(result);
				$('#mensagem-excluir').text('');
			},
            error: function(xhr, status) {
                if (status !== 'abort' && sequencia === sequenciaPedidos) {
                    console.warn('Não foi possível atualizar a lista de pedidos.');
                }
            }
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

		$('#mensagem-baixar').text('Carregando!!');
		$('#btn_confirmar').hide();

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

				$('#mensagem-baixar').text('');
				$('#btn_confirmar').show();


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




	$("#form-desconto").submit(function() {

		$('#mensagem-desconto').text('Carregando!!');
		$('#btn_desconto').hide();

		event.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: 'paginas/' + pag + "/desconto.php",
			type: 'POST',
			data: formData,

			success: function(mensagem) {
				$('#mensagem-desconto').text('');
				$('#mensagem-desconto').removeClass()
				if (mensagem.trim() == "Baixado com Sucesso") {
					$('#btn_desconto').show();
					$('#desconto_valor').val('');
					$('#btn-fechar-desconto').click();
					listar();

				} else {

					$('#mensagem-desconto').addClass('text-danger')
					$('#mensagem-desconto').text(mensagem)
				}

				$('#btn_desconto').show();


			},

			cache: false,
			contentType: false,
			processData: false,

		});

	});
</script>




<script>
	function inserirSaudacao() {
		var hora = new Date().getHours();
		var saudacao = "";

		if (hora >= 5 && hora < 12) {
			saudacao = "Bom dia";
		} else if (hora >= 12 && hora < 18) {
			saudacao = "Boa tarde";
		} else {
			saudacao = "Boa noite";
		}

		var mensagem = `${saudacao}! 😊\nComo posso ajudar?`;
		inserirTexto(mensagem);
	}

	function confirmarPedido() {
		var mensagem = "✅ *PEDIDO CONFIRMADO* ✅\n\n" +
			"Olá! Seu pedido foi confirmado com sucesso e já está sendo preparado. Em breve você receberá mais atualizações. 😉";
		inserirTexto(mensagem);
	}

		function avaliarPedido() {
	var mensagem = "⭐ *AVALIAÇÃO DO PEDIDO* ⭐\n\n" +
		"Olá! Seu pedido foi finalizado e gostaríamos muito de saber como foi sua experiência. 😊\n\n" +
		"Se possível, deixe sua avaliação. Ela é muito importante para continuarmos melhorando!";
	inserirTexto(mensagem);
}


function cancelarPedido() {
	var mensagem = "❌ *PEDIDO CANCELADO* ❌\n\n" +
		"Olá! Informamos que o seu pedido foi cancelado.\n\n" +
		"Se tiver alguma dúvida ou precisar de ajuda, estamos à disposição para esclarecer.";
	inserirTexto(mensagem);
}



	function inserirTexto(novoTexto) {
		var textarea = document.getElementById('mensagem_whatsapp');
		var textoAtual = textarea.value;

		if (textoAtual) {
			textarea.value = textoAtual + "\n\n" + novoTexto;
		} else {
			textarea.value = novoTexto;
		}
	}
</script>


<script type="text/javascript">
		function enviarWhats() {
		let mensagem = document.getElementById('mensagem_whatsapp').value;
		let id = document.getElementById('id_whats').value;
		let tipo = document.getElementById('tipo_whats').value;

		if (mensagem.trim() === '') {
			alertWarning('Por favor, insira uma mensagem para enviar!');
			return;
		}

		$('#btn_enviar_whats').hide();
		$('#btn_carregando_whats').show();

		$.ajax({
			url: 'apis/enviar_whatsapp.php',
			method: 'POST',
			data: {
				id: id,
				mensagem: mensagem,
				tipo: tipo
			},
			success: function (response) {
				$('#btn_enviar_whats').show();
				$('#btn_carregando_whats').hide();

				if (response == 'Mensagem enviada com sucesso!') {
					alertsucesso(response);
					$('#mensagem_whatsapp').val('');
					$('#btn-fechar-whats').click();
					
				} else {
					alertErro(response);
				}
			},
			error: function (xhr, status, error) {
				$('#btn_enviar_whats').show();
				$('#btn_carregando_whats').hide();
				alertErro('Erro ao enviar mensagem: ' + error);
			},
			complete: function () {
				$('#mensagem_whatsapp').val(''); // Limpa o campo de mensagem
			}
		});
	}
</script>



<script type="text/javascript">
function alterarStatusDelivery(acao){

	let titulo = acao == 'pausar' ? 'Pausar Delivery?' : 'Ativar Delivery?';
	let texto = acao == 'pausar' 
		? 'Ao pausar, o cliente não conseguirá fazer novos pedidos pelo delivery.'
		: 'Ao ativar, o delivery voltará a receber pedidos normalmente.';

	let textoBotao = acao == 'pausar' ? 'Sim, pausar!' : 'Sim, ativar!';
	let corBotao = acao == 'pausar' ? '#f0ad4e' : '#28a745';

	Swal.fire({
		title: titulo,
		text: texto,
		icon: 'warning',
		showCancelButton: true,
		confirmButtonText: textoBotao,
		cancelButtonText: 'Cancelar',
		reverseButtons: true,
		confirmButtonColor: corBotao,
		cancelButtonColor: '#6c757d'
	}).then((result) => {

		if (result.isConfirmed) {

			$.ajax({
				url: 'paginas/' + pag + '/alterar_status_delivery.php',
				method: 'POST',
				data: {acao: acao},
				dataType: 'text',

				beforeSend: function(){
					Swal.fire({
						title: 'Aguarde...',
						text: 'Atualizando status do delivery.',
						allowOutsideClick: false,
						allowEscapeKey: false,
						didOpen: () => {
							Swal.showLoading();
						}
					});
				},

				success: function(resposta){

					if(resposta.trim() == 'Alterado com Sucesso'){

						let msgSucesso = acao == 'pausar' 
							? 'Delivery pausado com sucesso!'
							: 'Delivery ativado com sucesso!';

						Swal.fire({
							icon: 'success',
							title: msgSucesso,
							showConfirmButton: false,
							timer: 1200
						}).then(() => {
							location.reload();
						});

					}else{
						Swal.fire({
							icon: 'error',
							title: 'Erro ao alterar',
							text: resposta
						});
					}
				},

				error: function(){
					Swal.fire({
						icon: 'error',
						title: 'Erro',
						text: 'Não foi possível comunicar com o servidor.'
					});
				}
			});
		}
	});
}
</script>