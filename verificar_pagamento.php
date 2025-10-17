<?php
header('Content-Type: application/json');

// Substitua pelo seu Access Token real do Mercado Pago:
$access_token = 'APP_USR-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-xxxxxx';

if (!isset($_GET['payment_id'])) {
  echo json_encode(['error' => 'ID de pagamento não informado']);
  exit;
}

$payment_id = $_GET['payment_id'];
$url = "https://api.mercadopago.com/v1/payments/$payment_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer $access_token"
]);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
  $data = json_decode($response, true);
  echo json_encode([
    'status' => $data['status'] ?? 'unknown'
  ]);
} else {
  echo json_encode(['error' => 'Falha na comunicação com o Mercado Pago']);
}
?>

