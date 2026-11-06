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

<div class="breadcrumb-header justify-content-between">
	<div class="left-content mt-2">


	</div>
</div>


<div class="" style="padding:15px; margin-top: -5px">




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

	$query_dia = $pdo->query("SELECT * FROM dias where dia = '$dia_procurado'");
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


<div class="row mb-3" style="margin-top: -10px">
	<div class="col-md-12">
		<div class="d-flex justify-content-between align-items-center flex-wrap p-1" 
			style="background:#f8f9fa; border:1px solid #e5e5e5; border-radius:8px;">

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
		<div class="col-md-12" align="center">

			<button style="font-size:11px;" type="button" class="btn btn-dark mt-1 mb-1" title="Todas os Pedidos" onclick="buscarContas('')">
				<span>TODAS</span>
				<span class="badge bg-light rounded-pill ms-1" id="todos_pedidos">0</span>
			</button>


			<button style="font-size:11px;" type="button" class="btn btn_iniciado mt-1 mb-1" title="Iniciado" onclick="buscarContas('Iniciado')">
				<span>INCIADOS</span>
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
				</button>
			</div>

			<div class="modal-body">
				<form id="form-baixar">
					<div class="row">
						<div class="col-md-8">
							<select class="form-select " id="pgto" name="pgto" style="width:100%;">
								<?php
								$query = $pdo->query("SELECT * FROM formas_pgto order by id asc");
								$res = $query->fetchAll(PDO::FETCH_ASSOC);
								for ($i = 0; $i < @count($res); $i++) {
									foreach ($res[$i] as $key => $value) {
									}

								?>
									<option value="<?php echo $res[$i]['nome'] ?>"><?php echo $res[$i]['nome'] ?></option>

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
								$query = $pdo->query("SELECT * FROM usuarios WHERE nivel = 'Entregador' or nivel = 'Motoboy' or nivel = 'Motoboys' or nivel = 'Entregadores' ORDER BY id asc");
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
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>

<script type="text/javascript">
	var seg = '<?= $segundos ?>';
	$(document).ready(function() {
		setInterval(() => {
			listar();
		}, seg);
	});
</script>




<script type="text/javascript">
	function listar() {

		var status = $('#buscar-contas').val();
		var ult_pedido = $('#id_pedido').val();

		$.ajax({
			url: 'paginas/' + pag + "/listar_esteira.php",
			method: 'POST',
			data: {
				status,
				ult_pedido
			},
			dataType: "html",

			success: function(result) {
				$("#listar").html(result);
				$('#mensagem-excluir').text('');
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
				url: 'paginas/pedidos/alterar_status_delivery.php',
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