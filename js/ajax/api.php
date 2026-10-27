<?php
  $url = "http://api.enviame.com.br/send-text";
  $data = array('instance' => '5531971390746',
                'to' => '5531975275084',
                'token' => '7Q1MW-2AK-12302',
                'message' => "Dados do Pedido!");


  $options = array('http' => array(
                 'method' => 'POST',
                 'content' => http_build_query($data)
  ));

  $stream = stream_context_create($options);

  $result = file_get_contents($url, false, $stream);

  echo $result;
?>
