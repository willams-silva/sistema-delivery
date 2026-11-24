<?php
require_once("cabecalho.php");
$url = $_GET['url'];


$query = $pdo->query("SELECT * FROM categorias where url = '$url' and empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
	$nome = $res[0]['nome'];
	$descricao = $res[0]['descricao'];
	$id = $res[0]['id'];


	@session_start();
	$sessao = @$_SESSION['sessao_usuario'];
	$sessao_pedido_balcao = @$_SESSION['pedido_balcao'];



	$pdo->query("DELETE FROM temp where carrinho = 0 and sessao = '$sessao' and empresa = '$id_empresa'");
}

if (@$_SESSION['nome_mesa'] != '') {
	unset($_SESSION['pedido_balcao']);
};


?>

<div class="main-container">
	<div class="container">
		<nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
			<div class="container-fluid">
				<div class="navbar-brand">
					<a href="site/<?php echo $url_site ?>"><big><i class="bi bi-arrow-left"></i></big></a>
					<span style="margin-left: 15px; font-size:14px"><?php echo mb_strtoupper($nome) ?> <?php echo @$_SESSION['nome_mesa'] ?> <?php echo @$_SESSION['pedido_balcao'] ?></span>
				</div>

				<?php require_once("icone-carrinho.php") ?>

			</div>
		</nav>

		<ol class="list-group " style="margin-top: 65px">


			<?php
			$query = $pdo->query("SELECT * FROM produtos where categoria = '$id' and ativo = 'Sim' and empresa = '$id_empresa' and (tem_estoque = 'Não' OR estoque > 0) order by valor_venda asc");
			$res = $query->fetchAll(PDO::FETCH_ASSOC);

			// Na vitrine do delivery, considera somente produtos disponíveis para delivery.
			// Em pedidos de mesa, mantém também os produtos exclusivos do atendimento local.
			if (@$_SESSION['nome_mesa'] == "") {
				$res_filtrados = array();
				foreach ($res as $produto_categoria) {
					if ($produto_categoria['delivery'] != 'Não') {
						$res_filtrados[] = $produto_categoria;
					}
				}
				$res = $res_filtrados;
			}

			$total_reg = count($res);
			if ($total_reg > 0) {
				for ($i = 0; $i < $total_reg; $i++) {
					foreach ($res[$i] as $key => $value) {
					}
					$id = $res[$i]['id'];
					$foto = $res[$i]['foto'];
					$nome = $res[$i]['nome'];
					$descricao = $res[$i]['descricao'];
					$url = $res[$i]['url'];
					$estoque = $res[$i]['estoque'];
					$tem_estoque = $res[$i]['tem_estoque'];
					$valor = $res[$i]['valor_venda'];
					$promocao = $res[$i]['promocao'];
					$delivery = $res[$i]['delivery'];
					$val_promocional = $res[$i]['val_promocional'];
					$promocao = $res[$i]['promocao'];

					$val_promocionalF = @number_format($val_promocional, 2, ',', '.');


					$valorF = @number_format($valor, 2, ',', '.');

					if (@$_SESSION['nome_mesa'] == "" and $delivery == 'Não') {
						continue;
					}


					if ($tem_estoque == 'Sim' and $estoque <= 0) {
						$mostrar = '';
						$url_produto = '#';
					} else {
						$mostrar = 'ocultar';
						$url_produto = 'produto-' . $url;
					}


					if ($promocao == 'Sim') {
						$mostrar_promo = '';
					} else {
						$mostrar_promo = 'ocultar';
					}


					//verificar se o produto tem grade
					$query3 = $pdo->query("SELECT * FROM grades where produto = '$id' and ativo = 'Sim'");
					$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
					$total_adc = @count($res3);
					if ($total_adc > 0) {
						if ($tem_estoque == 'Sim' and $estoque <= 0) {
							$url_produto = '#';
						} else {
							$url_produto = 'adicionais-' . $url;
						}
					} else {
						if ($tem_estoque == 'Sim' and $estoque <= 0) {
							$url_produto = '#';
						} else {
							$url_produto = 'observacoes-' . $url;
						}
					}



			?>

					<a href="<?php echo $url_produto ?>" class="link-neutro">
						<li class="list-group-item d-flex justify-content-between align-items-start mb-2" style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 8px;">

							<img class="<?php echo $mostrar ?>" src="img/esgotado.png" width="65px" height="65px" style="position:absolute; right:0; top:0px">

							

							<div class="row" style="width:100%">
								<div class="col-10">

									<div class="me-auto">
										<div class="fw-bold titulo-item" style="font-weight: 600"><?php echo $nome ?><?php if ($promocao == 'Sim' && (float)$val_promocional > 0 && (float)$val_promocional < (float)$valor) { ?> <span style="display:inline-block;vertical-align:middle;margin-left:6px;padding:3px 8px;border-radius:20px;background:#b45b20;color:#fff;font-size:10px;font-weight:700;line-height:1.2;white-space:nowrap">OFERTA</span><?php } ?></div>
										<div class="subtitulo-item-menor" style="font-size:10px; font-weight: 380"><?php echo $descricao ?></div>
										<span class="valor-item-maior">
											<?php
											$query2 = $pdo->query("SELECT * FROM grades where produto = '$id' and tipo_item = 'Variação' and ativo = 'Sim' order by id asc");
											$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
											$total_reg2 = @count($res2);
											if ($total_reg2 > 0) {
												// Exibe o preço promocional do produto mesmo quando há variações.
												if ($promocao == 'Sim' && (float)$val_promocional > 0) {
													echo '<span style="display:block; color:#198754; font-weight:600">R$ ' . $val_promocionalF . ' <del style="color:red; font-weight:400">R$ ' . $valorF . '</del></span>';
												}
												$id_grade = $res2[0]['id'];

												$query3 = $pdo->query("SELECT * FROM itens_grade where grade = '$id_grade' and ativo = 'Sim' order by id asc");
												$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
												$total_reg3 = @count($res3);
												if ($total_reg3 > 0) {
													for ($i3 = 0; $i3 < $total_reg3; $i3++) {
														$id_item = $res3[$i3]['id'];
														$nome_item = $res3[$i3]['texto'];
														$valor_item = $res3[$i3]['valor'];
														$valor_itemF = @number_format($valor_item, 2, ',', '.');

														echo '<span style="font-size:10px">(' . $nome_item . ') R$ ' . $valor_itemF;
														if ($i3 < $total_reg3 - 1) {
															echo ' / ';
														}
														echo '</span>';
													}
												}
											} else {
												if ($valor > 0) {

													if($promocao == 'Sim' && (float)$val_promocional > 0){
														echo 'R$ ' . $val_promocionalF;
														echo '  <del style="color: red">R$ ' . $valorF . ' </del';

														//$valor = $val_promocional;
													}else{
														echo 'R$ ' . $valorF;
													}

													
												}
											}
											?>

										</span>
									</div>

								</div>


								<div class="col-2" style="margin-top: 10px;" align="right">
									<img class="" src="/media-delivery.php?tipo=produtos&amp;arquivo=<?php echo rawurlencode($foto) ?>" width="60px" height="60px" style="border-radius:12px;object-fit:cover;">
								</div>



							</div>

						</li>
					</a>

			<?php }
			} else { ?>
				<li class="list-group-item text-center py-4 mb-2" style="border:1px solid #e9ecef; border-radius:10px; background:#f8f9fa;">
					<div id="aviso-categoria-vazia" role="dialog" aria-modal="true" aria-labelledby="titulo-categoria-vazia" style="position:fixed;inset:0;background:rgba(53,33,20,.48);z-index:100000;display:flex;align-items:center;justify-content:center;padding:20px;">
						<div style="width:100%;max-width:420px;background:#fffdf9;border:1px solid #efddc9;border-radius:20px;padding:34px 24px 28px;text-align:center;box-shadow:0 18px 55px rgba(78,43,20,.18);">
							<div aria-hidden="true" style="font-size:42px;color:#a85b24;margin-bottom:14px;"><i class="bi bi-basket"></i></div>
							<h5 id="titulo-categoria-vazia" style="font-size:23px;font-weight:750;color:#753d1e;margin-bottom:12px;">Categoria sem produtos</h5>
							<p style="font-size:15px;color:#80634e;line-height:1.6;margin-bottom:24px;">Ainda não temos produtos disponíveis nesta categoria. Confira novamente em breve!</p>
							<a href="site/<?php echo $url_site ?>" class="btn" style="display:inline-block;background:#a85b24;color:#fff;border:1px solid #a85b24;border-radius:11px;padding:12px 24px;font-weight:750;text-decoration:none;">Voltar ao cardápio</a>
						</div>
					</div>
				</li>
			<?php } ?>



		</ol>


	</div>
</div>

<style>
li.list-group-item:has(img[src*="media-delivery.php?tipo=produtos"]){border:1px solid #efd5bb!important;border-radius:16px!important;margin-bottom:12px;padding:16px!important;background:#fffdf9;box-shadow:0 3px 12px #512c240b!important}
li.list-group-item:has(input[type=checkbox]:checked):has(img[src*="media-delivery.php?tipo=produtos"]){border-color:#a85b24!important;background:#fff3e5}
li.list-group-item .row:has(img[src*="media-delivery.php?tipo=produtos"]){display:flex;flex-wrap:nowrap;gap:12px;margin:0!important;width:100%!important}
li.list-group-item .row:has(img[src*="media-delivery.php?tipo=produtos"])>.col-10{flex:1;min-width:0;width:auto;padding:0}
li.list-group-item .row:has(img[src*="media-delivery.php?tipo=produtos"])>.col-2{flex:0 0 140px;width:140px;padding:0;margin-top:0!important}
li.list-group-item img[src*="media-delivery.php?tipo=produtos"]{width:140px!important;height:140px!important;max-width:100%;object-fit:cover!important;border-radius:12px!important;cursor:zoom-in}
li.list-group-item:has(img[src*="media-delivery.php?tipo=produtos"]) .titulo-item,li.list-group-item:has(img[src*="media-delivery.php?tipo=produtos"]) .titulo-item small{font-size:18px!important;color:#512c24;font-weight:700}
li.list-group-item:has(img[src*="media-delivery.php?tipo=produtos"]) .subtitulo-item-menor{font-size:14px!important;line-height:1.5}
li.list-group-item:has(img[src*="media-delivery.php?tipo=produtos"]) .valor-item-maior,li.list-group-item:has(img[src*="media-delivery.php?tipo=produtos"]) .valor-item-maior span{font-size:17px!important;font-weight:700}
li.list-group-item .valor-item-maior del{font-size:13px!important}
.bora-photo-dialog{position:fixed!important;inset:0!important;margin:auto!important;width:max-content;height:max-content;overflow:auto;box-sizing:border-box;border:0;border-radius:18px;padding:12px;background:#fffaf3;max-width:94vw;max-height:90vh;color:#512c24}
.bora-photo-dialog::backdrop{background:#24140ecc}
.bora-photo-dialog img{display:block;margin:0 auto;max-height:calc(90dvh - 80px);max-width:85vw;max-height:72vh;object-fit:contain;border-radius:12px}
.bora-photo-dialog button{display:block;margin:8px 0 8px auto;border:0;background:#a85b24;color:white;border-radius:20px;padding:8px 16px}
@media(max-width:600px){li.list-group-item .row:has(img[src*="media-delivery.php?tipo=produtos"])>.col-2{flex-basis:100px;width:100px}li.list-group-item img[src*="media-delivery.php?tipo=produtos"]{width:100px!important;height:100px!important}li.list-group-item:has(img[src*="media-delivery.php?tipo=produtos"]){padding:12px!important}}
</style>
<dialog class="bora-photo-dialog" id="bora-photo-dialog" aria-label="Foto do produto"><button type="button" aria-label="Fechar foto">Fechar ×</button><img alt="Foto ampliada do produto"></dialog>
<script>
(function(){
const d=document.getElementById('bora-photo-dialog');if(!d||!d.showModal)return;
const selector='li.list-group-item img[src*="media-delivery.php?tipo=produtos"]';
function open(e){const img=e.target.closest(selector);if(!img)return;e.preventDefault();e.stopImmediatePropagation();d.querySelector('img').src=img.currentSrc||img.src;d.showModal();}
document.addEventListener('click',open,true);
document.querySelectorAll(selector).forEach(img=>{img.tabIndex=0;img.setAttribute('role','button');img.setAttribute('aria-label','Ampliar foto do produto');img.title='Ampliar foto';});
document.addEventListener('keydown',e=>{if((e.key==='Enter'||e.key===' ')&&e.target.matches(selector))open(e);},true);
d.querySelector('button').addEventListener('click',()=>d.close());d.addEventListener('click',e=>{if(e.target===d)d.close();});
})();
</script>

</body>

</html>