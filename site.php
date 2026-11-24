<?php
@session_start();
$url_site = $_GET['url'];
$_SESSION['url_site'] = $url_site;
// Identifica que esta requisição é a página pública de uma loja específica.
$configurar_config_site = true;
require_once("cabecalho.php");

 // Verifica se cada seção possui produtos disponíveis para delivery.
$query_disp = $pdo->query("SELECT COUNT(*) FROM categorias c WHERE c.ativo = 'Sim' AND c.delivery = 'Sim' AND c.empresa = '$id_empresa' AND EXISTS (SELECT 1 FROM produtos p WHERE p.categoria = c.id AND p.ativo = 'Sim' AND p.delivery = 'Sim' AND p.empresa = '$id_empresa' AND (p.tem_estoque = 'Não' OR p.estoque > 0))");
$qtd_categorias_disponiveis = (int) $query_disp->fetchColumn();
$query_disp = $pdo->query("SELECT COUNT(*) FROM produtos WHERE ativo = 'Sim' AND combo = 'Sim' AND delivery = 'Sim' AND empresa = '$id_empresa' AND (tem_estoque = 'Não' OR estoque > 0)");
$qtd_combos_disponiveis = (int) $query_disp->fetchColumn();
$query_disp = $pdo->query("SELECT COUNT(*) FROM produtos WHERE ativo = 'Sim' AND promocao = 'Sim' AND delivery = 'Sim' AND empresa = '$id_empresa' AND (tem_estoque = 'Não' OR estoque > 0)");
$qtd_promocoes_disponiveis = (int) $query_disp->fetchColumn();




$id_ab_mesa = "";
$id_mesa = @$_POST['id_mesa'];
$pedido_balcao = @$_POST['pedido_balcao'];

$url_instagram = 'https://www.instagram.com/' . $instagram_sistema . '/';

if ($pedido_balcao != "") {
  unset($_SESSION['id_mesa'], $_SESSION['nome_mesa'], $_SESSION['id_ab_mesa'], $_SESSION['sessao_usuario'], $_SESSION['id_edicao']);
  $_SESSION['pedido_balcao'] = $pedido_balcao;

  
}

@$sessão_balcao = $_SESSION['pedido_balcao'];

if ($sessão_balcao != '') {
  $nome_sistema = 'Pedido Balcão';
}


if ($id_mesa != "") {
  $_SESSION['id_mesa'] = $id_mesa;
  unset($_SESSION['id_edicao']);
}

if (@$_SESSION['id_mesa'] != "") {
  $id_mesa = $_SESSION['id_mesa'];
  unset($_SESSION['id_edicao']);
}



//buscar informações da edição pedido
$id_edicao = @$_POST['id_edicao'];

if ($id_edicao != "") {
  $_SESSION['id_edicao'] = $id_edicao;
  unset($_SESSION['id_mesa'], $_SESSION['nome_mesa'], $_SESSION['id_ab_mesa'], $_SESSION['sessao_usuario']);
}

if (@$_SESSION['id_edicao'] != "") {
  $id_edicao = $_SESSION['id_edicao'];
  unset($_SESSION['id_mesa'], $_SESSION['nome_mesa'], $_SESSION['id_ab_mesa'], $_SESSION['sessao_usuario']);
}

//buscar os dados da mesa
$query2 = $pdo->query("SELECT * FROM mesas where id = '$id_mesa' ");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$nome_mesa = 'Mesa: ' . @$res2[0]['nome'];

if (@$res2[0]['nome'] == "") {
  $_SESSION['nome_mesa'] = '';
} else {
  $_SESSION['nome_mesa'] = $nome_mesa;
}

if($id_mesa > 0){
  $query2 = $pdo->query("SELECT * FROM abertura_mesa where mesa = '$id_mesa' and status = 'Aberta'");
  $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
  if(@count($res2) == 0){
    $pdo->query("INSERT INTO abertura_mesa SET mesa = '$id_mesa', cliente = '$nome_mesa', data = curDate(), horario_abertura = curTime(), garcon = '0', status = 'Aberta'");
    $pdo->query("UPDATE mesas set status = 'Aberta' where id = '$id_mesa'");
  }

  $query2 = $pdo->query("SELECT * FROM abertura_mesa where mesa = '$id_mesa' and status = 'Aberta'");
  $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
  $id_ab_mesa = @$res2[0]['id'];
  $_SESSION['id_ab_mesa'] = $id_ab_mesa;
}




// Fechamentos por data específica: somente delivery, preservando mesas e balcão.
require_once __DIR__ . '/sistema/horarios_especiais.php';
$bc_horario_especial_hoje = null;
if ($id_mesa == "" && $sessão_balcao == "" && (int)$id_empresa > 0) {
  try {
    $bc_horario_especial_hoje = bcHorarioEspecial($pdo, (int)$id_empresa, date('Y-m-d'));
  } catch (PDOException $e) {
    error_log('Horário especial do cardápio: ' . $e->getMessage());
  }
}
$bc_fechamento_excepcional_hoje = false;
if ($id_mesa == "" && $sessão_balcao == "" && (int)$id_empresa > 0) {
  try {
    $bc_fechamento_stmt = $pdo->prepare("SELECT 1 FROM fechamentos_excepcionais WHERE empresa = :empresa AND data_fechamento = :data LIMIT 1");
    $bc_fechamento_stmt->execute([':empresa' => (int)$id_empresa, ':data' => date('Y-m-d')]);
    $bc_fechamento_excepcional_hoje = (bool)$bc_fechamento_stmt->fetchColumn();
  } catch (PDOException $e) {
    error_log('Falha ao consultar fechamentos excepcionais: ' . $e->getMessage());
  }
}

$img = 'aberto.png';


if($id_edicao == ""){

    // 🔴 PRIORIDADE MÁXIMA: DELIVERY PAUSADO
  if (@$pausar_delivery == 'Sim' and $id_mesa == "" and $sessão_balcao == "") {
    $img = 'pausado.png'; // crie essa imagem
  } else {

      if ($status_estabelecimento == "Fechado" and $id_mesa == "" and $sessão_balcao == "") {
        $img = 'fechado.png';
      }

      if ($id_mesa == "" and $sessão_balcao == "") {
        $data = date('Y-m-d');
        if ($bc_fechamento_excepcional_hoje) {
          $img = 'fechado.png';
        }
        //verificar se está aberto hoje
        $diasemana = array("Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado");
        $diasemana_numero = date('w', strtotime($data));
        $dia_procurado = $diasemana[$diasemana_numero];

        //percorrer os dias da semana que ele trabalha
        $query = $pdo->query("SELECT * FROM dias where dia = '$dia_procurado' and empresa = '$id_empresa' ");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        if (@count($res) > 0) {
          $img = 'fechado.png';
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
                $img = 'fechado.png';
              }
            }
          } else {
            $img = 'fechado.png';
          }
        }
        if ($bc_horario_especial_hoje !== null && !$bc_fechamento_excepcional_hoje && $status_estabelecimento != 'Fechado') {
          $img = bcHorarioEspecialAberto($bc_horario_especial_hoje, date('H:i:s')) ? 'aberto.png' : 'fechado.png';
        }
        if ($bc_fechamento_excepcional_hoje) $img = 'fechado.png';
        // Mesma regra usada no selo ABERTO/FECHADO e na finalização.
        try {
          $bc_estado_img = bcEstadoDelivery($pdo, (int)$id_empresa, date('Y-m-d'), date('H:i:s'), (string)@$pausar_delivery, (string)@$status_estabelecimento, (string)$horario_abertura, (string)$horario_fechamento);
          $img = $bc_estado_img['aberto'] ? 'aberto.png' : ($bc_estado_img['motivo'] === 'pausado' ? 'pausado.png' : 'fechado.png');
        } catch (PDOException $e) {
          error_log('Estado visual do delivery: '.$e->getMessage());
          $img = 'fechado.png';
        }
      }

    }

}



?>
<link rel="stylesheet" href="css/font-awesome.css">
<link rel="stylesheet" href="css/templatemo_style.css">
<link rel="stylesheet" href="css/templatemo_misc.css">
<link rel="stylesheet" href="css/flexslider.css">
<link rel="stylesheet" href="css/testimonails-slider.css">

<link rel="stylesheet" href="css/style_cards_index.css">

<script src="js/vendor/modernizr-2.6.1-respond-1.1.0.min.js"></script>

<script src="js/vendor/jquery-1.11.0.min.js"></script>
<script src="js/vendor/jquery.gmap3.min.js"></script>
<script src="js/plugins.js"></script>
<script src="js/main.js"></script>



<style>
/* Cardápio público: identidade consistente com checkout e seleção de produtos. */
body.cardapio-unificado { background:#fffaf5 !important; color:#503827; }
.cardapio-unificado .tpsection__sub-title,.cardapio-unificado .section-title { color:#753d1e !important; font-weight:800 !important; }
.cardapio-unificado .tpsection__sub-title { font-size:clamp(22px,3vw,30px); letter-spacing:-.4px; }
.cardapio-unificado .category-area,.cardapio-unificado .blog-area,.cardapio-unificado .product-area { background:transparent !important; }
.cardapio-unificado .category-area,.cardapio-unificado .blog-area { padding-left:10px; padding-right:10px; }
.cardapio-unificado .category-area .swiper-slide,.cardapio-unificado .blog-area .swiper-slide { border-radius:16px; }
.cardapio-unificado .category-area .swiper-slide > a,.cardapio-unificado .blog-area .swiper-slide > a { border-radius:16px; }
.cardapio-unificado .category-area img,.cardapio-unificado .blog-area img { border-radius:13px; }
.cardapio-unificado .nav-pills .nav-link { border-radius:12px !important; color:#82451f !important; transition:background .2s ease; }
.cardapio-unificado .nav-pills .nav-link.active,.cardapio-unificado .nav-pills .show > .nav-link { background:#a85b24 !important; color:#fff !important; }
.cardapio-unificado .nav-pills .nav-link:hover { background:#fff2e4; }
.cardapio-unificado .btn-primary,.cardapio-unificado .btn-warning,.cardapio-unificado .btn-success { background:#a85b24 !important; border-color:#a85b24 !important; color:#fff !important; border-radius:11px; font-weight:750; }
.cardapio-unificado .btn-outline-primary { color:#a85b24 !important; border-color:#a85b24 !important; border-radius:11px; }
.cardapio-unificado .btn-outline-primary:hover { background:#a85b24 !important; color:#fff !important; }
.cardapio-unificado .blog-area .tpblog__item,.cardapio-unificado .blog-area .tpblog__content,.cardapio-unificado .category-area .tpcategory__item { border-radius:16px; }
.cardapio-unificado .blog-area .tpblog__item { background:#fffdf9; border:1px solid #efddc9; box-shadow:0 5px 20px rgba(100,58,29,.07); overflow:hidden; }
.cardapio-unificado a:hover { color:#a85b24; }
.cardapio-unificado .scroll-top { background:#a85b24 !important; color:#fff !important; border-radius:50%; }
@media(max-width:767px) {
 .cardapio-unificado .tpsection__sub-title { font-size:22px; }
 .cardapio-unificado .category-area,.cardapio-unificado .blog-area { padding-left:6px; padding-right:6px; }
 .cardapio-unificado .nav-pills .nav-link { padding:9px 7px; }
}
</style>
<script>document.body.classList.add('cardapio-unificado');</script>


<style>
/* Ajuste específico dos elementos efetivamente usados na vitrine principal. */
body.cardapio-unificado .container-xxl .section-title span { color:#82451f !important; }
body.cardapio-unificado .container-xxl .nav-pills { border-bottom:1px solid #ead7c3 !important; padding-bottom:12px; }
body.cardapio-unificado .container-xxl .nav-pills .nav-item > a { background:#fffdf9; border:1px solid #efddc9; border-radius:15px !important; padding:12px 10px !important; margin:0 6px !important; min-height:83px; box-shadow:0 4px 15px rgba(100,58,29,.06); }
body.cardapio-unificado .container-xxl .nav-pills .titulo_icones { color:#753d1e !important; font-weight:750; }
body.cardapio-unificado .container-xxl .nav-pills .subtitulo_icones { color:#9a6949 !important; }
body.cardapio-unificado .container-xxl .nav-pills .icone-tapioca-personalizado,
body.cardapio-unificado .container-xxl .nav-pills .icone-combo-personalizado,
body.cardapio-unificado .container-xxl .nav-pills .icone-promocao-personalizado { background:#fff2e4 !important; color:#a85b24 !important; border-radius:13px !important; }
body.cardapio-unificado a[href="/meus-pedidos"].btn { background:#a85b24 !important; border-color:#a85b24 !important; color:#fff !important; border-radius:24px !important; }
body.cardapio-unificado #buscar { background:#fffdf9 !important; border:1px solid #ddc4ad !important; border-radius:12px !important; padding:13px 16px !important; margin-top:0 !important; color:#503827; }
body.cardapio-unificado #buscar:focus { border-color:#a85b24 !important; box-shadow:0 0 0 3px rgba(168,91,36,.1); }
body.cardapio-unificado .category-area .tpsection__sub-title,
body.cardapio-unificado .blog-area .tpsection__sub-title { color:#753d1e !important; }
body.cardapio-unificado .category-area .tpbrandproduct__item,
body.cardapio-unificado .category-area .tpcategory__item,
body.cardapio-unificado .category-area .tpblog__item,
body.cardapio-unificado .blog-area .tpbrandproduct__item { background:#fffdf9 !important; border:1px solid #efddc9 !important; border-radius:16px !important; box-shadow:0 5px 20px rgba(100,58,29,.07) !important; overflow:hidden; }
body.cardapio-unificado .category-area .tpbrandproduct__item a,
body.cardapio-unificado .category-area .tpbrandproduct__item span,
body.cardapio-unificado .category-area .tpbrandproduct__item h5,
body.cardapio-unificado .category-area .tpbrandproduct__item h6 { color:#82451f !important; }
body.cardapio-unificado .category-area .swiper-slide img { border-radius:12px !important; }
body.cardapio-unificado .rodape { background:#fffdf9 !important; border-top:1px solid #efddc9; color:#604837 !important; }
@media(max-width:767px) {
 body.cardapio-unificado .container-xxl .nav-pills .nav-item > a { margin:0 3px !important; padding:9px 5px !important; min-height:75px; }
 body.cardapio-unificado .container-xxl .nav-pills .titulo_icones { font-size:12px !important; }
 body.cardapio-unificado .container-xxl .nav-pills .subtitulo_icones { font-size:10px !important; }
}
</style>

<?php if ($url_site == 'rainhadastapiocas') { ?>
<style>
/* Recorte circular da marca: elimina os cantos pretos sem alterar o arquivo original. */
.main-container > .navbar .navbar-brand .logo-loja-topo {
  aspect-ratio: 1 / 1;
  object-fit: contain;
  border-radius: 50% !important;
  clip-path: circle(49% at 50% 50%);
}

/* Menu de atalhos: impedir que os títulos ultrapassem os cards no celular. */
@media (max-width: 767px) {
  .container-xxl .tab-class > .nav-pills {
    display: flex !important;
    flex-wrap: nowrap !important;
    width: 100% !important;
  }
  .container-xxl .tab-class > .nav-pills > .nav-item {
    flex: 1 1 0 !important;
    width: 0 !important;
    min-width: 0 !important;
  }
  .container-xxl .tab-class > .nav-pills > .nav-item > a {
    box-sizing: border-box;
    width: calc(100% - 4px);
    margin: 0 2px !important;
    padding: 9px 3px !important;
    min-width: 0;
    gap: 3px;
  }
  .container-xxl .tab-class > .nav-pills > .nav-item > a .icone_mobile {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    flex: 0 0 36px;
    font-size: 20px;
  }
  .container-xxl .tab-class > .nav-pills > .nav-item > a > div {
    min-width: 0;
    padding-left: 0 !important;
    overflow-wrap: anywhere;
  }
  .container-xxl .tab-class .titulo_icones {
    display: block;
    font-size: clamp(9px, 2.65vw, 12px) !important;
    line-height: 1.2;
    overflow-wrap: anywhere;
  }
  .container-xxl .tab-class .subtitulo_icones {
    font-size: 10px !important;
    line-height: 1.2;
  }
}
</style>
<?php } ?>


<style>
/* Vitrine principal: seletores diretos, sem depender de classe JS no body. */
body { background:#fffaf5 !important; }
.container-xxl .section-title span[style] { color:#753d1e !important; }
.container-xxl .tab-class > .nav-pills { border-bottom:1px solid #ead7c3 !important; }
.container-xxl .tab-class > .nav-pills .nav-item > a { background:#fffdf9 !important; border:1px solid #efddc9 !important; border-radius:15px !important; padding:12px 10px !important; margin:0 6px !important; box-shadow:0 4px 15px rgba(100,58,29,.07) !important; }
.container-xxl .tab-class .titulo_icones { color:#753d1e !important; font-weight:750 !important; }
.container-xxl .tab-class .subtitulo_icones { color:#9a6949 !important; }
a[href="/meus-pedidos"].btn-warning { background:#a85b24 !important; border-color:#a85b24 !important; color:white !important; }
.padding_input #buscar { background:#fffdf9 !important; border:1px solid #ddc4ad !important; border-radius:12px !important; padding:13px 16px !important; margin-top:0 !important; }
.category-area .tpsection__sub-title,.blog-area .tpsection__sub-title { color:#753d1e !important; font-weight:800 !important; }
.category-area .swiper-slide .tpbrandproduct__item,.category-area .swiper-slide .tpcategory__item,.category-area .swiper-slide .tpblog__item,.blog-area .swiper-slide .tpbrandproduct__item { background:#fffdf9 !important; border:1px solid #efddc9 !important; border-radius:16px !important; box-shadow:0 5px 20px rgba(100,58,29,.07) !important; overflow:hidden; }
.category-area .swiper-slide a,.category-area .swiper-slide h5,.category-area .swiper-slide h6 { color:#82451f !important; }
.rodape { background:#fffdf9 !important; border-top:1px solid #efddc9 !important; }
@media(max-width:767px) { .container-xxl .tab-class > .nav-pills .nav-item > a { padding:9px 5px !important; margin:0 3px !important; } }
</style>


<style>
/* Carrinho lateral da vitrine: mesmo visual do checkout. */
/* Evita corte da primeira letra no texto de carrinho vazio. */
.tpcart__product { padding-left:16px !important; padding-right:16px !important; box-sizing:border-box; }
.tpcart__product p,.tpcart__product span,.tpcart__product div { text-indent:0 !important; }
.tpcart__product p { margin-left:0 !important; overflow:visible !important; }

.tpcartinfo,.tpcart,.tpcart__area { background:#fffdf9 !important; color:#503827 !important; }
.tpcart__close,.tpcart__title { background:#fff2e4 !important; color:#753d1e !important; }
.tpcart__title h4,.tpcart__title h5,.tpcart__title span,.tpcart__title strong { color:#753d1e !important; font-weight:800 !important; }
.tpcart__close button,.tpcart__close a { color:#a85b24 !important; }
.tpcart__product,.tpcart__item { border-color:#efddc9 !important; }
.tpcart__item { background:#fffaf5 !important; border-radius:12px !important; padding:12px !important; margin-bottom:10px !important; }
.tpcart__content-title,.tpcart__content-title a,.tpcart__cart-price,.tpcart__total-price { color:#503827 !important; }
.tpcart__total-price { font-weight:800 !important; }
.tpcart__checkout a,.tpcart__checkout button { background:#a85b24 !important; color:#fff !important; border:1px solid #a85b24 !important; border-radius:14px !important; font-weight:750 !important; }
.tpcart__checkout a:hover,.tpcart__checkout button:hover { background:#87441b !important; border-color:#87441b !important; }
.tpcart__del a,.tpcart__del button { color:#a85b24 !important; }
.tpcart__total { border-top:1px solid #efddc9 !important; }
.cartbody-overlay { background:rgba(53,33,20,.46) !important; }
@media(max-width:575px){.tpcartinfo,.tpcart { max-width:100vw !important; }.tpcart__item{padding:10px !important;}}
</style>

<!-- BOTÃO Scroll-top -->
<button class="scroll-top scroll-to-target mb-25" data-target="html">
  <i class="icon-chevrons-up"></i>
</button>
<!-- Scroll-top-end-->

<style type="text/css">
  .img-aberto {
    animation-duration: 2s;
    animation-name: slidein;
    opacity: 0.9;
    position: fixed;
    bottom: 10px;
    left: 0;
    z-index: 300;
  }

  /* No celular o status já aparece no cabeçalho.
     Ocultar apenas o selo flutuante evita cobrir categorias e rodapé. */
  @media (max-width: 767px) {
    .img-aberto {
      display: none !important;
      animation: none !important;
    }
  }

  @keyframes slidein {
    from {
      margin-left: 200%;
      width: 200%
    }

    to {
      margin-left: 0%;
      width: 70px;
    }
  }

  @media (max-width: 350px) {
    .ocultar_mobile_menor{
      display:none;
    }
  }

  /* Ícones temáticos exclusivos da loja Rainha das Tapiocas */
  .icone-tapioca-personalizado,
  .icone-combo-personalizado,
  .icone-promocao-personalizado {
    width: 50px;
    height: 50px;
    min-width: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #fff3df;
    color: #a85b24;
    font-size: 29px;
  }
  .icone-combo-personalizado {
    background: #fce9d7;
    color: #9b4b20;
  }
  .icone-promocao-personalizado {
    background: #f9e5df;
    color: #a9432b;
  }
/* Carrossel isolado do FlexSlider e do Bootstrap, para desktop e mobile. */
#slider.bc-banner-publico { display:block !important; visibility:visible !important; width:100%; }
#slider .bc-banner-carousel { position:relative; display:block !important; width:min(100%,1400px); margin:0 auto; overflow:hidden; border-radius:12px; }
#slider .bc-banner-slide { display:none !important; width:100%; }
#slider .bc-banner-slide.bc-banner-active { display:block !important; }
#slider .bc-banner-slide img { display:block !important; width:100%; height:auto; object-fit:contain; }
/* Banners apenas no celular; alertas de funcionamento continuam visíveis no desktop. */
@media (min-width:768px) {
  #slider.bc-banner-publico .bc-banner-carousel { display:none !important; }
  #slider.bc-banner-publico { margin-top:80px !important; }
}
@media(max-width:767px) { #slider .bc-banner-carousel { border-radius:0; } }

/* Cabeçalho mobile compacto: mantém a logo legível e elimina espaço vazio antes do banner. */
@media (max-width: 767px) {
  .main-container > .navbar {
    padding-top: 4px !important;
    padding-bottom: 4px !important;
  }
  .main-container > .navbar .navbar-brand .logo-loja-topo {
    display: block !important;
    width: 56px !important;
    height: 56px !important;
    max-width: 56px !important;
    max-height: 56px !important;
    object-fit: contain !important;
  }
  .main-container > .navbar .navbar-brand {
    margin-right: 0 !important;
    padding: 0 !important;
    flex: 0 0 auto;
  }
  .main-container > .navbar .container-fluid {
    flex-wrap: nowrap !important;
    min-height: 60px;
  }
  .main-container > #slider {
    margin-top: 72px !important;
  }
}
</style>


<div class="main-container">

  <nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
    <div class="container-fluid">


      <a class="navbar-brand" href="site/<?php echo $url_site ?>">
        <img src="/media-delivery.php?tipo=logos&amp;arquivo=<?php echo rawurlencode($logo_sistema) ?>" alt="" width="80" class="d-inline-block align-text-top logo-loja-topo">
        <small>
          <?php
          if ($id_mesa == "") {
            //echo '<span class="ocultar_mobile_menor">'.$nome_sistema.'<span>';
          } else {
            echo $nome_mesa;
          }
          ?>
        </small>
      </a>



<?php
$status_delivery_txt = 'ABERTO';
$status_delivery_cor = '#198754';
$status_delivery_icone = 'bi-check-circle';
if ($id_mesa == "" && $sessão_balcao == "") {
  try {
    $bc_estado_delivery = bcEstadoDelivery($pdo, (int)$id_empresa, date('Y-m-d'), date('H:i:s'), (string)@$pausar_delivery, (string)@$status_estabelecimento, (string)$horario_abertura, (string)$horario_fechamento);
    if (!$bc_estado_delivery['aberto']) {
      $status_delivery_txt = $bc_estado_delivery['motivo'] === 'pausado' ? 'PAUSADO' : 'FECHADO';
      $status_delivery_cor = $bc_estado_delivery['motivo'] === 'pausado' ? '#f59e0b' : '#dc3545';
      $status_delivery_icone = $bc_estado_delivery['motivo'] === 'pausado' ? 'bi-pause-circle' : 'bi-x-circle';
    }
  } catch (PDOException $e) {
    error_log('Estado do delivery no cardápio: '.$e->getMessage());
    $status_delivery_txt = 'FECHADO';
    $status_delivery_cor = '#dc3545';
    $status_delivery_icone = 'bi-x-circle';
  }
}
?>

<div class="status-delivery-topo">
  <span style="background: <?php echo $status_delivery_cor ?>;">
    <i class="bi <?php echo $status_delivery_icone ?>"></i>
    <?php echo $status_delivery_txt ?>
  </span>
</div>


      <?php require_once("icone-carrinho.php") ?>



    </div>
  </nav>




  <div id="slider" style="margin-top: 50px;" class="bc-banner-publico">

    <?php if(@$pausar_delivery == 'Sim'){ ?>
  <div class="alert alert-warning">
    <strong>Delivery Pausado</strong><br>
    <?php echo $texto_fechamento != '' ? $texto_fechamento : 'Estamos temporariamente sem receber pedidos.'; ?>
  </div>
<?php } ?>



    <?php if ($id_mesa == "") { ?>

      <?php
      if ($banner_rotativo == 'Sim') {
        $query = $pdo->prepare("SELECT id, foto, categoria FROM banner_rotativo WHERE empresa = :empresa AND ativo = 'Sim' AND (inicio_em IS NULL OR inicio_em <= NOW()) AND (fim_em IS NULL OR fim_em >= NOW()) ORDER BY ordem ASC, id DESC");
        $query->execute([':empresa'=>(int)$id_empresa]);
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $total_reg = @count($res);
        if ($total_reg > 0) {
      ?>


          <div id="bc-banner-carousel" class="bc-banner-carousel" aria-label="Banners promocionais">
            <?php
            foreach ($res as $indice => $banner) {
              $foto = basename((string)$banner['foto']);
              if (!preg_match('/^[a-zA-Z0-9_.-]+$/D', $foto)) continue;
              $categoria = (int)$banner['categoria'];
              $url = '#';
              if ($categoria > 0) {
                $query2 = $pdo->prepare('SELECT url FROM categorias WHERE id = :id AND empresa = :empresa LIMIT 1');
                $query2->execute([':id' => $categoria, ':empresa' => (int)$id_empresa]);
                $categoriaUrl = $query2->fetchColumn();
                if ($categoriaUrl !== false && $categoriaUrl !== '') {
                  $url = 'categoria-' . rawurlencode((string)$categoriaUrl);
                }
              }
            ?>
              <a class="bc-banner-slide<?php echo $indice === 0 ? ' bc-banner-active' : ''; ?>"
                 href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"
                 <?php echo $indice === 0 ? '' : 'aria-hidden="true" tabindex="-1"'; ?>>
                <img src="/media-delivery.php?tipo=banners&amp;arquivo=<?php echo rawurlencode($foto); ?>"
                     alt="Banner promocional" <?php echo $indice === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
              </a>
            <?php } ?>
          </div>
          <script>
          (function () {
            function iniciarBanner() {
              var root = document.getElementById('bc-banner-carousel');
              if (!root || root.dataset.iniciado === '1') return;
              root.dataset.iniciado = '1';
              var slides = Array.prototype.slice.call(root.querySelectorAll('.bc-banner-slide'));
              if (slides.length < 2) return;
              var atual = 0;
              window.setInterval(function () {
                if (document.hidden) return;
                slides[atual].classList.remove('bc-banner-active');
                slides[atual].setAttribute('aria-hidden', 'true');
                slides[atual].setAttribute('tabindex', '-1');
                atual = (atual + 1) % slides.length;
                slides[atual].classList.add('bc-banner-active');
                slides[atual].removeAttribute('aria-hidden');
                slides[atual].removeAttribute('tabindex');
              }, 5000);
            }
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciarBanner);
            else iniciarBanner();
          })();
          </script>

      <?php }
      } ?>
    <?php } ?>
  </div>

  <!-- Menu Start -->
  <div class="container-xxl py-5 margin_top_web" style="margin-bottom: -70px">
    <div class="margem_container">
      <?php if ($id_mesa != "") { ?>
        <div class="text-center wow fadeInUp ocultar-mobile" data-wow-delay="0.1s" style="margin-bottom: 15px">
          <h5 class="section-title ff-secondary text-center text-primary fw-normal"><span style="color:#FEA116">Pedido Mesa</span></h5>

        </div>
      <?php } else if ($sessão_balcao != "") { ?>

        <div class="text-center wow fadeInUp ocultar-mobile" data-wow-delay="0.1s" style="margin-bottom: 15px">
          <h5 class="section-title ff-secondary text-center text-primary fw-normal"><span style="color:#FEA116">Pedido Balcão</span></h5>

        </div>


      <?php } else { ?>


        <div class="text-center wow fadeInUp ocultar-mobile" data-wow-delay="0.1s" style="margin-bottom: 15px">
          <h5 class="section-title ff-secondary text-center text-primary fw-normal"><span style="color:#FEA116">Nosso Cardápio</span></h5>

        </div>


      <?php

      } ?>
      <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">

        <ul class="nav nav-pills d-inline-flex justify-content-center border-bottom mb-5" style="width:100%;">
          <li class="nav-item" style="width:34%;">
            <a class="d-flex align-items-center text-start mx-3 ms-0 pb-3 " href="site/<?php echo $url_site ?>#categoria" onclick="return verificarDisponibilidade(event, 'Categorias', <?php echo $qtd_categorias_disponiveis ?>)">
              <?php if ($url_site == 'rainhadastapiocas') { ?><span class="icone_mobile icone-tapioca-personalizado" aria-label="Tapioca">🫓</span><?php } else { ?><img class="icone_mobile" src="img/acai.png" width="50px" height="50px"><?php } ?>
              <div class="ps-1">
                <small class="text-body titulo_icones">Categorias</small>
                <h6 class="mt-n1 mb-0 subtitulo_icones" style="font-size: 12px">Produtos</h6>
              </div>
            </a>
          </li>
          <li class="nav-item" style="width:34%;">
            <a class="d-flex align-items-center text-start mx-3 pb-3" href="site/<?php echo $url_site ?>#combo" onclick="return verificarDisponibilidade(event, 'Combos', <?php echo $qtd_combos_disponiveis ?>)">
              <?php if ($url_site == 'rainhadastapiocas') { ?><span class="icone_mobile icone-combo-personalizado"><i class="bi bi-basket2-fill"></i></span><?php } else { ?><img class="icone_mobile" src="img/acai2.png" width="50px" height="50px"><?php } ?>
              <div class="ps-1">
                <small class="text-body titulo_icones">Combos</small>
                <h6 class="mt-n1 mb-0 subtitulo_icones" style="font-size: 12px">Diversos</h6>
              </div>
            </a>
          </li>
          <li class="nav-item" style="width:32%;">
            <a class="d-flex align-items-center text-start mx-3 me-0 pb-3" href="site/<?php echo $url_site ?>#promocao" onclick="return verificarDisponibilidade(event, 'Promoções', <?php echo $qtd_promocoes_disponiveis ?>)">
              <?php if ($url_site == 'rainhadastapiocas') { ?><span class="icone_mobile icone-promocao-personalizado"><i class="bi bi-tag-fill"></i></span><?php } else { ?><img class="icone_mobile" src="img/icone3.png" width="50px" height="50px"><?php } ?>
              <div class="ps-1">
                <small class="text-body titulo_icones">Promoções</small>
                <h6 class="mt-n1 mb-0 subtitulo_icones" style="font-size: 12px">Ofertas</h6>
              </div>
            </a>
          </li>
        </ul>

      </div>
    </div>
  </div>

  <div class="text-center" style="display:block!important; visibility:visible!important; opacity:1!important; clear:both; position:relative; z-index:20; margin:10px auto 25px; padding:0 12px;">
    <a href="/meus-pedidos" class="btn btn-warning" style="display:inline-flex!important; align-items:center; gap:8px; visibility:visible!important; opacity:1!important; border-radius:25px; padding:10px 22px; font-weight:700; color:#fff;">
      <i class="bi bi-receipt-cutoff"></i> Meus Pedidos
    </a>
  </div>
  <!-- Menu End -->


<div style=" display:flex; align-items:center; margin-bottom:20px; " class="padding_input">
  <input 
    onkeyup = "buscarProduto()"
    placeholder="Digite o nome do Produto" 
    type="text" 
    name="buscar" 
    id="buscar" 
    style="flex-grow:1; margin-right:10px; border:none; border-bottom:1px solid #b3b3b3; outline:none; margin-top:-10px"
  >
  <i class="icon-search"></i>
</div>


<div id="area_busca" style="display:none; margin-top:-30px">
  
</div>

  <?php
  $query = $pdo->query("SELECT * FROM categorias where ativo = 'Sim' and delivery = 'Sim' and empresa = '$id_empresa' order by posicao asc");
  $res = $query->fetchAll(PDO::FETCH_ASSOC);
  $total_cat = @count($res);

  if ($total_cat > 0) { ?>



    <!-- CATEGORIAS -->
    <section class="category-area ocultar_dasktop" id="categoria">
      <div class="row">
        <div class="col-lg-12 text-center mb-10">
          <h4 class="tpsection__sub-title">Nossas Categorias</h4>
        </div>
      </div>


      <div class="swiper-container category-active">
        <div class="swiper-wrapper">

          <?php
          $query = $pdo->query("SELECT * FROM categorias where ativo = 'Sim' and delivery = 'Sim' and empresa = '$id_empresa' order by posicao asc");
          $res = $query->fetchAll(PDO::FETCH_ASSOC);
          $total_reg = @count($res);
          if ($total_reg > 0) {
            for ($i = 0; $i < $total_reg; $i++) {
              foreach ($res[$i] as $key => $value) {
              }
              $cor = $res[$i]['cor'];
              $foto = $res[$i]['foto'];
              $nome = $res[$i]['nome'];
              $descricao = $res[$i]['descricao'];
              $url = $res[$i]['url'];
              $id = $res[$i]['id'];
              $mais_sabores = $res[$i]['mais_sabores'];
              $delivery = $res[$i]['delivery'];

              if ($id_mesa == "" and $delivery == 'Não' and $sessão_balcao == '') {
                continue;
              }

              $query2 = $pdo->query("SELECT * FROM produtos where categoria = '$id' and ativo = 'Sim' and delivery = 'Sim'");
              $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
              $tem_produto = @count($res2);
              $mostrar = 'ocultar';
              if ($tem_produto > 0) {
                for ($i2 = 0; $i2 < $tem_produto; $i2++) {
                  foreach ($res2[$i2] as $key => $value) {
                  }

                  $id_prod = $res2[$i2]['id'];
                  $estoque = $res2[$i2]['estoque'];
                  $tem_estoque = $res2[$i2]['tem_estoque'];

                  if (($tem_estoque == 'Sim' and $estoque > 0) or ($tem_estoque == 'Não')) {
                    $mostrar = '';
                  }
                }
              } else {
                $mostrar = 'ocultar';
              }

              $descricaoF = mb_strimwidth($descricao, 0, 23, "...");

              if ($mais_sabores == 'Sim') {
                $link_cat =  "categoria-sabores-" . $url;
              } else {
                $link_cat =  "categoria-" . $url;
              }



          ?>




              <a href="<?php echo $link_cat ?>">
                <div class="swiper-slide">
                  <div class="category__item mb-30" style="height: 200px; background: #f5f5f5; box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px">
                    <div class="quadrado">
                      <div class="fix">
                        <img src="/media-delivery.php?tipo=categorias&amp;arquivo=<?php echo rawurlencode($foto) ?>" alt="category-thumb" class="img-fluid w-100 h-100 object-fit-contain">

                      </div>


                    </div>
                    <div class="category__content">
                      <a href="<?php echo $link_cat ?>">
                        <h4 class="category__title"></h4>
                        <h6 style="color: #000000"><b><?php echo $nome ?></b></h6>
                        <p style=" color: #adadad; font-size: 11px; margin-top: -10px"><?php echo $descricaoF ?></p>
                        <p style="color:#474747; font-size: 11px; margin-top: -20px"><?php echo $tem_produto ?> Itens</p>
                      </a>
                    </div>
                  </div>
                </div>
              </a>

          <?php }
          } ?>

        </div>

        <div class="collapse">
          <div class="card card-body">


            <?php
            $query = $pdo->query("SELECT * FROM produtos where ativo = 'Sim' and combo = 'Sim' and delivery = 'Sim' and empresa = '$id_empresa' and (tem_estoque = 'Não' OR estoque > 0)");
            $res = $query->fetchAll(PDO::FETCH_ASSOC);
            $tem_produto = @count($res);
            $mostrar = 'ocultar';
            if ($tem_produto > 0) {
              for ($i = 0; $i < $tem_produto; $i++) {
                foreach ($res[$i] as $key => $value) {
                }
                $id_prod = $res[$i]['id'];
                $foto = $res[$i]['foto'];
                $nome = $res[$i]['nome'];
                $descricao = $res[$i]['descricao'];
                $url = $res[$i]['url'];
                $estoque = $res[$i]['estoque'];
                $tem_estoque = $res[$i]['tem_estoque'];
                $valor = $res[$i]['valor_venda'];
                $valorF = number_format($valor, 2, ',', '.');

                $descricaoF = mb_strimwidth($descricao, 0, 70, "...");

                if ($tem_estoque == 'Sim' and $estoque <= 0) {
                  continue;
                } else {

                  $url_produto = 'produto-' . $url;
                }




                //verificar se o produto tem adicionais
                $query3 = $pdo->query("SELECT * FROM grades where produto = '$id_prod'");
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



                <div class="col-lg-9">
                  <div class="row gx-3">
                    <div class="col-xl-4 col-lg-6">
                      <div class="tpbrandproduct__item d-flex mb-20">
                        <div class="tpbrandproduct__img p-relative">
                          <img src="/media-delivery.php?tipo=produtos&amp;arquivo=<?php echo rawurlencode($foto) ?>" alt="">
                          <div class="tpproduct__info bage tpbrandproduct__bage">

                          </div>
                        </div>
                        <div class="tpbrandproduct__contact">
                          <span class="tpbrandproduct__product-title"><a href="javascript:void(0)"><?php echo $nome ?></a></span>
                          <span class="fst-italic subtitulo_itens" style="color:#474747; font-size: 10px"><?php echo $descricaoF ?></span>
                          <div class="tpproduct__rating mb-5">
                            <a href="javascript:void(0)"><i class="icon-star_outline1"></i></a>
                            <a href="javascript:void(0)"><i class="icon-star_outline1"></i></a>
                            <a href="javascript:void(0)"><i class="icon-star_outline1"></i></a>
                            <a href="javascript:void(0)"><i class="icon-star_outline1"></i></a>
                            <a href="javascript:void(0)"><i class="icon-star_outline1"></i></a>
                          </div>
                          <div class="tpproduct__price">
                            <span><?php echo $valorF ?></span>

                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>


            <?php }
            } ?>


          </div>
        </div>
      </div>
    </section>
    <!-- FIM CATEGORIAS -->


  <?php } ?>






  <?php
  $query = $pdo->query("SELECT * FROM categorias where ativo = 'Sim' and delivery = 'Sim' and empresa = '$id_empresa' order by posicao asc");
  $res = $query->fetchAll(PDO::FETCH_ASSOC);
  $total_cat2 = @count($res);

  if ($total_cat2 > 0) { ?>


    <!-- CATEGORIAS DASKTOP -->
    <section class="brand-product ocultar_mobile" id="categoria">
      <div class="container">
        <div class="sections__wrapper white-bg pl-50 pr-50 pb-40 brand-product">
          <div class="row">
            <div class="col-lg-12 text-center mb-20">
              <div class="tpsection">
                <h4 class="tpsection__sub-title">Nossas Categorias</h4>
              </div>
            </div>
          </div>
          <div class="row">

            <?php
            $query = $pdo->query("SELECT * FROM categorias where ativo = 'Sim' and delivery = 'Sim' and empresa = '$id_empresa' order by posicao asc");
            $res = $query->fetchAll(PDO::FETCH_ASSOC);
            $total_reg = @count($res);
            if ($total_reg > 0) {
              for ($i = 0; $i < $total_reg; $i++) {
                foreach ($res[$i] as $key => $value) {
                }
                $cor = $res[$i]['cor'];
                $foto = $res[$i]['foto'];
                $nome = $res[$i]['nome'];
                $descricao = $res[$i]['descricao'];
                $url = $res[$i]['url'];
                $id = $res[$i]['id'];
                $mais_sabores = $res[$i]['mais_sabores'];
                $delivery = $res[$i]['delivery'];

                if ($id_mesa == "" and $delivery == 'Não' and $sessão_balcao == '') {
                  continue;
                }

                $query2 = $pdo->query("SELECT * FROM produtos where categoria = '$id' and ativo = 'Sim'  and delivery = 'Sim'");
                $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
                $tem_produto = @count($res2);
                $mostrar = 'ocultar';
                if ($tem_produto > 0) {
                  for ($i2 = 0; $i2 < $tem_produto; $i2++) {
                    foreach ($res2[$i2] as $key => $value) {
                    }

                    $id_prod = $res2[$i2]['id'];
                    $estoque = $res2[$i2]['estoque'];
                    $tem_estoque = $res2[$i2]['tem_estoque'];

                    if (($tem_estoque == 'Sim' and $estoque > 0) or ($tem_estoque == 'Não')) {
                      $mostrar = '';
                    }
                  }
                } else {
                  $mostrar = 'ocultar';
                }

                $descricaoF = mb_strimwidth($descricao, 0, 50, "...");

                if ($mais_sabores == 'Sim') {
                  $link_cat =  "categoria-sabores-" . $url;
                } else {
                  $link_cat =  "categoria-" . $url;
                }



            ?>




                <div class="col-xl-4">
                  <a href="<?php echo $link_cat ?>">
                    <div class="tpbrandproduct__item d-flex mb-10" style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;">
                      <div class="imgem-cat-div p-relative">
                        <img class="imagem-cat" src="/media-delivery.php?tipo=categorias&amp;arquivo=<?php echo rawurlencode($foto) ?>" alt="">
                      </div>

                      <div class="tpbrandproduct__contact">
                        <span><?php echo $nome ?></span><br>
                        <span class="" style="color:#474747; font-size: 13px"><?php echo $descricaoF ?></span>

                        <div class="tpproduct__price">
                          <span style="color:#474747; font-size: 12px"><?php echo $tem_produto ?> Itens</span>
                        </div>
                      </div>
                    </div>
                  </a>
                </div>


            <?php }
            } ?>





          </div>
        </div>
      </div>
    </section>
    <!-- FIM CATEGORIAS DASKTOP -->


  <?php } ?>







  <?php
  $query = $pdo->query("SELECT * FROM produtos where ativo = 'Sim' and promocao = 'Sim'  and delivery = 'Sim' and empresa = '$id_empresa' and (tem_estoque = 'Não' OR estoque > 0)");
  $res = $query->fetchAll(PDO::FETCH_ASSOC);
  $tem_produto = @count($res);
  if ($tem_produto > 0) { ?>




    <!-- OFERTAS DA SEMANA -->
    <section class="brand-product" style="margin-top: 20px" id="promocao">
      <div class="container">
        <div class="sections__wrapper white-bg pl-50 pr-50 pb-40 brand-product">
          <div class="row">
            <div class="col-lg-12 text-center mb-20">
              <div class="tpsection">
                <h4 class="tpsection__sub-title">Ofertas da Semana</h4>
              </div>
            </div>
          </div>
          <div class="row">

            <?php
            $query = $pdo->query("SELECT * FROM produtos where ativo = 'Sim' and promocao = 'Sim'  and delivery = 'Sim' and empresa = '$id_empresa' and (tem_estoque = 'Não' OR estoque > 0)");
            $res = $query->fetchAll(PDO::FETCH_ASSOC);
            $tem_produto = @count($res);
            $mostrar = 'ocultar';
            if ($tem_produto > 0) {
              for ($i = 0; $i < $tem_produto; $i++) {
                foreach ($res[$i] as $key => $value) {
                }
                $id_prod = $res[$i]['id'];
                $foto = $res[$i]['foto'];
                $nome = $res[$i]['nome'];
                $descricao = $res[$i]['descricao'];
                $url = $res[$i]['url'];
                $estoque = $res[$i]['estoque'];
                $tem_estoque = $res[$i]['tem_estoque'];
                $valor = $res[$i]['valor_venda'];
                $val_promocional = $res[$i]['val_promocional'];

                $valorF = number_format($valor, 2, ',', '.');
                $val_promocionalF = number_format($val_promocional, 2, ',', '.');

                $descricaoF = mb_strimwidth($descricao, 0, 50, "...");

                $valor_porc = $valor - $val_promocional;

                $valor_porcentagem = ($valor_porc / $valor) * 100;

                $valor_porcentagemF = number_format($valor_porcentagem, 2, ',', '.') . '%';


                if ($tem_estoque == 'Sim' and $estoque <= 0) {
                  continue;
                } else {

                  $url_produto = 'produto-' . $url;
                }



                //verificar se o produto tem adicionais
                $query3 = $pdo->query("SELECT * FROM grades where produto = '$id_prod'");
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

                // Ofertas da Rainha: abrir o configurador da categoria
                // para permitir adicionais, opcionais e proteínas por sabor.
                if ($url_site === 'rainhadastapiocas') {
                  $stmt_cat_oferta = $pdo->prepare("SELECT nome, url FROM categorias WHERE id = ? AND empresa = ? LIMIT 1");
                  $stmt_cat_oferta->execute([(int)$res[$i]['categoria'], $id_empresa]);
                  $cat_oferta = $stmt_cat_oferta->fetch(PDO::FETCH_ASSOC);
                  $nome_cat_oferta = mb_strtolower(trim((string)($cat_oferta['nome'] ?? '')), 'UTF-8');
                  if (in_array($nome_cat_oferta, ['tapiocas doces', 'tapiocas salgadas'], true)
                      && !empty($cat_oferta['url'])) {
                    $url_produto = 'pizza.php?url=' . rawurlencode($cat_oferta['url']) . '&oferta=' . (int)$id_prod;
                  }
                }





            ?>







                <div class="col-xl-4">
                  <div class="tpbrandproduct__item d-flex mb-20" style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;">
                    <a href="<?php echo $url_produto ?>">
                      <div class="imgem-cat-div p-relative">
                        <img src="/media-delivery.php?tipo=produtos&amp;arquivo=<?php echo rawurlencode($foto) ?>" alt="">
                        <div class="tpproduct__info bage tpbrandproduct__bage">
                          <span class="tpproduct__info-discount bage__discount">-<?php echo $valor_porcentagemF ?></span>
                        </div>
                      </div>


                      <div class="tpbrandproduct__contact">
                        <span><?php echo $nome ?></span><br>
                        <div class="tpproduct__rating">
                          <a href="<?php echo $url_produto ?>"><i class="icon-star"></i></a>
                          <a href="<?php echo $url_produto ?>"><i class="icon-star"></i></a>
                          <a href="<?php echo $url_produto ?>"><i class="icon-star"></i></a>
                          <a href="<?php echo $url_produto ?>"><i class="icon-star"></i></a>
                          <a href="<?php echo $url_produto ?>"><i class="icon-star"></i></a>
                        </div>
                        <div class="tpproduct__price">
                          <span>R$ <?php echo $val_promocionalF    ?></span>
                          <del>R$ <?php echo $valorF ?></del>
                        </div>
                      </div>
                    </a>
                  </div>

                </div>


            <?php }
            } ?>





          </div>
        </div>
      </div>
    </section>
    <!-- FIM OFERTA DA SEMANA -->



  <?php } ?>


  <?php
  $query = $pdo->query("SELECT * FROM produtos where ativo = 'Sim' and combo = 'Sim' and delivery = 'Sim' and empresa = '$id_empresa' and (tem_estoque = 'Não' OR estoque > 0)");
  $res = $query->fetchAll(PDO::FETCH_ASSOC);
  $tem_produto = @count($res);
  $mostrar = 'ocultar';
  if ($tem_produto > 0) { ?>


    <!-- APROVEITE NOSSO COMBOS -->
    <section class="blog-area pt-75 pb-30" style="margin-top: 0; margin-bottom: 40px;" id="combo">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <div class="tpsection mb-35">
              <h4 class="tpsection__sub-title">Combos Especiais</h4><p class="bora-combo-subtitle">Combinações irresistíveis para você</p>
            </div>
          </div>
        </div>

        <div class="bora-combo-container">
          <div class="bora-combo-grid">


            <?php
            $query = $pdo->query("SELECT * FROM produtos where ativo = 'Sim' and combo = 'Sim'  and delivery = 'Sim' and empresa = '$id_empresa' and (tem_estoque = 'Não' OR estoque > 0)");
            $res = $query->fetchAll(PDO::FETCH_ASSOC);
            $tem_produto = @count($res);
            $mostrar = 'ocultar';
            if ($tem_produto > 0) {
              for ($i = 0; $i < $tem_produto; $i++) {
                foreach ($res[$i] as $key => $value) {
                }
                $id_prod = $res[$i]['id'];
                $foto = $res[$i]['foto'];
                $nome = $res[$i]['nome'];
                $descricao = $res[$i]['descricao'];
                $url = $res[$i]['url'];
                $estoque = $res[$i]['estoque'];
                $tem_estoque = $res[$i]['tem_estoque'];
                $valor = $res[$i]['valor_venda'];
                $valorF = number_format($valor, 2, ',', '.');

                $promocao = $res[$i]['promocao'];
                $val_promocional = $res[$i]['val_promocional'];

          $val_promocionalF = @number_format($val_promocional, 2, ',', '.');

          if($val_promocional != 0 and $promocao != 'Não'){
            $valorF = $val_promocionalF;
          }

                $descricaoF = mb_strimwidth($descricao, 0, 70, "...");

                if ($tem_estoque == 'Sim' and $estoque <= 0) {
                  continue;
                } else {

                  $url_produto = 'produto-' . $url;
                }




                //verificar se o produto tem adicionais
                $query3 = $pdo->query("SELECT * FROM grades where produto = '$id_prod'");
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


                <article class="bora-combo-card">
                  <a class="bora-combo-photo" href="<?php echo htmlspecialchars($url_produto, ENT_QUOTES, 'UTF-8') ?>">
                    <img src="/media-delivery.php?tipo=produtos&amp;arquivo=<?php echo rawurlencode($foto) ?>" alt="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                    <span class="bora-combo-tag">♛ Combo</span>
                  </a>
                  <div class="bora-combo-body">
                    <h3><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?php echo htmlspecialchars($descricaoF, ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="bora-combo-bottom">
                      <strong>R$ <?php echo $valorF ?></strong>
                      <a class="bora-combo-action" href="<?php echo htmlspecialchars($url_produto, ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-cart3"></i> Escolher combo <span aria-hidden="true">›</span></a>
                    </div>
                  </div>
                </article>
            <?php }
            } else { ?>
              <div class="swiper-slide" style="width:100%;">
                <div class="text-center p-4" style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:10px; margin:0 12px;">
                  <div style="font-size:32px; color:#adb5bd; margin-bottom:8px;"><i class="bi bi-basket"></i></div>
                </div>
              </div>
            <?php } ?>
          </div>

        </div>

      </div>
    </section><br>
    <style>
#combo{scroll-margin-top:100px}
#combo .bora-combo-subtitle{color:#806e60;margin:8px 0 0}
#combo .bora-combo-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,280px),1fr));gap:24px;max-width:1100px;margin:auto}
#combo .bora-combo-card{display:flex;flex-direction:column;min-width:0;max-width:540px;background:#fffaf5;border:1px solid #efd5bb;border-radius:20px;padding:14px;box-shadow:0 8px 24px rgba(100,53,20,.08)}
#combo .bora-combo-photo{display:block;position:relative;aspect-ratio:16/9;overflow:hidden;border-radius:14px;background:#fff}
#combo .bora-combo-photo img{display:block;width:100%;height:100%;object-fit:cover;margin:0;padding:0}
#combo .bora-combo-tag{position:absolute;bottom:10px;left:10px;background:#74380e;color:#fff;padding:5px 12px;border-radius:20px;font-size:13px;font-weight:700}
#combo .bora-combo-body{display:flex;flex-direction:column;flex:1;padding:14px 4px 4px}
#combo .bora-combo-body h3{font-size:21px;color:#71380f;margin:0 0 8px;line-height:1.25}
#combo .bora-combo-body p{font-size:14px;color:#75685e;line-height:1.5;margin:0 0 16px}
#combo .bora-combo-bottom{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:auto}
#combo .bora-combo-bottom strong{font-size:23px;color:#a85b24;white-space:nowrap}
#combo .bora-combo-action{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#a85b24;color:#fff;border-radius:24px;padding:10px 16px;font-weight:700;font-size:14px;text-decoration:none}
#combo .bora-combo-action:hover,#combo .bora-combo-action:focus{background:#783b13;color:#fff}
@media(max-width:600px){#combo .bora-combo-grid{grid-template-columns:1fr;gap:16px}#combo .bora-combo-card{max-width:none}#combo .bora-combo-action{flex:1}#combo .bora-combo-bottom strong{font-size:20px}}
</style>
<!-- FIM APROVEITE NOSSO COMBOS -->


  <?php } ?>

</div>


<?php if ($tem_produto >= 0 and $total_cat2 >= 0 and $total_cat >= 0) {
  
}
?>

<?php if($id_mesa == ""){ ?>
<button id="botao-instalar" style="display: none; position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); padding: 12px 20px; background: #007bff; color: #fff; border: none; border-radius: 8px; z-index: 9999;">Instalar App</button>
<?php } ?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />



<?php if ($id_mesa == "" and $sessão_balcao == "") { ?>
  <footer class="rodape">
    <div class="row">
      <div class="col-md-6">
        <?php if ($endereco_sistema == "") { ?>
          <span class="ocultar-mobile"><?php echo $nome_sistema ?></span>
        <?php } else { ?>
          <span><?php echo $endereco_sistema ?></span>
        <?php } ?>
      </div>
      <div class="col-md-6">
        <span style="margin-left: 15px"><a target="_blank"
            href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whats ?>" class="link-neutro"><i
              class="bi bi-whatsapp text-success"></i> <?php echo $telefone_sistema ?></a></span>
        /
        <span style="margin-left: 15px"><a target="_blank" href="<?php echo $url_instagram ?>"
            class="link-neutro"><i class="bi bi-instagram" style="color:#d11144"></i> @Instagram</a></span>


        <span class="" style="margin-left: 15px"> / <a href="sistema" class="link-neutro "><i
              class="bi bi-lock" style="color:red"></i> Painel Sistema</a></span>
      </div>




  </footer>

<?php 

$img_rodape = 'aberto.png';
$classe_extra = '';

if (@$status_delivery_txt == 'FECHADO') {
  $img_rodape = 'fechado.png';
  $classe_extra = 'status-fechado';
}

if (@$status_delivery_txt == 'PAUSADO') {
  $img_rodape = 'pausado.png';
  $classe_extra = 'status-pausado';
}

if ($img_rodape == "aberto.png" and $mostrar_aberto != "Sim") {
  // não mostra aberto se a configuração estiver desativada
} else {
?>
  <img src="img/<?php echo $img_rodape ?>" width="70px" class="img-aberto <?php echo $classe_extra ?>">
<?php } ?>



<?php } ?>





<div id="aviso-secao-vazia" class="aviso-secao-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="aviso-secao-titulo">
  <div class="aviso-secao-caixa">
    <button type="button" class="aviso-secao-fechar" onclick="fecharAvisoSecao()" aria-label="Fechar">&times;</button>
    <div class="aviso-secao-icone"><i class="bi bi-basket"></i></div>
    <h5 id="aviso-secao-titulo">Nenhum produto disponível</h5>
    <p id="aviso-secao-mensagem">Ainda não temos produtos disponíveis nesta opção. Confira novamente em breve!</p>
    <button type="button" class="btn btn-warning" onclick="fecharAvisoSecao()">Entendi</button>
  </div>
</div>
<style>
.aviso-secao-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:100000;display:flex;align-items:center;justify-content:center;padding:20px}
.aviso-secao-caixa{position:relative;width:100%;max-width:360px;background:#fff;border-radius:16px;padding:28px 22px;text-align:center;box-shadow:0 12px 40px rgba(0,0,0,.25)}
.aviso-secao-fechar{position:absolute;right:12px;top:7px;border:0;background:transparent;font-size:28px;color:#777;line-height:1}
.aviso-secao-icone{font-size:38px;color:#f0a500;margin-bottom:8px}
.aviso-secao-caixa h5{font-weight:700;color:#333;margin-bottom:8px}
.aviso-secao-caixa p{font-size:14px;color:#666;margin-bottom:20px}
</style>
<script>
function verificarDisponibilidade(evento, secao, quantidade) {
  if (Number(quantidade) > 0) return true;
  evento.preventDefault();
  var modal = document.getElementById('aviso-secao-vazia');
  document.getElementById('aviso-secao-titulo').textContent = secao + ' sem produtos';
  document.getElementById('aviso-secao-mensagem').textContent = 'Ainda não temos produtos disponíveis em ' + secao.toLowerCase() + '. Confira novamente em breve!';
  modal.style.display = 'flex';
  return false;
}
function fecharAvisoSecao() { document.getElementById('aviso-secao-vazia').style.display = 'none'; }
document.addEventListener('keydown', function(evento) { if (evento.key === 'Escape') fecharAvisoSecao(); });
document.getElementById('aviso-secao-vazia').addEventListener('click', function(evento) { if (evento.target === this) fecharAvisoSecao(); });
</script>

<!-- Cards de ofertas e combos: apresentacao responsiva sem alterar regras de venda. -->
<style>
@media(max-width:767px){
  #promocao .tpbrandproduct__item,
  #combo .tpbrandproduct__item{
    box-sizing:border-box;
    background:#fffdf9!important;
    border:1px solid #efddc9!important;
    border-radius:16px!important;
    overflow:hidden;
    box-shadow:0 4px 14px rgba(100,58,29,.09)!important;
  }
  #promocao .tpbrandproduct__item>a,
  #combo .tpbrandproduct__item>a{
    display:block;
    width:100%;
    min-width:0;
    text-decoration:none;
  }
  #promocao .tpbrandproduct__contact,
  #combo .tpbrandproduct__contact{
    min-width:0;
    padding:10px 12px;
    overflow-wrap:anywhere;
    line-height:1.4;
  }
  #promocao .tpproduct__price,
  #combo .tpproduct__price{
    display:flex;
    flex-wrap:wrap;
    align-items:baseline;
    gap:5px 10px;
    margin-top:7px;
  }
  #promocao .tpproduct__price del,
  #combo .tpproduct__price del{opacity:.7;font-size:.88em}
  #promocao .imgem-cat-div img,
  #combo .img_combo img{
    display:block;
    max-width:100%;
    object-fit:contain!important;
  }
  #combo .img_combo{background:#fffaf5}
}
</style>
<!-- Ajustes responsivos dos cards de categorias sem alterar produtos ou precos. -->
<style>
@media(max-width:767px){
  .category-area .category__item{
    box-sizing:border-box;
    border:1px solid #efddc9;
    border-radius:16px;
    overflow:hidden;
    background:#fffdf9!important;
    box-shadow:0 4px 14px rgba(100,58,29,.09)!important;
  }
  .category-area .category__item .quadrado,
  .category-area .category__item .fix{overflow:hidden}
  .category-area .category__item img{
    display:block;
    max-width:100%;
    object-fit:contain;
  }
  .category-area .category__content{
    padding:8px 10px;
    overflow-wrap:anywhere;
  }
  .category-area .category__content h6{
    line-height:1.3;
    margin-bottom:6px;
  }
  .category-area .swiper-slide>a,
  .category-area a:has(>.swiper-slide){text-decoration:none}
}
</style>
<!-- Navegacao acessivel entre secoes do cardapio. -->
<style>
html{scroll-behavior:smooth}
#categoria,#promocao,#combo{scroll-margin-top:95px}
#buscar{min-height:44px;width:100%;font-size:16px}
#area_busca[aria-busy="true"]{min-height:36px}
@media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}}
</style>
<script>
(function(){
  var busca=document.getElementById('buscar');
  var resultados=document.getElementById('area_busca');
  if(busca){
    busca.setAttribute('aria-label','Buscar produtos pelo nome');
    busca.setAttribute('autocomplete','off');
    busca.setAttribute('aria-controls','area_busca');
  }
  if(resultados)resultados.setAttribute('aria-live','polite');
})();
</script>
<!-- Barra de carrinho responsiva: reaproveita os totais do carrinho existente. -->
<style>
.bc-carrinho-fixo{position:fixed;left:50%;bottom:calc(12px + env(safe-area-inset-bottom,0px));transform:translateX(-50%);z-index:9990;display:none;align-items:center;justify-content:space-between;gap:12px;width:min(94vw,520px);padding:11px 14px;background:#fffaf5;border:1px solid #ead3bc;border-radius:17px;box-shadow:0 7px 26px rgba(65,34,15,.2);color:#55351f}
.bc-carrinho-fixo.bc-visivel{display:flex}
.bc-carrinho-fixo.bc-confirmado{border-color:#a85b24;box-shadow:0 7px 26px rgba(65,34,15,.2),0 0 0 3px rgba(168,91,36,.15)}
.bc-carrinho-fixo.bc-confirmado #bc-carrinho-qtd{color:#82451f;font-weight:700}
@media(prefers-reduced-motion:no-preference){.bc-carrinho-fixo{transition:border-color .2s,box-shadow .2s}}
.bc-carrinho-fixo__dados{display:flex;flex-direction:column;min-width:0;line-height:1.25}
.bc-carrinho-fixo__dados strong{font-size:15px}
.bc-carrinho-fixo__dados small{font-size:12px;color:#795a43}
.bc-carrinho-fixo__link{display:inline-flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap;padding:11px 14px;background:#a85b24;color:#fff!important;border-radius:12px;font-weight:700;font-size:14px;text-decoration:none}
@media(min-width:992px){.bc-carrinho-fixo{left:auto;right:22px;transform:none;width:360px}}
body:has(.bc-carrinho-fixo.bc-visivel){padding-bottom:85px}
#buscar:focus{border-bottom:2px solid #a85b24!important;box-shadow:0 3px 0 -1px #e9c5a5}
</style>
<div id="bc-carrinho-fixo" class="bc-carrinho-fixo" aria-live="polite">
 <div class="bc-carrinho-fixo__dados"><strong id="bc-carrinho-valor">Ver seu pedido</strong><small id="bc-carrinho-qtd">Itens no carrinho</small></div>
 <a class="bc-carrinho-fixo__link" href="carrinho" aria-label="Abrir resumo do pedido"><i class="bi bi-bag-check"></i> Ver carrinho <i class="bi bi-arrow-right"></i></a>
</div>
<script>
(function(){
 var barra=document.getElementById('bc-carrinho-fixo'),qtd=document.getElementById('bc-carrinho-qtd'),valor=document.getElementById('bc-carrinho-valor');
 if(!barra)return;
 var ultimaQuantidade=null, sequencia=0, temporizadorConfirmacao=null;
 function atualizar(){
   var consulta=++sequencia;
   // O menu mobile pode nao renderizar os contadores do carrinho lateral.
   // Consultar a mesma sessao no servidor, independentemente do menu desktop.
   fetch('js/ajax/resumo-carrinho-flutuante.php',{credentials:'same-origin',cache:'no-store'})
     .then(function(r){if(!r.ok)throw Error('Resumo indisponivel');return r.json();})
     .then(function(d){
       if(consulta!==sequencia)return;
       var n=Math.max(0,Number(d.itens)||0);
       var aumentou=ultimaQuantidade!==null && n>ultimaQuantidade;
       ultimaQuantidade=n;
       barra.classList.toggle('bc-visivel',n>0);
       qtd.textContent=n+(n===1?' item no carrinho':' itens no carrinho');
       valor.textContent='Total: '+Number(d.total||0).toLocaleString('pt-BR',{style:'currency',currency:'BRL'});
       if(aumentou){
         clearTimeout(temporizadorConfirmacao);
         barra.classList.add('bc-confirmado');
         qtd.textContent='Produto adicionado! '+n+(n===1?' item':' itens');
         temporizadorConfirmacao=setTimeout(function(){
           barra.classList.remove('bc-confirmado');
           qtd.textContent=n+(n===1?' item no carrinho':' itens no carrinho');
         },2200);
       }
     }).catch(function(){ /* Mantem o ultimo estado conhecido se a rede falhar. */ });
 }
 atualizar();
 window.addEventListener('pageshow',atualizar);
 document.addEventListener('visibilitychange',function(){if(!document.hidden)atualizar();});
 // Sincroniza quando o cliente retorna ao cardapio ou altera o carrinho.
 document.addEventListener('click',function(e){if(e.target.closest('.tp-cart-toggle, .btn-add-carrinho, [onclick*="adicionarCarrinho"], [onclick*="addCarrinho"]'))setTimeout(atualizar,700);});
 // Mantem o resumo atualizado mesmo quando a adicao e feita por AJAX no mobile.
 window.addEventListener('focus',atualizar);
 window.setInterval(function(){if(!document.hidden)atualizar();},15000);
})();
</script>
<!-- JS here -->
<script src="assets/js/waypoints.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/swiper-bundle.js"></script>
<script src="assets/js/nice-select.js"></script>
<script src="assets/js/slick.js"></script>
<script src="assets/js/magnific-popup.js"></script>
<script src="assets/js/counterup.js"></script>
<script src="assets/js/wow.js"></script>
<script src="assets/js/isotope-pkgd.js"></script>
<script src="assets/js/imagesloaded-pkgd.js"></script>
<script src="assets/js/countdown.js"></script>
<script src="assets/js/ajax-form.js"></script>
<script src="assets/js/meanmenu.js"></script>
<script src="assets/js/main.js"></script>

</body>

</html>


<script type="text/javascript">
  // Busca com atraso curto para evitar uma requisicao a cada tecla.
  let temporizadorBuscaProduto = null;
  let requisicaoBuscaProduto = null;
  function buscarProduto(){
    clearTimeout(temporizadorBuscaProduto);
    const termo = ($('#buscar').val() || '').trim();
    const area = $('#area_busca');
    if (!termo) {
      if (requisicaoBuscaProduto) requisicaoBuscaProduto.abort();
      area.hide().empty();
      return;
    }
    temporizadorBuscaProduto = setTimeout(function(){
      if (requisicaoBuscaProduto) requisicaoBuscaProduto.abort();
      area.show().attr('aria-busy','true');
      requisicaoBuscaProduto = $.ajax({
        url: 'js/ajax/buscar_produtos.php',
        method: 'POST',
        data: { buscar: termo },
        dataType: 'text',
        success: function(html) {
          if (($('#buscar').val() || '').trim() === termo) area.html(html);
        },
        error: function(xhr, status) {
          if (status !== 'abort') area.html('<p role="status">Não foi possível buscar agora. Tente novamente.</p>');
        },
        complete: function(){ area.attr('aria-busy','false'); }
      });
    }, 300);
  }
</script>









<script>
  let deferredPrompt;

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    document.getElementById('botao-instalar').style.display = 'block';

    document.getElementById('botao-instalar').addEventListener('click', async () => {
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      console.log('Resultado da instalação:', outcome);
      deferredPrompt = null;
    });
  });

  window.addEventListener('appinstalled', () => {
    console.log('✅ App instalado!');
  });
</script>


<script>
  document.addEventListener("DOMContentLoaded", function () {
      

    // Registrar Service Worker
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('sw.js')
        .then(reg => console.log('✅ SW registrado:', reg.scope))
        .catch(err => console.error('❌ Erro ao registrar SW:', err));
    }

    // Gerenciar instalação do PWA
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt = e;
      const btn = document.getElementById('botao-instalar');
      if (btn) {
        btn.style.display = 'block';

        btn.addEventListener('click', () => {
          btn.style.display = 'none';
          deferredPrompt.prompt();
          deferredPrompt.userChoice.then((result) => {
            console.log('Resultado da instalação:', result.outcome);
            deferredPrompt = null;
          });
        });
      }
    });

    window.addEventListener('appinstalled', () => {
      console.log('✅ App instalado!');
    });
  });
</script>

 
<script src="app_assets/js/custom.js"></script>

