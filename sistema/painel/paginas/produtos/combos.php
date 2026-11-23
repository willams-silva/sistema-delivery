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
 $q=$pdo->prepare('SELECT c.*,p.nome AS produto_nome FROM combo_configuracoes c JOIN produtos p ON p.id=c.produto_combo_id AND p.empresa=c.empresa WHERE c.empresa=? ORDER BY c.id DESC');$q->execute([$empresa]);$configs=$q->fetchAll(PDO::FETCH_ASSOC);
 $q=$pdo->prepare('SELECT g.*,c.produto_combo_id FROM combo_grupos g JOIN combo_configuracoes c ON c.id=g.combo_configuracao_id AND c.empresa=g.empresa WHERE g.empresa=? ORDER BY g.id');$q->execute([$empresa]);$grupos=$q->fetchAll(PDO::FETCH_ASSOC);
 $q=$pdo->prepare('SELECT i.*,p.nome AS produto_nome FROM combo_grupo_produtos i JOIN combo_grupos g ON g.id=i.combo_grupo_id AND g.empresa=i.empresa JOIN produtos p ON p.id=i.produto_id AND p.empresa=i.empresa WHERE i.empresa=? ORDER BY i.id');$q->execute([$empresa]);$itens=$q->fetchAll(PDO::FETCH_ASSOC);
} catch(Throwable $e){http_response_code(503);exit('Estrutura de combos indisponível. Confira a migration no banco.');}
?><!doctype html><html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Configurar combos - Bora Comanda</title><style>body{font:15px system-ui;background:#faf7f4;color:#34251d;margin:0;padding:24px}main{max-width:1050px;margin:auto}section{background:white;padding:20px;border-radius:14px;margin:16px 0;box-shadow:0 2px 12px #0001}h1,h2{margin-top:0}form{display:flex;gap:10px;flex-wrap:wrap;align-items:end}label{display:grid;gap:5px;flex:1;min-width:150px}input,select,button{padding:10px;border:1px solid #d8c5b8;border-radius:8px;font:inherit}button{background:#924a27;color:white;cursor:pointer;border:0}small{color:#735e53}.alert{padding:12px;border-radius:8px;background:#fff0dc}.error{background:#ffe3e3}li{margin:8px 0}a{color:#924a27}</style></head><body><main><h1>Configuração de combos</h1><p><a href="../../index.php">Voltar ao painel</a> · Esta tela prepara os combos; a venda configurável permanece desativada até integrar o carrinho.</p>
<?php if($erro):?><p class="alert error"><?=h($erro)?></p><?php endif;?><?php if($sucesso):?><p class="alert"><?=h($sucesso)?></p><?php endif;?>
<section><h2>1. Criar configuração</h2><form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf_combos'])?>"><input type="hidden" name="acao" value="config"><label>Produto marcado como Combo<select name="produto_combo_id" required><option value="">Selecione</option><?php foreach($produtos as $p): if($p['combo']==='Sim'):?><option value="<?=h($p['id'])?>"><?=h($p['nome'])?></option><?php endif; endforeach;?></select></label><label>Tipo<select name="tipo"><option value="fixo">Fixo</option><option value="personalizavel">Personalizável</option></select></label><button>Salvar combo</button></form></section>
<section><h2>2. Criar grupo de escolhas</h2><form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf_combos'])?>"><input type="hidden" name="acao" value="grupo"><label>Combo<select name="config" required><?php foreach($configs as $c):?><option value="<?=h($c['id'])?>"><?=h($c['produto_nome'])?></option><?php endforeach;?></select></label><label>Nome do grupo<input name="nome" placeholder="Ex.: Tapiocas" maxlength="120" required></label><label>Mínimo<input type="number" name="minimo" value="1" min="0" max="20" required></label><label>Máximo<input type="number" name="maximo" value="2" min="1" max="20" required></label><button>Adicionar grupo</button></form></section>
<section><h2>3. Vincular produtos ao grupo</h2><form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf_combos'])?>"><input type="hidden" name="acao" value="item"><label>Grupo<select name="grupo" required><?php foreach($grupos as $g):?><option value="<?=h($g['id'])?>"><?=h($g['nome'])?> (#<?=h($g['combo_configuracao_id'])?>)</option><?php endforeach;?></select></label><label>Produto<select name="produto" required><?php foreach($produtos as $p): if($p['ativo']==='Sim'&&$p['combo']!=='Sim'):?><option value="<?=h($p['id'])?>"><?=h($p['nome'])?></option><?php endif; endforeach;?></select></label><label>Acréscimo (R$)<input name="acrescimo" value="0.00" inputmode="decimal" required></label><button>Adicionar produto</button></form></section>
<section><h2>Configurações cadastradas</h2><?php if(!$configs):?><p>Nenhum combo configurado ainda.</p><?php endif;?><?php foreach($configs as $c):?><h3><?=h($c['produto_nome'])?> <small>(<?=h($c['tipo'])?> · não publicado)</small></h3><ul><?php foreach($grupos as $g): if($g['combo_configuracao_id']==$c['id']):?><li><b><?=h($g['nome'])?></b> — escolha de <?=h($g['minimo'])?> a <?=h($g['maximo'])?><ul><?php foreach($itens as $i): if($i['combo_grupo_id']==$g['id']):?><li><?=h($i['produto_nome'])?><?php if((float)$i['acrescimo']>0):?> (+ R$ <?=h(number_format((float)$i['acrescimo'],2,',','.'))?>)<?php endif;?></li><?php endif; endforeach;?></ul></li><?php endif; endforeach;?></ul><?php endforeach;?></section></main></body></html>