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

$query1 = $pdo->query("SELECT * from formas_pgto where id = '$forma_pgto'");
$res1 = $query1->fetchAll(PDO::FETCH_ASSOC);
$nome_forma_pgto = $res1[0]['nome'];

$query1 = $pdo->query("SELECT * from abertura_mesa where id = '$id_ab'");
$res1 = $query1->fetchAll(PDO::FETCH_ASSOC);
$nome_cliente_ab = $res1[0]['cliente'];

//lançar a comissão do garçon
$query = $pdo->prepare("INSERT INTO pagar SET descricao = 'Comissão Garçon', tipo = 'Comissão', valor = :valor, data_lanc = curDate(), vencimento = curDate(), usuario_lanc = '$id_usuario', foto = 'sem-foto.png', arquivo = 'sem-foto.jpg', pessoa = '0', pago = 'Não', funcionario = '$garcon', referencia = 'Comissão', id_ref = '$id_ab', caixa = '$id_caixa',  comissao = 'Garçon' ");
$query->bindValue(":valor", "$comissao");
$query->execute();


#######LANÇAR AS VENDAS###########################################
$query = $pdo->prepare("INSERT INTO receber SET descricao = 'Venda Mesa', tipo = 'Venda', valor = :valor, data_lanc = curDate(), vencimento = curDate(), data_pgto = curDate(), usuario_lanc = '$id_usuario', usuario_pgto = '$id_usuario', foto = 'sem-foto.png', arquivo = 'sem-foto.jpg', pessoa = '0', pago = 'Sim', referencia = 'Venda', id_ref = '$id_ab', caixa = '$id_caixa', forma_pgto = '$forma_pgto', subtotal = '$subtotal'");
$query->bindValue(":valor", "$subtotal");
$query->execute();



$pdo->query("INSERT INTO vendas SET cliente = '0', valor = '$subtotal', total_pago = '$subtotal', troco = '0', data = curDate(), hora = curTime(), status = 'Finalizado', pago = 'Sim',  taxa_entrega = '0', tipo_pgto = '$nome_forma_pgto', usuario_baixa = '$id_usuario', entrega = 'Pedido Mesa', mesa = '$id_mesa', nome_cliente = '$nome_cliente_ab', cupom = '0', pago_entregador = 'Não', pedido = '', ref_api = '', tipo_pedido = 'Mesa'");


//atualizar produtos
$query = $pdo->query("SELECT * FROM carrinho where mesa = '$id_ab'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
  for ($i = 0; $i < $total_reg; $i++) {
    foreach ($res[$i] as $key => $value) {
    }

    $id = $res[$i]['id'];
    $total_item = $res[$i]['total_item'];
    $produto = $res[$i]['produto'];
    $quantidade = $res[$i]['quantidade'];   

    $query2 = $pdo->query("SELECT * FROM produtos where id = '$produto'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    $id_categoria = $res2[0]['categoria'];
    $valor_produto = $res2[0]['valor_venda'];
    $estoque = $res2[0]['estoque'];
    $tem_estoque = $res2[0]['tem_estoque'];


    if ($tem_estoque == 'Sim') {
      $total_produtos = $estoque - $quantidade;
      $pdo->query("UPDATE produtos SET estoque = '$total_produtos' where id = '$produto'");
    }

  }
} 



echo 'Salvo com Sucesso';

?>