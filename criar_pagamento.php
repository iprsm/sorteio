<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// ⚙️ Access Token — use variável de ambiente em produção
$access_token = getenv('PAGBANK_ACCESS_TOKEN');
if (!$access_token) {
    $access_token = 'c0c7c542-0150-441f-80a3-afda883eb6fa490cd9da4e38a18feda184884d6fa2dd4773-a291-4cda-a84b-d63de60c739a'; // apenas para testes
}

// URLs de retorno (devem ser absolutas)
$back_urls = [
    "success" => "https://sorteio.iprsm.com.br/pagamento_concluido.html",
    "failure" => "https://sorteio.iprsm.com.br/pagamento_aguardando.html",
    "pending" => "https://sorteio.iprsm.com.br/pagamento_aguardando.html"
];

// Recebe dados do frontend
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

// Corpo da requisição (preference)
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
        "phone" => [
            "area_code" => substr($telefone, 0, 2),
            "number" => substr($telefone, 2)
        ]
    ],
    "back_urls" => $back_urls,
    "auto_return" => "approved",
    "binary_mode" => false,

    // 💳 Permitir apenas PIX e Cartão de Crédito
    "payment_methods" => [
        "excluded_payment_types" => [
            ["id" => "ticket"],            // exclui boleto
            ["id" => "atm"],               // exclui caixa eletrônico
            ["id" => "debit_card"],        // exclui cartão de débito
            ["id" => "digital_currency"],  // exclui criptomoedas
            ["id" => "prepaid_card"],      // exclui pré-pagos
            ["id" => "bank_transfer"],     // exclui transferências bancárias
            ["id" => "account_money"]      // exclui saldo em conta PagBank
        ],
        "installments" => 12 // máximo de parcelas no cartão de crédito
    ]
];

// Envia requisição para API do PagBank
$ch = curl_init("https://sandbox.api.pagbank.com.br/checkout/preferences");
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
?>