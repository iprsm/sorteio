<?php
header('Content-Type: application/json; charset=utf-8');
include 'config.php'; // deve conter $TOKEN com o Access Token do PagBank

// ====== VALIDAÇÃO BÁSICA ======
if (!isset($_POST['valor']) || !isset($_POST['descricao'])) {
    echo json_encode(["erro" => "Dados incompletos para criar o pagamento."]);
    exit;
}

$valor = floatval($_POST['valor']);
$descricao = trim($_POST['descricao']);
$metodo = isset($_POST['metodo']) ? strtoupper($_POST['metodo']) : 'PIX'; // PIX ou CREDIT_CARD

// Valor deve ser em centavos (ex: 25.00 => 2500)
$amount_value = intval($valor * 100);

// ====== DADOS DO PAGAMENTO ======
$dados = [
    "reference_id" => uniqid("rifa_"),
    "description" => $descricao,
    "amount" => [
        "value" => $amount_value,
        "currency" => "BRL"
    ],
    "payment_method" => [
        "type" => $metodo
    ],
    "notification_urls" => [
        "https://sorteio.iprsm.com.br/notificacao.php"
    ]
];

// ====== CONFIGURAÇÃO DO CURL ======
$api_url = "https://sandbox.api.pagbank.com/charges"; // produção
// $api_url = "https://sandbox.api.pagbank.com/charges"; // sandbox (para testes)

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $TOKEN",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

// ====== EXECUÇÃO ======
$response = curl_exec($ch);

// ====== TRATAMENTO DE ERROS CURL ======
if ($response === false) {
    $erro_curl = curl_error($ch);
    curl_close($ch);
    echo json_encode(["erro" => "Falha na conexão com PagBank: $erro_curl"]);
    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ====== INTERPRETAÇÃO DA RESPOSTA ======
$data = json_decode($response, true);

// Verifica se a resposta é JSON válida
if ($data === null) {
    echo json_encode([
        "erro" => "Resposta inválida do servidor PagBank.",
        "http_code" => $http_code,
        "resposta" => $response
    ]);
    exit;
}

// ====== SUCESSO OU ERRO DO PAGBANK ======
if ($http_code >= 200 && $http_code < 300) {
    // Retorna os dados completos (ex: QRCode PIX, link do checkout, etc.)
    echo json_encode([
        "status" => "sucesso",
        "dados" => $data
    ]);
} else {
    // Retorna erro detalhado
    echo json_encode([
        "status" => "erro",
        "http_code" => $http_code,
        "detalhes" => $data
    ]);
}
?>

