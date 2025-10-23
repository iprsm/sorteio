<?php
// verificar_pagamento.php — Consulta de Status PagBank (Charge) e atualiza DB via polling
header('Content-Type: application/json; charset=utf-8');

// ⚙️ Access Token — use variável de ambiente em produção
$access_token = getenv('PAGBANK_ACCESS_TOKEN');
if (!$access_token) {
    // ⚠️ SUBSTITUA ESTE TOKEN PELO SEU TOKEN DE SANDBOX OU PRODUÇÃO ⚠️
    $access_token = 'cd5197ed-5397-466b-853d-3439ea40ce42643cb79747bca3d5489631f5f2794aabc4bc-6123-46fd-b616-71fac4c12699'; 
}

// O ID da Cobrança (Charge ID: CHAR_...)
$id = $_GET['payment_id'] ?? $_GET['charge_id'] ?? $_GET['collection_id'] ?? null; 
$id = preg_replace('/[^a-zA-Z0-9_\\-]/', '', $id ?? ''); 

if (!$id) {
    echo json_encode(['error' => 'charge_id (payment_id) não informado']);
    exit;
}

require 'conexao.php';
require_once __DIR__ . '/pagbank_helper.php';
$base_url = get_pagbank_base_url();
$endpoint = rtrim($base_url, '/') . '/charges/' . rawurlencode($id);
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $access_token"
]);

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
    
    $status_pagbank = $result['status'] ?? 'UNKNOWN';
    $order_reference = $result['reference_id'] ?? null; // Sua referência externa

    // Mapeamento de status PagBank para o status do seu sistema
    $status_sistema = 'pendente'; 
    if ($status_pagbank === 'APPROVED' || $status_pagbank === 'PAID') {
        $status_sistema = 'aprovado';
    } elseif ($status_pagbank === 'DECLINED' || $status_pagbank === 'CANCELED') {
        $status_sistema = 'cancelado';
    }

    // 📌 ATUALIZA STATUS NO BANCO DE DADOS (para sincronizar o DB enquanto o webhook não chega)
    if ($order_reference) {
        $stmt_up = $conn->prepare("UPDATE rifas SET status = ?, payment_id = ? WHERE external_reference = ? AND status = 'pendente' LIMIT 1");
        if ($stmt_up) {
            $stmt_up->bind_param("sss", $status_sistema, $id, $order_reference);
            $stmt_up->execute();
            $stmt_up->close();
        }
    }
    
    echo json_encode([
        'status' => $status_sistema, 
        'status_pagbank' => $status_pagbank, 
        'charge_id' => $id 
    ]);

} else {
    echo json_encode(['error' => 'Erro ao consultar PagBank Charge API', 'http_code' => $httpcode, 'details' => $response]);
}
?>
