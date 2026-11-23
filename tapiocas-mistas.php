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
<li class="list-group-item d-flex justify-content-between align-items-start" data-mista="<?= $pid ?>" data-limite="<?= $limite ?>">
 <div class="row mista-linha" style="width:100%;margin:0">
  <div class="col-10" style="padding-left:0">
   <div class="me-auto">
    <div class="fw-bold titulo-item">
     <input type="checkbox" class="form-check-input mista-abrir" id="mista-abrir-<?= $pid ?>" aria-controls="mista-opcoes-<?= $pid ?>" aria-expanded="false" style="margin-top:5px">
     <label for="mista-abrir-<?= $pid ?>" style="cursor:pointer"><?= escMista($p['nome']) ?></label>
    </div>
    <div class="subtitulo-item-menor" style="color:#450703"><?= escMista($p['descricao']) ?></div>
    <span class="valor-item-maior" style="color:green">R$ <?= number_format($preco,2,',','.') ?></span>
    <small class="d-block text-muted" style="font-size:11px"><?= $limite ?> proteínas inclusas no preço</small>
   </div>
  </div>
  <div class="col-2 text-end" style="padding-right:0">
   <img src="sistema/painel/images/produtos/<?= escMista(rawurlencode($p['foto'])) ?>" width="60" height="60" style="object-fit:cover;max-width:100%" alt="">
  </div>
  <div class="col-12 mista-botoes" style="padding:0;margin-top:12px">
   <button type="button" class="btn btn-sm btn-outline-success mb-1 me-1 mista-abrir-btn" data-abrir="inclusas">＋ Sabores inclusos</button>
   <button type="button" class="btn btn-sm btn-outline-success mb-1 me-1 mista-abrir-btn" data-abrir="adicional">＋ Adicionais</button>
   <button type="button" class="btn btn-sm btn-outline-success mb-1 me-1 mista-abrir-btn" data-abrir="opcional">＋ Opcionais · retirar sem custo</button>
   <button type="button" class="btn btn-sm btn-outline-success mb-1 me-1 mista-abrir-btn" data-abrir="proteina">＋ Proteínas</button>
  </div>
  <div class="col-12">
   <div id="mista-opcoes-<?= $pid ?>" class="mista-opcoes mt-3" hidden style="padding:12px;background:#fff8ef;border-radius:10px">
    <div class="mista-inclusas">
     <strong>Escolha exatamente <?= $limite ?> proteínas inclusas</strong>
  <?php if (count($proteinas_mistas)<$limite): ?><div class="text-danger">Cadastre pelo menos <?= $limite ?> proteínas nesta categoria.</div><?php endif; ?>
  <div class="row mt-2 mb-3">
  <?php foreach ($proteinas_mistas as $a): ?>
   <label class="col-6 col-md-4 mb-2"><input type="checkbox" class="proteina-inclusa" value="<?= (int)$a['id'] ?>"> <?= escMista($a['nome']) ?></label>
  <?php endforeach; ?>
  </div>
  <div class="text-muted mb-3 contador-inclusas" aria-live="polite">0 de <?= $limite ?> selecionadas</div>
    </div>
  <?php foreach (['proteina'=>'Proteínas extras (pagas)', 'adicional'=>'Adicionais', 'opcional'=>'Opcionais (retirar sem custo)'] as $tipo=>$titulo):
    $itens = array_values(array_filter($extras_mistos, function($a) use ($tipo){return ($a['tipo'] ?? 'adicional') === $tipo;}));
    if (!$itens) continue; ?>
  <details class="mb-3 mista-grupo" data-tipo="<?= escMista($tipo) ?>"><summary style="cursor:pointer;font-weight:600"><?= escMista($titulo) ?></summary>
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
  </div>
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
     btn.addEventListener('click',function(){
       alternar(true);
       var tipo=btn.getAttribute('data-abrir');
       var inclusas=card.querySelector('.mista-inclusas');
       if (tipo==='inclusas') {
         inclusas.scrollIntoView({behavior:'smooth',block:'nearest'});
       } else {
         var grupo=card.querySelector('.mista-grupo[data-tipo="'+tipo+'"]');
         if (grupo) {grupo.open=true;grupo.scrollIntoView({behavior:'smooth',block:'nearest'});}
       }
     });
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
