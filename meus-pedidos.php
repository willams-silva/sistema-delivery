<?php
@session_start();
if (empty($_SESSION['url_site'])) {
  $url_site = $_GET['url'] ?? '';
  if ($url_site !== '') $_SESSION['url_site'] = $url_site;
}
require_once("cabecalho.php");

// O logout precisa ser processado antes de calcular o estado de autenticação.
if (isset($_GET['sair'])) {
  unset(
    $_SESSION['meus_pedidos_telefone_autenticado'],
    $_SESSION['meus_pedidos_empresa_autenticada'],
    $_SESSION['meus_pedidos_autenticado_em']
  );
  header('Location: /meus-pedidos');
  exit;
}

$telefone_autenticado = $_SESSION['meus_pedidos_telefone_autenticado'] ?? '';
$empresa_autenticada = (int)($_SESSION['meus_pedidos_empresa_autenticada'] ?? 0);
$autenticado_em = (int)($_SESSION['meus_pedidos_autenticado_em'] ?? 0);
$autenticado = (
  $telefone_autenticado !== '' &&
  $empresa_autenticada === (int)$id_empresa &&
  $autenticado_em > 0 &&
  (time() - $autenticado_em <= 1800)
);
?>
<style>
.mp-page{min-height:calc(100vh - 70px);background:#f7f7f7;padding:26px 14px 60px}
.mp-container{max-width:960px;margin:0 auto}
.mp-topbar{background:#fff;border-radius:16px;padding:13px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px;box-shadow:0 3px 15px rgba(0,0,0,.06);margin-bottom:26px}
.mp-brand{display:flex;align-items:center;gap:12px;color:#333;text-decoration:none;font-weight:700}
.mp-brand img{max-width:76px;max-height:52px;object-fit:contain}
.mp-brand span{font-size:15px}
.mp-back{color:#555;text-decoration:none;font-size:13px;font-weight:600}
.mp-hero{text-align:center;padding:18px 10px 24px}
.mp-hero-icon{width:62px;height:62px;margin:0 auto 12px;border-radius:18px;background:#fff0d8;color:#fea116;display:flex;align-items:center;justify-content:center;font-size:27px}
.mp-hero h1{font-size:28px;font-weight:800;color:#252525;margin:0 0 7px}
.mp-hero p{color:#777;margin:0;font-size:14px}
.mp-panel{background:#fff;border-radius:18px;padding:22px;box-shadow:0 5px 24px rgba(0,0,0,.06);margin:0 auto 17px}
.mp-login{max-width:480px}
.mp-label{font-size:13px;font-weight:700;color:#444;margin-bottom:7px;display:block}
.mp-input{width:100%;padding:13px 14px;border:1px solid #e4e4e4;border-radius:10px;margin-bottom:14px;outline:none;transition:.2s}
.mp-input:focus{border-color:#fea116;box-shadow:0 0 0 3px rgba(254,161,22,.13)}
.mp-btn{background:#fea116;color:#fff;border:0;border-radius:10px;padding:13px 18px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;transition:.2s}
.mp-btn:hover{background:#ed9208;color:#fff;transform:translateY(-1px)}
.mp-btn:disabled{opacity:.65;cursor:wait;transform:none}
.mp-btn-outline{background:#fff;color:#666;border:1px solid #e2e2e2;padding:9px 14px;font-size:13px}
.mp-btn-outline:hover{background:#f8f8f8;color:#333}
.mp-muted{color:#888;font-size:13px}
.mp-order{background:#fff;border-radius:16px;padding:20px;margin:14px 0;box-shadow:0 4px 18px rgba(0,0,0,.05);border:1px solid #f0f0f0}
.mp-order-head,.mp-line,.mp-total{display:flex;justify-content:space-between;align-items:center;gap:12px}
.mp-order-title{font-size:16px;font-weight:800;color:#292929}
.mp-status{display:inline-block;border-radius:30px;padding:6px 12px;background:#fff2d9;color:#99600a;font-size:11px;font-weight:800;text-transform:uppercase}
.mp-separator{border:0;border-top:1px solid #eee;margin:15px 0}
.mp-line{font-size:13px;color:#555;padding:4px 0}
.mp-total{font-size:15px;color:#222;font-weight:800}
.mp-footer-link{display:inline-block;color:#777;text-decoration:none;font-size:13px;font-weight:600;margin-top:22px}
.mp-footer-link:hover{color:#fea116}
.mp-message{margin-top:10px;font-size:13px;color:#777}
@media(max-width:575px){.mp-page{padding:14px 10px 40px}.mp-topbar{padding:10px 12px}.mp-brand span{font-size:13px}.mp-hero h1{font-size:24px}.mp-panel,.mp-order{padding:17px}.mp-order-head{align-items:flex-start}}

/* Identidade visual do checkout: creme, caramelo e marrom. */
.mp-page{background:#fffaf5;color:#503827}
.mp-topbar{background:#fffdf9;border:1px solid #efddc9;box-shadow:0 4px 18px rgba(100,58,29,.07)}
.mp-brand,.mp-brand:hover{color:#753d1e}
.mp-back,.mp-back:hover{color:#a85b24}
.mp-hero-icon{background:#fff2e4;color:#a85b24}
.mp-hero h1{color:#753d1e}
.mp-hero p,.mp-muted,.mp-message{color:#80634e}
.mp-panel,.mp-order{background:#fffdf9;border:1px solid #efddc9;box-shadow:0 6px 22px rgba(100,58,29,.07)}
.mp-label,.mp-order-title,.mp-total{color:#503827}
.mp-input{background:#fff;border-color:#ddc4ad;color:#503827}
.mp-input:focus{border-color:#a85b24;box-shadow:0 0 0 3px rgba(168,91,36,.12)}
.mp-btn{background:#a85b24;color:#fff;border-radius:11px}
.mp-btn:hover{background:#87441b;color:#fff}
.mp-btn-outline{background:#fff2e4;color:#82451f;border:1px solid #e8c9a9}
.mp-btn-outline:hover{background:#f6e2cf;color:#753d1e}
.mp-status{background:#fff2e4;color:#82451f}
.mp-separator{border-top-color:#ead7c3}
.mp-line{color:#604837}
.mp-footer-link,.mp-footer-link:hover{color:#a85b24}
@media(max-width:575px){.mp-topbar{gap:8px}.mp-back{font-size:12px}.mp-order-head{gap:8px}.mp-line{align-items:flex-start}.mp-line span:last-child{white-space:nowrap}}

</style>

<div class="mp-page">
  <div class="mp-container">
    <div class="mp-topbar">
      <a class="mp-brand" href="site/<?php echo htmlspecialchars($url_site ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <img src="sistema/img/<?php echo htmlspecialchars($logo_sistema ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="Logo">
        <span>Área do Cliente</span>
      </a>
      <a class="mp-back" href="site/<?php echo htmlspecialchars($url_site ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <i class="bi bi-arrow-left"></i> Voltar ao cardápio
      </a>
    </div>

    <div class="mp-hero">
      <div class="mp-hero-icon"><i class="bi bi-receipt"></i></div>
      <h1>Meus Pedidos</h1>
      <p>Acompanhe suas compras e veja os detalhes dos seus pedidos.</p>
    </div>

    <?php if (!$autenticado) { ?>
      <div class="mp-panel mp-login">
        <label class="mp-label" for="mp-telefone">Telefone cadastrado na compra</label>
        <input class="mp-input" id="mp-telefone" type="tel" placeholder="(00) 00000-0000" autocomplete="tel">
        <button class="mp-btn w-100" id="mp-validar-telefone" type="button">
          <i class="bi bi-search"></i> Consultar meus pedidos
        </button>
        <div id="mp-msg" class="mp-message" role="status"></div>
        <p class="mp-muted mt-3 mb-0">Digite o mesmo telefone informado ao realizar o pedido.</p>
      </div>
    <?php } else {
      $q = $pdo->prepare("SELECT v.id, v.pedido, v.data, v.hora, v.valor, v.status, v.pago, v.entrega, v.tipo_pgto
        FROM vendas v INNER JOIN clientes c ON c.id = v.cliente
        WHERE c.empresa = :empresa_cliente AND v.empresa = :empresa_venda AND c.telefone = :telefone
        ORDER BY v.id DESC LIMIT 50");
      $q->execute([':empresa_cliente'=>$id_empresa, ':empresa_venda'=>$id_empresa, ':telefone'=>$telefone_autenticado]);
      $pedidos = $q->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 px-1">
        <span class="mp-muted"><i class="bi bi-check-circle-fill text-success"></i> Telefone confirmado: <?php echo htmlspecialchars($telefone_autenticado) ?></span>
        <a href="meus-pedidos?sair=1" class="mp-btn mp-btn-outline"><i class="bi bi-box-arrow-right"></i> Sair</a>
      </div>
      <?php if (!$pedidos) { ?>
        <div class="mp-panel text-center">
          <div class="mp-hero-icon"><i class="bi bi-inbox"></i></div>
          <strong>Nenhum pedido encontrado</strong>
          <p class="mp-muted mb-0 mt-2">Não encontramos pedidos vinculados a este telefone.</p>
        </div>
      <?php } ?>
      <?php foreach ($pedidos as $pedido) {
        $dataF = !empty($pedido['data']) ? date('d/m/Y', strtotime($pedido['data'])) : '';
        $valorF = number_format((float)$pedido['valor'], 2, ',', '.');
        $itensQ = $pdo->prepare("SELECT nome_produto, quantidade, (total_item * quantidade) AS total_item FROM carrinho WHERE pedido = :pedido AND empresa = :empresa ORDER BY id");
        $itensQ->execute([':pedido'=>$pedido['id'], ':empresa'=>$id_empresa]);
        $itens = $itensQ->fetchAll(PDO::FETCH_ASSOC);
      ?>
        <div class="mp-order">
          <div class="mp-order-head">
            <div>
              <div class="mp-order-title">Pedido #<?php echo htmlspecialchars($pedido['pedido']) ?></div>
              <div class="mp-muted mt-1"><?php echo $dataF ?> às <?php echo htmlspecialchars(substr($pedido['hora'],0,5)) ?></div>
            </div>
            <span class="mp-status"><?php echo htmlspecialchars($pedido['status']) ?></span>
          </div>
          <hr class="mp-separator">
          <?php foreach ($itens as $item) { ?>
            <div class="mp-line">
              <span><?php echo htmlspecialchars($item['quantidade']) ?>x <?php echo htmlspecialchars($item['nome_produto']) ?></span>
              <span>R$ <?php echo number_format((float)$item['total_item'],2,',','.') ?></span>
            </div>
          <?php } ?>
          <hr class="mp-separator">
          <div class="mp-total"><span>Total do pedido</span><span>R$ <?php echo $valorF ?></span></div>
          <div class="mp-muted mt-2"><?php echo htmlspecialchars($pedido['entrega']) ?> · <?php echo htmlspecialchars($pedido['tipo_pgto']) ?> · Pagamento: <?php echo htmlspecialchars($pedido['pago']) ?></div>
          <a class="mp-btn mt-3" href="pedido/<?php echo base64_encode($pedido['id'].'|hugo') ?>"><i class="bi bi-geo-alt"></i> Acompanhar pedido</a>
        </div>
      <?php } ?>
    <?php } ?>

    <div class="text-center">
      <a class="mp-footer-link" href="site/<?php echo htmlspecialchars($url_site ?? '', ENT_QUOTES, 'UTF-8') ?>">Continuar escolhendo no cardápio <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
</div>

<?php if (!$autenticado) { ?>
<script>
(function(){
 const endpoint='js/ajax/meus-pedidos-auth.php';
 const msg=document.getElementById('mp-msg');
 const validar=document.getElementById('mp-validar-telefone');
 validar.addEventListener('click', async function(){
   const telefone=document.getElementById('mp-telefone').value.trim();
   if(!telefone){msg.textContent='Informe o telefone utilizado na compra.';return;}
   validar.disabled=true; msg.textContent='Verificando telefone...';
   try {
     const body=new URLSearchParams({acao:'validar_telefone',telefone});
     const r=await fetch(endpoint,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body});
     const j=await r.json(); msg.textContent=j.mensagem;
     if(j.ok) window.location.reload();
   } catch(e){msg.textContent='Não foi possível validar o telefone. Tente novamente.';}
   finally{validar.disabled=false;}
 });
})();
</script>
<?php } ?>
</body></html>
