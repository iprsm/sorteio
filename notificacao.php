<?php
// notificacao.php
require 'conexao.php';

$access_token = getenv('PAGBANK_ACCESS_TOKEN');
if (!$access_token) {
    $access_token = 'c0c7c542-0150-441f-80a3-afda883eb6fa490cd9da4e38a18feda184884d6fa2dd4773-a291-4cda-a84b-d63de60c739a'; // substitua pelo token de acesso real
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
$ch = curl_init("https://api.pagbank.com.br/v1/payments/$payment_id");
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
?>