<?php 
require_once("../../../conexao.php");

$dataMes = Date('m');
$dataDia = Date('d');
$dataAno = Date('Y');
$data_atual = date('Y-m-d');

$data_semana = date('Y/m/d', strtotime("-7 days",strtotime($data_atual)));

@session_start();
$id_usuario = $_SESSION['id'];

$clientes = $_POST['cli'];

// Buscar os Contatos que serão enviados
if($clientes == "Teste"){
	$resultado = $pdo->query("SELECT telefone FROM config where telefone != ''");	

}else if ($clientes == "Clientes Mês"){
	$resultado = $pdo->query("SELECT telefone FROM clientes where month(data_cad) = '$dataMes' and year(data_cad) = '$dataAno' and telefone != '' and (marketing = '' or marketing is null)");

}else if ($clientes == "Clientes Semana"){
	$resultado = $pdo->query("SELECT telefone FROM clientes where data_cad >= '$data_semana' and telefone != '' and (marketing = '' or marketing is null)");

}else if ($clientes == "Todos"){
	$resultado = $pdo->query("SELECT telefone FROM clientes where telefone != '' and (marketing = '' or marketing is null)");

}else if ($clientes == "Retorno"){
	$resultado = $pdo->query("SELECT telefone FROM clientes where telefone != '' and (retorno_enviado != 'Sim' or retorno_enviado is null) and (marketing = '' or marketing is null)");

}else{
	
}

$res = @$resultado->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);

echo $total_reg;

 ?>