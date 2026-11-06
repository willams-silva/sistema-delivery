<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if($id_empresa == ""){
  echo 'Sem Sessão da Empresa, refaça o Login!';
  exit();
}
$tabela = 'vendas';

$id_usuario = $_SESSION['id'];

if($id_usuario == ""){
    echo 'Sua sessão expirou, Faça Login novamente no sistema para dar baixa!';
    exit();
}



$id = @$_POST['id'];
$pgto = $_POST['pgto'];

$custo = 0;

$query = $pdo->query("SELECT * FROM vendas where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
  $valor = $res[0]['valor'];
  $entrega = $res[0]['entrega'];
  $cliente = $res[0]['cliente'];
  $custo = $res[0]['custo'];
}


  $query2 = $pdo->query("SELECT * FROM formas_pgto WHERE nome = '$pgto' and empresa = '$id_empresa' ");
  $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
  if (@count($res2) > 0) {
    $id_tipo_pgto = @$res2[0]['id'];
  }



$pdo->query("UPDATE $tabela SET pago = 'Sim', usuario_baixa = '$id_usuario', tipo_pgto = '$pgto' where id = '$id'");


//verificar caixa aberto
$query1 = $pdo->query("SELECT * from caixas where operador = '$id_usuario' and data_fechamento is null order by id desc limit 1");
$res1 = $query1->fetchAll(PDO::FETCH_ASSOC);
if (@count($res1) > 0) {
  $id_caixa = @$res1[0]['id'];
} else {
  $id_caixa = 0;
}

$pdo->query("INSERT INTO receber SET descricao = '$entrega', cliente = '$cliente', valor = '$valor', subtotal = '$valor', data_lanc = curDate(), hora = curTime(), pago = 'Sim', usuario_pgto = '$id_usuario', vencimento = curDate(), data_pgto = curDate(), foto = 'sem-foto.png', arquivo = 'sem-foto.png', forma_pgto = '$id_tipo_pgto', caixa = '$id_caixa', referencia = '$entrega', custo = '$custo', empresa = '$id_empresa' ");

echo 'Baixado com Sucesso';
