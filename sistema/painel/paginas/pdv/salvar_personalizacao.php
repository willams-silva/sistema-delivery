<?php
@session_start();
require_once('../../../conexao.php');
$empresa=(int)($_SESSION['empresa']??0);
$usuario=(int)($_SESSION['id']??0);
$sessao=(string)($_SESSION['sessao_usuario']??'');
if (!$empresa || !$usuario || $sessao==='') { http_response_code(403); exit('Sessão expirada'); }
$produto=(int)($_POST['produto']??0);
$extras=json_decode((string)($_POST['extras']??''),true);
if (!$produto || !is_array($extras) || count($extras)>100) exit('Seleção inválida');
$stmt=$pdo->prepare("SELECT p.*, c.nome AS categoria_nome FROM produtos p JOIN categorias c ON c.id=p.categoria AND c.empresa=p.empresa WHERE p.id=? AND p.empresa=? AND p.ativo='Sim' AND c.ativo='Sim' LIMIT 1");
$stmt->execute([$produto,$empresa]);
$p=$stmt->fetch(PDO::FETCH_ASSOC);
if (!$p || mb_stripos((string)$p['categoria_nome'],'tapioca')===false) exit('Tapioca indisponível');
if ($p['tem_estoque']==='Sim' && (float)$p['estoque']<1) exit('Estoque insuficiente');
$stmt=$pdo->prepare("SELECT id,nome,valor,tipo FROM adicionais_cat WHERE categoria=? AND empresa=? AND ativo='Sim'");
$stmt->execute([(int)$p['categoria'],$empresa]);
$catalogo=[];
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) $catalogo[(int)$a['id']]=$a;
$base=($p['promocao']==='Sim' && (float)$p['val_promocional']>0)?(float)$p['val_promocional']:(float)$p['valor_venda'];
if($base<=0) exit('Preço inválido');
$total=$base;
$selecionados=[];
foreach($extras as $key=>$value){
 if(!ctype_digit((string)$key)||!isset($catalogo[(int)$key])||!is_numeric($value)) exit('Adicional inválido');
 $qtd=(int)$value;
 if($qtd<0||$qtd>20||(string)$qtd!==(string)$value) exit('Quantidade inválida');
 if(!$qtd) continue;
 $a=$catalogo[(int)$key];
 if(!in_array($a['tipo'],['adicional','proteina','opcional'],true)) exit('Tipo inválido');
 if($a['tipo']==='opcional' && $qtd!==1) exit('Opcional inválido');
 $valor=$a['tipo']==='opcional'?0:(float)$a['valor'];
 if($valor<0) exit('Preço de adicional inválido');
 $total+=$qtd*$valor;
 $selecionados[]=['id'=>(int)$key,'qtd'=>$qtd,'valor'=>$valor];
}
$pedido=(int)($_SESSION['id_edicao']??0);
$mesa=(int)($_SESSION['id_ab_mesa']??0);
$status=$p['preparado']!=='Não'?'Aguardando':'';
try{
 $pdo->beginTransaction();
 // Mesma estrutura de INSERT já utilizada pelo fluxo de tapiocas mistas.
 // O valor_unitario é atualizado depois, sem depender de colunas opcionais no INSERT.
 $stmt=$pdo->prepare("INSERT INTO carrinho (sessao,cliente,produto,quantidade,total_item,obs,pedido,id_sabor,data,variacao,mesa,nome_cliente,sabores,nome_produto,borda,categoria,status,hora,empresa) VALUES (?,0,?,1,?,'',?,0,curDate(),0,?,'',0,'',0,?,?,curTime(),?)");
 if (!$stmt->execute([$sessao,$produto,$total,$pedido,$mesa,(int)$p['categoria'],$status,$empresa])) {
  throw new RuntimeException('Falha ao inserir produto no carrinho: '.implode(' ', $stmt->errorInfo()));
 }
 $idCarrinho=(int)$pdo->lastInsertId();
 if ($idCarrinho<=0) throw new RuntimeException('Carrinho não gerado');
 $stmt=$pdo->prepare("INSERT INTO temp (sessao,tabela,id_item,id_sabor,carrinho,data,categoria,quantidade,valor_item,empresa) VALUES (?,'adicionais',?,0,?,curDate(),'Sim',?,?,?)");
 foreach($selecionados as $a) {
  if (!$stmt->execute([$sessao,$a['id'],$idCarrinho,$a['qtd'],$a['valor'],$empresa])) {
   throw new RuntimeException('Falha ao registrar adicional: '.implode(' ', $stmt->errorInfo()));
  }
 }
 if($pedido){
  $stmt=$pdo->prepare("SELECT valor,total_pago FROM vendas WHERE id=? AND empresa=? FOR UPDATE");
  $stmt->execute([$pedido,$empresa]);$v=$stmt->fetch(PDO::FETCH_ASSOC);
  if(!$v) throw new Exception('Venda não encontrada');
  $novo=(float)$v['valor']+$total;$pago=max((float)$v['total_pago'],$novo);
  $stmt=$pdo->prepare("UPDATE vendas SET valor=?,total_pago=?,troco=? WHERE id=? AND empresa=?");
  $stmt->execute([$novo,$pago,max(0,$pago-$novo),$pedido,$empresa]);
 }
 $pdo->commit();
 echo 'Inserido com Sucesso';
}catch(Throwable $e){
 if($pdo->inTransaction()) $pdo->rollBack();
 error_log('[PDV personalizacao] empresa='.$empresa.' produto='.$produto.' erro='.$e->getMessage());
 http_response_code(500);echo 'Não foi possível adicionar o item. Consulte o log PHP da Hostinger.';
}
