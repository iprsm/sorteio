<?php
$servername = "iprsmrifa_db.mysql.dbaas.com.br"; // Host da Locaweb
$username = "iprsmrifa_db"; // Usuário do banco
$password = "RooT017803@"; // Senha do banco
$dbname = "iprsmrifa_db"; // Nome do banco

// Cria conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}
?>

