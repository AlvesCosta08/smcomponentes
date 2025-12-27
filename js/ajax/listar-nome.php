<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once ('../../sistema/conexao.php');
$tel = @$_POST['tel'];

if ($tel == "") {
  exit();
}


$taxa_entrega = "0";
$taxa_entregaF = "0";

$query = $pdo->query("SELECT * FROM clientes where telefone = '$tel' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if (@count($res) > 0) {
  $nome = $res[0]['nome'];
  $id_cliente = $res[0]['id'];
  $rua = $res[0]['endereco'];
  $numero = $res[0]['numero'];
  $bairro = $res[0]['bairro'];
  $complemento = $res[0]['complemento'];
  $cep = $res[0]['cep'];
  $cidade = $res[0]['cidade'];


  $query = $pdo->query("SELECT * FROM bairros where nome = '$bairro'");
  $res = $query->fetchAll(PDO::FETCH_ASSOC);

  $taxa_entregaF = "";
  if (@count($res) > 0 and ($entrega_distancia != "Sim" or $chave_api_maps == "")) {
    $taxa_entrega = $res[0]['valor'];
    $taxa_entregaF = number_format($taxa_entrega, 2, ',', '.');
  } else {
    $taxa_entrega = "";
  }


}

echo @$nome . '**' . @$rua . '**' . @$numero . '**' . @$bairro . '**' . @$complemento . '**' . $taxa_entrega . '**' . $taxa_entregaF . '**' . @$id_cliente . '**' . @$cep . '**' . @$cidade;

?>