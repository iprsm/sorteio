<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// criar_pagamento.php — integração direta com API REST do Mercado Pago (sem SDK)
header('Content-Type: application/json; charset=utf-8');

// ⚙️ Configure seu Access Token (use variável de ambiente em produção)
$access_token = getenv('MP_ACCESS_TOKEN');
if (!$access_token) {
    $access_token = 'APP_USR-2221051077100454-101711-2e56ad1a374acc9fcb5370beb067fe35-169840778'; // substitua apenas para testes
}

// URLs de retorno (devem ser absolutas)
$back_urls = [
    "success" => "https://sorteio.iprsm.com.br/pagamento_concluido.html",
    "failure" => "https://sorteio.iprsm.com.br//pagamento_aguardando.html",
    "pending" => "https://sorteio.iprsm.com.br//pagamento_aguardando.html"
];

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

$nome = htmlspecialchars(trim($data['nome'] ?? ''), ENT_QUOTES, 'UTF-8');
$telefone = preg_replace('/\D/', '', $data['telefone'] ?? '');
$qtd = (int)($data['qtd'] ?? 1);
$valor = floatval($data['valor'] ?? 0);

$body = [
    "items" => [
        [
            "title" => "Cota Sorteio IPRSM ({$qtd} números)",
            "quantity" => 1,
            "unit_price" => $valor,
            "currency_id" => "BRL"
        ]
    ],
    "payer" => [
        "name" => $nome,
        "phone" => ["area_code" => substr($telefone, 0, 2), "number" => substr($telefone, 2)]
    ],
    "back_urls" => $back_urls,
    "auto_return" => "approved",
    "binary_mode" => false
];

$ch = curl_init("https://api.mercadopago.com/checkout/preferences");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $access_token"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

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
        'init_point' => $result['init_point'] ?? null,
        'preference_id' => $result['id'] ?? null
    ]);
} else {
    echo json_encode(['error' => 'Erro ao criar pagamento', 'details' => $response]);
}

