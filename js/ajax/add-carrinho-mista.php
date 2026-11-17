<?php
@session_start();
require_once('../../sistema/conexao.php');
$id_empresa = (int)($_SESSION['empresa'] ?? 0);
if (!$id_empresa || empty($_SESSION['sessao_usuario'])) { http_response_code(403); exit('Sessão expirada. Atualize a página.'); }
require_once('../../sistema/painel/buscar_config.php');
if (($url_site ?? '') !== 'rainhadastapiocas') { http_response_code(403); exit('Loja inválida'); }
$produto_id = (int)($_POST['produto'] ?? 0);
$inclusas = json_decode($_POST['inclusas'] ?? '', true);
$extras = json_decode($_POST['extras'] ?? '', true);
if (!is_array($inclusas) || !is_array($extras) || !$produto_id || count($extras)>100) exit('Seleção inválida');
$stmt = $pdo->prepare("SELECT p.*, m.limite_proteinas FROM produtos p JOIN categorias c ON c.id = p.categoria AND c.empresa = p.empresa JOIN tapiocas_mistas_config m ON m.produto_id = p.id AND m.empresa = p.empresa WHERE p.id = ? AND p.empresa = ? AND c.nome = 'TAPIOCAS MISTAS' AND p.ativo = 'Sim' AND (p.tem_estoque = 'Não' OR p.estoque > 0) LIMIT 1");
$stmt->execute([$produto_id, $id_empresa]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$p) exit('Tapioca indisponível');
$limite = (int)$p['limite_proteinas'];
$ids = array_map('intval', $inclusas);
if (!in_array($limite,[2,3],true) || count($ids)!==$limite || count(array_unique($ids))!==$limite || min($ids)<=0) exit('Escolha exatamente '.$limite.' proteínas diferentes.');
$stmt = $pdo->prepare("SELECT id, nome, valor, tipo FROM adicionais_cat WHERE categoria = ? AND empresa = ? AND ativo = 'Sim'");
$stmt->execute([(int)$p['categoria'], $id_empresa]);
$catalogo = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) $catalogo[(int)$a['id']]=$a;
$nomes_inclusas=[];
foreach ($ids as $id) {
    if (!isset($catalogo[$id]) || $catalogo[$id]['tipo'] !== 'proteina') exit('Proteína inclusa inválida');
    $nomes_inclusas[]=$catalogo[$id]['nome'];
}
$total = ($p['promocao']==='Sim' && (float)$p['val_promocional']>0) ? (float)$p['val_promocional'] : (float)$p['valor_venda'];
$extras_validados=[];
foreach ($extras as $key=>$value) {
    if (!ctype_digit((string)$key) || !isset($catalogo[(int)$key]) || !is_numeric($value)) exit('Adicional inválido');
    $qtd=(int)$value;
    if ($qtd<0 || $qtd>20 || (string)$qtd !== (string)$value) exit('Quantidade inválida');
    if ($qtd===0) continue;
    $a=$catalogo[(int)$key];
    if (!in_array($a['tipo'],['proteina','adicional','opcional'],true)) exit('Tipo inválido');
    if ($a['tipo']==='opcional' && $qtd!==1) exit('Opcional inválido');
    $valor=$a['tipo']==='opcional'?0:(float)$a['valor'];
    $total += $qtd*$valor;
    $extras_validados[]=['id'=>(int)$key,'quantidade'=>$qtd,'valor'=>$valor];
}
$obs=trim((string)($_POST['obs'] ?? ''));
$obs=mb_substr($obs,0,180,'UTF-8');
$nome_produto=$p['nome'].' (Inclusas: '.implode(', ', $nomes_inclusas).')';
// Limite de tamanho do campo legado; informação integral também vai para obs.
$obs_final='Inclusas: '.implode(', ', $nomes_inclusas).($obs!==''?' | '.$obs:'');
$obs_final=mb_substr($obs_final,0,255,'UTF-8');
$nome_produto=mb_substr($nome_produto,0,255,'UTF-8');
$status=$p['preparado']!=='Não'?'Aguardando':'';
$pedido=(int)($_SESSION['id_edicao'] ?? 0);
$mesa=(int)($_SESSION['id_ab_mesa'] ?? 0);
if ($mesa) $status='Aguardando';
try {
 $pdo->beginTransaction();
 $stmt=$pdo->prepare("INSERT INTO carrinho (sessao, cliente, produto, quantidade, total_item, obs, pedido, id_sabor, data, variacao, mesa, nome_cliente, sabores, nome_produto, borda, categoria, status, hora, empresa) VALUES (?,0,?,1,?,?,?,0,curDate(),0,?,'',?,?,0,?,?,curTime(),?)");
 $stmt->execute([$_SESSION['sessao_usuario'],$produto_id,$total,$obs_final,$pedido,$mesa,$limite,$nome_produto,(int)$p['categoria'],$status,$id_empresa]);
 $carrinho=(int)$pdo->lastInsertId();
 $stmt=$pdo->prepare("INSERT INTO temp (sessao,tabela,id_item,id_sabor,carrinho,data,categoria,quantidade,valor_item,empresa) VALUES (?,'adicionais',?,0,?,curDate(),'Sim',?,?,?)");
 foreach ($extras_validados as $a) $stmt->execute([$_SESSION['sessao_usuario'],$a['id'],$carrinho,$a['quantidade'],$a['valor'],$id_empresa]);
 if ($pedido) {
    $stmt=$pdo->prepare('SELECT valor, total_pago FROM vendas WHERE id = ? AND empresa = ? FOR UPDATE');
    $stmt->execute([$pedido,$id_empresa]);
    $v=$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$v) throw new Exception('Venda não encontrada');
    $valor=(float)$v['valor']+$total;
    $pago=max((float)$v['total_pago'],$valor);
    $stmt=$pdo->prepare('UPDATE vendas SET valor = ?, total_pago = ?, troco = ? WHERE id = ? AND empresa = ?');
    $stmt->execute([$valor,$pago,max(0,$pago-$valor),$pedido,$id_empresa]);
 }
 $pdo->commit();
 echo 'Inserido com Sucesso';
} catch (Throwable $e) {
 if ($pdo->inTransaction()) $pdo->rollBack();
 http_response_code(500); echo 'Não foi possível adicionar a tapioca. Tente novamente.';
}
