<?php
// verificar_pref.php — retorna status da rifa por preference_id (polling)
header('Content-Type: application/json; charset=utf-8');

require 'conexao.php';

$pref = $_GET['pref'] ?? '';
$pref = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $pref);

if (!$pref) {
    echo json_encode(['error' => 'pref não informado']);
    exit;
}

$stmt = $conn->prepare("SELECT id, status, payment_id, numeros FROM rifas WHERE preference_id = ? OR external_reference = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['error' => 'erro no banco']);
    exit;
}
$stmt->bind_param("ss", $pref, $pref);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['status' => 'nao_encontrado']);
    exit;
}

echo json_encode([
    'status' => $row['status'],
    'payment_id' => $row['payment_id'] ?? null,
    'numeros' => $row['numeros'] ?? null
]);
?>