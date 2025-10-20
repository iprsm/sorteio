<?php
// verificar_pagamento.php — sem SDK
header('Content-Type: application/json; charset=utf-8');

$access_token = getenv('MP_ACCESS_TOKEN');
if (!$access_token) {
    $access_token = 'APP_USR-2221051077100454-101711-2e56ad1a374acc9fcb5370beb067fe35-169840778'; // substitua em testes
}

$id = $_GET['payment_id'] ?? $_GET['collection_id'] ?? null;
$id = preg_replace('/\D/', '', $id ?? '');

if (!$id) {
    echo json_encode(['error' => 'payment_id não informado']);
    exit;
}

$ch = curl_init("https://api.mercadopago.com/v1/payments/{$id}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $access_token"
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['error' => curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);

if ($httpcode >= 200 && $httpcode < 300) {
    $result = json_decode($response, true);
    echo json_encode([
        'status' => $result['status'] ?? 'desconhecido',
        'status_detail' => $result['status_detail'] ?? '',
        'id' => $result['id'] ?? null,
        'transaction_amount' => $result['transaction_amount'] ?? null
    ]);
} else {
    echo json_encode(['error' => 'Erro ao consultar pagamento', 'response' => $response]);
}

