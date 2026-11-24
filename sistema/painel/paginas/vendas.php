<?php
@session_start();
require_once("verificar.php");
require_once("../conexao.php");

if (@$vendas == 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}

$pag = 'vendas';


$data_hoje = date('Y-m-d');
$data_ontem = date('Y-m-d', strtotime("-1 days", strtotime($data_hoje)));

$mes_atual = Date('m');
$ano_atual = Date('Y');
$data_inicio_mes = $ano_atual . "-" . $mes_atual . "-01";

if ($mes_atual == '4' || $mes_atual == '6' || $mes_atual == '9' || $mes_atual == '11') {
	$dia_final_mes = '30';
} else if ($mes_atual == '2') {
	$dia_final_mes = '28';
} else {
	$dia_final_mes = '31';
}

$data_final_mes = $ano_atual . "-" . $mes_atual . "-" . $dia_final_mes;


?>



<style>
html body .ux-vendas{background:#fff!important;border:1px solid #e5d4c6!important;border-radius:14px!important}
html body .ux-vendas .form-control{background:#fff!important;color:#4e2d20!important;border:1px solid #d7c5b7!important;border-radius:9px!important;color-scheme:light}
html body .ux-vendas .btn-outline-primary{background:#fff!important;color:#63351e!important;border-color:#d7c5b7!important}
html body .ux-vendas .btn-check:checked+.btn-outline-primary,html body .ux-vendas a.btn-outline-primary{background:#914a29!important;color:#fff!important;border-color:#914a29!important}
html body #modalDados .modal-header{background:#914a29!important;color:#fff!important}
html body #modalDados .modal-title{color:#fff!important}
html body #modalDados .modal-content{background:#fff!important;border:1px solid #e5d4c6!important;border-radius:14px!important;overflow:hidden}
html body #modalDados td.bg-primary{background:#f4e9df!important;color:#63351e!important}
html body #modalDados td{border-color:#eaded4!important}
</style>
<div class="card mb-3" style="margin-top:16px;border:1px solid #e5d4c6;border-radius:14px"><div class="card-body d-flex align-items-center flex-wrap gap-3"><h2 class="mb-0" style="font-size:21px;font-weight:600;color:#4e2d20"><i class="fa-solid fa-cart-shopping me-2"></i>Vendas e Pedidos</h2><span style="font-size:12px;color:#795038">Acompanhe vendas, pedidos, pagamentos e histórico de estornos.</span></div></div>
<div class="bs-example widget-shadow ux-vendas" style="padding:15px">

	<div class="row">
		<div class="col-md-5" style="margin-bottom:5px;">
			<div style="float:left; margin-right:10px"><span><small><i title="Data  Inicial"
							class="fa fa-calendar-o"></i></small></span></div>
			<div style="float:left; margin-right:20px">
				<input type="date" class="form-control " name="data-inicial" id="data-inicial-caixa"
					value="<?php echo $data_hoje ?>" required>
			</div>

			<div style="float:left; margin-right:10px"><span><small><i title="Data  Final"
							class="fa fa-calendar-o"></i></small></span></div>
			<div style="float:left; margin-right:30px">
				<input type="date" class="form-control " name="data-final" id="data-final-caixa"
					value="<?php echo $data_hoje ?>" required>
			</div>
		</div>







		<div class="group-btn">
			<div class="row">
				<div class="btn-group" role="group" aria-label="Basic radio toggle button group">
					<input type="radio" class="btn-check" name="btnradio" id="btnradio21" checked onclick="valorData('<?php echo $data_ontem ?>', '<?php echo $data_ontem ?>')">
					<label class="btn btn-outline-primary" for="btnradio21">Ontem</label>

					<input type="radio" class="btn-check" name="btnradio" id="btnradio22" checked onclick="valorData('<?php echo $data_hoje ?>', '<?php echo $data_hoje ?>')">
					<label class="btn btn-outline-primary" for="btnradio22">Hoje</label>

					<input type="radio" class="btn-check" name="btnradio" id="btnradio33" onclick="valorData('<?php echo $data_inicio_mes ?>', '<?php echo $data_final_mes ?>')">
					<label class="btn btn-outline-primary" for="btnradio33">Mês</label>
				</div>
			</div>
		</div>


		<div class="group-btn">
			<div class="row">
				<div class="btn-group" role="group" aria-label="Basic radio toggle button group">


					<input type="radio" class="btn-check" name="btnradio" id="btnradio24" checked onclick="buscarContas('')">
					<label class="btn btn-outline-primary" for="btnradio24">Todas</label>

					<input type="radio" class="btn-check" name="btnradio" id="btnradio25" onclick="buscarContas('Cancelado')">
					<label class="btn btn-outline-primary" for="btnradio25">Canceladas</label>

					<input type="radio" class="btn-check" name="btnradio" id="btnradio36" onclick="buscarContas('Finalizado')">
					<label class="btn btn-outline-primary" for="btnradio36">Pagas</label>
				</div>
			</div>
		</div>




		<input type="hidden" id="buscar-contas">

	</div>

	<div class="d-flex justify-content-end my-3">
        <a class="btn btn-outline-primary" href="paginas/vendas/visualizar_estornos.php" target="_blank" rel="noopener noreferrer" title="Consultar histórico de estornos financeiros">
            <i class="fa fa-history" aria-hidden="true"></i> Histórico de estornos
        </a>
    </div>
	<hr>
	<div id="listar">

	</div>

</div>





<!-- Modal Dados -->
<div class="modal fade" id="modalDados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_dados"></span></h4>
				<button id="btn-fechar-perfil" aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span
						class="text-white" aria-hidden="true">&times;</span></button>
			</div>

			<div class="modal-body">
				<small>

					<div class="row">

						<div class="col-md-12">
							<div class="tile">
								<div class="table-responsive">
									<table id="" class="text-left table table-bordered">


										<tr>
											<td  class="bg-primary text-white width20">Valor :</td>
											<td><span id="valor_dados"></span></td>
										</tr>


										<tr>
											<td class="bg-primary text-white w_150">Total Pago:</td>
											<td><span id="total_pago_dados"></span></td>
										</tr>


										<tr>
											<td class="bg-primary text-white w_150">Taxa Entrega:</td>
											<td><span id="endereco_dados"></span></td>
										</tr>

										<tr>
											<td class="bg-primary text-white w_150">Data:</td>
											<td><span id="data_dados"></span></td>
										</tr>

										<tr>
											<td class="bg-primary text-white w_150">Hora:</td>
											<td><span id="hora_dados"></span></td>
										</tr>

										<tr>
											<td class="bg-primary text-white w_150">Troco:</td>
											<td><span id="troco_dados"></span></td>
										</tr>


										<tr>
											<td class="bg-primary text-white w_150">Tipo PGTO:</td>
											<td><span id="tipo_pgto_dados"></span></td>
										</tr>


										<tr>
											<td class="bg-primary text-white w_150">Usuário Baixa:</td>
											<td><span id="usuario_pgto_dados"></span></td>
										</tr>

										<tr>
											<td class="bg-primary text-white w_150">Status:</td>
											<td><span id="status_dados"></span></td>
										</tr>

										<tr>
											<td class="bg-primary text-white w_150">Pago:</td>
											<td><span id="pago_dados"></span></td>
										</tr>



									</table>
								</div>
							</div>
						</div>



						<div class="col-md-12">
							<div class="tile">
								<div class="table-responsive">
									<table id="" class="text-left table table-bordered">



										<tr>
											<td style="width: 8%" class="bg-primary text-white w_150">OBS:</td>
											<td><span id="obs_dados"></span></td>
										</tr>




									</table>
								</div>
							</div>
						</div>

					</div>



				</small>
				<div id="listar_servicos" style="margin-top: 15px"></div>
			</div>

		</div>
	</div>
</div>





<script type="text/javascript">
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>


<script type="text/javascript">
	$(document).ready(function() {
		$('.sel2').select2({
			dropdownParent: $('#modalForm')
		});
	});
</script>


<script type="text/javascript">
	function carregarImg() {
		var target = document.getElementById('target');
		var file = document.querySelector("#foto").files[0];


		var arquivo = file['name'];
		resultado = arquivo.split(".", 2);

		if (resultado[1] === 'pdf') {
			$('#target').attr('src', "images/pdf.png");
			return;
		}

		if (resultado[1] === 'rar' || resultado[1] === 'zip') {
			$('#target').attr('src', "images/rar.png");
			return;
		}



		var reader = new FileReader();

		reader.onloadend = function() {
			target.src = reader.result;
		};

		if (file) {
			reader.readAsDataURL(file);

		} else {
			target.src = "";
		}
	}
</script>



<script type="text/javascript">
	function valorData(dataInicio, dataFinal) {
		$('#data-inicial-caixa').val(dataInicio);
		$('#data-final-caixa').val(dataFinal);
		listar();
	}
</script>



<script type="text/javascript">
	$('#data-inicial-caixa').change(function() {
		//$('#tipo-busca').val('');
		listar();
	});

	$('#data-final-caixa').change(function() {
		//$('#tipo-busca').val('');
		listar();
	});
</script>





<script type="text/javascript">
	function listar() {

		var dataInicial = $('#data-inicial-caixa').val();
		var dataFinal = $('#data-final-caixa').val();
		var status = $('#buscar-contas').val();

		$.ajax({
			url: 'paginas/' + pag + "/listar.php",
			method: 'POST',
			data: {
				dataInicial,
				dataFinal,
				status
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
	function buscarContas(status) {
		$('#buscar-contas').val(status);
		listar();
	}
</script>




<script>
function solicitarCancelamentoVenda(id, pago) {
    // Vendas recebidas nunca devem cair na rotina de exclusao de pendencias.
    if (pago === 'Sim') {
        Swal.fire({
            icon: 'warning',
            title: 'Estorno financeiro necessário',
            html: 'A venda <strong>#' + Number(id) + '</strong> já foi recebida.<br>O cancelamento exige conferir as formas de pagamento, registrar o motivo e conciliar o estorno antes de devolver o estoque.<br><br><strong>Nenhum valor ou estoque foi alterado.</strong>',
            confirmButtonText: 'Consultar histórico de estornos',
            showCancelButton: true,
            cancelButtonText: 'Voltar',
            confirmButtonColor: '#512c24',
            cancelButtonColor: '#806357'
        }).then(function(result) {
            if (result.isConfirmed) window.open('paginas/vendas/visualizar_estornos.php', '_blank', 'noopener');
        });
        return;
    }
    Swal.fire({
        icon: 'question',
        title: 'Cancelar venda pendente?',
        text: 'A venda #' + Number(id) + ' será validada pelo servidor antes de qualquer alteração financeira ou de estoque.',
        showCancelButton: true,
        confirmButtonText: 'Validar cancelamento',
        cancelButtonText: 'Voltar',
        confirmButtonColor: '#512c24'
    }).then(function(result) {
        if (result.isConfirmed && typeof excluir === 'function') excluir(id);
    });
}
</script>

<script type="text/javascript">
	function baixar(id) {
		$.ajax({
			url: 'paginas/' + pag + "/baixar.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "text",

			success: function(mensagem) {
				if (mensagem.trim() == "Baixado com Sucesso") {
					listar();
				} else {
					$('#mensagem-excluir').addClass('text-danger')
					$('#mensagem-excluir').text(mensagem)
				}

			},

		});
	}
</script>