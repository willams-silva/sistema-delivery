<?php
$chave = 'bairros';
@session_start();
require_once("../../../conexao.php");
$empresa = (int) ($_SESSION['empresa'] ?? 0);
$usuario = (int) ($_SESSION['id'] ?? 0);
if ($empresa <= 0 || $usuario <= 0) { http_response_code(403); exit('Sessão inválida'); }
$op = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$op->execute([':id'=>$usuario, ':empresa'=>$empresa]);
$nivel = $op->fetchColumn();
if ($nivel === false) { http_response_code(403); exit('Acesso negado'); }
if ($nivel !== 'Administrador') {
    $per = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = :chave LIMIT 1");
    $per->execute([':usuario'=>$usuario, ':empresa'=>$empresa, ':chave'=>$chave]);
    if (!$per->fetchColumn()) { http_response_code(403); exit('Sem permissão'); }
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método não permitido'); }


header('Content-Type: application/json; charset=utf-8');
if (!hash_equals((string)($_SESSION['bc_frete_csrf'] ?? ''), (string)($_POST['csrf'] ?? '')) || empty($_SESSION['bc_frete_csrf'])) { http_response_code(403); exit('Sessão inválida'); }

$acao=$_POST['acao']??'listar';
try {
if($acao==='listar'){
$q=$pdo->prepare("SELECT z.id,z.nome,z.valor,z.bairro_id,b.nome AS bairro FROM bc_frete_zonas z JOIN bairros b ON b.id=z.bairro_id AND b.empresa=z.empresa WHERE z.empresa=? ORDER BY b.nome,z.nome");$q->execute([$empresa]);$rows=$q->fetchAll(PDO::FETCH_ASSOC);
$q=$pdo->prepare("SELECT rua FROM bc_frete_zona_ruas WHERE empresa=? AND zona_id=? AND bairro_id=? ORDER BY rua");
foreach($rows as &$row){$q->execute([$empresa,$row['id'],$row['bairro_id']]);$row['ruas']=$q->fetchAll(PDO::FETCH_COLUMN);}unset($row);echo json_encode($rows);exit;
}
$id=(int)($_POST['id']??0);
if($acao==='excluir'){
$pdo->beginTransaction();
$q=$pdo->prepare("DELETE FROM bc_frete_zona_ruas WHERE empresa=? AND zona_id=?");$q->execute([$empresa,$id]);
$q=$pdo->prepare("DELETE FROM bc_frete_zonas WHERE empresa=? AND id=?");$q->execute([$empresa,$id]);$pdo->commit();echo json_encode(['ok'=>true]);exit;
}
if($acao!=='salvar'){http_response_code(400);exit('Operação inválida');}
$b=(int)($_POST['bairro_id']??0);$nome=trim((string)($_POST['nome']??''));$valor=str_replace(',','.',trim((string)($_POST['valor']??'')));
$ruas=array_values(array_unique(array_filter(array_map('trim',preg_split('/\r\n|\r|\n/',(string)($_POST['ruas']??''))),static function($v){return $v!=='';})));
if($nome===''||mb_strlen($nome)>150||!preg_match('/^\d+(?:\.\d{1,2})?$/',$valor)||(float)$valor>999999.99||count($ruas)>500){http_response_code(400);exit('Dados inválidos');}
foreach($ruas as $rua){if(mb_strlen($rua)>150){http_response_code(400);exit('Rua muito longa');}}
$q=$pdo->prepare("SELECT id FROM bairros WHERE id=? AND empresa=?");$q->execute([$b,$empresa]);if(!$q->fetchColumn()){http_response_code(400);exit('Bairro inválido');}
$pdo->beginTransaction();
if($id>0){$q=$pdo->prepare("SELECT id FROM bc_frete_zonas WHERE id=? AND empresa=? AND bairro_id=? FOR UPDATE");$q->execute([$id,$empresa,$b]);if(!$q->fetchColumn()){$pdo->rollBack();http_response_code(404);exit('Zona inválida');}$q=$pdo->prepare("UPDATE bc_frete_zonas SET nome=?,valor=? WHERE id=? AND empresa=?");$q->execute([$nome,$valor,$id,$empresa]);}
else{$q=$pdo->prepare("INSERT INTO bc_frete_zonas (empresa,bairro_id,nome,valor) VALUES (?,?,?,?)");$q->execute([$empresa,$b,$nome,$valor]);$id=$pdo->lastInsertId();}
$q=$pdo->prepare("DELETE FROM bc_frete_zona_ruas WHERE empresa=? AND zona_id=?");$q->execute([$empresa,$id]);
$q=$pdo->prepare("INSERT INTO bc_frete_zona_ruas (empresa,bairro_id,zona_id,rua) VALUES (?,?,?,?)");foreach($ruas as $rua){$q->execute([$empresa,$b,$id,$rua]);}
$pdo->commit();echo json_encode(['ok'=>true]);
}catch(PDOException $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(409);echo json_encode(['erro'=>'Não foi possível salvar. Verifique se a zona ou alguma rua já está cadastrada.']);}
