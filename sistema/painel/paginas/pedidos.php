<?php 
@session_start();
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'pedidos';

$segundos = $tempo_atualizar * 1000;



?>



<div class="bs-example widget-shadow" style="padding:15px; margin-top: -5px">

		<div class="row">
		<div class="col-md-12"  align="center">	
			<div > 
				<small >
					<a title="Todas as Vendas" class="text-muted" href="#" onclick="buscarContas('')"><span>Todos(<span id="todos_pedidos"></span>)</span></a> /
					<a title="Todas as Vendas" class="text-muted" href="#" onclick="buscarContas('Iniciado')"><i class="fa fa-square text-primary"></i> <span>Iniciado(<span id="ini_pedidos"></span>)</span></a> / 
					<a title="Vendas Pendentes" class="text-muted" href="#" onclick="buscarContas('Preparando')"><i class="fa fa-square text-danger"></i> <span>Preparando(<span id="prep_pedidos"></span>)</span></a> / 
					<a title="Vendas Pagas" class="text-muted" href="#" onclick="buscarContas('Entrega')"><i class="fa fa-square text-laranja"></i> <span>Em Rota Entrega(<span id="ent_pedidos"></span>)</span></a>
				</small>
			</div>
		</div>

		<input type="hidden" id="buscar-contas">
		<input type="hidden" id="id_pedido">

	</div>

	
	<hr>
	<div id="listar">

	</div>
	
</div>







<!-- Modal Dados-->
<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel"><span id="nome_dados"></span></h4>
				<button id="btn-fechar-perfil" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">

				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-6">							
						<span><b>Valor : </b></span>
						<span id="valor_dados"></span>
					</div>	

					<div class="col-md-6">							
						<span><b>Total Pago: </b></span>
						<span id="total_pago_dados"></span>							
					</div>


				</div>


				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-6">							
						<span><b>Taxa Entrega : </b></span>
						<span id="taxa_entrega_dados"></span>
					</div>	

					<div class="col-md-6">							
						<span><b>Troco: </b></span>
						<span id="troco_dados"></span>							
					</div>


				</div>


				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					
					<div class="col-md-6">							
						<span><b>Tipo PGTO: </b></span>
						<span id="tipo_pgto_dados"></span>							
					</div>

						<div class="col-md-6">							
						<span><b>Usuário Baixa: </b></span>
						<span id="usuario_pgto_dados"></span>							
					</div>

				</div>




				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-6">							
						<span><b>Data: </b></span>
						<span id="data_dados"></span>							
					</div>

					<div class="col-md-6">							
						<span><b>Hora: </b></span>
						<span id="hora_dados"></span>							
					</div>


				</div>



				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-6">							
						<span><b>Status: </b></span>
						<span id="status_dados"></span>							
					</div>

					<div class="col-md-6">							
						<span><b>Pago: </b></span>
						<span id="pago_dados"></span>							
					</div>


				</div>

				

				<div class="row" style="border-bottom: 1px solid #cac7c7;">
					<div class="col-md-12">							
						<span><b>OBS: </b></span>
						<span id="obs_dados"></span>							
					</div>
				
				</div>




				


			</div>

			
		</div>
	</div>
</div>





<script type="text/javascript">var pag = "<?=$pag?>"</script>
<script src="js/ajax.js"></script>

<script type="text/javascript">
	var seg = '<?=$segundos?>';
	$(document).ready(function() {    
   setInterval(()=>{
        		listar();
    		}, seg);   
} );

</script>

			


<script type="text/javascript">
	function listar(){		
	
	var status = $('#buscar-contas').val();
	var ult_pedido = $('#id_pedido').val();	
	
    $.ajax({
        url: 'paginas/' + pag + "/listar.php",
        method: 'POST',
        data: {status, ult_pedido},
        dataType: "html",

        success:function(result){
            $("#listar").html(result);
            $('#mensagem-excluir').text('');
        }
    });
}
</script>



<script type="text/javascript">
	function buscarContas(status){
	 $('#buscar-contas').val(status);
	 listar();
	}
</script>




<script type="text/javascript">
	function baixar(id){
    $.ajax({
        url: 'paginas/' + pag + "/baixar.php",
        method: 'POST',
        data: {id},
        dataType: "text",

        success: function (mensagem) {            
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