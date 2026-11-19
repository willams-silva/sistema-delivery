<?php
@session_start();require_once('../../conexao.php');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método inválido.');}
if(!in_array($_SESSION['nivel']??'', ['Administrador','Gerente'],true)){http_response_code(403);exit('Acesso negado.');}
$empresa=(int)($_SESSION['empresa']??0);$usuario=(int)($_SESSION['id_usuario']??$_SESSION['id']??0);
if($empresa<1||empty($_SESSION['csrf_conc'])||!hash_equals($_SESSION['csrf_conc'],(string)($_POST['csrf_conc']??''))){http_response_code(403);exit('Sessão inválida.');}
if(!isset($_FILES['arquivo'])||$_FILES['arquivo']['error']!==UPLOAD_ERR_OK||$_FILES['arquivo']['size']>2097152||$_FILES['arquivo']['size']<1){http_response_code(400);exit('Envie um arquivo CSV ou OFX de até 2 MB.');}
$tmp=$_FILES['arquivo']['tmp_name'];$nome=basename((string)$_FILES['arquivo']['name']);$ext=strtolower(pathinfo($nome,PATHINFO_EXTENSION));
if(!in_array($ext,['csv','ofx'],true)||!is_uploaded_file($tmp)){http_response_code(400);exit('Formato inválido.');}
$raw=file_get_contents($tmp);if($raw===false){http_response_code(400);exit('Arquivo ilegível.');}
$hash=hash('sha256',$raw);$linhas=[];
$dataValida=static function($v){$v=trim((string)$v);foreach(['Y-m-d','d/m/Y','d-m-Y','Ymd'] as $fmt){$d=DateTimeImmutable::createFromFormat('!'.$fmt,$v);if($d&&$d->format($fmt)===$v)return $d->format('Y-m-d');}return null;};
$valorValido=static function($v){$v=trim(str_replace(['R$',' '],'',(string)$v));if(str_contains($v,','))$v=str_replace(',','. ',str_replace('.','',$v));$v=str_replace(' ','',$v);return preg_match('/^-?\d{1,10}(?:\.\d{1,2})?$/',$v)?number_format((float)$v,2,'.',''):null;};
if($ext==='csv'){
 $fp=fopen($tmp,'r');$header=fgetcsv($fp,0,';');if(!$header){http_response_code(400);exit('CSV vazio.');}
 $sep=count($header)>1?';':',';if($sep===','){rewind($fp);$header=fgetcsv($fp,0,',');}
 $header=array_map(static function($v){return strtolower(trim(str_replace("\xEF\xBB\xBF",'',(string)$v)));},$header);
 $col=array_flip($header);
 foreach(['data','valor','descricao'] as $key)if(!isset($col[$key])){fclose($fp);http_response_code(400);exit('CSV precisa das colunas: data;valor;descricao;identificador (opcional).');}
 while(($row=fgetcsv($fp,0,$sep))!==false){if(count($linhas)>=5000)break;$data=$dataValida($row[$col['data']]??'');$valor=$valorValido($row[$col['valor']]??'');if(!$data||$valor===null)continue;$linhas[]=[$data,$valor,mb_substr(trim($row[$col['descricao']]??''),0,500),mb_substr(trim($row[$col['identificador']]??''),0,120)];}
 fclose($fp);
}else{
 // OFX 1.x usa SGML sem fechamento de tags; extrai apenas blocos STMTTRN, sem executar entidades.
 if(preg_match_all('/<STMTTRN>(.*?)(?:<\/STMTTRN>|(?=<STMTTRN>)|$)/is',$raw,$matches)){
  foreach(array_slice($matches[1],0,5000) as $block){
   $get=static function($tag)use($block){return preg_match('/<'.$tag.'>\s*([^<\r\n]+)/i',$block,$m)?trim($m[1]):'';};
   $data=$dataValida(substr($get('DTPOSTED'),0,8));$valor=$valorValido($get('TRNAMT'));
   if(!$data||$valor===null)continue;
   $linhas[]=[$data,$valor,mb_substr($get('MEMO')?:$get('NAME'),0,500),mb_substr($get('FITID'),0,120)];
  }
 }
}
if(!$linhas){http_response_code(422);exit('Nenhuma movimentação válida. Confira o layout do arquivo.');}
try{
 $pdo->beginTransaction();
 $ins=$pdo->prepare('INSERT INTO conciliacao_extrato_importacoes (empresa,nome_arquivo,hash_arquivo,formato,usuario_id) VALUES (?,?,?,?,?)');
 $ins->execute([$empresa,mb_substr($nome,0,255),$hash,$ext,$usuario?:null]);$importacao=(int)$pdo->lastInsertId();
 $rowIns=$pdo->prepare('INSERT IGNORE INTO conciliacao_extrato_linhas (empresa,importacao_id,data_movimento,valor,descricao,identificador,hash_linha) VALUES (?,?,?,?,?,?,?)');
 $ocorrencias=[];$novas=0;
 foreach($linhas as $row){
  $base=implode('|',$row);$ocorrencias[$base]=($ocorrencias[$base]??0)+1;
  // FITID/identificador prevalece; sem identificador, assinatura e ocorrência ajudam a detectar reimportação.
  $assinatura=$row[3]!==''?'id|'.$row[3].'|'.$row[1]:'linha|'.$base.'|'.$ocorrencias[$base];
  $rowIns->execute([$empresa,$importacao,$row[0],$row[1],$row[2],$row[3]?:null,hash('sha256',$assinatura)]);$novas+=$rowIns->rowCount();
 }
 $pdo->commit();
 header('Location: ../index.php?pagina=conciliacao_financeira&aba=liquidacoes&extrato_importado='.$novas,true,303);exit;
}catch(PDOException $e){if($pdo->inTransaction())$pdo->rollBack();if($e->getCode()==='23000'){http_response_code(409);exit('Este arquivo já foi importado para a empresa.');}error_log('Importacao extrato: '.$e->getMessage());http_response_code(500);exit('Falha ao importar. Execute a migração SQL.');}
