<?php
@session_start();
require_once('../../conexao.php');
if (!in_array($_SESSION['nivel']??'', ['Administrador','Gerente'],true)) {http_response_code(403);exit('Acesso negado.');}
$empresa=(int)($_SESSION['empresa']??0);
if ($empresa<=0) {http_response_code(403);exit('Empresa inválida.');}
$inicio=(string)($_GET['inicio']??date('Y-m-01'));
$fim=(string)($_GET['fim']??date('Y-m-d'));
$validar=static function($d){return preg_match('/^\d{4}-\d{2}-\d{2}$/',$d) && checkdate((int)substr($d,5,2),(int)substr($d,8,2),(int)substr($d,0,4));};
if (!$validar($inicio)||!$validar($fim)||$inicio>$fim) {http_response_code(400);exit('Período inválido.');}
try {
 $st=$pdo->prepare("SELECT l.id,fp.nome AS forma,l.data_referencia,l.data_credito,l.valor_creditado,l.identificador,l.observacao,l.criado_em,l.cancelado_em,l.cancelado_por,l.motivo_cancelamento
 FROM conciliacao_liquidacoes l
 INNER JOIN formas_pgto fp ON fp.id=l.forma_pgto AND fp.empresa=l.empresa
 WHERE l.empresa=:empresa AND l.data_referencia BETWEEN :inicio AND :fim
 ORDER BY l.data_referencia DESC,l.id DESC");
 $st->execute([':empresa'=>$empresa,':inicio'=>$inicio,':fim'=>$fim]);
} catch(Throwable $e) {error_log('Exportacao conciliacao: '.$e->getMessage());http_response_code(500);exit('Não foi possível exportar.');}
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="liquidacoes_'.$inicio.'_a_'.$fim.'.csv"');
header('Cache-Control: no-store, private');
$out=fopen('php://output','w');
fwrite($out,"\xEF\xBB\xBF");
fputcsv($out,['ID','Forma de pagamento','Data referência','Data crédito','Valor creditado (R$)','Identificador','Observação','Registrado em','Situação','Cancelado em','Cancelado por','Justificativa'],';');
$seguro=static function($v) {
 $s=(string)($v??'');
 // Evita interpretação de fórmulas ao abrir o CSV em planilhas.
 if (preg_match('/^[\s\x00-\x1F]*[=+@\-]/u',$s)) return "'".$s;
 return $s;
};
while($row=$st->fetch(PDO::FETCH_ASSOC)) {
 fputcsv($out,[
  $row['id'],$seguro($row['forma']),$row['data_referencia'],$row['data_credito'],
  number_format((float)$row['valor_creditado'],2,',',''),
  $seguro($row['identificador']),$seguro($row['observacao']),$row['criado_em'], $row['cancelado_em']?'Cancelado':'Ativo',$row['cancelado_em'], $row['cancelado_por'],$seguro($row['motivo_cancelamento'])
 ],';');
}
fclose($out);
