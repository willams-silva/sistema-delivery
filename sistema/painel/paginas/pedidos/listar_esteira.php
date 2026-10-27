<?php 
require_once("../../../conexao.php");
$tabela = 'vendas';
$data_hoje = date('Y-m-d');

$status = '%'.@$_POST['status'].'%';
$ult_ped = @$_POST['ult_pedido'];

$total_vendas = 0;

//TOTAIS DOS PEDIDOS
$query = $pdo->query("SELECT * FROM $tabela where status = 'Iniciado'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_ini = @count($res);

$query = $pdo->query("SELECT * FROM $tabela where status = 'Preparando'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_prep = @count($res);

$query = $pdo->query("SELECT * FROM $tabela where status = 'Entrega'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_ent = @count($res);

$query = $pdo->query("SELECT * FROM $tabela where status != 'Finalizado' and status != 'Cancelado'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_pedidos = @count($res);


$query = $pdo->query("SELECT * FROM $tabela where status != 'Finalizado' and status != 'Cancelado' order by id desc limit 1");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_ult_pedido = @$res[0]['id'];

if($ult_ped < $id_ult_pedido and $ult_ped != ""){
	echo '<audio autoplay="true">
<source src="../../img/audio.mp3" type="audio/mpeg" />
</audio>';
}

if($ult_ped == "" and $id_ult_pedido != ""){
	echo '<audio autoplay="true">
<source src="../../img/audio.mp3" type="audio/mpeg" />
</audio>';
}

$ids = '';
$query = $pdo->query("SELECT * FROM $tabela where status LIKE '$status' and status != 'Finalizado' and status != 'Cancelado' order by hora asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){


for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];	
	$cliente = $res[$i]['cliente'];
	$valor = $res[$i]['valor'];
	$total_pago = $res[$i]['total_pago'];
	$troco = $res[$i]['troco'];
	$data = $res[$i]['data'];
	$hora = $res[$i]['hora'];
	$status = $res[$i]['status'];
	$pago = $res[$i]['pago'];
	$obs = $res[$i]['obs'];
	$taxa_entrega = $res[$i]['taxa_entrega'];
	$tipo_pgto = $res[$i]['tipo_pgto'];
	$usuario_baixa = $res[$i]['usuario_baixa'];
	$entrega = $res[$i]['entrega'];
	$mesa = $res[$i]['mesa'];
	$nome_cliente_ped = $res[$i]['nome_cliente'];
	$pedido = $res[$i]['pedido'];
	$impressao = $res[$i]['impressao'];
	$codigo_pix = $res[$i]['ref_api'];

	//verificar pgto pix
	if($codigo_pix != "" and $pago != "Sim"){
		require("../../../../js/ajax/verificar_pgto.php");		
		if(@$status_api == 'approved'){
		  	$pdo->query("UPDATE vendas set pago = 'Sim', tipo_pgto = 'Pix' where id = '$id'");
		}
	}
	
	$valorF = number_format($valor, 2, ',', '.');
	$total_pagoF = number_format($total_pago, 2, ',', '.');
	$trocoF = number_format($troco, 2, ',', '.');
	$taxa_entregaF = number_format($taxa_entrega, 2, ',', '.');
	$dataF = implode('/', array_reverse(explode('-', $data)));
	$horaF = date("H:i", strtotime($hora));	

	$hora_pedido = date('H:i', strtotime("+$previsao_entrega minutes",strtotime($hora)));

	if($hora_pedido < date('H:i')){
		$classe_hora = 'text-danger';
	}else{
		$classe_hora = '';
	}

		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_baixa'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_usuario_pgto = $res2[0]['nome'];
		}else{
			$nome_usuario_pgto = 'Nenhum!';
		}


		$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if($total_reg2 > 0){
			$nome_cliente = $res2[0]['nome'];
			$telefone_cliente = $res2[0]['telefone'];
		}else{
			if($mesa != '0' and $mesa != ''){
				$nome_cliente = 'Mesa: '.$mesa;
			}else{
				$nome_cliente = $nome_cliente_ped;
			}
			$telefone_cliente = '';
		}


		$whatsapp_cliente = '55'.preg_replace('/[ ()-]+/' , '' , $telefone_cliente);


		if($status == 'Iniciado'){
			$classe_alerta = 'text-primary';	
			$total_vendas += $valor;
			$titulo_link = 'Mudar para Preparando!';
			$cor_icone_link = 'text-danger';
			$acao_link = 'Preparando';
			
			
		}else if($status == 'Preparando'){
			$classe_alerta = 'text-danger';
			$total_vendas += 0;
			$titulo_link = 'Mudar para Entrega!';
			$cor_icone_link = 'text-laranja';
			$acao_link = 'Entrega';
			
		}else{
			$classe_alerta = 'text-laranja';
			$total_vendas += $valor;
			$titulo_link = 'Mudar para Finalizado!';
			$cor_icone_link = 'text-verde';
			$acao_link = 'Finalizado';
						
		}

		if($pago == 'Sim'){
			$classe_excluir = 'ocultar';
			$visivel = 'ocultar';
			$classe_pago = 'text-verde';
			$texto_pago = '(Pago)';
		}else{
			$classe_excluir = '';
			$visivel = '';
			$classe_pago = 'text-danger';
			$texto_pago = '';
		}

		if($obs != ""){
			$classe_info = 'text-laranja';
		}else{
			$classe_info = 'text-secondary';
		}

		if($entrega == 'Delivery'){
			$classe_entrega = 'text-danger';
		}else if($entrega == 'Retirar'){
			$classe_entrega = 'text-primary';
		}else{
			$classe_entrega = 'text-verde';
		}

		
		//validando impressao automatica
		if($status == 'Iniciado' and $impressao_automatica == 'Site' and $impressao != "Sim"){
			$ids .= $id.'-';
			//coloco a impressao = sim
			$pdo->query("UPDATE $tabela SET impressao = 'Sim' where id = '$id'");
		}

		$nome_clienteF = mb_strimwidth($nome_cliente, 0, 16, "...");

		$classe_troco = '';
		if($troco == "" || $troco == 0){
			$classe_troco = 'ocultar';
		}


echo <<<HTML
<div class="col-md-3 widget " style="margin:5px">
			<div class="" style="padding:4px; min-height: 100px;
  background: #ffffff;">
				<div style="border-bottom: 1px solid #000">
					<span> <i class="fa fa-square {$classe_alerta}"></i> Pedido <b>{$pedido}</b></span>
					<span style="position:absolute; right:5px" class="{$classe_hora}"><i>{$horaF}</i></span>
				</div>

				<div style="margin-top: 5px">
					<span><small><i><span style="color:#000">{$nome_clienteF} <span class="{$classe_entrega}"><small>({$entrega}) </small></span></span></i></small></span>					
				</div>

				<div>
					<span><small><i>Status <b><a title="{$titulo_link}" href="#" onclick="ativarPedido('{$id}','{$acao_link}','{$whatsapp_cliente}','{$valorF}','{$tipo_pgto}','{$hora_pedido}','{$entrega}')">
{$status} 
<i class="fa fa-arrow-right {$cor_icone_link}"></i>
</a></b></i></small></span>					
				</div>

				<div style="border-bottom: 1px solid #000; padding-bottom: 5px">
				<small>
					<span class=" {$classe_pago}">R$ {$total_pagoF} <small>{$texto_pago}</small></span>	
					<span class="{$classe_troco}">Troco: {$trocoF}</span>
<span class="text-primary"><small>({$tipo_pgto})</small></span>	
	</small>		
				</div>

				<div align="center" style="">
					<span>	

		<a href="#" onclick="mostrar('{$id}', '{$nome_cliente}', '{$pedido}')" title="Ver Dados"><i class="fa fa-info-circle {$classe_info}"></i></a>



		<li class="dropdown head-dpdn2" style="display: inline-block;">
		<a class="{$classe_excluir}" title="Cancelar Venda" href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-trash-o text-danger"></i></a>

		<ul class="dropdown-menu" style="margin-left:-230px;">
		<li>
		<div class="notification_desc2">
		<p>Confirmar Exclusão? <a href="#" onclick="excluir('{$id}')"><span class="text-danger">Sim</span></a></p>
		</div>
		</li>										
		</ul>
		</li>


<a class="{$classe_excluir}" href="#" onclick="baixar('{$id}', '{$nome_cliente}', '{$tipo_pgto}')" title="Confirmar Pgto"><i class="fa fa-check-square text-verde"></i></a>


		<a  href="#" onclick="gerarComprovante('{$id}')" title="Gerar Comprovante"><i class="fa fa-file-pdf-o text-primary"></i></a>


		
	
		</span>				
				</div>
				
			</div>
		</div>	
HTML;

}

$total_vendasF = number_format($total_vendas, 2, ',', '.');

}else{
	echo '<small>Não possui nenhum Pedido Hoje ainda!</small>';
}



$query = $pdo->query("SELECT * FROM vendas where data = CurDate() and status = 'Iniciado'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_dos_itens_pedidos = @count($res); 


?>

<script type="text/javascript">
	$(document).ready( function () {

	var ids = "<?=$ids?>";
	var id_imp = ids.split("-");
	//alert(ids)
		
	for(i=0; i<id_imp.length-1; i++){
		var id_pedido = id_imp[i];
		
		let a= document.createElement('a');
		a.target= '_blank';
		a.href= 'rel/comprovante.php?id='+ id_pedido;
		a.click();			
	}



	$('#todos_pedidos').text("<?=$total_pedidos?>");
	$('#ini_pedidos').text("<?=$total_ini?>");
	$('#prep_pedidos').text("<?=$total_prep?>");
	$('#ent_pedidos').text("<?=$total_ent?>");
	$('#id_pedido').val("<?=$id_ult_pedido?>");

	$('#total-dos-pedidos').text("<?=$total_dos_itens_pedidos?>");
			
    $('#tabela').DataTable({
    		"ordering": false,
			"stateSave": true
    	});
    $('#tabela_filter label input').focus();
} );
</script>


<script type="text/javascript">
	function mostrar(id, cliente, pedido){		
		listarPedido(id);
		$('#nome_dados').text('Pedido ' + pedido + ' - Cliente: ' + cliente);
		$('#modalDados').modal('show');
	}
</script>

<script type="text/javascript">
	function baixar(id, cliente, pgto){		
		
		$('#nome_baixar').text('Confirmar Pagamento: Pedido ' + id + ' - Cliente: ' + cliente);
		$('#pgto').val(pgto).change();
		$('#id_baixar').val(id);
		$('#modalBaixar').modal('show');
	}
</script>


<script type="text/javascript">
	
function ativarPedido(id, acao, telefone, total, pagamento, hora, entrega){
	var pedido_whatsapp = "<?=$status_whatsapp?>";


	if(entrega == 'Delivery'){
		
		var texto = 'Seu Pedido saiu para entrega';
	}else if(entrega == 'Retirar'){
		var texto = 'Seu Pedido ficou pronto, pode vir retirá-lo';
	}else{
		var texto = 'Seu Pedido já ficou pronto, pode vir!!';
	}

	var imp_aut = '<?=$impressao_automatica?>';
	
		if(imp_aut == 'Sim' && acao == 'Preparando'){
		let a= document.createElement('a');
		                a.target= '_blank';
		                a.href= 'rel/comprovante.php?id='+ id;
		                a.click();
		}
	
		if(acao == 'Entrega' && entrega == 'Delivery'){
			$('#id_entregador').val(id);
			$('#modalEntregador').modal('show');
		}
	
    $.ajax({
        url: 'paginas/' + pag + "/mudar-status.php",
        method: 'POST',
        data: {id, acao, telefone, total, pagamento, texto},
        dataType: "text",

        success: function (mensagem) {  
        var split = mensagem.split("***"); 

            if (split[0] == "Alterado com Sucesso") {                
                listar();     
                if(acao.trim() === 'Entrega'){
                	if(pedido_whatsapp == 'Sim'){
		              let a= document.createElement('a');
		                a.target= '_blank';
		                a.href= 'http://api.whatsapp.com/send?1=pt_BR&phone='+telefone+'&text= *Atenção*  %0A '+texto+' %0A *Total* R$'+total+' %0A *Tipo Pagamento* '+pagamento+' %0A';
		                a.click();
           }
                }           
            } else {
                $('#mensagem-excluir').addClass('text-danger')
                $('#mensagem-excluir').text(mensagem)
            }

        },      

    });
}

</script>

<script type="text/javascript">
	function gerarComprovante(id){
		let a= document.createElement('a');
		                a.target= '_blank';
		                a.href= 'rel/comprovante.php?id='+ id + '&imp=Não';
		                a.click();
	}
</script>


