<?php 
@session_start();
require_once('../../sistema/conexao.php');

$id_empresa = @$_SESSION['empresa'];
if($id_empresa == ""){
	echo 'Sem Sessão da Empresa, acesse novamente!';
	exit();
}
require_once("../../sistema/painel/buscar_config.php");

$buscar = $_POST['buscar'];


echo <<<HTML


<style>
#area_busca .bora-search-grid{list-style:none;display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,340px),1fr));gap:14px;max-width:1120px;margin:42px auto 24px;padding:0 16px}
#area_busca .bora-search-grid>a{display:block;min-width:0;text-decoration:none;color:#512c24}
#area_busca .bora-search-grid li{background:#fffaf5;border:1px solid #efd5bb;border-radius:18px;padding:14px;box-shadow:0 5px 18px rgba(100,53,20,.08);height:100%;margin:0!important}
#area_busca .bora-search-grid .titulo-item{font-size:18px;font-weight:700!important;color:#512c24}
#area_busca .bora-search-grid .subtitulo-item-menor{font-size:13px!important;line-height:1.45;color:#705445;margin:5px 0 8px}
#area_busca .bora-search-grid .valor-item-maior{font-size:18px;font-weight:700;color:#a85b24}
#area_busca .bora-search-content{display:flex;align-items:center;gap:12px;width:100%}
#area_busca .bora-search-text{flex:1;min-width:0}
#area_busca .bora-search-promo{position:absolute;top:8px;right:8px;background:#a85b24;color:#fff;padding:5px 10px;border-radius:12px;font-size:14px;font-weight:700;z-index:1;line-height:1.2}#area_busca .bora-search-promo.ocultar{display:none!important}#area_busca .bora-search-photo{width:96px;height:96px;flex:0 0 96px;object-fit:cover;border-radius:12px;background:#f3e5d5}
@media(max-width:600px){#area_busca .bora-search-grid{grid-template-columns:1fr;padding:0 12px}#area_busca .bora-search-photo{width:80px;height:80px;flex-basis:80px}}
</style>
<ol class="bora-search-grid">


HTML;
				
			$buscar = trim($_POST['buscar'] ?? '');
			$like = "%{$buscar}%";

			// Busca produtos que batem pelo nome ou pela categoria
			$sql = "
			  SELECT p.*
			    FROM produtos p
			   WHERE p.ativo = 'Sim' and p.empresa = :empresa
                 AND (COALESCE(p.tem_estoque, 'Não') <> 'Sim' OR COALESCE(p.estoque, 0) > 0)
                 AND EXISTS (SELECT 1 FROM categorias ca WHERE ca.id = p.categoria AND ca.empresa = p.empresa AND ca.ativo = 'Sim') 
			     AND (
			       p.nome LIKE :q1
			       OR p.categoria IN (
			            SELECT c.id FROM categorias c
			             WHERE c.ativo = 'Sim' AND c.empresa = p.empresa
			               AND (c.nome LIKE :q2 OR c.url LIKE :q3)
			       )
			     )
			   ORDER BY p.valor_venda ASC
			";

			$stmt = $pdo->prepare($sql);
			$stmt->execute([':empresa' => $id_empresa, ':q1' => $like, ':q2' => $like, ':q3' => $like]);
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$total_reg = count($res);


			$total_reg = @count($res);
            $exibidos = 0;
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
					$categoria = $res[$i]['categoria'];

					$query3 = $pdo->query("SELECT * FROM categorias where id = '$categoria'");
					$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
					$url_cat = $res3[0]['url'];
					$mais_sabores = $res3[0]['mais_sabores'];
					$ativo = $res3[0]['ativo'];

					if($ativo != 'Sim'){
						continue;
					}



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



					if($mais_sabores == "Sim"){
						$link_red = 'categoria-sabores-'.$url_cat;
					}else{
						$link_red = $url_produto;
					}



$exibidos++;
$foto_url = '/media-delivery.php?tipo=produtos&amp;arquivo=' . rawurlencode($foto);
$nome = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
$descricao = htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8');
$link_red = htmlspecialchars($link_red, ENT_QUOTES, 'UTF-8');
echo <<<HTML

					<a href="{$link_red}" class="link-neutro">
						<li class="list-group-item d-flex justify-content-between align-items-start mb-2" >

							<img class="{$mostrar}" src="img/esgotado.png" width="65px" height="65px" style="position:absolute; right:0; top:0px">

HTML;

if($mostrar != "") {

echo <<<HTML
								<span class="bora-search-promo {$mostrar_promo}">Promoção</span>

HTML;

}

echo <<<HTML

							<div class="bora-search-content">
								<div class="bora-search-text">

									<div class="me-auto">
										<div class="fw-bold titulo-item" style="font-weight: 600">{$nome}</div>
										<div class="subtitulo-item-menor" style="font-size:10px; font-weight: 380">{$descricao}</div>
										<span class="valor-item-maior">
HTML;

											$query2 = $pdo->query("SELECT * FROM grades where produto = '$id' and tipo_item = 'Variação' and ativo = 'Sim' order by id asc");
											$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
											$total_reg2 = @count($res2);
											if ($total_reg2 > 0) {
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

													if($val_promocional != 0 and $promocao != 'Não'){
														echo 'R$ ' . $val_promocionalF;
														echo '  <del style="color: red">R$ ' . $valorF . ' </del>';

														//$valor = $val_promocional;
													}else{
														echo 'R$ ' . $valorF;
													}

													
												}
											}
echo <<<HTML

										</span>
									</div>

								</div>


								<img class="bora-search-photo" src="{$foto_url}" alt="{$nome}" loading="lazy">



							</div>

						</li>
					</a>

HTML;

		 }
			} 

if ($exibidos === 0) { echo '<li role="status" style="grid-column:1/-1;text-align:center;color:#705445">Nenhum produto disponível encontrado.</li>'; }
echo <<<HTML

		</ol>

HTML;

 ?>

