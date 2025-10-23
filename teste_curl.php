<?php
$ch = curl_init("https://api.pagbank.com/charges");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
if ($response === false) {
  echo "❌ Erro cURL: " . curl_error($ch);
} else {
  echo "✅ Requisição enviada com sucesso.<br><br>";
  echo "Resposta: <pre>" . htmlspecialchars($response) . "</pre>";
}
curl_close($ch);
?>

