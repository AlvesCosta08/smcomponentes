<?php
require_once ('../../sistema/conexao.php');
require_once ('ApiConfig.php');
@session_start();

$id_usuario = @$_SESSION['id'];

$sessao_pedido_balcao = @$_SESSION['pedido_balcao'];
$tipo_pedido = '';
if($sessao_pedido_balcao == 'BALCÃO'){
  $tipo_pedido = 'Balcão';
}

$data_atual = date('Y-m-d');
$data_dias_retorno = date('Y-m-d', strtotime("+$dias_retorno days",strtotime($data_atual)));


$pagamento = filter_var(@$_POST['pagamento'], @FILTER_SANITIZE_STRING);
$entrega = filter_var(@$_POST['entrega'], @FILTER_SANITIZE_STRING);
$rua = filter_var(@$_POST['rua'], @FILTER_SANITIZE_STRING);
$numero = filter_var(@$_POST['numero'], @FILTER_SANITIZE_STRING);
$bairro = filter_var(@$_POST['bairro'], @FILTER_SANITIZE_STRING);
$complemento = filter_var(@$_POST['complemento'], @FILTER_SANITIZE_STRING);
$total_pago = filter_var(@$_POST['troco'], @FILTER_SANITIZE_STRING);
$obs = filter_var(@$_POST['obs'], @FILTER_SANITIZE_STRING);
$sessao = @$_SESSION['sessao_usuario'];
$total_pago = str_replace(',', '.', $total_pago);
$nome_cliente_ped = filter_var(@$_POST['nome_cliente'], @FILTER_SANITIZE_STRING);
$tel_cliente = filter_var(@$_POST['tel_cliente'], @FILTER_SANITIZE_STRING);
$cliente = filter_var(@$_POST['id_cliente'], @FILTER_SANITIZE_STRING);
$mesa = filter_var(@$_POST['mesa'], @FILTER_SANITIZE_STRING);
$cupom = filter_var(@$_POST['cupom'], @FILTER_SANITIZE_STRING);
$codigo_pix = filter_var(@$_POST['codigo_pix'], @FILTER_SANITIZE_STRING);
$cep = filter_var(@$_POST['cep'], @FILTER_SANITIZE_STRING);
$cidade = filter_var(@$_POST['cidade'], @FILTER_SANITIZE_STRING);
$taxa_entrega = filter_var(@$_POST['taxa_entrega'], @FILTER_SANITIZE_STRING);
$taxa_entrega = str_replace(',', '.', $taxa_entrega);
$total_pago = str_replace(',', '.', $total_pago);

$esta_pago = filter_var(@$_POST['esta_pago'], @FILTER_SANITIZE_STRING);


//verificar pgto pix
require ("verificar_pgto.php");
if (@$status_api == 'approved' or $esta_pago == 'Sim') {
  $pago = 'Sim';
} else {
  $pago = 'Não';
}


if ($pagamento == 'Pix' and $pago == 'Não' and $dados_pagamento == "") {
  echo 'Pagamento nao realizado!!';
  exit();
}

if ($cupom == "") {
  $cupom = 0;
}

if ($taxa_entrega == "") {
  $taxa_entrega = 0;
}

$cliente = 0;
$total_cartoes_cliente = 0;

if ($tel_cliente != "") {
  $query = $pdo->prepare("SELECT * FROM clientes where telefone = :telefone ");
  $query->bindValue(":telefone", "$tel_cliente");
  $query->execute();
  $res = $query->fetchAll(PDO::FETCH_ASSOC);
  if (@count($res) > 0) {
    $cliente = $res[0]['id'];
    $total_cartoes_cliente = $res[0]['cartoes'];
    if($total_cartoes_cliente == ""){
      $total_cartoes_cliente = 0;
    }
    if($total_cartoes_cliente < $total_cartoes_config){
      $total_cartoes_cliente = $total_cartoes_cliente + 1;
    }

    if($total_cartoes_cliente == $total_cartoes_config and $cupom == $valor_cupom_config){
      $total_cartoes_cliente = 0;
    }
    

    //atualiza os dados do cliente
    $query = $pdo->prepare("UPDATE clientes SET nome = :nome, endereco = :rua, numero = :numero, complemento = :complemento, bairro = :bairro, cep = :cep, cidade = :cidade, data_mensagem = '$data_dias_retorno', retorno_enviado = 'Não', cartoes = '$total_cartoes_cliente' where id = '$cliente'");
    $query->bindValue(":nome", "$nome_cliente_ped");
    $query->bindValue(":rua", "$rua");
    $query->bindValue(":numero", "$numero");
    $query->bindValue(":complemento", "$complemento");
    $query->bindValue(":bairro", "$bairro");
    $query->bindValue(":cep", "$cep");
    $query->bindValue(":cidade", "$cidade");

    $query->execute();

  } else {
    $query = $pdo->prepare("INSERT INTO clientes SET nome = :nome, telefone = :telefone, endereco = :rua, numero = :numero, bairro = :bairro, complemento = :complemento, data_cad = curDate(), cep = :cep, cidade = :cidade, data_mensagem = '$data_dias_retorno', retorno_enviado = 'Não', cartoes = '1'");
    $query->bindValue(":nome", "$nome_cliente_ped");
    $query->bindValue(":telefone", "$tel_cliente");
    $query->bindValue(":rua", "$rua");
    $query->bindValue(":numero", "$numero");
    $query->bindValue(":bairro", "$bairro");
    $query->bindValue(":complemento", "$complemento");
    $query->bindValue(":cep", "$cep");
    $query->bindValue(":cidade", "$cidade");
    $query->execute();
    $cliente = $pdo->lastInsertId();
  }
}



$total_carrinho = 0;
$query = $pdo->query("SELECT * FROM carrinho where sessao = '$sessao'");
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

    $total_item = $total_item * $quantidade;

    $total_carrinho += $total_item;

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
} else {
  echo '0';
  exit();
}




@$total_com_frete = @$total_carrinho + @$taxa_entrega - @$cupom;

if ($total_pago == "") {
  $total_pago = $total_com_frete;
}
$troco = $total_pago - $total_com_frete;

//recuperar número do pedido
$query = $pdo->query("SELECT * FROM vendas where data = curDate() order by id desc limit 1");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$num_pedido = @$res[0]['pedido'];
if ($num_pedido == "") {
  $num_pedido = 0;
}
$pedido = $num_pedido + 1;

$query = $pdo->prepare("INSERT INTO vendas SET cliente = '$cliente', valor = '$total_com_frete', total_pago = '$total_pago', troco = '$troco', data = curDate(), hora = curTime(), status = 'Iniciado', pago = '$pago', obs = :obs, taxa_entrega = '$taxa_entrega', tipo_pgto = '$pagamento', usuario_baixa = '0', entrega = '$entrega', mesa = '$mesa', nome_cliente = '$nome_cliente_ped', cupom = '$cupom', pago_entregador = 'Não', pedido = '$pedido', ref_api = '$codigo_pix', tipo_pedido = '$tipo_pedido'");
$query->bindValue(":obs", "$obs");
$query->execute();
$id_pedido = $pdo->lastInsertId();
$id_pedido_feito = $pdo->lastInsertId();



//relacionar itens do carrinho com o pedido
$pdo->query("UPDATE carrinho SET cliente = '$cliente', pedido = '$id_pedido' where sessao = '$sessao' and pedido = '0'");

//limpar a sessao aberta
@$_SESSION['sessao_usuario'] = "";
//session_destroy();

$hora_pedido = date('H:i', strtotime("+$previsao_entrega minutes", strtotime(date('H:i'))));
echo $hora_pedido . '*';





if ($api_whatsapp != 'Não') {
  $telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $tel_cliente);
  $total_com_freteF = number_format($total_com_frete, 2, ',', '.');

  $mensagem = '*Pedido:* ' . $pedido . '%0A';
  $mensagem .= '*Cliente:* ' . $nome_cliente_ped . '%0A';
  $mensagem .= '*Telefone:* ' . $tel_cliente . '%0A';
  $mensagem .= '*Valor:* R$ ' . $total_com_freteF . '%0A';
  $mensagem .= '*Pagamento:* ' . $pagamento . '%0A';
  $mensagem .= '*Pago:* ' . $pago . '%0A';
  $mensagem .= '*Previsão Entrega:* ' . $hora_pedido . '%0A';
  $mensagem .= '%0A________________________________%0A%0A';
  $mensagem .= '*_Detalhes do Pedido_* %0A%0A';





  //ABAIXO É PARA PEGAR OS PRODUTOS COMPRADOS
  $nome_produto2 = '';
  $res = $pdo->query("SELECT * from carrinho where pedido = '$id_pedido' order by id asc");
  $dados = $res->fetchAll(PDO::FETCH_ASSOC);
  $linhas = count($dados);


  $sub_tot;
  for ($i = 0; $i < count($dados); $i++) {
    foreach ($dados[$i] as $key => $value) {
    }
    $texto_produtos = '';
    $id_carrinho = $dados[$i]['id'];
    $id_produto = $dados[$i]['produto'];
    $quantidade = $dados[$i]['quantidade'];
    $total_item = $dados[$i]['total_item'];
    $obs_item = $dados[$i]['obs'];
    $item = $dados[$i]['item'];
    $variacao = $dados[$i]['variacao'];
    $nome_produto_tab = $dados[$i]['nome_produto'];
    $sabores = $dados[$i]['sabores'];
    $borda = $dados[$i]['borda'];
    $categoria = $dados[$i]['categoria'];
    $valor_unitario = $dados[$i]['valor_unitario'];

    $query2 = $pdo->query("SELECT * FROM variacoes where id = '$variacao'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    if (@count(@$res2) > 0) {
      $sigla_variacao = '(' . $res2[0]['sigla'] . ')';
    } else {
      $sigla_variacao = '';
    }


    $query2 = $pdo->query("SELECT * FROM temp where carrinho = '$id_carrinho' and tabela = 'Variação' order by id asc limit 1");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    $id_do_item = @$res2[0]['id_item'];

    $query2 = $pdo->query("SELECT * FROM itens_grade where id = '$id_do_item'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    if (@$res2[0]['texto'] != "") {
      $sigla_grade = @$res2[0]['texto'];
    } else {
      $sigla_grade = '';
    }

    $query2 = $pdo->query("SELECT * FROM produtos where id = '$id_produto'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    if (@count(@$res2) > 0) {
      $nome_produto = $res2[0]['nome'];
      $foto_produto = $res2[0]['foto'];
    } else {
      $nome_produto = $nome_produto_tab;
      $foto_produto = "";
    }


    $tabela_ad = 'adicionais';
    if ($sabores > 0) {
      $nome_produto = $nome_produto_tab;
      $tabela_ad = 'adicionais_cat';
    }


    $query8 = $pdo->query("SELECT * FROM bordas where id = '$borda'");
    $res8 = $query8->fetchAll(PDO::FETCH_ASSOC);
    $total_reg8 = @count($res8);
    if ($total_reg8 > 0) {
      $nome_borda = ' - ' . $res8[0]['nome'];
    } else {
      $nome_borda = '';
    }

    $texto_produtos .= '✅' . $quantidade . ' - ' . $nome_produto . ' ' . $sigla_variacao . ' ' . $sigla_grade . '%0A';



    $mensagem .= '%0A' . $texto_produtos;

    if ($total_reg8 > 0) {
      $mensagem .= $nome_borda . '%0A';
    }

    //COMEÇAR VER OS ADICIONAIS E OUTROS DOS DEMAIS ITENS QUE NAO SAO PIZZA 2 SAB
    $query2 = $pdo->query("SELECT * FROM temp where carrinho = '$id_carrinho' and tabela = 'adicionais'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    $total_reg2 = @count($res2);
    if ($total_reg2 > 0) {
      if ($total_reg2 > 1) {
        $texto_adicional = @$total_reg2 . ' Tipos de Adicionais';
      } else {
        $texto_adicional = @$total_reg2 . ' Tipo de Adicional';
      }
      $mensagem .= ' *' . $texto_adicional . '* %0A';
      for ($i2 = 0; $i2 < $total_reg2; $i2++) {
        foreach ($res2[$i2] as $key => $value) {
        }
        $id_temp = $res2[$i2]['id'];
        $id_item = $res2[$i2]['id_item'];
        $quantidade_temp = $res2[$i2]['quantidade'];

        $query3 = $pdo->query("SELECT * FROM $tabela_ad where id = '$id_item'");
        $res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
        $total_reg3 = @count($res3);
        $nome_adc = $res3[0]['nome'];
        if ($i2 < ($total_reg2 - 1)) {
          $nome_adc .= '%0A';
        }

        $mensagem .= '```(' . $quantidade_temp . ') ' . $nome_adc . '```' . '';
      }

      $mensagem .= '%0A';

    }





    //percorrer as grades do produto
    $query20 = $pdo->query("SELECT * FROM grades where produto = '$id_produto' and tipo_item != 'Variação'");
    $res20 = $query20->fetchAll(PDO::FETCH_ASSOC);
    $total_reg20 = @count($res20);
    if ($total_reg20 > 0) {
      for ($i20 = 0; $i20 < $total_reg20; $i20++) {
        $id_da_grade = $res20[$i20]['id'];
        $nome_da_grade = $res20[$i20]['nome_comprovante'];
        $tipo_item_grade = $res20[$i20]['tipo_item'];


        //buscar os itens selecionados pela grade
        $query2 = $pdo->query("SELECT * FROM temp where carrinho = '$id_carrinho' and grade = '$id_da_grade'");
        $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
        $total_reg2 = @count($res2);
        if ($total_reg2 > 0) {

          $mensagem .= '*' . $nome_da_grade . '* %0A';

          for ($i2 = 0; $i2 < $total_reg2; $i2++) {
            foreach ($res2[$i2] as $key => $value) {
            }
            $id_temp = $res2[$i2]['id'];
            $id_item = $res2[$i2]['id_item'];
            $tabela_item = $res2[$i2]['tabela'];
            $tipagem_item = $res2[$i2]['tipagem'];
            $grade_item = $res2[$i2]['grade'];

            if ($tipo_item_grade == 'Múltiplo') {
              $quant_item = '(' . $res2[$i2]['quantidade'] . ')';
            } else {
              $quant_item = '';
            }



            $query3 = $pdo->query("SELECT * FROM itens_grade where id = '$id_item'");
            $res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
            $total_reg3 = @count($res3);
            $nome_item = $res3[0]['texto'];
            if ($i2 < ($total_reg2 - 1)) {
              $nome_item .= ', ';
            }
            $mensagem .= '```' . $quant_item . $nome_item . '```%0A';
          }

          $mensagem .= '%0A';


        }

      }
    }


    if ($obs_item != "") {
      $mensagem .= ' ' . '```Observações: ' . $obs_item . '```' . '%0A';
    }



  }


  //ond pizza 2 sab
  if ($obs != "") {
    $mensagem .= '%0A*Observações do Pedido*%0A';
    $mensagem .= '_' . $obs . '_' . '%0A%0A';
  }


  if ($entrega == "Delivery") {
    $mensagem .= '%0A*Endereço do Cliente*%0A';
    $endereco = $rua . ' ' . $numero . ' ' . $complemento . ' ' . $bairro . ' ' . $cidade;
    $mensagem .= '_' . $endereco . '_';

  }


  if($total_cartoes_config > 0 and $total_cartoes_cliente > 0){
    $mensagem .= '%0A%0A' . 'Você ganhou mais um cartão Fidelidade' . '%0A';
    $mensagem .= 'Você agora possui *'.$total_cartoes_cliente . '* cartões!';
  }

   if($total_cartoes_config == $total_cartoes_cliente){
    $mensagem .= '%0A%0A' . '*Você já completou seus cartões Fidelidade*' . '%0A';
    $mensagem .= 'Na sua próxima compra use o codigo de cupom (cartao) para ter um desconto de '.$valor_cupom_config.' reais!';
  }


  $mensagem .= '%0A%0A' . '```Obrigado pela preferência```' . '%0A';
  $mensagem .= $url_sistema . '%0A%0A';

  $mensagem .= 'Acompanhe o Status do Seu pedido%0A';
  $mensagem .= $url_sistema . 'pedido/'. $id_pedido_feito.'%0A';
  

  $data_mensagem = date('Y-m-d H:i:s');
  require ("api_texto.php");

}


$query2 = $pdo->query("SELECT * FROM formas_pgto WHERE nome = '$pagamento'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if (@count($res2) > 0) {
  $id_tipo_pgto = $res2[0]['id'];
}

$id_caixa = 0;

if($tipo_pedido == 'Balcão'){
  $entrega = 'Balcão';

  //verificar caixa aberto
  $query1 = $pdo->query("SELECT * from caixas where operador = '$id_usuario' and data_fechamento is null order by id desc limit 1");
  $res1 = $query1->fetchAll(PDO::FETCH_ASSOC);
  if (@count($res1) > 0) {
    $id_caixa = @$res1[0]['id'];
  } else {
    $id_caixa = 0;
  }

}else{
  $id_usuario = 0;
}



if($pago == 'Sim'){
  $pdo->query("INSERT INTO receber SET descricao = '$entrega', cliente = '$cliente', valor = '$total_com_frete', subtotal = '$total_com_frete', data_lanc = curDate(), hora = curTime(), pago = 'Sim', vencimento = curDate(), data_pgto = curDate(), foto = 'sem-foto.png', arquivo = 'sem-foto.png', forma_pgto = '$id_tipo_pgto', referencia = '$entrega', caixa = '$id_caixa', usuario_pgto = '$id_usuario'");
}



echo "Pedido Finalizado*" . $id_pedido;
