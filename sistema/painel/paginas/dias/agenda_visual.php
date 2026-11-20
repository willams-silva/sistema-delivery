<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
function bcAgendaResponder(int $codigo, array $dados): void {
 http_response_code($codigo); echo json_encode($dados, JSON_UNESCAPED_UNICODE); exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') bcAgendaResponder(405, ['erro'=>'Método inválido']);
$empresa=(int)($_SESSION['empresa']??0);
$usuario=(int)($_SESSION['id']??0);
if (!$empresa || !$usuario) bcAgendaResponder(403, ['erro'=>'Sessão expirada']);
require_once '../../../conexao.php';
try {
 $q=$pdo->prepare('SELECT nivel FROM usuarios WHERE id=:usuario AND empresa=:empresa LIMIT 1');
 $q->execute([':usuario'=>$usuario, ':empresa'=>$empresa]);
 $nivel=$q->fetchColumn();
 if ($nivel===false) bcAgendaResponder(403,['erro'=>'Acesso negado']);
 if ($nivel!=='Administrador') {
  $q=$pdo->prepare('SELECT 1 FROM usuarios_permissoes up JOIN acessos a ON a.id=up.permissao WHERE up.usuario=:usuario AND a.chave=:chave LIMIT 1');
  $q->execute([':usuario'=>$usuario, ':chave'=>'dias']);
  if (!$q->fetchColumn()) bcAgendaResponder(403,['erro'=>'Sem permissão']);
 }
 $inicio=(string)($_POST['inicio']??'');
 $dt=DateTimeImmutable::createFromFormat('!Y-m-d',$inicio);
 if (!$dt || $dt->format('Y-m-d')!==$inicio) bcAgendaResponder(400,['erro'=>'Data inválida']);
 $fim=$dt->modify('+6 days')->format('Y-m-d');
 $q=$pdo->prepare('SELECT horario_abertura,horario_fechamento,status_estabelecimento,pausar_delivery FROM config WHERE empresa=:empresa LIMIT 1');
 $q->execute([':empresa'=>$empresa]);
 $cfg=$q->fetch(PDO::FETCH_ASSOC);
 if (!$cfg) bcAgendaResponder(404,['erro'=>'Configuração da empresa não encontrada']);
 $q=$pdo->prepare('SELECT dia FROM dias WHERE empresa=:empresa');
 $q->execute([':empresa'=>$empresa]);
 $semanais=array_fill_keys($q->fetchAll(PDO::FETCH_COLUMN),true);
 $q=$pdo->prepare('SELECT data_fechamento,motivo FROM fechamentos_excepcionais WHERE empresa=:empresa AND data_fechamento BETWEEN :inicio AND :fim');
 $q->execute([':empresa'=>$empresa,':inicio'=>$inicio,':fim'=>$fim]);
 $integrais=[];
 foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $x) $integrais[$x['data_fechamento']]=$x['motivo'];
 $q=$pdo->prepare('SELECT data_especial,TIME_FORMAT(hora_inicio,"%H:%i") AS inicio,TIME_FORMAT(hora_fim,"%H:%i") AS fim,descricao FROM horarios_especiais WHERE empresa=:empresa AND data_especial BETWEEN :inicio AND :fim');
 $q->execute([':empresa'=>$empresa,':inicio'=>$inicio,':fim'=>$fim]);
 $especiais=[];
 foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $x) $especiais[$x['data_especial']]=$x;
 $nomes=['Domingo','Segunda-Feira','Terça-Feira','Quarta-Feira','Quinta-Feira','Sexta-Feira','Sábado'];
 $dias=[];
 for($i=0;$i<7;$i++){
  $d=$dt->modify('+'.$i.' days');
  $data=$d->format('Y-m-d');
  $dia=$nomes[(int)$d->format('w')];
  $tipo='normal'; $horario=substr((string)$cfg['horario_abertura'],0,5).' às '.substr((string)$cfg['horario_fechamento'],0,5);
  $descricao='Horário normal';
  if (isset($semanais[$dia])) {$tipo='semanal';$horario='Fechado';$descricao='Fechamento semanal';}
  if (isset($especiais[$data])) {
   $x=$especiais[$data];$tipo='especial';
   $horario=$x['inicio'].' às '.$x['fim'].($x['fim']<$x['inicio']?' (dia seguinte)':'');
   $descricao=$x['descricao']?:'Horário especial';
  }
  if (array_key_exists($data,$integrais)) {
   $tipo='integral';$horario='Fechado';$descricao=$integrais[$data]?:'Fechamento integral';
  }
  $dias[]=['data'=>$data,'dia'=>$dia,'tipo'=>$tipo,'horario'=>$horario,'descricao'=>$descricao];
 }
 bcAgendaResponder(200,['dias'=>$dias,'pausado'=>($cfg['pausar_delivery']??'')==='Sim','fechado_manual'=>($cfg['status_estabelecimento']??'')==='Fechado','observacao'=>'Agenda de datas de início de expediente. Horários noturnos podem continuar na madrugada seguinte, salvo fechamento integral. Pausa e fechamento manual prevalecem sobre esta agenda.']);
} catch (PDOException $e) {
 error_log('Agenda visual: '.$e->getMessage());
 bcAgendaResponder(503,['erro'=>'Não foi possível carregar a agenda.']);
}
