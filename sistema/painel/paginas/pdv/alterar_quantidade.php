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
 $stmt=$pdo->prepare("SELECT c.id,c.produto,c.quantidade,c.total_item,p.estoque,p.tem_estoque,p.nome AS nome_produto FROM carrinho c INNER JOIN produtos p ON p.id=c.produto AND p.empresa=c.empresa WHERE c.id=? AND c.sessao=? AND c.empresa=? AND c.pedido=0 AND c.mesa=? FOR UPDATE");
 $stmt->execute([$id,(string)$usuario,$empresa,$mesa]);
 $item=$stmt->fetch(PDO::FETCH_ASSOC);
 if(!$item){$pdo->rollBack();http_response_code(404);exit('Item não encontrado nesta comanda');}
 $atual=(float)$item['quantidade'];
 if($atual<=0)throw new RuntimeException('Quantidade atual inválida');

 $entrada=trim((string)($_POST['quantidade']??''));
 if(!preg_match('/^[0-9]+(?:[.,][0-9]{1,3})?$/D',$entrada)){
  $pdo->rollBack();http_response_code(400);exit('Informe uma quantidade válida maior que zero');
 }
 $nova=(float)str_replace(',','.',$entrada);
 if($nova<=0){
  $pdo->rollBack();http_response_code(400);exit('Para remover o item, use o botão de excluir');
 }
 if($item['tem_estoque']==='Sim' && $nova>(float)$item['estoque']){
  $pdo->rollBack();
  $nome=trim((string)$item['nome_produto']);
  $disponivel=rtrim(rtrim(number_format(max(0,(float)$item['estoque']),3,',','.'), '0'), ',');
  $solicitado=rtrim(rtrim(number_format($nova,3,',','.'), '0'), ',');
  http_response_code(409);
  exit('Estoque insuficiente para '.$nome.'. Disponível: '.$disponivel.'; solicitado: '.$solicitado.'. Ajuste a quantidade. Os demais itens da comanda foram preservados.');
 }
 $unitario=(float)$item['total_item']/$atual;
 $stmt=$pdo->prepare("UPDATE carrinho SET quantidade=?,total_item=? WHERE id=? AND empresa=?");
 $stmt->execute([$nova,round($unitario*$nova,2),$id,$empresa]);

 $pdo->commit();
 echo 'Excluído com Sucesso';
} catch(Throwable $e) {
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('[PDV quantidade] '.$e->getMessage());
 http_response_code(500);
 echo 'Não foi possível alterar a quantidade.';
}
