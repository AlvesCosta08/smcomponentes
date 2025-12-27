<?php 
$tabela = 'receber';
require_once("../../../conexao.php");

@session_start();
$id_usuario = @$_SESSION['id'];

$hora = date('H');

if ($hora < 12 && $hora >= 6)
	$saudacao = "Bom Dia";

if ($hora >= 12 && $hora < 18)
	$saudacao = "Boa Tarde";

if ($hora >= 18 && $hora <= 23)
	$saudacao = "Boa Noite";
if ($hora < 6 && $hora >= 0)
	$saudacao = "Boa madrugada";


$descricao = $_POST['descricao'];
$valor = $_POST['valor'];
$cliente = $_POST['cliente'];
$vencimento = $_POST['vencimento'];
$data_pgto = $_POST['data_pgto'];
$forma_pgto = $_POST['forma_pgto'];
$frequencia = $_POST['frequencia'];
$obs = $_POST['obs'];
$id = $_POST['id'];



$valor = str_replace(',', '.', $valor);

$valorF = @number_format($valor, 2, ',', '.');

if($cliente == ""){
	$cliente = 0;
}

if($forma_pgto == ""){
	$forma_pgto = 0;
}

if($frequencia == ""){
	$frequencia = 0;
}

if($data_pgto == ""){
	$pgto = '';
	$usu_pgto = '';
	$pago = 'Não';
}else{
	$pgto = " ,data_pgto = '$data_pgto'";
	$usu_pgto = " ,usuario_pgto = '$id_usuario'";
	$pago = 'Sim';
}

//validacao
if($descricao == "" and $cliente == "0"){
	echo 'Selecione um Cliente ou uma Descrição!';
	exit();
}

$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_cliente = $res2[0]['nome'];
	$telefone_cliente = $res2[0]['telefone'];
}else{
	$nome_cliente = 'Sem Registro';
	$telefone_cliente = "";
}

// extrair o primiero nome
$primeiroNome = explode(" ", $nome_cliente);

if($descricao == ""){
	$descricao = $nome_cliente;
}

//validar troca da foto
$query = $pdo->query("SELECT * FROM $tabela where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	$foto = $res[0]['arquivo'];
	$vencimento_antiga = $res[0]['vencimento'];
}else{
	$foto = 'sem-foto.png';
	$vencimento_antiga = '';
}



//SCRIPT PARA SUBIR FOTO NO SERVIDOR
$nome_img = date('d-m-Y H:i:s') .'-'.@$_FILES['foto']['name'];
$nome_img = preg_replace('/[ :]+/' , '-' , $nome_img);

$caminho = '../../images/contas/' .$nome_img;

$imagem_temp = @$_FILES['foto']['tmp_name']; 

if(@$_FILES['foto']['name'] != ""){
	$ext = pathinfo($nome_img, PATHINFO_EXTENSION);   
	if($ext == 'png' or $ext == 'jpg' or $ext == 'jpeg' or $ext == 'gif' or $ext == 'pdf' or $ext == 'rar' or $ext == 'zip' or $ext == 'doc' or $ext == 'docx' or $ext == 'webp' or $ext == 'PNG' or $ext == 'JPG' or $ext == 'JPEG' or $ext == 'GIF' or $ext == 'PDF' or $ext == 'RAR' or $ext == 'ZIP' or $ext == 'DOC' or $ext == 'DOCX' or $ext == 'WEBP' or $ext == 'xlsx' or $ext == 'xlsm' or $ext == 'xls' or $ext == 'xml'){ 
	
			//EXCLUO A FOTO ANTERIOR
			if($foto != "sem-foto.png"){
				@unlink('../../images/contas/'.$foto);
			}

			$foto = $nome_img;

		//pegar o tamanho da imagem
		list($largura, $altura) = getimagesize($imagem_temp);
		if ($largura > 1400) {
			echo 'Diminua a imagem para um tamanho de no máximo 1400px de largura!';
			exit();
		} else {
			move_uploaded_file($imagem_temp, $caminho);
		}


	}else{
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}


//verificar caixa aberto
$query1 = $pdo->query("SELECT * from caixas where operador = '$id_usuario' and data_fechamento is null order by id desc limit 1");
$res1 = $query1->fetchAll(PDO::FETCH_ASSOC);
if(@count($res1) > 0){
	$id_caixa = @$res1[0]['id'];
}else{
	$id_caixa = 0;
}


if($id == ""){
$query = $pdo->prepare("INSERT INTO $tabela SET descricao = :descricao, cliente = :cliente, valor = :valor, vencimento = '$vencimento' $pgto, data_lanc = curDate(), forma_pgto = '$forma_pgto', frequencia = '$frequencia', obs = :obs, arquivo = '$foto', foto = '$foto', subtotal = :valor, usuario_lanc = '$id_usuario' $usu_pgto, pago = '$pago', referencia = 'Conta', caixa = '$id_caixa', hora = curTime() ");



	$data_venc = $vencimento;

	################# CRIAR A PRÓXIMA CONTA A RECEBER CASO TENHA SIDO PAGA #################
	$dias_frequencia = $frequencia;

	if ($dias_frequencia == 30 || $dias_frequencia == 31) {
		$nova_data_vencimento = date('Y/m/d', strtotime("+1 month", strtotime($vencimento)));
	} else if ($dias_frequencia == 90) {
		$nova_data_vencimento = date('Y/m/d', strtotime("+3 month", strtotime($vencimento)));
	} else if ($dias_frequencia == 180) {
		$nova_data_vencimento = date('Y/m/d', strtotime("+6 month", strtotime($vencimento)));
	} else if ($dias_frequencia == 360 || $dias_frequencia == 365) {
		$nova_data_vencimento = date('Y/m/d', strtotime("+1 year", strtotime($vencimento)));
	} else {
		$nova_data_vencimento = date('Y/m/d', strtotime("+$dias_frequencia days", strtotime($vencimento)));
	}

	if (@$dias_frequencia > 0 and $data_pgto != '') {

		$pdo->query("INSERT INTO $tabela SET descricao = '$descricao', cliente = '$cliente', valor = '$valor', data_lanc = curDate(), vencimento = '$nova_data_vencimento', frequencia = '$frequencia', forma_pgto = '$forma_pgto', arquivo = '$foto', pago = 'Não', referencia = 'Conta', usuario_lanc = '$id_usuario', hora = curTime(), obs = '$obs', caixa = '$id_caixa'");
		$id_ult_registro = $pdo->lastInsertId();


		if ($api_whatsapp != 'Não' and $telefone_cliente != '') {


			$valorF = @number_format($valor, 2, ',', '.');
			$telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);
			$nova_data_vencimentoF = implode('/', array_reverse(explode('-', $nova_data_vencimento)));


			############## AGENDAR MENSAGEM PARA RENOVAÇÃO ###########################
			$mensagem_whatsapp = '🔔_Lembrete Automático de Vencimento!_ %0A%0A';

			$mensagem_whatsapp .= $saudacao . ' *' . $primeiroNome[0] . '* tudo bem? 😀%0A%0A';

			$mensagem_whatsapp .= '_Queremos lembrar que você tem uma Conta Vencendo Hoje_ %0A%0A';
			$mensagem_whatsapp .= 'Empresa: *' . $nome_sistema . '* %0A';
			$mensagem_whatsapp .= 'Nome: *' . $nome_cliente . '* %0A';
			$mensagem_whatsapp .= 'Valor: *R$ ' . $valorF . '* %0A';
			$mensagem_whatsapp .= 'Data de Vencimento: *' . $nova_data_vencimentoF . '* %0A%0A';
			$mensagem_whatsapp .= '_Entre em contato conosco para acertar o pagamento!_ %0A%0A';

			if ($dados_pagamento != "") {
				$mensagem_whatsapp .= '*Dados para o Pagamento:* %0A';
				$mensagem_whatsapp .= $dados_pagamento;
			}

			$mensagem_whatsapp .= '%0A';
			$mensagem_whatsapp .= '🤖 _Esta é uma mensagem automática!_';

			$data_agd = $nova_data_vencimento . ' 09:00:00';

			require('../../apis/agendar.php');

			$pdo->query("UPDATE $tabela SET hash = '$hash' where id = '$id_ult_registro'");
		}



}
	
}else{
$query = $pdo->prepare("UPDATE $tabela SET descricao = :descricao, cliente = :cliente, valor = :valor, vencimento = '$vencimento' $pgto, forma_pgto = '$forma_pgto', frequencia = '$frequencia', obs = :obs, arquivo = '$foto', foto = '$foto', subtotal = :valor where id = '$id'");



	if ($vencimento_antiga != $vencimento) {


		$query4 = $pdo->query("SELECT * FROM $tabela where id = '$id'");
		$res4 = $query4->fetchAll(PDO::FETCH_ASSOC);
		$hash = @$res4[0]['hash'];

		if ($hash != "") {
			require("../../apis/cancelar_agendamento.php");
		}

		$vencimentoF = implode('/', array_reverse(explode('-', $vencimento)));


		//enviar whatsapp
		if ($api_whatsapp != 'Não' and $telefone_cliente != '') {


			$telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);

			############## AGENDAR MENSAGEM PARA RENOVAÇÃO ###########################
			$mensagem_whatsapp = '🔔_Lembrete Automático de Vencimento!_ %0A%0A';
			$mensagem_whatsapp .= $saudacao . ' *' . $primeiroNome[0] . '* tudo bem? 😀%0A%0A';
			$mensagem_whatsapp .= '_Queremos lembrar que você tem uma Conta Vencendo Hoje_ %0A%0A';
			$mensagem_whatsapp .= 'Empresa: *' . $nome_sistema . '* %0A';
			$mensagem_whatsapp .= 'Nome: *' . $nome_cliente . '* %0A';
			$mensagem_whatsapp .= 'Valor: *R$ ' . $valorF . '* %0A';
			$mensagem_whatsapp .= 'Data de Vencimento: *' . $vencimentoF . '* %0A%0A';
			$mensagem_whatsapp .= '_Entre em contato conosco para acertar o pagamento!_ %0A%0A';


			if ($dados_pagamento != "") {
				$mensagem_whatsapp .= '*Dados para o Pagamento:* %0A';
				$mensagem_whatsapp .= $dados_pagamento;
			}

			$mensagem_whatsapp .= '%0A';
			$mensagem_whatsapp .= '🤖 _Esta é uma mensagem automática!_';

			$data_agd = $vencimento . ' 09:00:00';
			require('../../apis/agendar.php');

			$pdo->query("UPDATE $tabela SET hash = '$hash' where id = '$id'");
		}
	}


}


$query->bindValue(":descricao", "$descricao");
$query->bindValue(":cliente", "$cliente");
$query->bindValue(":valor", "$valor");
$query->bindValue(":obs", "$obs");
$query->execute();
$ultimo_id = $pdo->lastInsertId();






//enviar whatsapp
if($api_whatsapp != 'Não' and $telefone_cliente != ''  and $id == "" and $data_pgto == ""){

	$telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);

	$mensagem_whatsapp = '🔔_Lembrete Automático de Vencimento!_ %0A%0A';

	$mensagem_whatsapp .= $saudacao . ' *' . $primeiroNome[0] . '* tudo bem? 😀%0A%0A';

	$mensagem_whatsapp .= '*'.$nome_sistema.'*%0A';
	$mensagem_whatsapp .= '_Queremos lembrar que sua Conta Vencendo Hoje_ %0A';
	$mensagem_whatsapp .= '*Descrição:* '.$descricao.' %0A';
	$mensagem_whatsapp .= '*Valor:* '.$valorF.' %0A%0A';	


	if($dados_pagamento != ""){
				$mensagem_whatsapp .= '*Dados para o Pagamento:* %0A';
				$mensagem_whatsapp .= $dados_pagamento;
			}
	
	$data_agd = $vencimento.' 09:00:00';
	require('../../apis/agendar.php');

	$pdo->query("UPDATE $tabela SET hash = '$hash' where id = '$ultimo_id'");
	
}


echo 'Salvo com Sucesso';
