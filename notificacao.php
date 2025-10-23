<?php
require_once "conexao.php";
require_once "config.php";

header('Content-Type: application/json; charset=utf-8');

$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Salva log para debug (opcional)
file_put_contents("logs_notificacao.txt", date("Y-m-d H:i:s") . " - " . $input . "\n", FILE_APPEND);

// Garante ID de checkout
if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(["erro" => "ID da transação não encontrado"]);
    exit;
}

$pref = $data['id'];
$status = $data['status'] ?? 'pendente';

// Atualiza no banco
$stmt = $conn->prepare("UPDATE rifas SET status = ? WHERE preference_id = ?");
$stmt->bind_param("ss", $status, $pref);
$stmt->execute();

// Se aprovado, gera números
if ($status === 'PAID' || $status === 'approved' || $status === 'aprovado') {
    $numSorteio = [];
    for ($i = 0; $i < 5; $i++) {
        $numSorteio[] = rand(1, 999);
    }
    $numeros = json_encode($numSorteio);

    $stmt2 = $conn->prepare("UPDATE rifas SET numeros = ?, status = 'aprovado' WHERE preference_id = ?");
    $stmt2->bind_param("ss", $numeros, $pref);
    $stmt2->execute();
    $stmt2->close();
}

$stmt->close();
echo json_encode(["ok" => true]);
?>

