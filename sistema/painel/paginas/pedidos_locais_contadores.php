<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($_SESSION['empresa'])) { http_response_code(401); echo json_encode(['erro'=>'Sessão inválida']); exit; }
require_once("../../conexao.php");
require_once("../buscar_config.php");
$empresa = (int) $_SESSION['empresa'];
$incluiDelivery = isset($delivery_monitor) && $delivery_monitor === 'Sim';
$origem = $incluiDelivery ? '(c.cliente > 0 OR c.mesa > 0)' : 'c.mesa > 0';
// Limites enviados pelo painel: inteiros de 1 a 180 minutos, com padroes por setor.
$limites = ['Cozinha'=>20, 'Bar'=>5, 'Outro'=>15];
foreach (['Cozinha'=>'cozinha','Bar'=>'bar','Outro'=>'outro'] as $setor=>$chave) {
    $valor = filter_input(INPUT_GET, 'tempo_'.$chave, FILTER_VALIDATE_INT);
    if ($valor !== false && $valor !== null && $valor >= 1 && $valor <= 180) $limites[$setor] = $valor;
}
try {
    $sql = "SELECT p.preparado AS setor, c.status, COUNT(*) AS total
            FROM carrinho c INNER JOIN produtos p ON p.id = c.produto AND p.empresa = c.empresa
            WHERE c.empresa = :empresa AND $origem
              AND p.preparado IN ('Cozinha','Bar','Outro')
              AND c.status IN ('Aguardando','Preparando','Pronto')
            GROUP BY p.preparado, c.status";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':empresa'=>$empresa]);
    $dados = [];
    foreach (['Cozinha','Bar','Outro'] as $setor) $dados[$setor] = ['Aguardando'=>0,'Preparando'=>0,'Pronto'=>0,'total'=>0];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $setor = $linha['setor']; $status = $linha['status']; $n = (int)$linha['total'];
        $dados[$setor][$status] = $n; $dados[$setor]['total'] += $n;
    }
    // Identifica novos itens aguardando e atrasos, sem alterar o estado do pedido.
    $sqlDetalhes = "SELECT p.preparado AS setor,
        MAX(CASE WHEN c.status = 'Aguardando' THEN c.id ELSE 0 END) AS ultimo_aguardando,
        SUM(CASE WHEN c.status IN ('Aguardando','Preparando')
            AND TIMESTAMPDIFF(MINUTE, CONCAT(c.data, ' ', c.hora), NOW()) >
              CASE p.preparado WHEN 'Cozinha' THEN :cozinha WHEN 'Bar' THEN :bar ELSE :outro END
            THEN 1 ELSE 0 END) AS atrasados
        FROM carrinho c INNER JOIN produtos p ON p.id = c.produto AND p.empresa = c.empresa
        WHERE c.empresa = :empresa AND $origem
          AND p.preparado IN ('Cozinha','Bar','Outro')
          AND c.status IN ('Aguardando','Preparando','Pronto')
        GROUP BY p.preparado";
    $detalhes = $pdo->prepare($sqlDetalhes);
    $detalhes->execute([':empresa'=>$empresa, ':cozinha'=>$limites['Cozinha'], ':bar'=>$limites['Bar'], ':outro'=>$limites['Outro']]);
    foreach ($detalhes->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $setor = $linha['setor'];
        $dados[$setor]['ultimo_aguardando'] = (int)$linha['ultimo_aguardando'];
        $dados[$setor]['atrasados'] = (int)$linha['atrasados'];
    }
    echo json_encode(['setores'=>$dados, 'limites'=>$limites], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['erro'=>'Falha ao consultar contadores']);
}
