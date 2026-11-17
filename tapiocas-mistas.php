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
<div class="container" style="max-width:850px;padding:24px 12px 100px">
<h2 style="color:#713c20;text-align:center">Monte sua tapioca mista</h2>
<p style="text-align:center">Escolha uma tapioca, selecione as proteínas inclusas e acrescente extras, se desejar.</p>
<?php if (!$produtos_mistos): ?><p class="alert alert-info">Ainda não há tapiocas mistas configuradas no painel.</p><?php endif; ?>
<?php if (!$proteinas_mistas): ?><p class="alert alert-warning">Cadastre os ingredientes do tipo <strong>Proteína</strong> nos adicionais desta categoria.</p><?php endif; ?>
<?php foreach ($produtos_mistos as $p):
 $preco = $p['promocao']==='Sim' && (float)$p['val_promocional']>0 ? (float)$p['val_promocional'] : (float)$p['valor_venda'];
 $limite = (int)$p['limite_proteinas']; ?>
<section class="card mb-3 shadow-sm" style="border-radius:15px" data-mista="<?= (int)$p['id'] ?>" data-limite="<?= $limite ?>">
 <div class="card-body">
  <div class="d-flex gap-3 align-items-center mb-3">
   <img src="sistema/painel/images/produtos/<?= escMista(rawurlencode($p['foto'])) ?>" width="82" height="82" style="object-fit:cover;border-radius:12px" alt="">
   <div><h4 style="margin:0"><?= escMista($p['nome']) ?></h4><small><?= escMista($p['descricao']) ?></small>
    <div><strong>R$ <?= number_format($preco,2,',','.') ?></strong> · <?= $limite ?> proteínas inclusas</div></div>
  </div>
  <strong>Escolha exatamente <?= $limite ?> proteínas (inclusas)</strong>
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
      <label for="extra-<?= (int)$p['id'] ?>-<?= (int)$a['id'] ?>"><?= escMista($a['nome']) ?>
      <?php if ($tipo !== 'opcional'): ?><small class="text-success">+ R$ <?= number_format((float)$a['valor'],2,',','.') ?></small><?php endif; ?></label>
      <?php if ($tipo === 'opcional'): ?>
      <input id="extra-<?= (int)$p['id'] ?>-<?= (int)$a['id'] ?>" type="checkbox" class="extra-mista" data-id="<?= (int)$a['id'] ?>" data-tipo="opcional" aria-label="Retirar <?= escMista($a['nome']) ?>">
      <?php else: ?>
      <input id="extra-<?= (int)$p['id'] ?>-<?= (int)$a['id'] ?>" type="number" class="form-control extra-mista" style="width:80px" min="0" max="20" step="1" value="0" data-id="<?= (int)$a['id'] ?>" data-tipo="<?= escMista($tipo) ?>" aria-label="Quantidade extra de <?= escMista($a['nome']) ?>">
      <?php endif; ?>
     </div>
    <?php endforeach; ?>
   </div>
  </details>
  <?php endforeach; ?>
  <label for="obs-mista-<?= (int)$p['id'] ?>">Observações</label>
  <textarea id="obs-mista-<?= (int)$p['id'] ?>" class="form-control obs-mista" maxlength="180" rows="2" placeholder="Alguma observação?"></textarea>
  <button type="button" class="btn btn-warning w-100 mt-3 btn-mista" <?= count($proteinas_mistas)<$limite ? 'disabled' : '' ?>>Adicionar ao carrinho <span aria-hidden="true">→</span></button>
  <div class="resultado-mista mt-2" role="alert" aria-live="polite"></div>
 </div>
</section>
<?php endforeach; ?>
</div>
<script>
(function(){
 document.querySelectorAll('[data-mista]').forEach(function(card){
   var limite=Number(card.dataset.limite);
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
