<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Iniciando teste de conexão...</h3>";

$servername = "iprsmrifa_db.mysql.dbaas.com.br"; // Host da Locaweb
$username = "iprsmrifa_db"; // Usuário do banco
$password = "RooT017803@"; // Senha do banco
$dbname = "iprsmrifa_db"; // Nome do banco

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Falha na conexão: " . $conn->connect_error);
}
echo "✅ Conectado com sucesso!";
$conn->close();
?>

