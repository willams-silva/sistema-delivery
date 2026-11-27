<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once('../../sistema/conexao.php');
$id_empresa=(int)($_SESSION['empresa']??0);
function resposta($data,$code=200){http_response_code($code);echo json_encode($data);exit;}
if(!$id_empresa||$_SERVER['REQUEST_METHOD']!=='POST'||empty($_SESSION['bc_consulta_csrf'])||!hash_equals($_SESSION['bc_consulta_csrf'],(string)($_POST['csrf']??'')))resposta(['erro'=>'Sessão inválida'],403);
$q=$pdo->prepare("SELECT ativo FROM bc_frete_consulta_config WHERE empresa=?");$q->execute([$id_empresa]);if((int)$q->fetchColumn()!==1)resposta(['erro'=>'Consulta indisponível'],403);
$acao=$_POST['acao']??'';
$token=$_SESSION['bc_consulta_token'][$id_empresa]??'';
if($acao==='solicitar'){
 require_once('../../sistema/painel/buscar_config.php');
 require_once __DIR__.'/frete_ruas_helper.php';
 $end=[];foreach(['rua','numero','bairro','cidade','cep'] as $k){$end[$k]=trim((string)($_POST[$k]??''));if(strlen($end[$k])>200)resposta(['erro'=>'Endereço inválido'],400);}
 if(!$end['rua']||!$end['numero']||!$end['bairro']||strlen(preg_replace('/\D/','',$end['cep']))!==8)resposta(['erro'=>'Preencha o endereço completo'],400);
 $end['entrega']='Delivery';
 if(bcFreteHibrido($pdo,$id_empresa,$end,['modo'=>$entrega_distancia,'key'=>$chave_api_maps,'lat'=>$latitude_rest,'lng'=>$longitude_rest,'limite'=>$distancia_entrega_km,'km'=>$valor_km])!==null)resposta(['erro'=>'Já existe uma taxa automática para este endereço'],409);
 $endereco=json_encode($end,JSON_UNESCAPED_UNICODE);
 if($token){$q=$pdo->prepare("SELECT id,endereco,status FROM bc_frete_consultas WHERE empresa=? AND token_hash=? AND expira_em>NOW() AND status IN ('pendente','cotado','aceito')");$q->execute([$id_empresa,hash('sha256',$token)]);$old=$q->fetch(PDO::FETCH_ASSOC);if($old){if($old['endereco']===$endereco)resposta(['status'=>$old['status']]);resposta(['erro'=>'Já existe uma consulta ativa. Cancele antes de alterar o endereço.'],409);}}
 $token=bin2hex(random_bytes(32));$q=$pdo->prepare("INSERT INTO bc_frete_consultas (empresa,token_hash,endereco,expira_em) VALUES (?,?,?,DATE_ADD(NOW(),INTERVAL 15 MINUTE))");$q->execute([$id_empresa,hash('sha256',$token),$endereco]);$_SESSION['bc_consulta_token'][$id_empresa]=$token;resposta(['status'=>'pendente']);
}
if(!$token)resposta(['status'=>'inexistente']);
if($acao==='cancelar'){$q=$pdo->prepare("UPDATE bc_frete_consultas SET status='recusado' WHERE empresa=? AND token_hash=? AND status IN ('pendente','cotado','aceito')");$q->execute([$id_empresa,hash('sha256',$token)]);unset($_SESSION['bc_consulta_token'][$id_empresa]);resposta(['status'=>'recusado']);}
if($acao==='aceitar'){
 $end=[];foreach(['rua','numero','bairro','cidade','cep'] as $k){$end[$k]=trim((string)($_POST[$k]??''));}
 $end['entrega']='Delivery';
 $q=$pdo->prepare("UPDATE bc_frete_consultas SET status='aceito' WHERE empresa=? AND token_hash=? AND endereco=? AND status='cotado' AND valor IS NOT NULL AND expira_em>NOW()");
 $q->execute([$id_empresa,hash('sha256',$token),json_encode($end,JSON_UNESCAPED_UNICODE)]);
 if(!$q->rowCount())resposta(['erro'=>'Cotação indisponível ou endereço alterado'],409);
 $acao='status';
}
if($acao==='status'){$q=$pdo->prepare("SELECT status,valor,endereco FROM bc_frete_consultas WHERE empresa=? AND token_hash=? AND expira_em>NOW()");$q->execute([$id_empresa,hash('sha256',$token)]);resposta($q->fetch(PDO::FETCH_ASSOC)?:['status'=>'expirado']);}
resposta(['erro'=>'Operação inválida'],400);
