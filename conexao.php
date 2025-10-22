<?php
// conexao.php — cria $conn (mysqli) usando variáveis de ambiente; fallback para config.php

// tenta obter das variáveis de ambiente padrão
$DB_HOST = getenv('DB_HOST') ?: getenv('DB_HOSTNAME') ?: '127.0.0.1';
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME');

if (!$DB_USER || !$DB_NAME) {
    // fallback: tenta usar config.php (mantido para compatibilidade)
    $cfgPath = __DIR__ . '/config.php';
    if (file_exists($cfgPath)) {
        require_once $cfgPath;
        // se config.php já criou $conn, usa-o
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->set_charset('utf8mb4');
            return;
        }
        // se config.php definiu credenciais ($servername, $username, $password, $dbname), cria conexão
        if (isset($servername, $username, $password, $dbname)) {
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_errno) {
                error_log('conexao.php (config.php fallback): erro conexão DB: ' . $conn->connect_error);
                http_response_code(500);
                exit('Erro de conexão ao banco');
            }
            $conn->set_charset('utf8mb4');
            return;
        }
    }

    error_log('conexao.php: credenciais de banco não configuradas (DB_USER/DB_NAME) e config.php ausente ou inválido.');
    http_response_code(500);
    exit('Erro de configuração');
}

// cria conexão usando variáveis de ambiente
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_errno) {
    error_log('conexao.php: erro conexão DB: ' . $conn->connect_error);
    http_response_code(500);
    exit('Erro de conexão ao banco');
}
$conn->set_charset('utf8mb4');
?>