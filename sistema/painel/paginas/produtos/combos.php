<?php
@session_start();
require_once '../../../conexao.php';
if (empty($_SESSION['id']) || (int)($_SESSION['empresa'] ?? 0) <= 0) { http_response_code(403); exit('Acesso não autorizado'); }
$empresa=(int)$_SESSION['empresa'];
if (empty($_SESSION['csrf_combos'])) $_SESSION['csrf_combos']=bin2hex(random_bytes(24));
$erro='';$sucesso='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 try {
  if (!hash_equals($_SESSION['csrf_combos'],(string)($_POST['csrf']??''))) throw new RuntimeException('Sessão expirada. Atualize a página.');
  $acao=(string)($_POST['acao']??'');
  $pdo->beginTransaction();
  if ($acao==='config') {
   $produto=(int)($_POST['produto_combo_id']??0);
   $q=$pdo->prepare("SELECT id FROM produtos WHERE id=? AND empresa=? AND combo='Sim'");
   $q->execute([$produto,$empresa]);
   if (!$q->fetchColumn()) throw new RuntimeException('Selecione um produto marcado como Combo.');
   $tipo=(string)($_POST['tipo']??'fixo');
   if (!in_array($tipo,['fixo','personalizavel'],true)) throw new RuntimeException('Tipo inválido.');
   $q=$pdo->prepare("INSERT INTO combo_configuracoes (empresa,produto_combo_id,tipo,ativo) VALUES (?,?,?,'Não') ON DUPLICATE KEY UPDATE tipo=VALUES(tipo)");
   $q->execute([$empresa,$produto,$tipo]);
  } elseif ($acao==='grupo') {
   $config=(int)($_POST['config']??0);
   $nome=trim((string)($_POST['nome']??''));
   $min=filter_var($_POST['minimo']??null,FILTER_VALIDATE_INT);
   $max=filter_var($_POST['maximo']??null,FILTER_VALIDATE_INT);
   if ($nome==='' || mb_strlen($nome)>120 || $min===false || $max===false || $min<0 || $max<1 || $min>$max || $max>20) throw new RuntimeException('Nome ou limites inválidos (máximo 20).');
   $q=$pdo->prepare('SELECT id FROM combo_configuracoes WHERE id=? AND empresa=?');
   $q->execute([$config,$empresa]);
   if (!$q->fetchColumn()) throw new RuntimeException('Combo inválido.');
   $q=$pdo->prepare('INSERT INTO combo_grupos (empresa,combo_configuracao_id,nome,minimo,maximo) VALUES (?,?,?,?,?)');
   $q->execute([$empresa,$config,$nome,$min,$max]);
  } elseif ($acao==='item') {
   $grupo=(int)($_POST['grupo']??0);$produto=(int)($_POST['produto']??0);
   $acrescimo=str_replace(',','.',trim((string)($_POST['acrescimo']??'0')));
   if (!preg_match('/^\d{1,7}(\.\d{1,2})?$/',$acrescimo)) throw new RuntimeException('Acréscimo inválido.');
   $q=$pdo->prepare('SELECT id FROM combo_grupos WHERE id=? AND empresa=?');
   $q->execute([$grupo,$empresa]);
   if (!$q->fetchColumn()) throw new RuntimeException('Grupo inválido.');
   $q=$pdo->prepare("SELECT id FROM produtos WHERE id=? AND empresa=? AND ativo='Sim' AND combo<>'Sim'");
   $q->execute([$produto,$empresa]);
   if (!$q->fetchColumn()) throw new RuntimeException('Selecione um produto ativo que não seja combo.');
   $q=$pdo->prepare('INSERT INTO combo_grupo_produtos (empresa,combo_grupo_id,produto_id,acrescimo) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE acrescimo=VALUES(acrescimo)');
   $q->execute([$empresa,$grupo,$produto,$acrescimo]);
  } elseif ($acao==='editar_grupo') {
   $id=(int)($_POST['id']??0);$nome=trim((string)($_POST['nome']??''));
   $min=filter_var($_POST['minimo']??null,FILTER_VALIDATE_INT);$max=filter_var($_POST['maximo']??null,FILTER_VALIDATE_INT);
   if ($nome==='' || mb_strlen($nome)>120 || $min===false || $max===false || $min<0 || $max<1 || $min>$max || $max>20) throw new RuntimeException('Nome ou limites inválidos.');
   $q=$pdo->prepare('UPDATE combo_grupos SET nome=?,minimo=?,maximo=? WHERE id=? AND empresa=?');
   $q->execute([$nome,$min,$max,$id,$empresa]);
   if (!$q->rowCount()) { $q=$pdo->prepare('SELECT id FROM combo_grupos WHERE id=? AND empresa=?');$q->execute([$id,$empresa]);if(!$q->fetchColumn()) throw new RuntimeException('Grupo não encontrado.'); }
  } elseif ($acao==='excluir_grupo') {
   $id=(int)($_POST['id']??0);
   $q=$pdo->prepare('DELETE FROM combo_grupos WHERE id=? AND empresa=?');$q->execute([$id,$empresa]);
   if (!$q->rowCount()) throw new RuntimeException('Grupo não encontrado.');
  } elseif ($acao==='editar_item') {
   $id=(int)($_POST['id']??0);$acrescimo=str_replace(',','.',trim((string)($_POST['acrescimo']??'')));
   if (!preg_match('/^\\d{1,7}(\\.\\d{1,2})?$/',$acrescimo)) throw new RuntimeException('Acréscimo inválido.');
   $q=$pdo->prepare('UPDATE combo_grupo_produtos SET acrescimo=? WHERE id=? AND empresa=?');$q->execute([$acrescimo,$id,$empresa]);
   $q=$pdo->prepare('SELECT id FROM combo_grupo_produtos WHERE id=? AND empresa=?');$q->execute([$id,$empresa]);
   if (!$q->fetchColumn()) throw new RuntimeException('Produto vinculado não encontrado.');
  } elseif ($acao==='excluir_item') {
   $id=(int)($_POST['id']??0);
   $q=$pdo->prepare('DELETE FROM combo_grupo_produtos WHERE id=? AND empresa=?');$q->execute([$id,$empresa]);
   if (!$q->rowCount()) throw new RuntimeException('Produto vinculado não encontrado.');
  } else throw new RuntimeException('Operação inválida.');
  $pdo->commit();$sucesso='Configuração salva. Ainda não está disponível no cardápio.';
 } catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  $erro=$e instanceof RuntimeException?$e->getMessage():'Não foi possível salvar. Confira se o SQL foi executado.';
 }
}
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
try {
 $q=$pdo->prepare("SELECT id,nome,combo,ativo FROM produtos WHERE empresa=? ORDER BY nome");$q->execute([$empresa]);$produtos=$q->fetchAll(PDO::FETCH_ASSOC);
 $q=$pdo->prepare('SELECT c.*,p.nome AS produto_nome,p.ativo AS produto_ativo,p.delivery AS produto_delivery FROM combo_configuracoes c JOIN produtos p ON p.id=c.produto_combo_id AND p.empresa=c.empresa WHERE c.empresa=? ORDER BY c.id DESC');$q->execute([$empresa]);$configs=$q->fetchAll(PDO::FETCH_ASSOC);
 $q=$pdo->prepare('SELECT g.*,c.produto_combo_id FROM combo_grupos g JOIN combo_configuracoes c ON c.id=g.combo_configuracao_id AND c.empresa=g.empresa WHERE g.empresa=? ORDER BY g.id');$q->execute([$empresa]);$grupos=$q->fetchAll(PDO::FETCH_ASSOC);
 $q=$pdo->prepare('SELECT i.*,p.nome AS produto_nome FROM combo_grupo_produtos i JOIN combo_grupos g ON g.id=i.combo_grupo_id AND g.empresa=i.empresa JOIN produtos p ON p.id=i.produto_id AND p.empresa=i.empresa WHERE i.empresa=? ORDER BY i.id');$q->execute([$empresa]);$itens=$q->fetchAll(PDO::FETCH_ASSOC);
} catch(Throwable $e){http_response_code(503);exit('Estrutura de combos indisponível. Confira a migration no banco.');}
?><!doctype html><html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Configurar combos - Bora Comanda</title><style>body{font:15px system-ui;background:#faf7f4;color:#34251d;margin:0;padding:24px}main{max-width:1050px;margin:auto}section{background:white;padding:20px;border-radius:14px;margin:16px 0;box-shadow:0 2px 12px #0001}h1,h2{margin-top:0}form{display:flex;gap:10px;flex-wrap:wrap;align-items:end}label{display:grid;gap:5px;flex:1;min-width:150px}input,select,button{padding:10px;border:1px solid #d8c5b8;border-radius:8px;font:inherit}button{background:#924a27;color:white;cursor:pointer;border:0}small{color:#735e53}.alert{padding:12px;border-radius:8px;background:#fff0dc}.error{background:#ffe3e3}li{margin:8px 0}a{color:#924a27}.inline-form{display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;margin:0}.inline-form label{flex:0 1 auto;min-width:0}.inline-form input{width:100%;box-sizing:border-box;min-width:90px}.inline-form input[name=nome]{min-width:180px}.inline-form button{white-space:nowrap;min-height:42px}.danger{background:#b52c2c}.group-box{padding:18px;border:1px solid #eadbd2;border-radius:12px;margin:14px 0}.group-box ul{padding-left:0;list-style:none}.group-box li{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 0;border-top:1px solid #eee0d8;flex-wrap:wrap}.group-head{display:flex;gap:12px;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;margin-bottom:14px}.group-head .inline-form{flex:1}.group-head .inline-form label:first-of-type{flex:1;min-width:180px}.group-head .inline-form label:not(:first-of-type){width:105px}.item-name{font-weight:600;flex:1;min-width:180px}.item-actions{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}.item-actions .inline-form label{width:125px}.danger:hover{background:#941f1f}button:hover{filter:brightness(.94)}@media(max-width:700px){body{padding:12px}.group-box{padding:12px}.group-head,.group-box li{align-items:stretch}.group-head .inline-form,.item-actions{width:100%}.group-head .inline-form label:first-of-type{flex-basis:100%}.group-head .inline-form label:not(:first-of-type){flex:1}.inline-form button{width:auto}.item-name{min-width:100%}}</style></head><body><main><h1>Configuração de combos</h1><p><a href="../../index.php">Voltar ao painel</a> · Esta tela prepara os combos; a venda configurável permanece desativada até integrar o carrinho.</p>
<?php if($erro):?><p class="alert error"><?=h($erro)?></p><?php endif;?><?php if($sucesso):?><p class="alert"><?=h($sucesso)?></p><?php endif;?>
<section><h2>1. Criar configuração</h2><form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf_combos'])?>"><input type="hidden" name="acao" value="config"><label>Produto marcado como Combo<select name="produto_combo_id" required><option value="">Selecione</option><?php foreach($produtos as $p): if($p['combo']==='Sim'):?><option value="<?=h($p['id'])?>"><?=h($p['nome'])?></option><?php endif; endforeach;?></select></label><label>Tipo<select name="tipo"><option value="fixo">Fixo</option><option value="personalizavel">Personalizável</option></select></label><button>Salvar combo</button></form></section>
<section><h2>2. Criar grupo de escolhas</h2><form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf_combos'])?>"><input type="hidden" name="acao" value="grupo"><label>Combo<select name="config" required><?php foreach($configs as $c):?><option value="<?=h($c['id'])?>"><?=h($c['produto_nome'])?></option><?php endforeach;?></select></label><label>Nome do grupo<input name="nome" placeholder="Ex.: Tapiocas" maxlength="120" required></label><label>Mínimo<input type="number" name="minimo" value="1" min="0" max="20" required></label><label>Máximo<input type="number" name="maximo" value="2" min="1" max="20" required></label><button>Adicionar grupo</button></form></section>
<section><h2>3. Vincular produtos ao grupo</h2><form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf_combos'])?>"><input type="hidden" name="acao" value="item"><label>Grupo<select name="grupo" required><?php foreach($grupos as $g):?><option value="<?=h($g['id'])?>"><?=h($g['nome'])?> (#<?=h($g['combo_configuracao_id'])?>)</option><?php endforeach;?></select></label><label>Produto<select name="produto" required><?php foreach($produtos as $p): if($p['ativo']==='Sim'&&$p['combo']!=='Sim'):?><option value="<?=h($p['id'])?>"><?=h($p['nome'])?></option><?php endif; endforeach;?></select></label><label>Acréscimo (R$)<input name="acrescimo" value="0.00" inputmode="decimal" required></label><button>Adicionar produto</button></form></section>
<section><h2>Configurações cadastradas</h2><?php if(!$configs):?><p>Nenhum combo configurado ainda.</p><?php endif;?>
<?php foreach($configs as $c):?>
<h3><?=h($c['produto_nome'])?> <small>(<?=h($c['tipo'])?> · montagem ainda não integrada)</small></h3>
<?php
$gruposCombo=array_values(array_filter($grupos,static function($g)use($c){return (int)$g['combo_configuracao_id']===(int)$c['id'];}));
$pendencias=[];
if($c['tipo']==='personalizavel' && !$gruposCombo) $pendencias[]='Crie pelo menos um grupo de escolhas.';
foreach($gruposCombo as $gc){
 $quantidade=0;
 foreach($itens as $item){if((int)$item['combo_grupo_id']===(int)$gc['id']) $quantidade++;}
 if($quantidade<(int)$gc['minimo']) $pendencias[]='O grupo '.h($gc['nome']).' exige '.(int)$gc['minimo'].' escolhas, mas tem somente '.$quantidade.' produtos.';
}
?>
<?php if($c['produto_ativo']==='Sim' && $c['produto_delivery']==='Sim'):?><p class="alert">Este produto já pode aparecer no cardápio como combo simples. A montagem personalizada ainda não funciona no checkout.</p><?php endif;?>
<?php if($pendencias):?><div class="alert error"><strong>Pendências do cadastro:</strong><ul><?php foreach($pendencias as $pendencia):?><li><?=$pendencia?></li><?php endforeach;?></ul></div><?php else:?><p class="alert">Grupos cadastrados. Falta integrar seleção, valores e pedidos antes de liberar a montagem.</p><?php endif;?>
<?php foreach($grupos as $g): if($g['combo_configuracao_id']!=$c['id']) continue;?>
<div class="group-box"><div class="group-head">
<form class="inline-form" method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf_combos'])?>"><input type="hidden" name="acao" value="editar_grupo"><input type="hidden" name="id" value="<?=h($g['id'])?>"><label>Nome<input name="nome" maxlength="120" value="<?=h($g['nome'])?>" required></label><label>Mínimo<input type="number" name="minimo" min="0" max="20" value="<?=h($g['minimo'])?>" required></label><label>Máximo<input type="number" name="maximo" min="1" max="20" value="<?=h($g['maximo'])?>" required></label><button>Salvar grupo</button></form>
<form class="inline-form" method="post" onsubmit="return confirm('Excluir este grupo e todos os seus produtos vinculados?')"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf_combos'])?>"><input type="hidden" name="acao" value="excluir_grupo"><input type="hidden" name="id" value="<?=h($g['id'])?>"><button class="danger">Excluir grupo</button></form></div>
<ul><?php foreach($itens as $i): if($i['combo_grupo_id']!=$g['id']) continue;?><li><span class="item-name"><?=h($i['produto_nome'])?></span><div class="item-actions"><form class="inline-form" method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf_combos'])?>"><input type="hidden" name="acao" value="editar_item"><input type="hidden" name="id" value="<?=h($i['id'])?>"><label>Acréscimo (R$)<input name="acrescimo" inputmode="decimal" value="<?=h(number_format((float)$i['acrescimo'],2,'.',''))?>" required></label><button>Salvar preço</button></form>
<form class="inline-form" method="post" onsubmit="return confirm('Remover este produto do grupo?')"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf_combos'])?>"><input type="hidden" name="acao" value="excluir_item"><input type="hidden" name="id" value="<?=h($i['id'])?>"><button class="danger">Remover</button></form></div></li><?php endforeach;?></ul></div>
<?php endforeach; endforeach;?></section></main></body></html>