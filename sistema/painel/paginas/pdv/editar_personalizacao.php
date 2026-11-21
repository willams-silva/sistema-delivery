<?php
@session_start();
require_once('../../../conexao.php');
header('Content-Type: application/json; charset=utf-8');
$empresa=(int)($_SESSION['empresa']??0);$usuario=(int)($_SESSION['id']??0);
$mesa=isset($_SESSION['id_ab_mesa'])&&(int)$_SESSION['id_ab_mesa']>0?(string)(int)$_SESSION['id_ab_mesa']:'';
$id=(int)($_POST['carrinho']??0);$modo=(string)($_POST['modo']??'consultar');
if(!$empresa||!$usuario||$id<=0){http_response_code(403);exit(json_encode(['erro'=>'Sessão ou item inválido']));}
try{
 $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
 if($modo==='salvar')$pdo->beginTransaction();
 $sql="SELECT c.*,p.categoria,p.nome,cat.nome AS categoria_nome FROM carrinho c JOIN produtos p ON p.id=c.produto AND p.empresa=c.empresa JOIN categorias cat ON cat.id=p.categoria AND cat.empresa=p.empresa WHERE c.id=? AND c.empresa=? AND c.sessao=? AND c.pedido=0 AND c.mesa=? LIMIT 1";
 $st=$pdo->prepare($sql.($modo==='salvar'?' FOR UPDATE':''));$st->execute([$id,$empresa,(string)$usuario,$mesa]);$item=$st->fetch(PDO::FETCH_ASSOC);
 if(!$item||mb_stripos((string)$item['categoria_nome'],'tapioca')===false)throw new RuntimeException('Tapioca não encontrada nesta comanda');
 $st=$pdo->prepare("SELECT id,nome,tipo,valor FROM adicionais_cat WHERE empresa=? AND categoria=? AND ativo='Sim' AND tipo IN ('adicional','proteina','opcional') ORDER BY tipo,nome");
 $st->execute([$empresa,$item['categoria']]);$catalogo=[];foreach($st->fetchAll(PDO::FETCH_ASSOC) as $a)$catalogo[(int)$a['id']]=$a;
 $st=$pdo->prepare("SELECT id_item,quantidade,valor_item FROM temp WHERE carrinho=? AND empresa=? AND tabela='adicionais'");$st->execute([$id,$empresa]);
 $selecionados=[];$extrasAntigos=0;
 foreach($st->fetchAll(PDO::FETCH_ASSOC) as $a){$selecionados[(int)$a['id_item']]=(int)$a['quantidade'];$extrasAntigos+=(int)$a['quantidade']*(float)$a['valor_item'];}
 if($modo==='consultar'){
  $itens=[];foreach($catalogo as $a)$itens[]=['id'=>(int)$a['id'],'nome'=>$a['nome'],'tipo'=>$a['tipo'],'valor'=>$a['tipo']==='opcional'?0:(float)$a['valor']];
  echo json_encode(['nome'=>$item['nome'],'itens'=>$itens,'selecionados'=>$selecionados],JSON_UNESCAPED_UNICODE);exit;
 }
 if($modo!=='salvar')throw new RuntimeException('Operação inválida');
 $entrada=json_decode((string)($_POST['extras']??''),true);
 if(!is_array($entrada)||count($entrada)>100)throw new RuntimeException('Seleção inválida');
 $novos=[];$totalExtras=0;
 foreach($entrada as $key=>$value){
  if(!ctype_digit((string)$key)||!isset($catalogo[(int)$key])||!is_numeric($value))throw new RuntimeException('Adicional inválido');
  $q=(int)$value;if($q<0||$q>20||(string)$q!==(string)$value)throw new RuntimeException('Quantidade inválida');
  if(!$q)continue;$a=$catalogo[(int)$key];if($a['tipo']==='opcional'&&$q!==1)throw new RuntimeException('Opcional inválido');
  $preco=$a['tipo']==='opcional'?0:(float)$a['valor'];if($preco<0)throw new RuntimeException('Preço inválido');
  $totalExtras+=$q*$preco;$novos[]=['id'=>(int)$key,'q'=>$q,'preco'=>$preco];
 }
 $qtd=(float)$item['quantidade'];if($qtd<=0)throw new RuntimeException('Quantidade do item inválida');
 $base=round((float)$item['total_item']/$qtd-$extrasAntigos,2);
 if($base<0)throw new RuntimeException('Valor anterior inconsistente');
 $novoTotal=round(($base+$totalExtras)*$qtd,2);
 $st=$pdo->prepare("DELETE FROM temp WHERE carrinho=? AND empresa=? AND tabela='adicionais'");$st->execute([$id,$empresa]);
 $sessao=(string)($_SESSION['sessao_usuario']??$usuario);
 $st=$pdo->prepare("INSERT INTO temp(sessao,tabela,id_item,id_sabor,carrinho,data,categoria,quantidade,valor_item,empresa) VALUES(?,'adicionais',?,0,?,CURDATE(),'Sim',?,?,?)");
 foreach($novos as $a)$st->execute([$sessao,$a['id'],$id,$a['q'],$a['preco'],$empresa]);
 $st=$pdo->prepare("UPDATE carrinho SET total_item=?,valor_unitario=? WHERE id=? AND empresa=? AND sessao=? AND pedido=0 AND mesa=?");
 $st->execute([$novoTotal,round($novoTotal/$qtd,2),$id,$empresa,(string)$usuario,$mesa]);
 $pdo->commit();echo json_encode(['ok'=>true,'total'=>$novoTotal]);
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(400);echo json_encode(['erro'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
