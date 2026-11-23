<?php
@session_start();
require_once("cabecalho.php");

$sessao = @$_SESSION['sessao_usuario'];
$nome_mesa = @$_SESSION['nome_mesa'];
$pedido_balcao = @$_SESSION['pedido_balcao'];
$pedido_balcao = @$_SESSION['pedido_balcao'];
// Formatando a data e hora para exibir apenas horas e minutos
$horario_aberturaF = date('H:i', strtotime($horario_abertura));
// Formatando a data e hora para exibir apenas horas e minutos
$horario_fechamentoF = date('H:i', strtotime($horario_fechamento));


if (@$_SESSION['nome_mesa'] != '') {
  unset($pedido_balcao);
}


$id_edicao = 0;
if (@$_SESSION['id_edicao'] != "") {
  $id_edicao = $_SESSION['id_edicao'];
}


if($id_edicao == ""){
// VERFICAR SE ESTÁ ABERTO O ESTABELICMENTO

if ($nome_mesa == '' and $pedido_balcao == "") {
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
                    window.location.href = 'index';
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
                    window.location.href = 'index';
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
                    window.location.href = 'index';
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
                    window.location.href = 'index';
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

<style type="text/css">
  body {
    background: #f2f2f2;
  }
</style>

<style>
/* Resumo do carrinho no mesmo padrão do checkout. */
body { background:#fffaf5 !important; color:#493425; }
.main-container { max-width:1360px; margin:0 auto; padding:0 14px; }
.main-container .navbar { background:#fffdf9 !important; border-bottom:1px solid #efddc9; box-shadow:0 3px 16px rgba(94,53,25,.09) !important; }
.main-container .navbar .container-fluid { max-width:1360px; margin:auto; }
.main-container .navbar-brand { display:flex; align-items:center; gap:12px; color:#753d1e; font-weight:750; }
.main-container .navbar-brand a { color:#a85b24; }
.main-container .navbar-brand span { font-size:18px !important; margin-left:0 !important; }
.main-container .btn-warning,.area-pedidos .btn-warning { background:#a85b24 !important; border:1px solid #a85b24 !important; color:#fff !important; font-weight:750; border-radius:11px; padding:10px 17px; }
.main-container .btn-warning:hover,.area-pedidos .btn-warning:hover { background:#87441b !important; }
#listar-itens-carrinho { margin-top:92px !important; margin-bottom:190px !important; padding:0; overflow:visible !important; height:auto !important; display:flex; flex-direction:column; gap:14px; }
#listar-itens-carrinho > li,#listar-itens-carrinho > .list-group-item,#listar-itens-carrinho > div { background:#fff !important; border:1px solid #efddc9 !important; border-radius:16px !important; box-shadow:0 5px 20px rgba(100,58,29,.065) !important; padding:16px !important; }
#listar-itens-carrinho .list-group-item { border-color:#efddc9 !important; }
#listar-itens-carrinho .bg-light,#listar-itens-carrinho .bg-secondary { background:#fff2e4 !important; }
#listar-itens-carrinho button { border-radius:9px; }
#listar-itens-carrinho .btn-warning { background:#a85b24 !important; color:#fff !important; }
#listar-itens-carrinho img { max-width:100%; object-fit:cover; }
.area-pedidos { background:#fffdf9 !important; border-top:1px solid #efddc9 !important; box-shadow:0 -5px 20px rgba(95,54,25,.08); padding:12px max(14px,calc((100vw - 1360px)/2 + 14px)) !important; }
.area-pedidos .total-pedido { background:#fff !important; border:1px solid #efddc9 !important; border-radius:12px !important; padding:12px 16px !important; color:#503827; }
/* Subtotal e ação final usam exatamente a mesma largura e centro. */
.area-pedidos .total-pedido,
.area-pedidos .abaixo {
  box-sizing:border-box !important;
  width:min(100%, 360px) !important;
  max-width:360px !important;
  margin-left:auto !important;
  margin-right:auto !important;
}
.area-pedidos .total-pedido::after { content:""; display:block; clear:both; }
.area-pedidos .abaixo {
  margin-top:18px !important;
  display:flex !important;
  justify-content:center !important;
  align-items:stretch !important;
}
.area-pedidos .abaixo > .btn-lg {
  box-sizing:border-box !important;
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  flex-wrap:nowrap !important;
  gap:10px;
  width:100% !important;
  min-width:0 !important;
  max-width:100% !important;
  margin:0 !important;
  padding:13px 12px !important;
  font-size:clamp(14px, 3.8vw, 20px) !important;
  white-space:nowrap;
  text-align:center;
}
.area-pedidos .abaixo > .btn-lg i { flex-shrink:0; }
@media(max-width:767px) {
 .main-container { padding:0 10px; }
 .main-container .navbar-brand { gap:6px; }
 .main-container .navbar-brand span { font-size:13px !important; }
 .main-container .btn-warning { font-size:12px; padding:8px 10px; }
 #listar-itens-carrinho { margin-top:78px !important; margin-bottom:175px !important; gap:10px; }
 #listar-itens-carrinho > li,#listar-itens-carrinho > .list-group-item,#listar-itens-carrinho > div { padding:12px !important; }
 .area-pedidos { padding:10px !important; }
}
/* Acessibilidade e toque no resumo do pedido. */
#listar-itens-carrinho button,
#listar-itens-carrinho .btn,
.area-pedidos .abaixo .btn-lg{
  min-height:44px;
  touch-action:manipulation;
}
#listar-itens-carrinho button:focus-visible,
#listar-itens-carrinho a:focus-visible,
.area-pedidos a:focus-visible,
.main-container .navbar a:focus-visible{
  outline:3px solid #a85b24;
  outline-offset:3px;
}
#listar-itens-carrinho .tpproduct__price,
#listar-itens-carrinho .preco,
#listar-itens-carrinho .valor{
  font-variant-numeric:tabular-nums;
}
.area-pedidos #total-do-pedido{font-variant-numeric:tabular-nums}
@media(max-width:767px){
  #listar-itens-carrinho{overflow-wrap:anywhere}
  #listar-itens-carrinho .btn{white-space:normal}
  .area-pedidos{padding-bottom:calc(10px + env(safe-area-inset-bottom,0px))!important}
}
</style>

<div class="main-container">

  <nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
    <div class="container-fluid">
      <div class="navbar-brand">
        <a href="site/<?php echo $url_site ?>"><big><i class="bi bi-arrow-left"></i></big></a>
        <span style="margin-left: 15px; font-size:14px">RESUMO DO PEDIDO <?php echo $nome_mesa ?> <?php echo @$_SESSION['pedido_balcao'] ?></span>

      </div>

      <a class="" href="site/<?php echo $url_site ?>">
        <div class="">
          <button type="button" class="btn btn-warning btn-sm">Comprar Mais?</button>
        </div>
      </a>


    </div>
  </nav>



  <ol class="list-group" style="margin-top: 65px; margin-bottom: 95px; overflow: scroll; height:100%; scrollbar-width: thin;"
    id="listar-itens-carrinho">
  </ol>


</div>


<div class="area-pedidos">
  <div class="total-pedido" style="border: solid 1px #ababab; border-radius: 10px;">
    <big>
      <span><b>SUB TOTAL</b></span>
      <span class="direita"> <b>R$ <span id="total-do-pedido"></span></b></span>
    </big>
  </div>


  <div class="d-grid gap-2 mt-4 abaixo">

    <?php if($id_edicao > 0){ ?>
          <a href='' onclick="window.close()" class="btn btn-warning btn-lg">Finalizar Edição <i class="fal fa-long-arrow-right"></i></a>
    <?php }else{ ?>

      <?php if ($nome_mesa == "") { ?>
        <a href='finalizar' class="btn btn-warning btn-lg">Finalizar Pedido <i class="fal fa-long-arrow-right"></i></a>
      <?php } else { ?>
        
      <?php } ?>


  <?php } ?>
  </div>
</div>


</body>

</html>





<!-- Modal -->
<div class="modal fade" id="modalObs" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><span id="nome_item"></span></h5>
        <button type="button" id="btn-fechar-obs" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="form-obs">
        <div class="modal-body">
          <div class="destaque-qtd">
            <b>OBSERVAÇÕES</b>
            <div class="form-group mt-3">
              <textarea maxlength="255" class="form-control" type="text" name="obs" id="obs" placeholder="Deseja adicionar alguma Observação?"></textarea>
            </div>
          </div>

          <input type="hidden" name="id" id="id_obs">
          <br><small>
            <div id="mensagem-obs" align="center"></div>
          </small>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>




<!-- Modal -->
<div class="modal fade" id="modalAdc" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><span id="nome_item_adc"></span></h5>
        <button type="button" id="btn-fechar-adc" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="listar-adc-carrinho">

        </div>

      </div>
    </div>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="modalGrades" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><span id="nome_item_grade"></span></h5>
        <button type="button" id="btn-fechar-grade" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="listar-grade-carrinho">

        </div>

      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
  $(document).ready(function() {
    listarCarrinho()
  });

  function listarCarrinho() {

    $.ajax({
      url: 'js/ajax/listar-itens-carrinho.php',
      method: 'POST',
      data: {},
      dataType: "html",

      success: function(result) {

        $("#listar-itens-carrinho").html(result);

      }
    });
  }
</script>


<script type="text/javascript">
  $("#form-obs").submit(function() {

    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
      url: 'js/ajax/editar-obs-carrinho.php',
      type: 'POST',
      data: formData,

      success: function(mensagem) {
        $('#mensagem-obs').text('');
        $('#mensagem-obs').removeClass()
        if (mensagem.trim() == "Salvo com Sucesso") {
          $('#btn-fechar-obs').click();
          listarCarrinho();

        } else {

          $('#mensagem-obs').addClass('text-danger')
          $('#mensagem-obs').text(mensagem)
        }


      },

      cache: false,
      contentType: false,
      processData: false,

    });

  });
</script>