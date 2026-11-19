<?php
@session_start();
require_once('../../conexao.php');
if(!in_array($_SESSION['nivel']??'', ['Administrador','Gerente'],true)){http_response_code(403);exit('Acesso negado.');}
$empresa=(int)($_SESSION['empresa']??0);if($empresa<1){http_response_code(403);exit('Empresa inválida.');}
$inicio=(string)($_GET['inicio']??date('Y-m-01'));$fim=(string)($_GET['fim']??date('Y-m-d'));
$validar=static function($d){return preg_match('/^\d{4}-\d{2}-\d{2}$/D',$d)&&checkdate((int)substr($d,5,2),(int)substr($d,8,2),(int)substr($d,0,4));};
if(!$validar($inicio)||!$validar($fim)||$inicio>$fim){http_response_code(400);exit('Período inválido.');}
$situacao=(string)($_GET['situacao']??'todos');
if(!in_array($situacao,['todos','pendentes','vinculados'],true)){http_response_code(400);exit('Situação inválida.');}
$where="e.empresa=:empresa AND e.data_movimento BETWEEN :inicio AND :fim";
if($situacao==='pendentes')$where.=" AND e.liquidacao_id IS NULL";
if($situacao==='vinculados')$where.=" AND e.liquidacao_id IS NOT NULL";
try{
 $st=$pdo->prepare("SELECT e.id,e.data_movimento,e.valor,e.descricao,e.identificador,e.liquidacao_id,i.nome_arquivo,
 l.valor_creditado,l.cancelado_em
 FROM conciliacao_extrato_linhas e
 INNER JOIN conciliacao_extrato_importacoes i ON i.id=e.importacao_id AND i.empresa=e.empresa
 LEFT JOIN conciliacao_liquidacoes l ON l.id=e.liquidacao_id AND l.empresa=e.empresa
 WHERE ".$where." ORDER BY e.data_movimento DESC,e.id DESC");
 $st->execute([':empresa'=>$empresa,':inicio'=>$inicio,':fim'=>$fim]);
}catch(Throwable $e){error_log('Exportar extrato conciliacao: '.$e->getMessage());http_response_code(500);exit('Não foi possível gerar o relatório.');}
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="extrato_conciliacao_'.$inicio.'_a_'.$fim.'.csv"');
header('Cache-Control: no-store, private');
$out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");
fputcsv($out,['Movimentação ID','Data','Valor (R$)','Descrição','Identificador','Arquivo de origem','Crédito vinculado ID','Valor do crédito (R$)','Situação do vínculo'],';');
$safe=static function($v){$s=(string)($v??'');return preg_match('/^[\s\x00-\x1F]*[=+@\-]/u',$s)?"'".$s:$s;};
while($row=$st->fetch(PDO::FETCH_ASSOC)){
 $status='Sem vínculo';
 if($row['liquidacao_id']!==null){
  $status=($row['valor_creditado']===null||$row['cancelado_em']!==null||(int)round((float)$row['valor']*100)!==(int)round((float)$row['valor_creditado']*100))?'Vínculo com divergência':'Vinculado (não aprovado)';
 }
 fputcsv($out,[(int)$row['id'],$row['data_movimento'],number_format((float)$row['valor'],2,',',''),$safe($row['descricao']),$safe($row['identificador']),$safe($row['nome_arquivo']),$row['liquidacao_id']===null?'':(int)$row['liquidacao_id'],$row['valor_creditado']===null?'':number_format((float)$row['valor_creditado'],2,',',''),$status],';');
}
fclose($out);
