<?php 
require_once("../../../conexao.php");
$tabela = 'marketing';

$id = $_POST['id'];

$query = $pdo->query("SELECT * FROM $tabela where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
$foto = $res[0]['arquivo'];

if($foto != "sem-foto.jpg"){
	@unlink('../../images/marketing/'.$foto);
}

$pdo->query("UPDATE $tabela SET arquivo = 'sem-foto.jpg' where id = '$id'");
echo 'Excluído com Sucesso';
 ?>