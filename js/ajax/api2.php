<?php
  $url = "http://api.enviame.com.br/agendar-text";
  $data = array('instance' => $whatsapp_sistema,
                'to' => $tel_cliente_whats,
                'token' => 'SIPHO-HF2-022F2',
                'message' => $mensagem,
                'data' => $data_mensagem);


  $options = array('http' => array(
                 'method' => 'POST',
                 'content' => http_build_query($data)
  ));

  $stream = stream_context_create($options);

  $result = file_get_contents($url, false, $stream);

  echo $result;
?>
  
  