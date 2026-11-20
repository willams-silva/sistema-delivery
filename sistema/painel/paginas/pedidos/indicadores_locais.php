<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once('../../../conexao.php');
require_once(__DIR__.'/permissao_setor.php');
function bcResponder($codigo,$dados){http_response_code($codigo);echo json_encode($dados,JSON_UNESCAPED_UNICODE);exit;}
$empresa=filter_var($_SESSION['empresa']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$empresa||!$usuario||($_SESSION['aut_token_SaL1P']??'')!=='25tNX1L1MSaL1P')bcResponder(401,['erro'=>'Sessao invalida']);
$dados=[];
try {
 foreach(['Cozinha','Bar','Outro'] as $setor){
  if(!bcPermiteSetor($pdo,(int)$empresa,(int)$usuario,$setor))continue;
  $q=$pdo->prepare("SELECT status_novo,COUNT(*) total FROM pedidos_locais_historico
    WHERE empresa=:empresa AND setor=:setor AND criado_em >= CURDATE()
    AND criado_em < DATE_ADD(CURDATE(),INTERVAL 1 DAY)
    GROUP BY status_novo");
  $q->execute([':empresa'=>$empresa,':setor'=>$setor]);
  $res=['Preparando'=>0,'Pronto'=>0,'Finalizado'=>0];
  foreach($q->fetchAll(PDO::FETCH_ASSOC) as $r)if(isset($res[$r['status_novo']]))$res[$r['status_novo']]=(int)$r['total'];
  $dados[$setor]=$res;
 }
 bcResponder(200,['data'=>date('Y-m-d'),'setores'=>$dados,'observacao'=>'Contagem de transicoes registradas no historico, nao de pedidos unicos']);
} catch(PDOException $e){bcResponder(503,['erro'=>'Indicadores indisponiveis; verifique a tabela de historico']);}
