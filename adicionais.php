<?php
@session_start();
$id_usuario = @$_SESSION['id'];

// Retorna um ícone automático para cada adicional, usando o nome cadastrado.
if (!function_exists('iconeAdicionalDelivery')) {
    function iconeAdicionalDelivery($nome) {
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
        if (strpos($nome_normalizado, ' doce') !== false || strpos($nome_normalizado, 'doce') === 0) return '🍬';
        return '🥄';
    }
}

require_once("cabecalho.php");
$url_completa = $_GET['url'];

$sessao = date('Y-m-d-H:i:s-') . rand(0, 1500);


$nome_mesa = @$_SESSION['nome_mesa'];
$id_ab_mesa = @$_SESSION['id_ab_mesa'];
$id_mesa = @$_SESSION['id_mesa'];
$pedido_balcao = @$_SESSION['pedido_balcao'];
// Formatando a data e hora para exibir apenas horas e minutos
$horario_aberturaF = date('H:i', strtotime($horario_abertura));
// Formatando a data e hora para exibir apenas horas e minutos
$horario_fechamentoF = date('H:i', strtotime($horario_fechamento));


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
    $query = $pdo->query("SELECT * FROM dias where dia = '$dia_procurado' and empresa = '$id_empresa' ");
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


if (@$_SESSION['sessao_usuario'] == "") {
    $_SESSION['sessao_usuario'] = $sessao;
}

$nova_sessao = $_SESSION['sessao_usuario'];

$separar_url = explode("_", $url_completa);
$url = $separar_url[0];

$pdo->query("DELETE FROM temp where carrinho = '0' and sessao = '$nova_sessao' and empresa = '$id_empresa'");

$tem_variação = 'Não';

$data = date('Y-m-d');


$query = $pdo->query("SELECT * FROM produtos where url = '$url' and empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
    $nome = $res[0]['nome'];
    $descricao = $res[0]['descricao'];
    $foto = $res[0]['foto'];
    $id_prod = $res[0]['id'];
    $valor = $res[0]['valor_venda'];
    
    $categoria = $res[0]['categoria'];
    $val_promocional = $res[0]['val_promocional'];
    $promocao = $res[0]['promocao'];

    if($promocao == 'Sim'){
         $valor = $val_promocional;
    }

    $valorF = number_format($valor, 2, ',', '.');


    $query2 = $pdo->query("SELECT * FROM categorias where id = '$categoria'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    $total_reg2 = @count($res2);
    if ($total_reg2 > 0) {
        $url_cat = $res2[0]['url'];
    }


    $valor_total_do_item = $valor;
}
?>
<style>
/* Identidade visual das telas de seleção de produtos, sem alterar lógica. */
body.selecao-cardapio { background:#fffaf5 !important; color:#503827; }
.selecao-cardapio .main-container { max-width:1360px; margin:0 auto; padding:0 14px; }
.selecao-cardapio .navbar { background:#fffdf9 !important; border-bottom:1px solid #efddc9; box-shadow:0 3px 16px rgba(94,53,25,.09) !important; }
.selecao-cardapio .navbar-brand { display:flex; align-items:center; gap:10px; color:#753d1e; font-weight:750; }
.selecao-cardapio .navbar-brand a { color:#a85b24; }
.selecao-cardapio .navbar-brand span { color:#753d1e; font-weight:750; }
.selecao-cardapio .list-group-item { border-color:#efddc9 !important; background:#fff !important; }
.selecao-cardapio .titulo-itens,.selecao-cardapio .titulo-itens-pizza,.selecao-cardapio .titulo-itens-2 { background:#fff2e4 !important; color:#82451f !important; padding:11px 15px !important; font-weight:750; border-radius:10px; }
.selecao-cardapio .list-group { border-radius:14px; }
.selecao-cardapio .list-group-item:first-child { border-radius:14px 14px 0 0; }
.selecao-cardapio .list-group-item:last-child { border-radius:0 0 14px 14px; }
.selecao-cardapio .btn-warning,.selecao-cardapio .btn-success,.selecao-cardapio .btn-primary { background:#a85b24 !important; border-color:#a85b24 !important; color:#fff !important; border-radius:11px; font-weight:750; }
.selecao-cardapio .btn-outline-success { color:#a85b24 !important; border-color:#a85b24 !important; border-radius:10px; }
.selecao-cardapio .btn-outline-success:hover { color:#fff !important; background:#a85b24 !important; }
.selecao-cardapio .form-check-input:checked { background-color:#a85b24; border-color:#a85b24; }
.selecao-cardapio textarea,.selecao-cardapio .form-control { border:1px solid #ddc4ad; border-radius:11px; background:#fffdfb; color:#503827; }
.selecao-cardapio textarea:focus,.selecao-cardapio .form-control:focus { border-color:#a85b24; box-shadow:0 0 0 .2rem rgba(168,91,36,.12); }
.selecao-cardapio .area-pedidos { background:#fffdf9 !important; border-top:1px solid #efddc9; }
.selecao-cardapio .area-pedidos .btn,.selecao-cardapio .abaixo .btn { background:#a85b24 !important; border-color:#a85b24 !important; color:#fff !important; border-radius:12px; }
@media(max-width:767px) {
 .selecao-cardapio .main-container { padding:0 9px; }
 .selecao-cardapio .navbar-brand span { font-size:13px !important; }
 .selecao-cardapio .list-group-item { padding:12px !important; }
 .selecao-cardapio .titulo-itens,.selecao-cardapio .titulo-itens-pizza { padding:10px !important; }
}
</style>
<script>document.body.classList.add('selecao-cardapio');</script>


<div class="main-container">



    <nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
        <div class="container-fluid">
            <div class="navbar-brand">

                <a href="categoria-<?php echo $url_cat ?>"><big><i class="bi bi-arrow-left"></i></big></a>

                <span style="margin-left: 15px; font-size:14px"><?php echo $nome ?> - <?php echo $nome_mesa ?></span>
            </div>

            <?php require_once("icone-carrinho.php") ?>

        </div>
    </nav>

    <div class="destaque" style="border: solid 1px #ababab; border-radius: 10px;">
        <b><?php echo mb_strtoupper($nome); ?></b>
        <div class="col-lg-4 col-md-6">
            <div class="tpbanner__item">
                <img class="ocultar_img" src="sistema/painel/images/produtos/<?php echo $foto ?>" width="70%" height="70%" style="border-radius: 10px">
            </div>

        </div>
    </div>

    <div id="" style='margin-top: 15px'>
        <?php
        $query = $pdo->query("SELECT * FROM grades where produto = '$id_prod' and ativo = 'Sim' order by posicao asc");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $total_reg = @count($res);
        if ($total_reg > 0) {
            for ($i = 0; $i < $total_reg; $i++) {
                $id_grade = $res[$i]['id'];
                $tipo_item = $res[$i]['tipo_item'];
                $valor_item = $res[$i]['valor_item'];
                $texto = $res[$i]['texto'];
                $limite = $res[$i]['limite'];

                if ($tipo_item == 'Variação') {
                    $tem_variação = 'Sim';
                }


        ?>

                <div class="titulo-itens">
                    <input type="hidden" id="qt_<?php echo $id_grade ?>">
                    <?php echo $texto ?> <?php if ($limite > 0) {
                                                echo ' <span style="font-size:13px; color:#000">(até ' . $limite . ' itens!)</span>';
                                            } ?>
                </div>
                <ol class="list-group">

                    <?php
                    $query2 = $pdo->query("SELECT * FROM itens_grade where grade = '$id_grade' and ativo = 'Sim' order by posicao asc");
                    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
                    $total_reg2 = @count($res2);
                    if ($total_reg2 > 0) {
                        for ($i2 = 0; $i2 < $total_reg2; $i2++) {
                            $id_item = $res2[$i2]['id'];
                            $texto_item = $res2[$i2]['texto'];
                            $valor_item_grade = $res2[$i2]['valor'];
                            $limite_item = $res2[$i2]['limite'];
                            $valor_item_gradeF = number_format($valor_item_grade, 2, ',', '.');

                            $ocultar_valor = 'ocultar';
                            if ($valor_item_grade > 0) {
                                $ocultar_valor = '';
                            }

                    ?>


                            <li class="list-group-item">
                                <span style="font-size: 12px"><span class="icone-adicional" aria-hidden="true" style="display:inline-block; width:25px; margin-right:5px; font-size:18px; vertical-align:middle"><?php echo iconeAdicionalDelivery($texto_item) ?></span><?php echo $texto_item ?></span> <span class="valor-item <?php echo $ocultar_valor ?>">(R$ <?php echo $valor_item_gradeF ?>) </span> <?php if ($limite_item > 0) {
                                                                                                                                                                                                        echo ' <span style="font-size:11px; color:red">(até ' . $limite_item . ' itens!)</span>';
                                                                                                                                                                                                    } ?>
                                <?php if ($tipo_item == '1 de Cada') { ?>
                                    <span class="form-switch direita">
                                        <input class="form-check-input" type="checkbox" id="<?php echo $id_item ?>" onchange="itens('<?php echo $id_item ?>', '<?php echo $id_grade ?>', '<?php echo $valor_item_grade ?>', '<?php echo $tipo_item ?>', '1', '<?php echo $valor_item ?>', '<?php echo $limite ?>' )">
                                    </span>
                                <?php } ?>

                                <?php if ($tipo_item == 'Múltiplo') { ?>
                                    <span class="direita" style="font-size:14px">

                                        <span><button onclick="dim('<?php echo $id_grade ?>', '<?php echo $id_item ?>', '<?php echo $valor_item_grade ?>', '<?php echo $tipo_item ?>', '<?php echo $valor_item ?>', '<?php echo $limite_item ?>', '<?php echo $limite ?>')" style="background:transparent; border:none"><i class="bi bi-dash-circle-fill text-danger"></i></button></span>
                                        <span> <b><input id="quantidade_item_<?php echo $id_item ?>" value="0" style="background: transparent; border:none; width:20px; text-align: center"></b> </span>
                                        <span><button onclick="aum('<?php echo $id_grade ?>', '<?php echo $id_item ?>', '<?php echo $valor_item_grade ?>', '<?php echo $tipo_item ?>', '<?php echo $valor_item ?>', '<?php echo $limite_item ?>', '<?php echo $limite ?>')" style="background:transparent; border:none"><i class="bi bi-plus-circle-fill text-success"></i></button></span>

                                    </span>


                                <?php } ?>


                                <?php if ($tipo_item == 'Único' || $tipo_item == 'Variação') { ?>
                                    <span class="direita">

                                        <input class="form-check-input" type="radio" value="Sim" name="<?php echo $id_grade ?>" id="<?php echo $id_grade ?>" onchange="itens('<?php echo $id_item ?>', '<?php echo $id_grade ?>', '<?php echo $valor_item_grade ?>', '<?php echo $tipo_item ?>', '1', '<?php echo $valor_item ?>' )">

                                    </span>
                                <?php } ?>


                            </li>


                <?php }
                    }
                } ?>


    </div>
<?php




            $valor_total_pedidoF = @number_format($valor_total_do_item, 2, ',', '.');
        } ?>

<div class="destaque-qtd" style="border:  solid 1px #ababab; border-radius: 10px; display:none">
    <b>QUANTIDADE</b>
    <span class="direita">
        <big>
            <span><button style="background:transparent; border:none" onclick="diminuirQuant()"><i class="bi bi-dash-circle-fill text-danger"></i></button></span>
            <span> <b><span id="quant"></span></b> </span>
            <span><button style="background:transparent; border:none" onclick="aumentarQuant()"><i class="bi bi-plus-circle-fill text-success"></i></button></span>
        </big>
    </span>
</div>

<div class="destaque-qtd" style="border:  solid 1px #ababab; border-radius: 10px;">
    <b>Subtotal</b>
    <span class="direita">
        <input type="hidden" id="valor_total_produto" value="<?php echo $valor_total_do_item ?>">
        <input type="hidden" id="valor_total_input" value="<?php echo $valor_total_do_item ?>">
        <b>R$ <span id="valor_item_quantF"><?php echo $valor_total_pedidoF ?></span></b>
    </span>
</div>

<div class="total">

</div>



<?php

$texto_botao = 'Adicionar ao Carrinho';
$funcao_botao = 'finalizarItem()';


if (@$_SESSION['sessao_usuario'] == "") {
    $sessao = date('Y-m-d-H:i:s-') . rand(0, 1500);
    $_SESSION['sessao_usuario'] = $sessao;
} else {
    $sessao = $_SESSION['sessao_usuario'];
}



if (@$_SESSION['id'] != "") {
    $id_usuario = $_SESSION['id'];
} else {
    $id_usuario = '';
}

$query = $pdo->query("SELECT * FROM carrinho where sessao = '$sessao' and empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
    $id_cliente = $res[0]['cliente'];
    $mesa_carrinho = $res[0]['mesa'];
    $nome_cli_pedido = $res[0]['nome_cliente'];


    $query = $pdo->query("SELECT * FROM clientes where id = '$id_cliente'");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    if (@count($res) > 0) {
        $nome_cliente = $res[0]['nome'];
        $telefone_cliente = $res[0]['telefone'];
    } else {
        $nome_cliente = $nome_cli_pedido;
        $telefone_cliente = '';
    }
}

$separar_url = explode("_", $url_completa);
$url = $separar_url[0];


$query = $pdo->query("SELECT * FROM produtos where url = '$url' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
    $nome = $res[0]['nome'];
    $descricao = $res[0]['descricao'];
    $foto = $res[0]['foto'];
    $id_prod = $res[0]['id'];
    $valor = $res[0]['valor_venda'];
    $valorF = number_format($valor, 2, ',', '.');
    $categoria = $res[0]['categoria'];
}

$query = $pdo->query("SELECT * FROM categorias where id = '$categoria'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
    $url_cat = $res[0]['url'];
}



if (@$id_mesa == "" and $pedido_balcao == "") {

    if ($status_estabelecimento == "Fechado") {
        echo "<script>window.alert('$texto_fechamento')</script>";
        echo "<script>window.location='site/".$url_site."'</script>";
        exit();
    }


    $data = date('Y-m-d');
    //verificar se está aberto hoje
    $diasemana = array("Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado");
    $diasemana_numero = date('w', strtotime($data));
    $dia_procurado = $diasemana[$diasemana_numero];

    //percorrer os dias da semana que ele trabalha
    $query = $pdo->query("SELECT * FROM dias where dia = '$dia_procurado' and empresa = '$id_empresa' ");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    if (@count($res) > 0) {
        echo "<script>window.alert('Estamos Fechados! Não funcionamos Hoje!')</script>";
        echo "<script>window.location='site/".$url_site."'</script>";
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
                if ($now < $end) {
                } else {
                    echo "<script>window.alert('$texto_fechamento_horario')</script>";
                    echo "<script>window.location='site/".$url_site."'</script>";
                    exit();
                }
            }
        } else {
            echo "<script>window.alert('$texto_fechamento_horario')</script>";
            echo "<script>window.location='site/".$url_site."'</script>";
            exit();
        }
    }
}


?>

<hr>





<input type="hidden" id="quantidade" value="1">



<div class="destaque-qtd" style="border:  solid 1px #ababab; border-radius: 10px;">
    <b>OBSERVAÇÕES</b>
    <div class="form-group mt-3">
        <textarea maxlength="255" class="form-control" type="text" name="obs" id="obs" placeholder="Deseja adicionar alguma Observação?"></textarea>
    </div>
</div>



</div>


<div class="d-grid gap-2 col-8 mx-auto mt-3">
    <button onclick="addCarrinho()" class="btn btn-warning btn-lg">Adicionar ao Pedido <i class="fal fa-long-arrow-right"></i></button>
</div>

<br>

</body>

</html>


<script type="text/javascript">
    $(document).ready(function() {

    });

    function itens(id, grade, valor, tipo, quantidade, tipagem, limite_grade) {


        var marcado = $("#" + grade).val();
        var qtd_marcada = $("#qt_" + grade).val();
        if (qtd_marcada == "") {
            qtd_marcada = 0;
        }

        if (tipo == '1 de Cada' && limite_grade > 0) {
            if ($('#' + id).is(":checked") == true) {
                qtd_marcada_final = parseFloat(qtd_marcada) + 1;
            } else {
                qtd_marcada_final = parseFloat(qtd_marcada) - 1;
            }

            if (qtd_marcada_final > limite_grade) {
                alert('O limite para essa escolha é de ' + limite_grade + ' Itens!');
                $('#' + id).prop("checked", false);
                return;
            } else {
                $("#qt_" + grade).val(qtd_marcada_final);
            }
        }


        $.ajax({
            url: 'js/ajax/adicionar_item.php',
            method: 'POST',
            data: {
                id,
                grade,
                valor,
                tipo,
                marcado,
                quantidade,
                tipagem
            },
            dataType: "text",

            success: function(mensagem) {
                //alert(mensagem)
                if (mensagem.trim() == "Alterado com Sucesso") {
                    listarItens();
                }

            },

        });
    }




    function listarItens() {


        var id = '<?= $id_prod ?>';

        $.ajax({
            url: 'js/ajax/listar_itens_grade.php',
            method: 'POST',
            data: {
                id
            },
            dataType: "html",

            success: function(result) {
                //alert(result)
                var split = result.split('*');

                $("#valor_total_input").val(split[0]);
                $("#valor_item_quantF").text(split[1]);
                $("#valor_total_produto").val(split[2]);
            }
        });
    }
</script>




<script type="text/javascript">
    function aum(grade, item, valor, tipo, tipagem, limite, limite_grade) {
        var quant = $("#quantidade_item_" + item).val();
        var quantidade = parseFloat(quant) + 1;


        if (limite > 0) {
            if (quantidade > limite) {
                alert("A quantidade de itens não pode ser maior que " + limite)
                return;
            }
        }




        var qt_grade = $("#qt_" + grade).val();
        if (qt_grade == "") {
            qt_grade = 0;
        }
        qt_grade = parseFloat(qt_grade) + 1;


        if (limite_grade > 0) {
            if (qt_grade > limite_grade) {
                alert("A quantidade de itens selecionados não pode ser maior que " + limite_grade)
                return;
            }
        }
        $("#qt_" + grade).val(qt_grade);


        $("#quantidade_item_" + item).val(quantidade);



        itens(item, grade, valor, tipo, quantidade, tipagem);
    }

    function dim(grade, item, valor, tipo, tipagem, limite, limite_grade) {
        var quant = $("#quantidade_item_" + item).val();

        var quantidade = parseFloat(quant) - 1;

        if (quantidade < 0) {
            alert('Insira um valor igual ou maior que zero');
            return;
        }


        var qt_grade = $("#qt_" + grade).val();
        if (qt_grade == "") {
            qt_grade = 0;
        }
        qt_grade = parseFloat(qt_grade) - 1;
        $("#qt_" + grade).val(qt_grade);


        $("#quantidade_item_" + item).val(quantidade);



        itens(item, grade, valor, tipo, quantidade, tipagem);
    }
</script>




<script type="text/javascript">
    $(document).ready(function() {
        var quant = $("#quantidade").val();
        $("#quant").text(quant);

    });

    function aumentarQuant() {

        var quant = $("#quantidade").val();
        var novo_valor = parseInt(quant) + parseInt(1);
        $("#quant").text(novo_valor)
        $("#quantidade").val(novo_valor);


        var total_quant = parseInt(quant) + parseInt(1);
        var total_inicial = $("#valor_total_input").val();
        var total_it = parseFloat(total_inicial) * parseFloat(total_quant);
        $("#total_item").text(total_it.toFixed(2));
        $("#total_item_input").val(total_it);

        $("#valor_item_quantF").text(total_it.toFixed(2));
    }

    function diminuirQuant() {
        var quant = $("#quantidade").val();
        if (quant > 1) {
            var novo_valor = parseInt(quant) - parseInt(1);
            $("#quant").text(novo_valor)
            $("#quantidade").val(novo_valor)

            var total_quant = parseInt(quant) - parseInt(1);
            var total_inicial = $("#valor_total_input").val();
            var total_it = parseFloat(total_inicial) * parseFloat(total_quant);
            $("#total_item").text(total_it.toFixed(2));
            $("#total_item_input").val(total_it);

            $("#valor_item_quantF").text(total_it.toFixed(2));
        }

    }
</script>




<script type="text/javascript">
    function addCarrinho() {

        var quantidade = $('#quantidade').val();
        var total_item = $('#valor_total_input').val();
        var produto = "<?= $id_prod ?>";
        var obs = $('#obs').val();
        var tem_var = "<?= $tem_variação ?>";
        var mesa = "<?= $id_ab_mesa ?>";

        var valor_produto = $('#valor_total_produto').val();

        if (total_item <= 0 && valor_produto <= 0) {
            alert("O valor do Pedido é zero, selecione as opções!");
            return;
        }

        if (valor_produto <= 0 && tem_var == 'Sim') {
            alert("Selecione a Variação do Item");
            return;
        }



        $.ajax({
            url: 'js/ajax/add-carrinho.php',
            method: 'POST',
            data: {
                quantidade,
                total_item,
                produto,
                obs,
                valor_produto,
                mesa
            },
            dataType: "text",

            success: function(mensagem) {
                if (mensagem.trim() == "Inserido com Sucesso") {
                    window.location = 'carrinho';
                } else {
                    alert(mensagem)
                }

            },

        });
    }
</script>