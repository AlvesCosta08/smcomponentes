<?php
require_once("../sistema/conexao.php");

$query5 = $pdo->query("SELECT * from disparos where hora <= curTime()");
$res5 = $query5->fetchAll(PDO::FETCH_ASSOC);
$linhas5 = @count($res5);
if ($linhas5 > 0) {
	for ($i5 = 0; $i5 < $linhas5; $i5++) {

		$id = $res5[$i5]['id'];
		$nome = $res5[$i5]['nome'];
		$telefone = $res5[$i5]['telefone'];
		$campanha = $res5[$i5]['campanha'];

		$telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone);
		//enviar a notificação via whatsapp

		if ($api_whatsapp != 'Não') {

			$url_envio = '';

			$query = $pdo->query("SELECT * FROM marketing where id = '$campanha'");
			$res = $query->fetchAll(PDO::FETCH_ASSOC);
			$total_reg = @count($res);

			$titulo = $res[0]['titulo'];	
			$mensagem_disparo = $res[0]['mensagem'];
			$item1 = $res[0]['item1'];
			$item2 = $res[0]['item2'];
			$item3 = $res[0]['item3'];
			$item4 = $res[0]['item4'];
			$item5 = $res[0]['item5'];
			$item6 = $res[0]['item6'];
			$item7 = $res[0]['item7'];
			$item8 = $res[0]['item8'];

			$item9 = $res[0]['item9'];
			$item10 = $res[0]['item10'];
			$item11 = $res[0]['item11'];
			$item12 = $res[0]['item12'];
			$item13 = $res[0]['item13'];
			$item14 = $res[0]['item14'];
			$item15 = $res[0]['item15'];
			$item16 = $res[0]['item16'];
			$item17 = $res[0]['item17'];
			$item18 = $res[0]['item18'];
			$item19 = $res[0]['item19'];
			$item20 = $res[0]['item20'];


			$conclusao = $res[0]['conclusao'];
			$conclusao2 = $res[0]['conclusao2'];
			$conclusao3 = $res[0]['conclusao3'];
			$conclusao4 = $res[0]['conclusao4'];
			$conclusao5 = $res[0]['conclusao5'];

			$arquivo = $res[0]['arquivo'];
			$audio = $res[0]['audio'];
			$documento = $res[0]['documento'];

			$link = $res[0]['link'];
			$link2 = $res[0]['link2'];
			$video = $res[0]['video'];

			$envios = $res[0]['envios'];

			$mensagem = '';
			if($titulo != ""){
				$mensagem .= '🤩 *'.$titulo.'* %0A%0A';
			}

			if($mensagem_disparo != ""){
				$mensagem .= '_'.$mensagem_disparo.'_ %0A%0A';
			}

			if($item1 != ""){
				$mensagem .= '✅'.$item1.' %0A';
			}

			if($item2 != ""){
				$mensagem .= '✅'.$item2.' %0A';
			}

			if($item3 != ""){
				$mensagem .= '✅'.$item3.' %0A';
			}

			if($item4 != ""){
				$mensagem .= '✅'.$item4.' %0A';
			}

			if($item5 != ""){
				$mensagem .= '✅'.$item5.' %0A';
			}

			if($item6 != ""){
				$mensagem .= '✅'.$item6.' %0A';
			}

			if($item7 != ""){
				$mensagem .= '✅'.$item7.' %0A';
			}

			if($item8 != ""){
				$mensagem .= '✅'.$item8.' %0A';
			}


			if($item9 != ""){
				$mensagem .= '✅'.$item9.' %0A';
			}

			if($item10 != ""){
				$mensagem .= '✅'.$item10.' %0A';
			}

			if($item11 != ""){
				$mensagem .= '✅'.$item11.' %0A';
			}

			if($item12 != ""){
				$mensagem .= '✅'.$item12.' %0A';
			}

			if($item13 != ""){
				$mensagem .= '✅'.$item13.' %0A';
			}

			if($item14 != ""){
				$mensagem .= '✅'.$item14.' %0A';
			}

			if($item15 != ""){
				$mensagem .= '✅'.$item15.' %0A';
			}

			if($item16 != ""){
				$mensagem .= '✅'.$item16.' %0A';
			}

			if($item17 != ""){
				$mensagem .= '✅'.$item17.' %0A';
			}

			if($item18 != ""){
				$mensagem .= '✅'.$item18.' %0A';
			}

			if($item19 != ""){
				$mensagem .= '✅'.$item19.' %0A';
			}

			if($item20 != ""){
				$mensagem .= '✅'.$item20.' %0A';
			}

			if($conclusao != ""){
				$mensagem .= '%0A'.$conclusao;
			}


			if($link != ""){
				$mensagem .= '%0A'.$link.' %0A';
			}



			if($conclusao2 != ""){
				$mensagem .= '%0A'.$conclusao2;
			}

			if($video != ""){
				$mensagem .= '%0A'.$video.' %0A';
			}


			if($conclusao5 != ""){
				$mensagem .= '%0A'.$conclusao5;
			}


			if($link2 != ""){
				$mensagem .= '%0A'.$link2.'%0A';
			}


			if($conclusao3 != ""){
				$mensagem .= '%0A _'.$conclusao3.'_ %0A';
			}

			if($conclusao4 != ""){
				$mensagem .= '%0A _'.$conclusao4.'_';
			}


require ("api_texto_retorno.php");


$url_envio = $url_sistema."sistema/painel/images/marketing/".$arquivo;
if($arquivo != "sem-foto.jpg"){
	require("marketing_file.php");
}

$url_envio = $url_sistema."sistema/painel/images/marketing/".$audio;
if($audio != ""){	
	require("marketing_file.php");
}

$url_envio = $url_sistema."sistema/painel/images/marketing/".$documento;
if($documento != "sem-foto.jpg"){	
	$mensagem = 'arquivo.pdf';
	require("marketing_file.php");
}




			if((@$status_mensagem == "Mensagem enviada com sucesso." or @$status_mensagem == "O número não existe") and $api_whatsapp == 'menuia'){
				$pdo->query("DELETE from disparos where id = '$id'");
			}

			if($api_whatsapp != 'menuia'){
				$pdo->query("DELETE from disparos where id = '$id'");
			}




		}

	}

}

echo $linhas5;