<?php
// notificacao.php
require 'conexao.php';

$access_token = getenv('MP_ACCESS_TOKEN');
if (!$access_token) {
    $access_token = 'APP_USR-2221051077100454-101711-2e56ad1a374acc9fcb5370beb067fe35-169840778';
}

// lê o corpo da notificação
$input = file_get_contents('php://input');
file_put_contents('logs_notificacao.txt', date('Y-m-d H:i:s')." => ".$input.PHP_EOL, FILE_APPEND);

$data = json_decode($input, true);
if (!isset($data['data']['id'])) {
    http_response_code(400);
    exit('sem id');
}

$payment_id = $data['data']['id'];

// consulta status do pagamento
$ch = curl_init("https://api.mercadopago.com/v1/payments/$payment_id");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $access_token",
        "Content-Type: application/json"
    ]
]);
$response = curl_exec($ch);
curl_close($ch);
$pagamento = json_decode($response, true);

if (!isset($pagamento['status'])) {
    http_response_code(400);
    exit('sem status');
}

// verifica se é aprovado
if ($pagamento['status'] === 'approved') {
    $pref_id = $pagamento['order']['id'] ?? $pagamento['metadata']['preference_id'] ?? null;

    // busca o registro no banco
    $stmt = $conn->prepare("SELECT id, quantidade FROM rifas WHERE preference_id = ? AND status = 'pendente' LIMIT 1");
    $stmt->bind_param("s", $pref_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $rifa_id = $row['id'];
        $quantidade = $row['quantidade'];

        // gera números únicos (não repetidos)
        $numerosGerados = [];
        while (count($numerosGerados) < $quantidade) {
            $num = rand(1, 10000);
            // verifica se o número já foi usado
            $check = $conn->query("SELECT COUNT(*) AS total FROM rifas WHERE numeros LIKE '%\"$num\"%' AND status = 'aprovado'");
            $exists = $check->fetch_assoc()['total'];
            if ($exists == 0) {
                $numerosGerados[] = $num;
            }
        }
        sort($numerosGerados);
        $jsonNumeros = json_encode($numerosGerados, JSON_UNESCAPED_UNICODE);

        // atualiza registro como aprovado
        $up = $conn->prepare("UPDATE rifas SET status='aprovado', numeros=?, payment_id=? WHERE id=?");
        $up->bind_param("ssi", $jsonNumeros, $payment_id, $rifa_id);
        $up->execute();
    }
}

http_response_code(200);
echo "OK";

