<?php
@session_start();
require_once("../../../conexao.php");
$empresa=(int)($_SESSION['empresa']??0);
$usuario=(int)($_SESSION['id']??0);
$mesa=isset($_SESSION['id_ab_mesa']) && (int)$_SESSION['id_ab_mesa']>0 ? (string)(int)$_SESSION['id_ab_mesa'] : '';
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if($empresa<=0||$usuario<=0){http_response_code(403);exit('Sessão expirada');}
if(!$id||$id<=0){http_response_code(400);exit('Item inválido');}
try {
 $pdo->beginTransaction();
 $stmt=$pdo->prepare("SELECT c.id,c.produto,c.quantidade,c.total_item,p.estoque,p.tem_estoque FROM carrinho c INNER JOIN produtos p ON p.id=c.produto AND p.empresa=c.empresa WHERE c.id=? AND c.sessao=? AND c.empresa=? AND c.pedido=0 AND c.mesa=? FOR UPDATE");
 $stmt->execute([$id,(string)$usuario,$empresa,$mesa]);
 $item=$stmt->fetch(PDO::FETCH_ASSOC);
 if(!$item){$pdo->rollBack();http_response_code(404);exit('Item não encontrado nesta comanda');}
 $atual=(float)$item['quantidade'];
 if($atual<=0)throw new RuntimeException('Quantidade atual inválida');

 $nova=$atual-1;
 if($nova<=0){
  $stmt=$pdo->prepare("DELETE FROM temp WHERE carrinho=? AND empresa=? AND tabela='adicionais'");
  $stmt->execute([$id,$empresa]);
  $stmt=$pdo->prepare("DELETE FROM carrinho WHERE id=? AND empresa=?");
  $stmt->execute([$id,$empresa]);
 }else{
  $unitario=(float)$item['total_item']/$atual;
  $stmt=$pdo->prepare("UPDATE carrinho SET quantidade=?,total_item=? WHERE id=? AND empresa=?");
  $stmt->execute([$nova,round($unitario*$nova,2),$id,$empresa]);
 }

 $pdo->commit();
 echo 'Excluído com Sucesso';
} catch(Throwable $e) {
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('[PDV quantidade] '.$e->getMessage());
 http_response_code(500);
 echo 'Não foi possível alterar a quantidade.';
}
