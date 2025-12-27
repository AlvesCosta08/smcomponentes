<?php 
require_once("../../../conexao.php");

$dataMes = Date('m');
$dataDia = Date('d');
$dataAno = Date('Y');
$data_atual = date('Y-m-d');


$data_semana = date('Y/m/d', strtotime("-7 days",strtotime($data_atual)));

$hash = '';
$hash2 = '';
$hash3 = '';
$hash4 = '';


@session_start();
$id_usuario = $_SESSION['id'];

$id = $_POST['id'];
$clientes = $_POST['clientes'];
$delay = 1;


// Buscar os Contatos que serão enviados
if($clientes == "Teste"){
	$query = $pdo->query("SELECT * FROM config where telefone != ''");	

}else if ($clientes == "Clientes Mês"){
	$query = $pdo->query("SELECT * FROM clientes where month(data_cad) = '$dataMes' and year(data_cad) = '$dataAno' and telefone != '' and (marketing = '' or marketing is null)");

}else if ($clientes == "Clientes Semana"){
	$query = $pdo->query("SELECT * FROM clientes where data_cad >= '$data_semana' and telefone != '' and (marketing = '' or marketing is null)");

}else if ($clientes == "Todos"){
	$query = $pdo->query("SELECT * FROM clientes where telefone != '' and (marketing = '' or marketing is null)");

}else if ($clientes == "Retorno"){
	$query = $pdo->query("SELECT * FROM clientes where telefone != '' and (retorno_enviado != 'Sim' or retorno_enviado is null) and (marketing = '' or marketing is null)");

}else{
	
}

$hora_atual = date('H:i:s'); // Obtém a hora atual
$hora = new DateTime($hora_atual);

$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_clientes = @count($res);
for($i=0; $i < $total_clientes; $i++){	
	$id_cliente = $res[$i]['id'];
	$nome_cliente = $res[$i]['nome'];
	$telefone_cliente = $res[$i]['telefone'];


	$hora->modify('+1 minute'); // Adiciona 1 minuto
	$hora_formatada = $hora->format('H:i:s'); // Converte para string

	$pdo->query("INSERT INTO disparos set campanha = '$id', cliente = '$id_cliente', nome = '$nome_cliente', telefone = '$telefone_cliente', hora = '$hora_formatada'");

}



echo 'Salvo com Sucesso';
 ?>