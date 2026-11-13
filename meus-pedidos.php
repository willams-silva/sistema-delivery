<?php
@session_start();
if (empty($_SESSION['url_site'])) {
  $url_site = $_GET['url'] ?? '';
  if ($url_site !== '') $_SESSION['url_site'] = $url_site;
}
require_once("cabecalho.php");

$telefone_autenticado = $_SESSION['meus_pedidos_telefone_autenticado'] ?? '';
$empresa_autenticada = (int)($_SESSION['meus_pedidos_empresa_autenticada'] ?? 0);
$autenticado = ($telefone_autenticado !== '' && $empresa_autenticada === (int)$id_empresa && (time() - (int)($_SESSION['meus_pedidos_autenticado_em'] ?? 0) <= 1800));

if (!$autenticado && isset($_GET['sair'])) {
  unset($_SESSION['meus_pedidos_telefone_autenticado'], $_SESSION['meus_pedidos_empresa_autenticada'], $_SESSION['meus_pedidos_autenticado_em']);
  echo '<script>window.location="meus-pedidos";</script>';
  exit;
}
?>
<style>
.meus-pedidos-wrap{max-width:900px;margin:35px auto 70px;padding:0 15px}
.mp-card{background:#fff;border-radius:12px;padding:18px;margin:14px 0;box-shadow:0 2px 12px #00000012;text-align:left;color:#333}
.mp-btn{background:#fe9f16;color:#fff;border:0;border-radius:8px;padding:11px 18px;font-weight:600}
.mp-muted{color:#777;font-size:13px}
.mp-status{display:inline-block;border-radius:30px;padding:5px 12px;background:#fff3cd;color:#856404;font-size:12px;font-weight:700}
.mp-input{width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;margin:8px 0 15px}
</style>
<div class="meus-pedidos-wrap">
  <div class="text-center mb-4">
    <h3 style="color:#fe9f16">Meus Pedidos</h3>
    <p class="mp-muted">Consulte suas compras e acompanhe o andamento dos pedidos.</p>
  </div>
  <?php if (!$autenticado) { ?>
    <div class="mp-card" style="max-width:480px;margin:20px auto">
      <label for="mp-telefone">Telefone utilizado na compra</label>
      <input class="mp-input" id="mp-telefone" type="tel" placeholder="(00) 00000-0000" autocomplete="tel">
      <button class="mp-btn w-100" id="mp-enviar" type="button">Enviar código pelo WhatsApp</button>
      <div id="mp-codigo-area" style="display:none;margin-top:18px">
        <label for="mp-codigo">Código de 6 dígitos</label>
        <input class="mp-input" id="mp-codigo" inputmode="numeric" maxlength="6" placeholder="000000">
        <button class="mp-btn w-100" id="mp-validar" type="button">Confirmar e visualizar pedidos</button>
      </div>
      <div id="mp-msg" class="mp-muted" role="status" style="margin-top:12px"></div>
    </div>
  <?php } else {
    $q = $pdo->prepare("SELECT v.id, v.pedido, v.data, v.hora, v.valor, v.status, v.pago, v.entrega, v.tipo_pgto
      FROM vendas v INNER JOIN clientes c ON c.id = v.cliente
      WHERE c.empresa = :empresa_cliente AND v.empresa = :empresa_venda AND c.telefone = :telefone
      ORDER BY v.id DESC LIMIT 50");
    $q->execute([':empresa_cliente'=>$id_empresa, ':empresa_venda'=>$id_empresa, ':telefone'=>$telefone_autenticado]);
    $pedidos = $q->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
      <span class="mp-muted">Telefone confirmado: <?php echo htmlspecialchars($telefone_autenticado) ?></span>
      <a href="meus-pedidos?sair=1" class="btn btn-outline-secondary btn-sm">Sair</a>
    </div>
    <?php if (!$pedidos) { ?><div class="mp-card text-center">Nenhum pedido encontrado para este telefone.</div><?php } ?>
    <?php foreach ($pedidos as $pedido) {
      $dataF = !empty($pedido['data']) ? date('d/m/Y', strtotime($pedido['data'])) : '';
      $valorF = number_format((float)$pedido['valor'], 2, ',', '.');
      $itensQ = $pdo->prepare("SELECT nome_produto, quantidade, (total_item * quantidade) AS total_item FROM carrinho WHERE pedido = :pedido AND empresa = :empresa ORDER BY id");
      $itensQ->execute([':pedido'=>$pedido['id'], ':empresa'=>$id_empresa]);
      $itens = $itensQ->fetchAll(PDO::FETCH_ASSOC);
    ?>
      <div class="mp-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
          <div><strong>Pedido #<?php echo htmlspecialchars($pedido['pedido']) ?></strong><div class="mp-muted"><?php echo $dataF ?> às <?php echo htmlspecialchars(substr($pedido['hora'],0,5)) ?></div></div>
          <span class="mp-status"><?php echo htmlspecialchars($pedido['status']) ?></span>
        </div>
        <hr>
        <?php foreach ($itens as $item) { ?>
          <div class="d-flex justify-content-between"><span><?php echo htmlspecialchars($item['quantidade']) ?>x <?php echo htmlspecialchars($item['nome_produto']) ?></span><span>R$ <?php echo number_format((float)$item['total_item'],2,',','.') ?></span></div>
        <?php } ?>
        <hr>
        <div class="d-flex justify-content-between"><strong>Total</strong><strong>R$ <?php echo $valorF ?></strong></div>
        <div class="mp-muted mt-2"><?php echo htmlspecialchars($pedido['entrega']) ?> · <?php echo htmlspecialchars($pedido['tipo_pgto']) ?> · Pagamento: <?php echo htmlspecialchars($pedido['pago']) ?></div>
        <a class="btn btn-sm btn-outline-warning mt-3" href="pedido/<?php echo base64_encode($pedido['id'].'|hugo') ?>">Acompanhar pedido</a>
      </div>
    <?php } ?>
  <?php } ?>
  <div class="text-center mt-4"><a href="site/<?php echo htmlspecialchars($url_site) ?>">Voltar ao cardápio</a></div>
</div>
<?php if (!$autenticado) { ?>
<script>
(function(){
 const endpoint='js/ajax/meus-pedidos-auth.php';
 const msg=document.getElementById('mp-msg');
 const enviar=document.getElementById('mp-enviar');
 const validar=document.getElementById('mp-validar');
 enviar.addEventListener('click', async function(){
   enviar.disabled=true; msg.textContent='Solicitando código...';
   try {
     const body=new URLSearchParams({acao:'enviar_codigo',telefone:document.getElementById('mp-telefone').value});
     const r=await fetch(endpoint,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body});
     const j=await r.json(); msg.textContent=j.mensagem;
     if(j.ok) document.getElementById('mp-codigo-area').style.display='block';
   } catch(e){msg.textContent='Não foi possível solicitar o código. Tente novamente.';}
   finally{enviar.disabled=false;}
 });
 validar.addEventListener('click', async function(){
   validar.disabled=true; msg.textContent='Validando código...';
   try {
     const body=new URLSearchParams({acao:'validar_codigo',codigo:document.getElementById('mp-codigo').value});
     const r=await fetch(endpoint,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body});
     const j=await r.json(); msg.textContent=j.mensagem;
     if(j.ok) window.location.reload();
   } catch(e){msg.textContent='Não foi possível validar o código. Tente novamente.';}
   finally{validar.disabled=false;}
 });
})();
</script>
<?php } ?>
</body></html>