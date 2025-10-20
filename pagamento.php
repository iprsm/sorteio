<?php
header("Content-Type: application/json; charset=UTF-8");

$input = json_decode(file_get_contents("php://input"), true);

$nome = $input["nome"] ?? "";
$telefone = $input["telefone"] ?? "";
$qtd = $input["qtd"] ?? 1;
$valor = $input["valor"] ?? 0;
$metodo = $input["metodo"] ?? "pix";

if (!$nome || !$telefone || !$valor) {
  echo json_encode(["erro" => "Dados inválidos."]);
  exit;
}

if ($metodo === "pix") {
// Mercado Pago SDK PIX
$mp = new MercadoPago\SDK();
$payment = new MercadoPago\Payment();
$payment->transaction_amount = $valor;
$payment->description = "Sorteio IPR São Miguel Paulista";
$payment->payment_method_id = "pix";
$payment->payer = ["email" => "teste@teste.com"];
$payment->save();


  // 🔹 Simula salvamento do status em arquivo local (poderia ser banco)
  file_put_contents(__DIR__ . "/pagamentos/$payment_id.json", json_encode([
    "id" => $payment_id,
    "status" => "pending",
    "nome" => $nome,
    "telefone" => $telefone,
    "qtd" => $qtd,
    "valor" => $valor,
    "timestamp" => time()
  ]));

  echo json_encode([
    "tipo" => "pix",
    "qr_code" => $codigo_pix,
    "qr_code_base64" => $qr_code_base64,
    "payment_id" => $payment_id
  ]);
  exit;
}

if ($metodo === "checkout") {
  include "criar_pagamento.php";
  exit;
}

echo json_encode(["erro" => "Método inválido."]);

