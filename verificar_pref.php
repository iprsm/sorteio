<?php
require_once "conexao.php";
header('Content-Type: application/json; charset=utf-8');

$pref = $_GET['pref'] ?? '';

if (!$pref) {
    echo json_encode(["status" => "erro", "mensagem" => "Faltando parâmetro."]);
    exit;
}

$stmt = $conn->prepare("SELECT status, numeros FROM rifas WHERE preference_id = ?");
$stmt->bind_param("s", $pref);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["status" => $row['status'], "numeros" => $row['numeros']]);
} else {
    echo json_encode(["status" => "nao_encontrado"]);
}
$stmt->close();
?>

