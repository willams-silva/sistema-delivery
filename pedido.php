<?php
@session_start();
require_once("cabecalho.php");
require_once("js/ajax/ApiConfig.php");

$id = @$_GET['id'];

if($id != ''){
  $id_decode = base64_decode($id);
  $partes = explode('|', $id_decode);

  if(count($partes) == 2 && $partes[1] == 'hugo'){
    $id = $partes[0];
  } else {
    echo "<script>window.location='../site/".$url_site."'</script>";
    exit();
  }
}



//BUSCAR AS INFORMAÇÕES DO PEDIDO
$query = $pdo->query("SELECT * from vendas where id = '$id' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);

$cliente = $res[0]['cliente'];
$valor = $res[0]['valor'];
$total_pago = $res[0]['total_pago'];
$troco = $res[0]['troco'];
$data = $res[0]['data'];
$hora = $res[0]['hora'];
$status = $res[0]['status'];
$pago = $res[0]['pago'];
$obs = $res[0]['obs'];
$taxa_entrega = $res[0]['taxa_entrega'];
$tipo_pgto = $res[0]['tipo_pgto'];
$usuario_baixa = $res[0]['usuario_baixa'];
$entrega = $res[0]['entrega'];
$mesa = $res[0]['mesa'];
$nome_cliente_ped = $res[0]['nome_cliente'];
$cupom = $res[0]['cupom'];
$n_pedido = $res[0]['pedido'];

$cupomF = number_format($cupom, 2, ',', '.');
$valorF = number_format($valor, 2, ',', '.');
$total_pagoF = number_format($total_pago, 2, ',', '.');
$trocoF = number_format($troco, 2, ',', '.');
$taxa_entregaF = number_format($taxa_entrega, 2, ',', '.');
$dataF = implode('/', array_reverse(explode('-', $data)));
//$horaF = date("H:i", strtotime($hora));	

$valor_dos_itens = $valor - $taxa_entrega + $cupom;
$valor_dos_itensF = number_format($valor_dos_itens, 2, ',', '.');

$hora_pedido = date('H:i', strtotime("+$previsao_entrega minutes", strtotime($hora)));

if ($pago == 'Sim') {
	$cor_pago = 'green';
} else {
	$cor_pago = 'red';
}


$query = $pdo->query("SELECT * FROM clientes where id = '$cliente' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$cartoes = @$res[0]['cartoes'];
   

?>

<script>document.body.classList.add("resumo-cardapio");</script>

<style>
/* Resumo do pedido: identidade visual do cardápio e checkout. */
body.resumo-cardapio { background:#fffaf5 !important; color:#503827; margin:0; }
.resumo-cardapio .resumo-container { max-width:760px; margin:22px auto 45px; padding:24px !important; background:#fff; border:1px solid #efddc9; border-radius:20px; box-shadow:0 8px 30px rgba(100,58,29,.08); font-family:inherit; }
.resumo-cardapio .resumo-container > img:first-child { width:72px; height:72px; object-fit:contain; }
.resumo-cardapio .resumo-container > span:first-of-type { display:inline-block; font-size:23px !important; color:#8d4a20; margin:8px 0 4px; }
.resumo-cardapio .resumo-container hr { border:0; border-top:1px solid #ead7c3; opacity:1; margin:18px 0 !important; }
.resumo-cardapio .resumo-container a { color:#a85b24; font-weight:700; text-decoration:none; }
.resumo-cardapio .resumo-container a:hover { text-decoration:underline; }
.resumo-cardapio .resumo-container > p { padding:17px 18px; border:1px solid #f0dfce; border-radius:14px; background:#fffaf5; line-height:1.9; }
.resumo-cardapio .resumo-itens-titulo { background:#a85b24 !important; padding:13px 16px !important; border-radius:12px; color:#fff !important; font-size:14px !important; font-weight:750; letter-spacing:.4px; }
.resumo-cardapio .resumo-itens { margin:0 !important; padding:12px 10px; border:1px solid #efddc9; border-radius:14px; background:#fffdf9; row-gap:8px; font-size:14px !important; }
.resumo-cardapio .resumo-itens > .col-9 { font-weight:650; color:#543925; padding:9px 10px; }
.resumo-cardapio .resumo-itens > .col-3 { font-weight:750; color:#925024; padding:9px 8px; white-space:nowrap; }
.resumo-cardapio .resumo-itens hr { margin:0 !important; opacity:.6; }
.resumo-cardapio .resumo-itens > .col-12 { color:#80634e !important; font-size:12px !important; padding-left:24px !important; }
.resumo-cardapio #listar_st { padding:16px; background:#fff7ed; border:1px solid #efddc9; border-radius:14px; margin-top:20px !important; }
.resumo-cardapio .resumo-container img { max-width:100%; }
@media(max-width:600px) {
  .resumo-cardapio .resumo-container { margin:12px 10px 30px; padding:17px 13px !important; border-radius:15px; }
  .resumo-cardapio .resumo-container > span:first-of-type { font-size:20px !important; }
  .resumo-cardapio .resumo-container > p { padding:13px 10px; }
  .resumo-cardapio .resumo-itens { padding:9px 3px; }
  .resumo-cardapio .resumo-itens > .col-9 { width:72%; }
  .resumo-cardapio .resumo-itens > .col-3 { width:28%; }
}
</style>

	<div class="container resumo-container" align="center" style="padding:10px; font-size: 'Verdana';">
		<img src="<?php echo $url_sistema ?>img/check_ok.png" width="80px">
		<br>
		<span style="font-size: 18px; margin-top: 5px"><b>Nº PEDIDO <?php echo $n_pedido ?></b></span>
		<br>
		<span style="font-size: 12px; margin-top: -5px">Ficamos gratos por realizar essa compra!</span><br>

		<?php if($total_cartoes_config > 0){ ?>
		<span style="font-size: 12px; margin-top: -5px">Você ganhou mais um cartão Fidelidade</span><br>

		<span style="font-size: 12px; margin-top: -5px">Você possui <b><?php echo $cartoes ?> 
		<?php if($cartoes == 1) {
			echo 'Cartão';
		} else { 
			echo 'Cartões';
		} ?>
		 Fidelidade </b></span><br>

		<?php } ?>


		<span style="font-size: 13px;"><b>Novo Pedido -> </b> <span><a href="<?php echo $url_sistema ?>site/<?php echo $url_site ?>">Clique Aqui</a></span></span>


		<hr style="margin:7px">
		<p>
			<span style="font-size: 13px;"><b>Pagamento: </b> <span><?php echo $tipo_pgto ?></span></span> /
			<span style="font-size: 13px;"><b>Pago: </b> <span style="color:<?php echo $cor_pago ?>"><?php echo $pago ?></span></span>
			<br>
			<span style="font-size: 13px;"><b>Opção de Entrega: </b> <span><?php echo $entrega ?></span></span><br>
			<span style="font-size: 13px;"><b>Pagamento: <span style="color:<?php echo $cor_pago ?>; font-size: 15px">R$ <?php echo $valorF ?></span></b></span><br>
			<?php if ($access_token == "") { ?>
				<span style="font-size: 13px;"><b>Chave Pix:</b> <span style="font-size: 13px"><?php echo $tipo_chave ?> <?php echo @$dados_pagamento ?></span></span><br>
			<?php } ?>
		</p>

		<hr style="margin:7px">
		<p class="resumo-itens-titulo" style="background: #757571; padding:2px; color:#FFF; font-size:13px">CONFIRA SEUS ITENS</p>



		<div class="row itens resumo-itens" style="font-size:12px">
			<?php
			$res = $pdo->query("SELECT * from carrinho where pedido = '$id' order by id asc");
			$dados = $res->fetchAll(PDO::FETCH_ASSOC);
			$linhas = count($dados);

			$sub_tot;
			for ($i = 0; $i < count($dados); $i++) {
				foreach ($dados[$i] as $key => $value) {
				}
				$id_carrinho = $dados[$i]['id'];
				$id_produto = $dados[$i]['produto'];
				$quantidade = $dados[$i]['quantidade'];
				$total_item = $dados[$i]['total_item'];
				$obs_item = $dados[$i]['obs'];
				$item = $dados[$i]['item'];
				$variacao = $dados[$i]['variacao'];
				$nome_produto_tab = $dados[$i]['nome_produto'];
				$sabores = $dados[$i]['sabores'];
				$borda = $dados[$i]['borda'];
				$categoria = $dados[$i]['categoria'];


				$query2 = $pdo->query("SELECT * FROM variacoes where id = '$variacao'");
				$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
				if (@count(@$res2) > 0) {
					$sigla_variacao = '(' . $res2[0]['sigla'] . ')';
				} else {
					$sigla_variacao = '';
				}

				$query2 = $pdo->query("SELECT * FROM temp where carrinho = '$id_carrinho' and tabela = 'Variação' order by id asc limit 1");
				$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
				$id_do_item = @$res2[0]['id_item'];

				$query2 = $pdo->query("SELECT * FROM itens_grade where id = '$id_do_item'");
				$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
				if (@$res2[0]['texto'] != "") {
					$sigla_grade = @$res2[0]['texto'];
				} else {
					$sigla_grade = '';
				}

				$query2 = $pdo->query("SELECT * FROM produtos where id = '$id_produto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count(@$res2) > 0) {
			$nome_produto = $res2[0]['nome'];
			$foto_produto = $res2[0]['foto'];
			$unidade = $res2[0]['unidade'];
		} else {
			$nome_produto = $nome_produto_tab;
			$foto_produto = "";
			$unidade = "";
		}



		$query3 = $pdo->query("SELECT * FROM unidade_medida where id = '$unidade'");
			$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
			if(@count($res3) > 0){
				$nome_unidade = $res3[0]['nome'];
			}else{
				$nome_unidade = 'Sem Unidade';
			}


		$ocultar_quantidades = '';
			$sigla_unidade = '';
			if($nome_unidade == 'Quilogramas' or $nome_unidade == 'Quilo' or $nome_unidade == 'Quilograma' or $nome_unidade == 'KG'){
				$sigla_unidade = ' (KG)';
				$ocultar_quantidades = 'ocultar';
			}

			if($nome_unidade == 'Metros' or $nome_unidade == 'Metro' or $nome_unidade == 'M' or $nome_unidade == 'm'){
				$sigla_unidade = ' (m)';
				$ocultar_quantidades = 'ocultar';
			}

			if($nome_unidade == 'Litro' or $nome_unidade == 'Litros' or $nome_unidade == 'L'){
				$sigla_unidade = ' (L)';
				$ocultar_quantidades = 'ocultar';
			}

			//tratamento separa string
			$qt = explode(".", $quantidade);
			if($qt[1] > 0){
				$quantidadeF = $quantidade;		
			}else{
				$quantidadeF = $qt[0];
			}

			?>



				<div align="left" class="col-9"> <span class=""><?php echo $quantidadeF ?> <?php echo $sigla_unidade ?> </span> - <?php echo $nome_produto ?> <?php echo $sigla_variacao ?> <?php echo $sigla_grade ?>

				</div>

				<div align="right" class="col-3">
					R$ <?php
							$total_itemF = number_format($total_item, 2, ',', '.');
							// $total = number_format( $cp1 , 2, ',', '.');
							echo $total_itemF;
							?>
				</div>

				<hr style="margin:2px">

				<?php
				$query_adc_pedido = $pdo->prepare("SELECT id_item, id_sabor, quantidade, valor_item FROM temp WHERE carrinho = ? AND tabela = 'adicionais' ORDER BY id_sabor, id");
				$query_adc_pedido->execute([$id_carrinho]);
				$adicionais_pedido = $query_adc_pedido->fetchAll(PDO::FETCH_ASSOC);
				foreach ($adicionais_pedido as $adc_pedido) {
					$id_adc_pedido = (int)$adc_pedido['id_item'];
					$query_nome_adc = $pdo->prepare("SELECT nome FROM adicionais_cat WHERE id = ? LIMIT 1");
					$query_nome_adc->execute([$id_adc_pedido]);
					$nome_adc_pedido = (string)$query_nome_adc->fetchColumn();
					$id_sabor_pedido = (int)$adc_pedido['id_sabor'];
					$nome_sabor_pedido = '';
					if ($id_sabor_pedido > 0) {
						$query_nome_sabor = $pdo->prepare("SELECT nome FROM produtos WHERE id = ? LIMIT 1");
						$query_nome_sabor->execute([$id_sabor_pedido]);
						$nome_sabor_pedido = (string)$query_nome_sabor->fetchColumn();
					}
					$quantidade_adc_pedido = (int)$adc_pedido['quantidade'];
					?>
					<div class="col-12" style="font-size:11px; padding-left:18px; color:#555">
						+ <?php echo $quantidade_adc_pedido ?>x <?php echo htmlspecialchars($nome_adc_pedido, ENT_QUOTES, 'UTF-8') ?>
						<?php if ($nome_sabor_pedido != '') { ?> <span>(<?php echo htmlspecialchars($nome_sabor_pedido, ENT_QUOTES, 'UTF-8') ?>)</span><?php } ?>
					</div>
				<?php } ?>

			<?php } ?>
		</div>



		<div style="font-size: 12px; margin-top: 15px" align="left" id="listar_st">



		</div>


		<?php if($total_cartoes_config > 0){ ?>


		<br>

		<?php if($total_cartoes_config == $cartoes){ ?>

			<span style="font-size: 12px; margin-top: -5px">Você completou <b><?php echo $total_cartoes_config ?> cartões fidelidades </b>, em sua próxima compra digite a palavra cartao no campo de cupom de desconto para ganhar um desconto de  <b><?php echo $valor_cupom_config ?> Reais!</b></span><br>

		<?php }else{ ?>

			<span style="font-size: 12px; margin-top: -5px">Após completar <b><?php echo $total_cartoes_config ?> cartões </b>, você poderá trocar por um cupom de <b>desconto de <?php echo $valor_cupom_config ?> Reais!</b></span><br>
		
		<?php } ?>

<br>
<div class="row" style="display: flex; justify-content: center; flex-wrap: wrap;">
 <?php 
for($i=1; $i<=$total_cartoes_config; $i++){ 
		if($cartoes >= $i){
			$valor = 0;
			$opacity = 1;
		}else{
			$valor = 1;
			$opacity = 0.3;		}
	?>

<div class="col-md-1 col-2" align="center">
<img src="<?php echo $url_sistema ?>img/cartao.png" width="100%" style="filter: grayscale(<?php echo $valor ?>); filter: opacity(<?php echo $opacity ?>)">
</div>
<?php } ?>
</div>


<?php } ?>

<br>



		<div style="margin-top: 25px">
			<a href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whats ?>" target="_blank"><img src="<?php echo $url_sistema ?>img/whats.png" width="80%"></a>
		</div>

</body>


<script type="text/javascript">
	listarStatus();


	function listarStatus() {
		var id = "<?= $id ?>";
		var url_sistema = "<?= $url_sistema ?>";

		$.ajax({
			url: url_sistema + 'js/ajax/listar_status.php',
			method: 'POST',
			data: {
				id
			},
			dataType: "html",

			success: function(result) {
				$('#listar_st').html(result);
			}
		});
	}

	setInterval(listarStatus, 15000);
</script>