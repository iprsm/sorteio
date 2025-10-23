<?php
// conexao.php
$servername = "iprsmrifa_db1.mysql.dbaas.com.br"; // Host da PagBank
$username = "iprsmrifa_db"; // Usuário do banco
$password = "RooT017803@"; // Senha do banco
$dbname = "iprsmrifa_db1"; // Nome do banco

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>

