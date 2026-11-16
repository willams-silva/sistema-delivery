<?php
@session_start();
require_once("cabecalho.php");

// Ícone automático para os adicionais da categoria.
function iconeAdicionalPizza($nome) {
    $nome_normalizado = mb_strtolower(trim((string) $nome), 'UTF-8');
    $nome_normalizado = strtr($nome_normalizado, [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c'
    ]);


    if (strpos($nome_normalizado, 'cebola') !== false) return '🧅';
    if (strpos($nome_normalizado, 'cenoura') !== false) return '🥕';
    if (strpos($nome_normalizado, 'beterraba') !== false) return '🫜';
    if (strpos($nome_normalizado, 'uva passa') !== false || strpos($nome_normalizado, 'uva-passa') !== false) return '🍇';
    if (strpos($nome_normalizado, 'oregano') !== false) return '🌿';
    if (strpos($nome_normalizado, 'manteiga') !== false) return '🧈';
    if (strpos($nome_normalizado, 'camarao') !== false) return '🍤';
    if (strpos($nome_normalizado, 'frango') !== false) return '🍗';
    if (strpos($nome_normalizado, 'queijo coalho') !== false || strpos($nome_normalizado, 'queijo ralado') !== false || strpos($nome_normalizado, 'queijo') !== false) return '🧀';
        if (strpos($nome_normalizado, 'tomate') !== false) return '🍅';
    if (strpos($nome_normalizado, 'passas') !== false) return '🍇';
    if (strpos($nome_normalizado, 'cheddar') !== false || strpos($nome_normalizado, 'catupiry') !== false) return '🧀';
    if (strpos($nome_normalizado, 'barbecue') !== false || strpos($nome_normalizado, 'barbecure') !== false || strpos($nome_normalizado, 'barbecue') !== false) return '🍖';
    if (strpos($nome_normalizado, 'charque') !== false || strpos($nome_normalizado, 'carne de sol') !== false) return '🥩';
    if (strpos($nome_normalizado, 'atum') !== false) return '🐟';
    if (strpos($nome_normalizado, 'dueto') !== false || (strpos($nome_normalizado, 'milho') !== false && strpos($nome_normalizado, 'ervilha') !== false)) return '🌽';
if (strpos($nome_normalizado, 'mel') !== false) return '🍯';
    if (strpos($nome_normalizado, 'banana') !== false) return '🍌';
    if (strpos($nome_normalizado, 'farinha lactea') !== false) return '🥛';
    if (strpos($nome_normalizado, 'acucar') !== false) return '🧂';
    if (strpos($nome_normalizado, 'canela') !== false) return '🍂';
    if (strpos($nome_normalizado, 'chocolate') !== false) return '🍫';
    if (strpos($nome_normalizado, 'morango') !== false) return '🍓';
    if (strpos($nome_normalizado, 'coco') !== false) return '🥥';
    if (strpos($nome_normalizado, 'leite') !== false) return '🥛';
    if (strpos($nome_normalizado, 'doce') !== false) return '🍬';
    return '🥄';
}

$url = $_GET['url'];
$id_usuario = @$_SESSION['id'];
$pedido_balcao = @$_SESSION['pedido_balcao'];
// Formatando a data e hora para exibir apenas horas e minutos
$horario_aberturaF = date('H:i', strtotime($horario_abertura));
// Formatando a data e hora para exibir apenas horas e minutos
$horario_fechamentoF = date('H:i', strtotime($horario_fechamento));


if (@$_SESSION['sessao_usuario'] == "") {
	$sessao = date('Y-m-d-H:i:s-') . rand(0, 1500);
	$_SESSION['sessao_usuario'] = $sessao;
} else {
	$sessao = $_SESSION['sessao_usuario'];
}


$nome_mesa = @$_SESSION['nome_mesa'];
$id_mesa = @$_SESSION['id_mesa'];
$id_ab_mesa = @$_SESSION['id_ab_mesa'];

$texto_botao = 'Adicionar ao Carrinho';

if ($_SESSION['nome_mesa'] != '') {
	unset($_SESSION['pedido_balcao']);
}

if ($nome_mesa != "") {
	$texto_botao = 'Adicionar a Mesa';
}

$query = $pdo->query("SELECT * FROM categorias where url = '$url' and empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
	$nome = $res[0]['nome'];
	$eh_tapiocas_doces = (strtolower(trim($nome)) === 'tapiocas doces');

	// Nas telas pequenas, empilha a área do produto e os adicionais.
	// Regra emitida na própria página para evitar cache antigo do arquivo CSS.
	if ($eh_tapiocas_doces) {
		echo '<style>
			@media (max-width: 767px) {
				li.item-tapioca-doce {
					display: flex !important;
					flex-direction: column !important;
					align-items: stretch !important;
				}
				li.item-tapioca-doce > .row,
				li.item-tapioca-doce > .mt-2 {
					width: 100% !important;
					max-width: 100% !important;
					flex: 0 0 auto !important;
				}
				li.item-tapioca-doce > .mt-2 {
					margin-top: 12px !important;
					padding-left: 0 !important;
				}
			}
		</style>';
	}
	$descricao = $res[0]['descricao'];
	$id = $res[0]['id'];
	$mais_sabores = $res[0]['mais_sabores'];


	$pdo->query("DELETE FROM temp where carrinho = 0 and sessao = '$sessao' and empresa = '$id_empresa' ");


}






if (@$_SESSION['id_edicao'] != "") {
  $id_edicao = $_SESSION['id_edicao'];
}


if(@$id_edicao == ""){
// VERFICAR SE ESTÁ ABERTO O ESTABELICMENTO

if ($nome_mesa == '' and $pedido_balcao == "") {



	// VERIFICAR SE DELIVERY ESTÁ PAUSADO
  if (@$pausar_delivery == "Sim") {

    echo "<script>
        window.onload = function() {
            Swal.fire({
                title: 'Delivery Pausado no momento',
                text: 'Estamos temporariamente sem receber pedidos. Tente novamente em instantes.',
                icon: 'warning',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'site/".$url_site."';
                }
            });
        };
    </script>";

    exit();
  }



  if ($status_estabelecimento == "Fechado") {

    echo "<script>
        window.onload = function() {
            Swal.fire({
                title: 'Delivery Fechado no momento',
                text: '$texto_fechamento',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ação a ser realizada após o clique no botão de confirmação
                    console.log('Botão OK clicado!');
                    // Você pode redirecionar para outra página, se necessário
                    window.location.href = 'site/".$url_site."';
                }
            });
        };
    </script>";



    //echo "<script>window.alert('$texto_fechamento')</script>";
    //echo "<script>window.location='index.php'</script>";
    exit();
  }


  $data = date('Y-m-d');
  //verificar se está aberto hoje
  $diasemana = array("Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado");
  $diasemana_numero = date('w', strtotime($data));
  $dia_procurado = $diasemana[$diasemana_numero];

  //percorrer os dias da semana que ele trabalha
  $query = $pdo->query("SELECT * FROM dias where dia = '$dia_procurado' and empresa = '$id_empresa'");
  $res = $query->fetchAll(PDO::FETCH_ASSOC);
  if (@count($res) > 0) {

    echo "<script>
        window.onload = function() {
            Swal.fire({
                title: 'Delivery Fechado no momento',
                text: 'Estamos Fechados! Não funcionamos Hoje!',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ação a ser realizada após o clique no botão de confirmação
                    console.log('Botão OK clicado!');
                    // Você pode redirecionar para outra página, se necessário
                    window.location.href = 'site/".$url_site."';
                }
            });
        };
    </script>";

    //echo "<script>window.alert('Estamos Fechados! Não funcionamos Hoje!')</script>";
    //echo "<script>window.location='index.php'</script>";
    exit();
  }


  $hora_atual = date('H:i:s');

  //nova verificação de horarios
  $start = strtotime(date('Y-m-d' . $horario_abertura));
  $end = strtotime(date('Y-m-d' . $horario_fechamento));
  $now = time();

  if ($start <= $now && $now <= $end) {
  } else {

    if ($end < $start) {

      if ($now > $start) {
      } else {
        if (
          $now < $end
        ) {
        } else {

          echo "<script>
        window.onload = function() {
            Swal.fire({
                title: 'Delivery Fechado no momento',
                text: '$texto_fechamento_horario $horario_aberturaF às $horario_fechamentoF',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ação a ser realizada após o clique no botão de confirmação
                    console.log('Botão OK clicado!');
                    // Você pode redirecionar para outra página, se necessário
                    window.location.href = 'site/".$url_site."';
                }
            });
        };
    </script>";

          //echo "<script>window.alert('$texto_fechamento_horario')</script>";
          //echo "<script>window.location='index.php'</script>";
          exit();
        }
      }
    } else {


      echo "<script>
        window.onload = function() {
            Swal.fire({
                title: 'Delivery Fechado no momento',
                text: '$texto_fechamento_horario $horario_aberturaF às $horario_fechamentoF',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ação a ser realizada após o clique no botão de confirmação
                    console.log('Botão OK clicado!');
                    // Você pode redirecionar para outra página, se necessário
                    window.location.href = 'site/".$url_site."';
                }
            });
        };
    </script>";



      //echo "<script>window.alert('$texto_fechamento_horario')</scripwindow.location=>";
      //echo "<script>window.location='index.php'</script>";
      exit();
    }
  }
}


}

?>

<div class="main-container">

	<nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
		<div class="container-fluid">
			<div class="navbar-brand">
				<a href="site/<?php echo $url_site ?>"><big><i class="bi bi-arrow-left"></i></big></a>
				<span style="margin-left: 15px; font-size:14px"><?php echo mb_strtoupper($nome) ?>
					<?php echo @$_SESSION['nome_mesa'] ?><?php echo @$_SESSION['pedido_balcao'] ?></span>
			</div>

			<?php require_once("icone-carrinho.php") ?>

		</div>
	</nav>


	<ol class="list-group " style="margin-top: 65px">

		<?php
		//VERIFICAR SE POSSUI VARIAÇÕES NA CATEGORIA
		$query = $pdo->query("SELECT * FROM variacoes_cat where categoria = '$id' order by id asc");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
		if ($total_reg > 0) {

			echo '<ul class="list-group form-check">';
			echo '<span class="titulo-itens-pizza"><span style="margin-left:20px">Escolha o tamanho</span></span>';
			for ($i = 0; $i < $total_reg; $i++) {
				$id_var = $res[$i]['id'];
				$nome_var = $res[$i]['nome'];
				$sigla_var = $res[$i]['sigla'];
				$descricao_var = $res[$i]['descricao'];
				$sabores_var = $res[$i]['sabores'];
		?>

				<li onclick="selecVar('<?php echo $sigla_var ?>', '<?php echo $id_var ?>')"
					class="list-group-item d-flex justify-content-between align-items-center">
					<small>(<?php echo $sigla_var ?>) <?php echo $nome_var ?> / <?php echo $descricao_var ?> / <small>Até
							<?php echo $sabores_var ?> Sabores</small></small>
					<input onchange="selecionarVar('<?php echo $sigla_var ?>', '<?php echo $id_var ?>')" class="form-check-input"
						type="radio" value="<?php echo $sigla_var ?>" name="variacoes" id="variacoes_<?php echo $sigla_var ?>">
				</li>




		<?php }
			echo '</ul>';
		} ?>




		<?php
		//VERIFICAR SE POSSUI VARIAÇÕES NA CATEGORIA
		$query = $pdo->query("SELECT * FROM bordas where categoria = '$id' and ativo = 'Sim' order by id asc");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
		if ($total_reg > 0) {
			echo '<ul class="list-group form-check" style="margin-bottom:15px">';
			echo '<span class="titulo-itens-pizza"><span style="margin-left:20px">Escolha a borda</span></span>';
		?>

			<li onclick="" class="list-group-item d-flex justify-content-between align-items-center">
				<small>Sem Borda + <span class="text-success">R$ 0</span></small>
				<input onchange="selecionarBorda('0')" class="form-check-input" type="radio" name="borda" id="borda_0" checked
					value="0">
			</li>

			<?php
			for ($i = 0; $i < $total_reg; $i++) {
				$id_bor = $res[$i]['id'];
				$nome_bor = $res[$i]['nome'];
				$valor_bor = $res[$i]['valor'];

			?>

				<li onclick="selectBorda('<?php echo $id_bor ?>')"
					class="list-group-item d-flex justify-content-between align-items-center">
					<small><?php echo $nome_bor ?> + <span class="text-success">R$ <?php echo $valor_bor ?></span></small>
					<input onchange="selecionarBorda('<?php echo $id_bor ?>')" class="form-check-input" type="radio" name="borda"
						id="borda_<?php echo $id_bor ?>" value="<?php echo $id_bor ?>">
				</li>



		<?php }
			echo '</ul>';
		} ?>

		<span class="titulo-itens-pizza"><span style="margin-left:20px">Escolha <span id="quant_sabores"> os </span> sabores</span></span>
		<?php
		$query = $pdo->query("SELECT * FROM produtos where categoria = '$id' and ativo = 'Sim'");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
		if ($total_reg > 0) {
			for ($i = 0; $i < $total_reg; $i++) {
				foreach ($res[$i] as $key => $value) {
				}
				$id_produto = $res[$i]['id'];
				$foto = $res[$i]['foto'];
				$nome = $res[$i]['nome'];
				$descricao = $res[$i]['descricao'];
				$url = $res[$i]['url'];
				$estoque = $res[$i]['estoque'];
				$tem_estoque = $res[$i]['tem_estoque'];
				$valor = (float) $res[$i]['valor_venda'];
				$valor_promocional = (float) $res[$i]['val_promocional'];
				$promocao = $res[$i]['promocao'];
				$fator_promocao = 1;
				if ($promocao == 'Sim' && $valor_promocional > 0 && $valor > 0) {
					$fator_promocao = $valor_promocional / $valor;
				}
				$valorF = number_format(($promocao == 'Sim' && $valor_promocional > 0) ? $valor_promocional : $valor, 2, ',', '.');
				$valorOriginalF = number_format($valor, 2, ',', '.');
				$tem_promocao_valida = ($promocao == 'Sim' && $valor_promocional > 0 && $valor_promocional < $valor);

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




		?>


				<li class="list-group-item d-flex justify-content-between align-items-start <?php echo !empty($eh_tapiocas_doces) ? 'item-tapioca-doce' : ''; ?>">

					<img onclick="selectPizzas('<?php echo $id_produto ?>')" class="<?php echo $mostrar ?>" src="img/esgotado.png"
						width="65px" height="65px" style="position:absolute; right:0; top:0px">

					<?php if ($mostrar != "") { ?>
						<img onclick="selectPizzas('<?php echo $id_produto ?>')" class="<?php echo $mostrar_promo ?>"
							src="img/promocao2.png" width="65px" height="65px" style="position:absolute; right:0; top:0px; opacity:80%">
					<?php } ?>

					<div class="row" style="width:100%">
						<div class="col-10">

							<div class="me-auto">
								<div class="fw-bold titulo-item">
									<input class="form-check-input" type="checkbox" value="<?php echo $id_produto ?>"
										id="seletor-<?php echo $id_produto ?>" onchange="selecionar('<?php echo $id_produto ?>')"
										style="margin-top: 5px">
									<small onclick="selectPizzas('<?php echo $id_produto ?>')"> <?php echo $nome ?></small>
								</div>

								<div onclick="selectPizzas('<?php echo $id_produto ?>')" class="subtitulo-item-menor" style="color:#450703">
									<?php echo $descricao ?></div>
								<span onclick="selectPizzas('<?php echo $id_produto ?>')" class="valor-item-maior">
									<?php
									$query2 = $pdo->query("SELECT * FROM variacoes where produto = '$id_produto' and ativo = 'Sim'");
									$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
									$total_reg2 = @count($res2);
									if ($total_reg2 > 0) {
										for ($i2 = 0; $i2 < $total_reg2; $i2++) {
											foreach ($res2[$i2] as $key => $value) {
											}
											$sigla = $res2[$i2]['sigla'];
											$valorvar_original = (float) $res2[$i2]['valor'];
											$valorvar = $valorvar_original;
											if ($fator_promocao < 1) {
												$valorvar = $valorvar_original * $fator_promocao;
											}
											$valorvarF = number_format($valorvar, 2, ',', '.');
											$valorvarOriginalF = number_format($valorvar_original, 2, ',', '.');

											echo '<span style="font-size:10px">(' . $sigla . ') ';
											if ($fator_promocao < 1) {
												echo '<del style="color:#888; font-weight:normal">R$ ' . $valorvarOriginalF . '</del> <strong style="color:#c62828">R$ ' . $valorvarF . '</strong>';
											} else {
												echo 'R$ ' . $valorvarF;
											}
											if ($i2 < $total_reg2 - 1) {
												echo ' / ';
											}
											echo '</span>';
										}
									} else {
										if ($tem_promocao_valida) {
											echo '<del style="color:#888; font-size:11px; font-weight:normal">R$ ' . $valorOriginalF . '</del> <strong style="color:#c62828">R$ ' . $valorF . '</strong>';
										} else {
											echo 'R$ ' . $valorF;
										}
					}
									?>

								</span>


							</div>

						</div>


						<div class="col-2" style="margin-top: 10px;" align="right">
							<img onclick="selectPizzas('<?php echo $id_produto ?>')" class=""
								src="sistema/painel/images/produtos/<?php echo $foto ?>" width="60px" height="60px">
						</div>



					</div>

					<?php if (!empty($eh_tapiocas_doces)) {
						$query_adc_sabor = $pdo->query("SELECT * FROM adicionais_cat where categoria = '$id' and ativo = 'Sim' order by nome asc");
						$adicionais_sabor = $query_adc_sabor->fetchAll(PDO::FETCH_ASSOC);
						if (count($adicionais_sabor) > 0) { ?>
							<div class="mt-2" style="padding-left:8px" onclick="event.stopPropagation();">
								<button type="button" class="btn btn-sm btn-outline-success" onclick="toggleAdicionaisSabor('<?php echo $id_produto ?>')">＋ Adicionais deste sabor</button>
								<div id="adicionais-sabor-<?php echo $id_produto ?>" style="display:none; margin-top:8px; padding:10px; background:#fff8e8; border-radius:8px;">
									<?php foreach ($adicionais_sabor as $adc_sabor) {
										$id_adc_sabor = (int) $adc_sabor['id'];
										$nome_adc_sabor = htmlspecialchars($adc_sabor['nome'], ENT_QUOTES, 'UTF-8');
										$valor_adc_sabor = number_format((float) $adc_sabor['valor'], 2, ',', '.'); ?>
										<div class="d-flex justify-content-between align-items-center" style="padding:5px 0; border-bottom:1px solid #eee;">
											<small><?php echo iconeAdicionalPizza($nome_adc_sabor) ?> <?php echo $nome_adc_sabor ?> <span class="text-success">(R$ <?php echo $valor_adc_sabor ?>)</span></small>
											<div class="d-flex align-items-center">
												<button type="button" class="btn btn-sm btn-link text-danger p-1" onclick="dimAdicionalSabor('<?php echo $id_adc_sabor ?>','<?php echo $id_produto ?>')">−</button>
												<b id="qtd-adc-<?php echo $id_produto ?>-<?php echo $id_adc_sabor ?>" style="min-width:18px;text-align:center">0</b>
												<button type="button" class="btn btn-sm btn-link text-success p-1" onclick="aumAdicionalSabor('<?php echo $id_adc_sabor ?>','<?php echo $id_produto ?>')">＋</button>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>
						<?php }
					} ?>

				</li>


		<?php }
		} ?>








		<?php if (empty($eh_tapiocas_doces)) {
		$query = $pdo->query("SELECT * FROM adicionais_cat where categoria = '$id' and ativo = 'Sim'");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = @count($res);
		if ($total_reg > 0) { ?>

			<span class="titulo-itens-pizza"><span
					style="margin-left:20px">Escolha os Adicionais</span></span>;
			<div style="margin-top: -18px">
				<?php
				for ($i = 0; $i < $total_reg; $i++) {
					foreach ($res[$i] as $key => $value) {
					}
					$id_adc = $res[$i]['id'];
					$nome = $res[$i]['nome'];
					$valor = $res[$i]['valor'];
					$valorF = number_format($valor, 2, ',', '.');

				?>


					<li class="list-group-item d-flex  ">
						<span style="font-size: 12px"><?php echo iconeAdicionalPizza($nome) ?> <?php echo $nome ?> (R$ <?php echo $valorF ?>) </span>

						<span class="direita" style="font-size:14px">

							<span><button onclick="dim('<?php echo $id_adc ?>', '<?php echo $sessao ?>')"
									style="background:transparent; border:none"><i
										class="bi bi-dash-circle-fill text-danger"></i></button></span>
							<span> <b><input id="quantidade_item_<?php echo $id_adc ?>" value="0"
										style="background: transparent; border:none; width:20px; text-align: center"></b> </span>
							<span><button onclick="aum('<?php echo $id_adc ?>', '<?php echo $sessao ?>')"
									style="background:transparent; border:none"><i
										class="bi bi-plus-circle-fill text-success"></i></button></span>

						</span>

					</li>




			<?php }
			} ?>

			</div>
			<?php } ?>


			<div class="destaque-qtd">
				<b>OBSERVAÇÕES</b>
				<div class="form-group mt-3">
					<textarea maxlength="255" class="form-control" type="text" name="obs" id="obs" placeholder="Deseja adicionar alguma Observação?"></textarea>
				</div>
			</div>



			<input type="hidden" id="variacao_sel">
			<input type="hidden" id="borda_sel">
			<input type="hidden" id="ids">
			<input type="hidden" id="quantidade_sabores">



			<div class="d-grid gap-2 col-8 mx-auto mt-4 mb-4">
				<button onclick="addCarrinho()" class="btn btn-warning btn-lg"><?php echo $texto_botao ?> <i class="fal fa-long-arrow-right"></i></button>
			</div>

	</ol>


</div>

</body>

</html>


<script type="text/javascript">
	var requisicoesAdicionaisSabor = [];
	function addCarrinho() {
		if (requisicoesAdicionaisSabor.length > 0) {
			var pendentes = requisicoesAdicionaisSabor.slice();
			requisicoesAdicionaisSabor = [];
			$.when.apply($, pendentes).always(function(){ addCarrinho(); });
			return;
		}
		var variacao = $('#variacao_sel').val();
		var borda = $('#borda_sel').val();
		var mesa = "<?= $id_ab_mesa ?>";

		if (variacao == "") {
			alert("Selecione um Tamanho");
			return;
		}

		var ids = $('#ids').val();
		if (ids == "") {
			alert("Selecione pelo menos um sabor de pizza!");
			return;
		}

		var obs = $('#obs').val();
		var quantidade_sabores = $('#quantidade_sabores').val();

		var id_prod = ids.split("-");

		if (id_prod.length - 1 > quantidade_sabores) {
			alert('Você pode escolher só até ' + quantidade_sabores + ' Sabores!');
			return;
		}

		$.ajax({
			url: 'js/ajax/add-carrinho-pizza.php',
			method: 'POST',
			data: {
				variacao,
				ids,
				obs,
				borda,
				mesa
			},
			dataType: "text",

			success: function(mensagem) {
				alert(mensagem)                
				if (mensagem.trim() == "Inserido com Sucesso") {
					window.location = 'carrinho';
				}

			},

		});
	}

	function selecionarVar(sigla, id) {
		$('#variacao_sel').val(id);

		$.ajax({
			url: 'js/ajax/buscar_variacoes.php',
			method: 'POST',
			data: {
				id
			},
			dataType: "text",

			success: function(mensagem) {
				$('#quant_sabores').text(" até " + mensagem);
				$('#quantidade_sabores').val(mensagem);
			},

		});

	}


	function selecionarBorda(id) {
		var id = $('#borda_' + id).val();
		$('#borda_sel').val(id);

	}


	function selecionar(id) {
		var ids = $('#ids').val();
		if ($('#seletor-' + id).is(":checked") == true) {
			if (ids.indexOf(id + '-') === -1) $('#ids').val(ids + id + '-');
		} else {
			$('#ids').val(ids.replace(id + '-', ''));
			if (typeof ehTapiocasDoces !== 'undefined' && ehTapiocasDoces) {
				$.post('js/ajax/adicionais.php', {id_sabor:id, limpar_sabor:1, ids:$('#ids').val()});
				$('#adicionais-sabor-' + id).hide();
				$('[id^="qtd-adc-' + id + '-"]').text('0');
			}
		}
	}

	var ehTapiocasDoces = <?php echo !empty($eh_tapiocas_doces) ? 'true' : 'false'; ?>;
	function toggleAdicionaisSabor(id) {
		if (!$('#seletor-' + id).is(':checked')) {
			Swal.fire({
				iconHtml: '<span style="font-size:42px;line-height:1;color:#ffb900;">&#128722;</span>',
				customClass: { icon: 'border-0', popup: 'rounded-4', confirmButton: 'btn btn-warning px-4 py-2' },
				buttonsStyling: false,
				title: 'Selecione o sabor',
				text: 'Selecione este sabor primeiro para escolher os adicionais.',
				confirmButtonText: 'ENTENDI',
				showCloseButton: true,
				width: 'min(92vw, 420px)',
				background: '#fff'
			});
			return;
		}
		$('#adicionais-sabor-' + id).stop(true, true).slideToggle(150);
	}
	function atualizarAdicionalSabor(idAdicional, idSabor, quantidade) {
		if (!$('#seletor-' + idSabor).is(':checked')) {
			alert('Selecione este sabor primeiro.');
			return;
		}
		$('#qtd-adc-' + idSabor + '-' + idAdicional).text(quantidade);
		var requisicao = $.ajax({url:'js/ajax/adicionais.php', method:'POST', dataType:'text', data:{id:idAdicional, quantidade:quantidade, id_sabor:idSabor, ids:$('#ids').val()}});
		requisicoesAdicionaisSabor.push(requisicao);
	}
	function aumAdicionalSabor(idAdicional, idSabor) {
		var atual = parseInt($('#qtd-adc-' + idSabor + '-' + idAdicional).text(), 10) || 0;
		atualizarAdicionalSabor(idAdicional, idSabor, atual + 1);
	}
	function dimAdicionalSabor(idAdicional, idSabor) {
		var atual = parseInt($('#qtd-adc-' + idSabor + '-' + idAdicional).text(), 10) || 0;
		if (atual > 0) atualizarAdicionalSabor(idAdicional, idSabor, atual - 1);
	}



	function func_adicional(id) {

		var id = $('#adicional_' + id).val();

		if ($('#adicional_' + id).is(":checked") == true) {
			var acao = 'Sim';
		} else {
			var acao = 'Não';
		}

		adicionar(id, acao, 'Sim');


	}



	function adicionar(id, quantidade) {

		$.ajax({
			url: 'js/ajax/adicionais.php',
			method: 'POST',
			data: {
				id,
				quantidade
			},
			dataType: "text",

			success: function(mensagem) {

				if (mensagem.trim() == "Alterado com Sucesso") {

				}

			},

		});
	}


	function selecVar(sigla, id) {
		$('#variacoes_' + sigla).prop('checked', true);
		selecionarVar(sigla, id)
	}

	function selectBorda(id) {
		$('#borda_' + id).prop('checked', true);
		selecionarBorda(id)
	}

	function selectAdc(id) {

		if ($('#adicional_' + id).is(":checked") == true) {
			var acao = 'Não';
			$('#adicional_' + id).prop('checked', false);
		} else {
			var acao = 'Sim';
			$('#adicional_' + id).prop('checked', true);
		}

		adicionar(id, acao, 'Sim');

	}



	function selectPizzas(id) {

		if ($('#seletor-' + id).is(":checked") == true) {
			$('#seletor-' + id).prop('checked', false);
		} else {
			$('#seletor-' + id).prop('checked', true);
		}

		selecionar(id);

	}
</script>



<script type="text/javascript">
	function aum(id, cat) {
		var quant = $("#quantidade_item_" + id).val();
		var quantidade = parseFloat(quant) + 1;

		$("#quantidade_item_" + id).val(quantidade);
		adicionar(id, quantidade)

	}

	function dim(id, cat) {
		var quant = $("#quantidade_item_" + id).val();
		var quantidade = parseFloat(quant) - 1;

		if (quantidade == -1) {
			var quant = $("#quantidade_item_" + id).val('0');
			return;
		}

		$("#quantidade_item_" + id).val(quantidade);
		adicionar(id, quantidade)



	}
</script>