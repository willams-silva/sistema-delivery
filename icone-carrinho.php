<!-- BOTÃO DO CARRINHO -->
<?php $bc_cart_rainha = ($url_site ?? $_SESSION['url_site'] ?? '') === 'rainhadastapiocas'; ?>
<?php if ($bc_cart_rainha) { ?>
<style>
.bc-cart-rainha{width:auto!important;flex:0 0 auto!important;margin-right:0!important}
.bc-cart-rainha .header__info-cart{margin:0!important}
.bc-cart-rainha .header__info-cart button{display:flex!important;align-items:center!important;justify-content:center!important;gap:9px;background:#a85b24!important;color:#fff!important;border-radius:24px!important;width:auto!important;height:auto!important;min-height:44px!important;padding:10px 14px!important;white-space:nowrap;line-height:1.2!important}
.bc-cart-rainha .header__info-cart button i{display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:8px;width:auto!important;height:auto!important;min-width:0!important;background:transparent!important;border:0!important;border-radius:0!important;padding:0!important;margin:0!important;position:static!important;line-height:1.2!important}
.bc-cart-rainha .header__info-cart button i img{width:19px!important;height:19px!important;display:block!important;filter:brightness(0) invert(1)}
.bc-cart-rainha #total-itens-carrinho{flex:0 0 auto!important;position:static!important;transform:none!important;inset:auto!important;margin:0!important;background:#fff!important;color:#a85b24!important;min-width:20px;height:20px;line-height:20px;border-radius:50%;text-align:center}
@media(min-width:768px){.bc-cart-rainha .header__info-cart button{min-width:172px!important}.bc-cart-rainha .header__info-cart button i::after{content:"Meu carrinho";display:block!important;font-size:14px;font-weight:600;font-style:normal;color:#fff;white-space:nowrap;margin:0!important}}
@media(max-width:767px){.bc-cart-rainha .header__info-cart button{gap:6px;padding:8px 10px!important;border-radius:15px!important;height:44px!important}.bc-cart-rainha #total-itens-carrinho{min-width:18px;height:18px;line-height:18px}}
 .bc-drawer-rainha{background:#fffaf5!important;color:#512c24!important;max-width:100vw;box-sizing:border-box}
.bc-drawer-rainha .tpcart__title{background:#fff0df!important;color:#82451f!important;border-bottom:1px solid #efddc9}
.bc-drawer-rainha .tpcart__close{color:#82451f!important}
.bc-drawer-rainha .tpcart__item{background:#fffdf9!important;border:1px solid #efddc9;border-radius:14px!important;padding:12px 44px 12px 12px!important;gap:10px;box-shadow:0 3px 10px rgba(100,58,29,.05)}
.bc-drawer-rainha .tpcart__img{flex:0 0 60px;width:60px;margin-right:0!important}
.bc-drawer-rainha .tpcart__img img{width:60px;height:60px;object-fit:cover;border-radius:10px}
.bc-drawer-rainha .tpcart__content{min-width:0;overflow-wrap:anywhere}
.bc-drawer-rainha .tpcart__content-title,.bc-drawer-rainha .quantity{color:#512c24!important}
.bc-drawer-rainha .tpcart__cart-price .text-success,.bc-drawer-rainha .heilight-price{color:#a85b24!important;font-weight:700}
.bc-drawer-rainha .tpcart__item .icon-x-circle{color:#a85b24!important;font-size:20px}
.bc-drawer-rainha .tpcart__checkout{background:#fffaf5!important}
.bc-drawer-rainha .tpcart__total-price{border-color:#efddc9!important;color:#512c24!important}
.bc-drawer-rainha .tpcheck-btn{background:#a85b24!important;color:#fff!important;border-radius:12px!important;min-height:48px;display:flex;align-items:center;justify-content:center}
.bc-drawer-rainha .tpcheck-btn:hover{background:#87441b!important}
@media(max-width:767px){.bc-drawer-rainha .tpcart__img{width:48px;flex-basis:48px}.bc-drawer-rainha .tpcart__img img{width:48px;height:48px}}
</style>
<?php } ?>
<div class="col-xl-3 mr-5<?php echo $bc_cart_rainha ? ' bc-cart-rainha' : ''; ?>">
  <div class="header__info d-flex align-items-center">
    <div class="header__info-cart tpcolor__oasis ml-10 tp-cart-toggle">
      <button type="button" aria-label="Meu carrinho"><i><img src="img/icon/cart-1.svg" alt=""></i>
        <span id="total-itens-carrinho"></span>
      </button>
    </div>
  </div>
</div>



<!-- header-cart-start -->
<div class="tpcartinfo tp-cart-info-area p-relative<?php echo $bc_cart_rainha ? ' bc-drawer-rainha' : ''; ?>">
  <button class="tpcart__close"><i class="icon-x"></i></button>
  <div class="tpcart">
    <h4 class="tpcart__title">CARRINHO</h4>
    <div class="tpcart__product" style="display:flex;flex-direction:column;max-height:80vh;">
      <div class="tpcart__product-list" style="flex:1;min-height:0;overflow-y:auto;">
        <ul id="listar-itens-carrinho-icone" style="margin:0;padding:0;list-style:none;">
          <!-- itens do carrinho aqui -->
        </ul>
      </div>
      <div class="tpcart__checkout">
        <div class="tpcart__total-price d-flex justify-content-between align-items-center">
          <span> Subtotal:</span>
          <span class="heilight-price">R$ <span id="total-carrinho-icone"></span></span>
        </div>
        <div class="tpcart__checkout-btn">
          <a class="tpcheck-btn link-neutro2" href="carrinho">VER CARRINHO</a>
        </div>
      </div>
    </div>
  </div>
</div>



<!-- JS here -->

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/nice-select.js"></script>
<script src="assets/js/main.js"></script>



<script type="text/javascript">
  $(document).ready(function() {
    listarCarrinhoIcone()
  });

  function listarCarrinhoIcone() {

    $.ajax({
      url: 'js/ajax/listar-itens-carrinho-icone.php',
      method: 'POST',
      data: {},
      dataType: "html",

      success: function(result) {
        //alert(result)
        $("#listar-itens-carrinho-icone").html(result);

      }
    });
  }
</script>