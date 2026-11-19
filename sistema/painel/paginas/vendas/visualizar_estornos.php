<?php
@session_start();
$empresa=filter_var($_SESSION['empresa']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (!$empresa || !$usuario) { http_response_code(403); exit('Sessão inválida.'); }
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Histórico de estornos - Bora Comanda</title>
<style>
body{font-family:system-ui,Arial,sans-serif;background:#f7f8fa;color:#20242a;margin:0;padding:24px}
main{max-width:1100px;margin:auto}.card{background:white;border:1px solid #e2e6eb;border-radius:12px;padding:20px}
form{display:flex;flex-wrap:wrap;gap:12px;align-items:end;margin-bottom:18px}
label{display:grid;gap:5px;font-size:14px}input,select,button{font:inherit;padding:10px;border:1px solid #cbd2da;border-radius:7px}
button{cursor:pointer;background:#233b59;color:white}button:disabled{opacity:.5;cursor:not-allowed}
.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;font-size:14px}
th,td{padding:11px;text-align:left;border-bottom:1px solid #e5e9ed;white-space:nowrap}
th{background:#f1f4f7}.pager{display:flex;gap:12px;align-items:center;margin-top:16px}
#error{color:#b42318}#status{min-height:20px}
</style></head><body><main><h1>Histórico de estornos</h1>
<p>Consulta financeira somente leitura. Este painel não realiza devoluções.</p>
<p><a href="../../index.php?pagina=vendas" style="color:#233b59">Voltar às vendas</a></p>
<section class="card">
<form id="filtros"><label>Venda <input id="venda" type="number" min="1" placeholder="Número da venda"></label>
<label>Status <select id="filtro-status"><option value="">Todos</option><option>Registrado</option><option>Concluido</option><option>Cancelado</option></select></label>
<button type="submit">Pesquisar</button></form>
<p id="status" role="status"></p><p id="resumo-devolvido" aria-live="polite"></p><p id="resumo-pendentes" aria-live="polite"></p><p id="error" role="alert"></p>
<div class="table-wrap"><table><thead><tr><th>Data</th><th>Venda</th><th>Status</th><th>Motivo</th><th>Valor da venda</th><th>Devolução registrada</th><th>Caixa</th></tr></thead><tbody id="linhas"></tbody></table></div>
<div class="pager"><button id="anterior" type="button">Anterior</button><span id="pagina-info"></span><button id="proximo" type="button">Próxima</button></div>
</section></main><script>
let pagina=1,totalPaginas=0,carregamentoAtual=0;
const el=id=>document.getElementById(id);
const moeda=v=>new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(Number(v)||0);
function celula(tr,valor){const td=document.createElement('td');td.textContent=String(valor??'');tr.appendChild(td)}
async function carregar(){
 const sequencia=++carregamentoAtual;
 el('error').textContent='';el('status').textContent='Carregando...';
 el('anterior').disabled=true;el('proximo').disabled=true;
 const q=new URLSearchParams({pagina:String(pagina)});
 if(el('venda').value)q.set('venda_id',el('venda').value);
 if(el('filtro-status').value)q.set('status',el('filtro-status').value);
 try{
  const res=await fetch('historico_estornos.php?'+q,{credentials:'same-origin',cache:'no-store'});
  const dados=await res.json();
  if(sequencia!==carregamentoAtual)return;
  if(!res.ok)throw new Error(dados.erro||'Falha na consulta');
  totalPaginas=Number(dados.total_paginas)||0;
  pagina=Number(dados.pagina)||1;
  el('linhas').replaceChildren();
  for(const e of dados.estornos){
   const tr=document.createElement('tr');
   celula(tr,e.criado_em);celula(tr,e.venda_id);celula(tr,e.status);
   celula(tr,e.motivo);celula(tr,moeda(e.valor_venda));
   celula(tr,moeda(e.valor_devolvido_registrado));celula(tr,e.caixa_estorno_id);
   el('linhas').appendChild(tr);
  }
  if (!dados.estornos.length) {
    const tr=document.createElement('tr');
    const td=document.createElement('td');
    td.colSpan=7;
    td.textContent='Nenhum estorno encontrado para os filtros selecionados.';
    tr.appendChild(td);
    el('linhas').appendChild(tr);
  }
  el('status').textContent=dados.total+' estorno(s) encontrado(s)';
  el('resumo-devolvido').textContent='Total devolvido em estornos concluídos (filtros atuais): '+moeda(dados.total_devolvido_concluido);
  el('resumo-pendentes').textContent='Estornos registrados aguardando conclusão (filtros atuais): '+(Number(dados.estornos_pendentes)||0);
  el('pagina-info').textContent='Página '+pagina+' de '+Math.max(1,totalPaginas);
  el('anterior').disabled=pagina<=1;el('proximo').disabled=pagina>=totalPaginas;
 }catch(e){if(sequencia===carregamentoAtual){
  el('status').textContent='';
  el('resumo-devolvido').textContent='';
  el('resumo-pendentes').textContent='';
  el('error').textContent=e.message;
  el('pagina-info').textContent='Consulta indisponível';
  el('linhas').replaceChildren();
  el('anterior').disabled=true;el('proximo').disabled=true;
 }}
}
el('filtros').addEventListener('submit',e=>{e.preventDefault();pagina=1;carregar()});
el('anterior').addEventListener('click',()=>{if(pagina>1){pagina--;carregar()}});
el('proximo').addEventListener('click',()=>{if(pagina<totalPaginas){pagina++;carregar()}});
carregar();
</script></body></html>