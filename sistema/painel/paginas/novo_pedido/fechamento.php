<?php 
@session_start();
$id_usuario = $_SESSION['id'];
require_once("../../../conexao.php");
$tabela = 'abertura_mesa';

$id_ab = $_POST['id_ab'];
$id_mesa = $_POST['id'];
$total_itens = $_POST['total_itens'];
$garcon = $_POST['garcon'];
$obs = $_POST['obs'];
$id_ab = $_POST['id_ab'];
$comissao = $_POST['comissao'];
$couvert = $_POST['couvert'];
$subtotal = $_POST['subtotal'];
$forma_pgto = $_POST['forma_pgto'];

$total_itens = str_replace(',', '.', $total_itens);
$comissao = str_replace(',', '.', $comissao);
$couvert = str_replace(',', '.', $couvert);
$subtotal = str_replace(',', '.', $subtotal);


$query = $pdo->prepare("UPDATE $tabela SET garcon = :garcon, total = :total, horario_fechamento = curTime(), status = 'Fechada', obs = :obs, comissao_garcon = :comissao, couvert = :couvert, subtotal = :subtotal, forma_pgto = :forma_pgto WHERE id = '$id_ab'");


$query->bindValue(":garcon", "$garcon");
$query->bindValue(":total", "$total_itens");
$query->bindValue(":obs", "$obs");
$query->bindValue(":comissao", "$comissao");
$query->bindValue(":couvert", "$couvert");
$query->bindValue(":subtotal", "$subtotal");
$query->bindValue(":forma_pgto", "$forma_pgto");
$query->execute();

$pdo->query("UPDATE mesas set status = 'Fechada' where id = '$id_mesa'");


//lançar a comissão do garçon
$query = $pdo->prepare("INSERT INTO pagar SET descricao = 'Comissão', tipo = 'Comissão', valor = :valor, data_lanc = curDate(), data_venc = curDate(), usuario_lanc = '$id_usuario', foto = 'sem-foto.jpg', pessoa = '0', pago = 'Não', funcionario = '$garcon', referencia = 'Comissão', id_ref = '$id_ab'");
$query->bindValue(":valor", "$comissao");
$query->execute();


//lançar a comissão do garçon
$query = $pdo->prepare("INSERT INTO receber SET descricao = 'Venda Mesa', tipo = 'Abertura', valor = :valor, data_lanc = curDate(), data_venc = curDate(), data_pgto = curDate(), usuario_lanc = '$id_usuario', usuario_baixa = '$id_usuario', foto = 'sem-foto.jpg', pessoa = '0', pago = 'Sim', referencia = 'Abertura', id_ref = '$id_ab'");
$query->bindValue(":valor", "$subtotal");
$query->execute();

echo 'Salvo com Sucesso';

?>