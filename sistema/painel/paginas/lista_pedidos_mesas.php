<?php
@session_start();
require_once("verificar.php");
require_once("../conexao.php");

if (@$lista_pedidos_mesas == 'ocultar') {
	echo "<script>window.location='../index.php'</script>";
	exit();
}

$pag = 'lista_pedidos_mesas';


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



<div class="bs-example widget-shadow" style="padding:15px">

	<div class="row">
		<div class="col-md-5" style="margin-bottom:5px;">
			<div style="float:left; margin-right:10px"><span><small><i title="Data  Inicial" class="fa fa-calendar-o"></i></small></span></div>
			<div style="float:left; margin-right:20px">
				<input type="date" class="form-control " name="data-inicial" id="data-inicial-caixa" value="<?php echo $data_hoje ?>" required>
			</div>

			<div style="float:left; margin-right:10px"><span><small><i title="Data  Final" class="fa fa-calendar-o"></i></small></span></div>
			<div style="float:left; margin-right:30px">
				<input type="date" class="form-control " name="data-final" id="data-final-caixa" value="<?php echo $data_hoje ?>" required>
			</div>
		</div>



		<div class="group-btn">
			<div class="row">
				<div class="btn-group" role="group" aria-label="Basic radio toggle button group">
					<input title="Conta de Ontem" type="radio" class="btn-check" name="btnradio" id="btnradio21" onclick="valorData('<?php echo $data_ontem ?>', '<?php echo $data_ontem ?>')">
					<label class="btn btn-outline-primary" for="btnradio21">Ontem</label>

					<input type="radio" class="btn-check" name="btnradio" id="btnradio22" checked onclick="valorData('<?php echo $data_hoje ?>', '<?php echo $data_hoje ?>')">
					<label class="btn btn-outline-primary" for="btnradio22">Hoje</label>

					<input type="radio" class="btn-check" name="btnradio" id="btnradio33" onclick="valorData('<?php echo $data_inicio_mes ?>', '<?php echo $data_final_mes ?>')">
					<label class="btn btn-outline-primary" for="btnradio33">Mês</label>
				</div>
			</div>
		</div>






		<input type="hidden" id="buscar-contas">
        <div class="col-md-3"><label for="bc-filtro-mesa" class="form-label">Mesa</label>
        <select class="form-select" id="bc-filtro-mesa" onchange="listar()">
        <option value="">Todas as mesas</option>
        <?php
        $qMesas=$pdo->prepare('SELECT id,nome FROM mesas WHERE empresa=:empresa ORDER BY CAST(nome AS UNSIGNED),id');
        $qMesas->execute([':empresa'=>(int)$id_empresa]);
        foreach($qMesas->fetchAll(PDO::FETCH_ASSOC) as $m){
         echo '<option value="'.(int)$m['id'].'">Mesa '.htmlspecialchars((string)$m['nome'],ENT_QUOTES,'UTF-8').'</option>';
        }
        ?>
        </select></div>

	</div>

	<hr>
	<div id="listar">

	</div>

</div>






<script type="text/javascript">
	var pag = "<?= $pag ?>"
</script>
<script src="js/ajax.js"></script>



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
        var mesa = $('#bc-filtro-mesa').val();

		$.ajax({
			url: 'paginas/' + pag + "/listar.php",
			method: 'POST',
			data: {
				dataInicial,
				dataFinal,
				status, mesa
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
(function(){
 var params=new URLSearchParams(window.location.search);
 var mesa=params.get('mesa');
 if(mesa && /^\d+$/.test(mesa)){
  var sel=document.getElementById('bc-filtro-mesa');
  if(sel && Array.from(sel.options).some(function(o){return o.value===mesa;})){
   sel.value=mesa;
   // Exibir histórico completo da mesa, não apenas o dia corrente.
   document.getElementById('data-inicial-caixa').value='2000-01-01';
   document.getElementById('data-final-caixa').value=new Date().toISOString().slice(0,10);
   setTimeout(function(){if(typeof listar==='function')listar();},0);
  }
 }
})();
</script>