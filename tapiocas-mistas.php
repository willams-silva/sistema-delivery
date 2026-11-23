<?php
// Montador exclusivo dos produtos com configuração individual de tapioca mista.
$stmt_mistas = $pdo->prepare("SELECT p.id, p.nome, p.descricao, p.foto, p.valor_venda, p.val_promocional, p.promocao, m.limite_proteinas
 FROM produtos p INNER JOIN tapiocas_mistas_config m ON m.produto_id = p.id AND m.empresa = p.empresa
 WHERE p.categoria = ? AND p.empresa = ? AND p.ativo = 'Sim' AND (p.tem_estoque = 'Não' OR p.estoque > 0) ORDER BY p.nome");
$stmt_mistas->execute([(int)$id, $id_empresa]);
$produtos_mistos = $stmt_mistas->fetchAll(PDO::FETCH_ASSOC);
$stmt_extras = $pdo->prepare("SELECT id, nome, valor, tipo FROM adicionais_cat WHERE categoria = ? AND empresa = ? AND ativo = 'Sim' ORDER BY tipo, nome");
$stmt_extras->execute([(int)$id, $id_empresa]);
$extras_mistos = $stmt_extras->fetchAll(PDO::FETCH_ASSOC);
$proteinas_mistas = array_values(array_filter($extras_mistos, function($a){return ($a['tipo'] ?? '') === 'proteina';}));
if (!function_exists('escMista')) { function escMista($x) { return htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8'); } }
?>
<?php if ($produtos_mistos): ?>
<?php foreach ($produtos_mistos as $p):
 $preco = $p['promocao']==='Sim' && (float)$p['val_promocional']>0 ? (float)$p['val_promocional'] : (float)$p['valor_venda'];
 $limite = (int)$p['limite_proteinas'];
 $pid = (int)$p['id']; ?>
<li class="list-group-item mb-2" data-mista="<?= $pid ?>" data-limite="<?= $limite ?>" style="border:1px solid #efddc9">
 <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
  <div class="d-flex align-items-start gap-2" style="flex:1;min-width:210px">
   <input type="checkbox" class="form-check-input mista-abrir" id="mista-abrir-<?= $pid ?>" aria-controls="mista-opcoes-<?= $pid ?>" aria-expanded="false">
   <div>
    <label for="mista-abrir-<?= $pid ?>" class="fw-bold" style="cursor:pointer"><?= escMista($p['nome']) ?></label>
    <div class="subtitulo-item-menor" style="font-size:11px;color:#713c20"><?= escMista($p['descricao']) ?></div>
    <div class="text-success" style="font-size:13px">R$ <?= number_format($preco,2,',','.') ?> · <?= $limite ?> proteínas inclusas</div>
   </div>
  </div>
  <img src="sistema/painel/images/produtos/<?= escMista(rawurlencode($p['foto'])) ?>" width="72" height="72" style="object-fit:cover" alt="">
  <div class="d-flex gap-1 flex-wrap" style="max-width:420px">
   <button type="button" class="btn btn-sm btn-outline-warning mista-abrir-btn">＋ Escolher proteínas</button>
   <button type="button" class="btn btn-sm btn-outline-warning mista-abrir-btn">＋ Adicionais e opcionais</button>
  </div>
 </div>
 <div id="mista-opcoes-<?= $pid ?>" class="mista-opcoes mt-3" hidden style="padding:12px;background:#fff8ef;border-radius:10px">
  <strong>Escolha exatamente <?= $limite ?> proteínas inclusas</strong>
  <?php if (count($proteinas_mistas)<$limite): ?><div class="text-danger">Cadastre pelo menos <?= $limite ?> proteínas nesta categoria.</div><?php endif; ?>
  <div class="row mt-2 mb-3">
  <?php foreach ($proteinas_mistas as $a): ?>
   <label class="col-6 col-md-4 mb-2"><input type="checkbox" class="proteina-inclusa" value="<?= (int)$a['id'] ?>"> <?= escMista($a['nome']) ?></label>
  <?php endforeach; ?>
  </div>
  <div class="text-muted mb-3 contador-inclusas" aria-live="polite">0 de <?= $limite ?> selecionadas</div>
  <?php foreach (['proteina'=>'Proteínas extras (pagas)', 'adicional'=>'Adicionais', 'opcional'=>'Opcionais (retirar sem custo)'] as $tipo=>$titulo):
    $itens = array_values(array_filter($extras_mistos, function($a) use ($tipo){return ($a['tipo'] ?? 'adicional') === $tipo;}));
    if (!$itens) continue; ?>
  <details class="mb-3"><summary style="cursor:pointer;font-weight:600"><?= escMista($titulo) ?></summary>
   <div class="mt-2">
    <?php foreach ($itens as $a): ?>
     <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid #eee">
      <label for="extra-<?= $pid ?>-<?= (int)$a['id'] ?>"><?= escMista($a['nome']) ?>
      <?php if ($tipo !== 'opcional'): ?><small class="text-success">+ R$ <?= number_format((float)$a['valor'],2,',','.') ?></small><?php endif; ?></label>
      <?php if ($tipo === 'opcional'): ?>
      <input id="extra-<?= $pid ?>-<?= (int)$a['id'] ?>" type="checkbox" class="extra-mista" data-id="<?= (int)$a['id'] ?>" data-tipo="opcional" aria-label="Retirar <?= escMista($a['nome']) ?>">
      <?php else: ?>
      <input id="extra-<?= $pid ?>-<?= (int)$a['id'] ?>" type="number" class="form-control extra-mista" style="width:80px" min="0" max="20" step="1" value="0" data-id="<?= (int)$a['id'] ?>" data-tipo="<?= escMista($tipo) ?>" aria-label="Quantidade extra de <?= escMista($a['nome']) ?>">
      <?php endif; ?>
     </div>
    <?php endforeach; ?>
   </div>
  </details>
  <?php endforeach; ?>
  <label for="obs-mista-<?= $pid ?>">Observações</label>
  <textarea id="obs-mista-<?= $pid ?>" class="form-control obs-mista" maxlength="180" rows="2" placeholder="Alguma observação?"></textarea>
  <button type="button" class="btn btn-warning w-100 mt-3 btn-mista" <?= count($proteinas_mistas)<$limite ? 'disabled' : '' ?>>Adicionar ao carrinho →</button>
  <div class="resultado-mista mt-2" role="alert" aria-live="polite"></div>
 </div>
</li>
<?php endforeach; ?>
<script>
(function(){
 document.querySelectorAll('[data-mista]').forEach(function(card){
   var limite=Number(card.dataset.limite);
   var painel=card.querySelector('.mista-opcoes');
   var abrir=card.querySelector('.mista-abrir');
   function alternar(aberto) {
     painel.hidden=!aberto;
     abrir.checked=aberto;
     abrir.setAttribute('aria-expanded',String(aberto));
   }
   abrir.addEventListener('change',function(){alternar(abrir.checked)});
   card.querySelectorAll('.mista-abrir-btn').forEach(function(btn){
     btn.addEventListener('click',function(){alternar(true);painel.scrollIntoView({behavior:'smooth',block:'nearest'})});
   });
   var checks=Array.from(card.querySelectorAll('.proteina-inclusa'));
   function atualizar(){
     var n=checks.filter(function(x){return x.checked}).length;
     card.querySelector('.contador-inclusas').textContent=n+' de '+limite+' selecionadas';
     checks.forEach(function(x){x.disabled=!x.checked && n>=limite});
   }
   checks.forEach(function(x){x.addEventListener('change',atualizar)});atualizar();
   card.querySelector('.btn-mista').addEventListener('click',function(){
     var btn=this, msg=card.querySelector('.resultado-mista');
     var incl=checks.filter(function(x){return x.checked}).map(function(x){return Number(x.value)});
     if(incl.length!==limite){msg.textContent='Escolha exatamente '+limite+' proteínas inclusas.';return;}
     var extras={};
     card.querySelectorAll('.extra-mista').forEach(function(x){
       var qtd=x.type==='checkbox'?(x.checked?1:0):Number(x.value);
       if(Number.isInteger(qtd)&&qtd>0) extras[x.dataset.id]=qtd;
     });
     btn.disabled=true;msg.textContent='Adicionando...';
     fetch('js/ajax/add-carrinho-mista.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({produto:card.dataset.mista,inclusas:JSON.stringify(incl),extras:JSON.stringify(extras),obs:card.querySelector('.obs-mista').value})})
       .then(function(r){return r.text()}).then(function(t){if(t.trim()==='Inserido com Sucesso'){location.href='carrinho';}else{msg.textContent=t;btn.disabled=false}})
       .catch(function(){msg.textContent='Falha de conexão. Tente novamente.';btn.disabled=false});
   });
 });
})();
</script>


<?php endif; ?>
