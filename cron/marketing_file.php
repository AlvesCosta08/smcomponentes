<?php

if ($api_whatsapp == 'menuia') {

	 $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://chatbot.menuia.com/api/create-message',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => array(
    'appkey' => $token_whatsapp,
    'authkey' => $instancia_whatsapp,
    'to' => $telefone_envio,
    'message' => $mensagem,
    'file' => $url_envio,
    'sandbox' => 'false'
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  echo $response;

}


if ($api_whatsapp == 'wm') {

	$mensagem = '';
	$data = array('instance' => "$instancia_whatsapp",
    'to' => "$telefone_envio",
    'token' => "$token_whatsapp",
    'message' => "$mensagem",
    'url' => "$url_envio");

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://api.wordmensagens.com.br/send-docnew',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $data,
  ));

  $response = curl_exec($curl);

  curl_close($curl);

  $response = json_decode($response, true);

  if($response['erro'] == false){
    //echo "Enviado com sucesso";
  }else if($response['erro'] == true){
    //echo "Erro no Envio > ".$response['message'];
  }

}




if ($api_whatsapp == 'newtek') {
  $mensagem = '';
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://webapi.newteksoft.com.br/send-docnew',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode(
      array(
        "instancia" => $instancia_whatsapp,
        "token" => $token_whatsapp,
        "mensagem" => $mensagem,
        "para" => array($telefone_envio),
        "delay" => "1"
      )
    ),
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
    ),
  )
  );

  $response = curl_exec($curl);

  curl_close($curl);
  echo $response;
}

?>