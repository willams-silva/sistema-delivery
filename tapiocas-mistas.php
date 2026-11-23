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
    <div class="fw-bold titulo-item" style="display:flex;align-items:center;gap:5px;font-size:inherit;line-height:1.35">
     <input type="checkbox" class="form-check-input mista-abrir" id="mista-abrir-<?= $pid ?>" aria-controls="mista-opcoes-<?= $pid ?>" aria-expanded="false" style="margin:0;flex:0 0 auto;accent-color:#a45b23">
     <label for="mista-abrir-<?= $pid ?>" style="cursor:pointer;font-size:inherit;font-weight:700;color:inherit;margin:0"><?= escMista($p['nome']) ?></label>
    </div>
    <div class="subtitulo-item-menor" style="color:#450703"><?= escMista($p['descricao']) ?></div>
    <span class="valor-item-maior" style="color:green">R$ <?= number_format($preco,2,',','.') ?></span>
    <small class="d-block text-muted" style="font-size:11px"><?= $limite ?> proteínas inclusas no preço</small>
   </div>
  </div>
  <div class="col-2 text-end" style="padding-right:0">
   <img src="sistema/painel/images/produtos/<?= escMista(rawurlencode($p['foto'])) ?>" width="60" height="60" style="object-fit:cover;max-width:100%" alt="">
  </div>
  <div class="col-12 mista-botoes mt-2" style="padding-left:8px">
   <button type="button" class="btn btn-sm btn-outline-success mb-1 me-1 mista-abrir-btn" data-abrir="inclusas">＋ Sabores inclusos</button>
   <button type="button" class="btn btn-sm btn-outline-success mb-1 me-1 mista-abrir-btn" data-abrir="adicional"><span aria-hidden="true">+</span> Adicionais</button>
   <button type="button" class="btn btn-sm btn-outline-success mb-1 me-1 mista-abrir-btn" data-abrir="opcional">＋ Opcionais · retirar sem custo</button>
   <button type="button" class="btn btn-sm btn-outline-success mb-1 me-1 mista-abrir-btn" data-abrir="proteina">＋ Proteínas</button>
  </div>
  <div class="col-12">
   <div id="mista-opcoes-<?= $pid ?>" class="mista-opcoes mt-2" hidden style="padding:10px;background:#fff8e8;border-radius:8px">
    <div class="mista-inclusas">
     <strong>Escolha exatamente <?= $limite ?> proteínas inclusas</strong>
  <?php if (count($proteinas_mistas)<$limite): ?><div class="text-danger">Cadastre pelo menos <?= $limite ?> proteínas nesta categoria.</div><?php endif; ?>
  <div class="mt-2 mb-3">
  <?php foreach ($proteinas_mistas as $a): ?>
   <label class="mista-item-quantidade mista-item-incluso" style="cursor:pointer;justify-content:flex-start">
    <input type="checkbox" class="form-check-input proteina-inclusa" value="<?= (int)$a['id'] ?>" style="margin:0;flex-shrink:0">
    <span class="mista-item-nome"><span aria-hidden="true"><?= function_exists('iconeAdicionalPizza') ? escMista(iconeAdicionalPizza($a['nome'])) : '🍽️' ?></span> <?= escMista($a['nome']) ?> <small class="text-success">(incluso)</small></span>
   </label>
  <?php endforeach; ?>
  </div>
  <div class="text-muted mb-3 contador-inclusas" aria-live="polite">0 de <?= $limite ?> selecionadas</div>
    </div>
  <?php foreach (['proteina'=>'Proteínas extras (pagas)', 'adicional'=>'Adicionais', 'opcional'=>'Opcionais (retirar sem custo)'] as $tipo=>$titulo):
    $itens = array_values(array_filter($extras_mistos, function($a) use ($tipo){return ($a['tipo'] ?? 'adicional') === $tipo;}));
    if (!$itens) continue; ?>
  <details class="mb-3 mista-grupo" data-tipo="<?= escMista($tipo) ?>" style="background:#fff8e8;border-radius:8px;padding:10px">
   <summary style="display:none" aria-hidden="true"><?= escMista($titulo) ?></summary>
   <div class="mt-2">
    <?php foreach ($itens as $a): ?>
     <div class="mista-item-quantidade">
      <small class="mista-item-nome"><span aria-hidden="true"><?= function_exists('iconeAdicionalPizza') ? escMista(iconeAdicionalPizza($a['nome'])) : '🍽️' ?></span> <?= escMista($a['nome']) ?>
       <?php if ($tipo !== 'opcional'): ?><small class="text-success">(R$ <?= number_format((float)$a['valor'],2,',','.') ?>)</small><?php else: ?><small class="text-success">(sem custo)</small><?php endif; ?>
      </small>
      <div class="mista-controles">
       <?php if ($tipo === 'opcional'): ?>
        <label class="d-flex align-items-center gap-1" style="white-space:nowrap;cursor:pointer">
         <input type="checkbox" class="extra-mista" data-id="<?= (int)$a['id'] ?>" data-tipo="opcional"> Retirar
        </label>
       <?php else: ?>
        <button type="button" class="mista-menos" aria-label="Retirar uma unidade de <?= escMista($a['nome']) ?>">−</button>
        <span class="mista-valor" aria-live="polite">0</span>
        <button type="button" class="mista-mais" aria-label="Adicionar uma unidade de <?= escMista($a['nome']) ?>">+</button>
        <input type="number" class="extra-mista" min="0" max="20" step="1" value="0" data-id="<?= (int)$a['id'] ?>" data-tipo="<?= escMista($tipo) ?>" hidden>
       <?php endif; ?>
      </div>
     </div>
    <?php endforeach; ?>
   </div>
  </details>
  <?php endforeach; ?>
  <div class="resultado-mista mt-2" role="alert" aria-live="polite"></div>
   </div>
  </div>
 </div>
</li>
<?php endforeach; ?>
<style>
[data-mista] .mista-item-quantidade{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid #e9e5dc;min-height:56px}
[data-mista] .mista-item-nome{flex:1;min-width:0;color:#30251d;overflow-wrap:anywhere}
[data-mista] .mista-controles{display:flex;align-items:center;gap:12px;flex-shrink:0}
[data-mista] .mista-controles button{display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:10px;font-size:23px;line-height:1;cursor:pointer;padding:0}
[data-mista] .mista-menos{background:#fff;color:#dc3545;border:1.5px solid #dc3545}
[data-mista] .mista-mais{background:#198754;color:#fff;border:1.5px solid #198754}
[data-mista] .mista-mais:disabled{opacity:.4;cursor:not-allowed}
[data-mista] .mista-valor{min-width:26px;text-align:center;font-weight:700;color:#171717}
@media(max-width:380px){[data-mista] .mista-controles{gap:5px}}
</style>
<script>
(function(){
 document.querySelectorAll('[data-mista]').forEach(function(card){
   var limite=Number(card.dataset.limite);
   var painel=card.querySelector('.mista-opcoes');
   var abrir=card.querySelector('.mista-abrir');
   var botoes=Array.from(card.querySelectorAll('.mista-abrir-btn'));
   var checks=Array.from(card.querySelectorAll('.proteina-inclusa'));
   var inclusas=card.querySelector('.mista-inclusas');
   var grupos=Array.from(card.querySelectorAll('.mista-grupo'));
   // Um grupo por vez, sem apagar escolhas feitas em outros grupos.
   function mostrarGrupo(tipo) {
     var visivel=tipo==='inclusas' ? !inclusas.hidden :
       grupos.some(function(g){return g.dataset.tipo===tipo && !g.hidden});
     var abrirGrupo=!visivel;
     inclusas.hidden=!(abrirGrupo && tipo==='inclusas');
     grupos.forEach(function(g){
       var ativo=abrirGrupo && g.dataset.tipo===tipo;
       g.hidden=!ativo;
       g.open=ativo;
     });
     painel.hidden=!abrirGrupo;
     botoes.forEach(function(btn){
       btn.setAttribute('aria-expanded',String(abrirGrupo && btn.dataset.abrir===tipo));
     });
     if (abrirGrupo) {
       var alvo=tipo==='inclusas' ? inclusas : grupos.find(function(g){return g.dataset.tipo===tipo});
       if (alvo) alvo.scrollIntoView({behavior:'smooth',block:'nearest'});
     }
   }
   function alternar(aberto) {
     abrir.checked=aberto;
     abrir.setAttribute('aria-expanded',String(aberto));
     // Selecionar a tapioca nao abre nenhuma categoria automaticamente.
     painel.hidden=true;
     inclusas.hidden=true;
     grupos.forEach(function(g){g.hidden=true;g.open=false;});
     botoes.forEach(function(btn){
       btn.disabled=false;
       btn.removeAttribute('aria-disabled');
       btn.setAttribute('aria-expanded','false');
     });
     if (!aberto) {
       checks.forEach(function(x){x.checked=false;x.disabled=false;});
       card.querySelectorAll('.extra-mista').forEach(function(x){
         if(x.type==='checkbox') x.checked=false;
         else x.value='0';
       });
       card.querySelector('.resultado-mista').textContent='';
       atualizar();
       if(typeof atualizarQuantidades==='function') atualizarQuantidades();
     }
   }
   abrir.addEventListener('change',function(){
     alternar(abrir.checked);
   });
   botoes.forEach(function(btn){
     btn.addEventListener('click',function(){
       if (!abrir.checked) {
         if (typeof mostrarAvisoSelecaoSabor==='function') {
           mostrarAvisoSelecaoSabor('Selecione o sabor','Selecione este sabor primeiro para escolher os adicionais.');
         } else {
           alert('Selecione a tapioca primeiro para escolher as opções.');
         }
         return;
       }
       mostrarGrupo(btn.dataset.abrir);
     });
   });
   alternar(false);
   function atualizar(){
     var n=checks.filter(function(x){return x.checked}).length;
     card.querySelector('.contador-inclusas').textContent=n+' de '+limite+' selecionadas';
     // Bloqueio nativo: ao atingir o limite, somente os sabores ainda
     // nao marcados ficam desabilitados. Os marcados continuam livres
     // para desmarcar, inclusive por toque no texto do label.
     checks.forEach(function(x){
       var bloquear=!x.checked && n>=limite;
       x.disabled=bloquear;
       x.setAttribute('aria-disabled',String(bloquear));
     });
   }
   function avisoLimite(){
     var mensagem='A tapioca '+card.querySelector('.titulo-item label').textContent.trim()+
       ' permite selecionar somente '+limite+' sabores inclusos.';
     if (typeof Swal!=='undefined' && typeof Swal.fire==='function')
       Swal.fire({icon:'warning',title:'Limite de sabores',text:mensagem});
     else alert(mensagem);
   }
   // Nao interceptar o click do <label>: impedir o default aqui
   // desfaz o estado do checkbox e bloqueia ate a desmarcacao.
   // O evento change abaixo reverte somente a selecao excedente.
   // Mesma interface +/- dos adicionais tradicionais. O estado real
   // continua nos inputs usados pelo envio do carrinho.
   card.querySelectorAll('.mista-item-quantidade:not(.mista-item-incluso)').forEach(function(linha){
     var input=linha.querySelector('input');
     var valor=linha.querySelector('.mista-valor');
     var mais=linha.querySelector('.mista-mais');
     var menos=linha.querySelector('.mista-menos');
     function sincronizar(){
       var qtd=input.type==='checkbox'?(input.checked?1:0):Math.max(0,Math.min(20,Number(input.value)||0));
       valor.textContent=String(qtd);
       mais.disabled=input.classList.contains('proteina-inclusa') ?
         (!input.checked && checks.filter(function(x){return x.checked}).length>=limite) :
         (input.type==='checkbox' ? input.checked : qtd>=20);
     }
     mais.addEventListener('click',function(){
       if(input.type==='checkbox') {
         if(input.checked)return;
         if(input.classList.contains('proteina-inclusa') && checks.filter(function(x){return x.checked}).length>=limite)return;
         input.checked=true;
       }else{input.value=Math.min(20,(Number(input.value)||0)+1);}
       atualizar();atualizarQuantidades();
     });
     menos.addEventListener('click',function(){
       if(input.type==='checkbox')input.checked=false;
       else input.value=Math.max(0,(Number(input.value)||0)-1);
       atualizar();atualizarQuantidades();
     });
     input.addEventListener('change',sincronizar);
     linha.atualizarQuantidade=sincronizar;
     sincronizar();
   });
   function atualizarQuantidades(){
     card.querySelectorAll('.mista-item-quantidade').forEach(function(linha){
       if(linha.atualizarQuantidade)linha.atualizarQuantidade();
     });
   }
   checks.forEach(function(x){
     x.addEventListener('change',function(){
       // Bloqueio efetivo mesmo quando o navegador altera o checkbox
       // por teclado, label ou scripts externos.
       var selecionadas=checks.filter(function(item){return item.checked});
       if (selecionadas.length>limite) {
         x.checked=false;
         atualizar();
         atualizarQuantidades();
         avisoLimite();
         return;
       }
       atualizar();
       atualizarQuantidades();
     });
   });
   atualizar();atualizarQuantidades();
   card.enviarMista=function(){
     var msg=card.querySelector('.resultado-mista');
     if (!abrir.checked) return false;
     var incl=checks.filter(function(x){return x.checked}).map(function(x){return Number(x.value)});
     if(incl.length!==limite){
       msg.textContent='Escolha exatamente '+limite+' proteínas inclusas.';
       mostrarGrupo('inclusas');
       return true;
     }
     var extras={};
     card.querySelectorAll('.extra-mista').forEach(function(x){
       var qtd=x.type==='checkbox'?(x.checked?1:0):Number(x.value);
       if(Number.isInteger(qtd)&&qtd>0) extras[x.dataset.id]=qtd;
     });
     if(card.dataset.enviando==='1')return true;
     card.dataset.enviando='1';msg.textContent='Adicionando...';
     fetch('js/ajax/add-carrinho-mista.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({produto:card.dataset.mista,inclusas:JSON.stringify(incl),extras:JSON.stringify(extras),obs:document.getElementById('obs')?.value||''})})
       .then(function(r){return r.text()}).then(function(t){
         if(t.trim()==='Inserido com Sucesso'){location.href='carrinho';}
         else{msg.textContent=t;card.dataset.enviando='0';}
       })
       .catch(function(){msg.textContent='Falha de conexão. Tente novamente.';card.dataset.enviando='0';});
     return true;
   };

 });
})();
</script>


<?php endif; ?>
