<?php
// Consulta de pagamento Mercado Pago, sem alterar vendas ou recebimentos.
$status_api = null;
$pagamento_pix_validado = false;
$codigoPagamento = trim((string)($codigo_pix ?? ''));
$tokenPagamento = trim((string)($api_merc ?? ''));
if ($codigoPagamento !== '' && preg_match('/^[0-9]{1,30}$/D', $codigoPagamento)
    && $tokenPagamento !== '' && function_exists('curl_init')) {
    $curl = curl_init('https://api.mercadopago.com/v1/payments/' . rawurlencode($codigoPagamento));
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 7,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: Bearer ' . $tokenPagamento
        ]
    ]);
    $response = curl_exec($curl);
    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($response !== false && $httpCode === 200) {
        $resultado = json_decode($response);
        // Nunca confiar somente no status: conferir também a identidade.
        if (is_object($resultado)
            && (string)($resultado->id ?? '') === $codigoPagamento
            && ($resultado->status ?? '') === 'approved'
            // Não aceitar cartão ou boleto como PIX mesmo que estejam aprovados.
            && strtolower((string)($resultado->payment_method_id ?? '')) === 'pix'
            && strtolower((string)($resultado->payment_type_id ?? '')) === 'bank_transfer'
            // Não quitar venda com valor diferente ou moeda não BRL.
            && strtoupper((string)($resultado->currency_id ?? '')) === 'BRL'
            && isset($valor) && is_numeric($valor)
            && isset($resultado->transaction_amount)
            && is_numeric($resultado->transaction_amount)
            && (int)round((float)$resultado->transaction_amount * 100) === (int)round((float)$valor * 100)) {
            $status_api = 'approved';
            $pagamento_pix_validado = true;
        }
    }
}
