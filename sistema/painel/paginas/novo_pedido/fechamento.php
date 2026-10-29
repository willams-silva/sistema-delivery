<?php
@session_start();
$id_usuario = $_SESSION['id'];
require_once ("../../../conexao.php");
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
$total_valor_fechamento = $_POST['total_valor_fechamento'];

$total_itens = str_replace(',', '.', $total_itens);
$comissao = str_replace(',', '.', $comissao);
$couvert = str_replace(',', '.', $couvert);
$subtotal = str_replace(',', '.', $subtotal);
$total_valor_fechamento = str_replace(',', '.', $total_valor_fechamento);



$query = $pdo->prepare("UPDATE $tabela SET garcon = :garcon, total = :total, horario_fechamento = curTime(), status = 'Fechada', obs = :obs, comissao_garcon = :comissao, couvert = :couvert, subtotal = :subtotal, forma_pgto = :forma_pgto, valor_adiantado = '$total_valor_fechamento' WHERE id = '$id_ab'");


$query->bindValue(":garcon", "$garcon");
$query->bindValue(":total", "$total_itens");
$query->bindValue(":obs", "$obs");
$query->bindValue(":comissao", "$comissao");
$query->bindValue(":couvert", "$couvert");
$query->bindValue(":subtotal", "$subtotal");
$query->bindValue(":forma_pgto", "$forma_pgto");
$query->execute();

$pdo->query("UPDATE mesas set status = 'Fechada' where id = '$id_mesa'");



//verificar caixa aberto
$query1 = $pdo->query("SELECT * from caixas where operador = '$id_usuario' and data_fechamento is null order by id desc limit 1");
$res1 = $query1->fetchAll(PDO::FETCH_ASSOC);
if (@count($res1) > 0) {
  $id_caixa = @$res1[0]['id'];
} else {
  $id_caixa = 0;
}



//lançar a comissão do garçon
$query = $pdo->prepare("INSERT INTO pagar SET descricao = 'Comissão Garçon', tipo = 'Comissão', valor = :valor, data_lanc = curDate(), vencimento = curDate(), usuario_lanc = '$id_usuario', foto = 'sem-foto.png', arquivo = 'sem-foto.jpg', pessoa = '0', pago = 'Não', funcionario = '$garcon', referencia = 'Comissão', id_ref = '$id_ab', caixa = '$id_caixa',  comissao = 'Garçon' ");
$query->bindValue(":valor", "$comissao");
$query->execute();


#######LANÇAR AS VENDAS###########################################
$query = $pdo->prepare("INSERT INTO receber SET descricao = 'Venda Mesa', tipo = 'Venda', valor = :valor, data_lanc = curDate(), vencimento = curDate(), data_pgto = curDate(), usuario_lanc = '$id_usuario', usuario_pgto = '$id_usuario', foto = 'sem-foto.png', arquivo = 'sem-foto.jpg', pessoa = '0', pago = 'Sim', referencia = 'Venda', id_ref = '$id_ab', caixa = '$id_caixa', forma_pgto = '$forma_pgto', subtotal = '$subtotal'");
$query->bindValue(":valor", "$subtotal");
$query->execute();

echo 'Salvo com Sucesso';

?>