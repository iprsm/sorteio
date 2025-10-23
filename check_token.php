<?php
<?php
// Verifica token PagBank de forma simples
require_once __DIR__ . '/pagbank_helper.php';
$base_url = function_exists('get_pagbank_base_url') ? get_pagbank_base_url() : (getenv('PAGBANK_BASE_URI') ?: 'https://api.pagbank.com.br');
$token = $argv[1] ?? getenv('PAGBANK_ACCESS_TOKEN') ?: '';

if (!$token) {
    echo "Uso: php check_token.php <TOKEN>\nOu exporte PAGBANK_ACCESS_TOKEN no ambiente.\n";
    exit(2);
}

$endpoint = rtrim($base_url, '/') . '/orders?page=1&per_page=1';

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer {$token}",
        "Content-Type: application/json"
    ],
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

echo "HTTP: {$http}\n";
if ($err) {
    echo "cURL error: {$err}\n";
}
echo "Body:\n";
echo $response . "\n";

if ($http >= 200 && $http < 300) {
    echo "RESULTADO: Token válido (resposta 2xx).\n";
    exit(0);
} elseif ($http == 401 || $http == 403) {
    echo "RESULTADO: Token inválido ou sem permissão (401/403).\n";
    exit(3);
} else {
    echo "RESULTADO: Resposta não conclusiva; verifique body para detalhes.\n";
    exit(4);
}
?>