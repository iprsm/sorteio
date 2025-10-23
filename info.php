<?php
header("Content-Type: text/html; charset=utf-8");

echo "<h2>🔍 Diagnóstico de Conexão PagBank - PHP</h2>";

$endpoint = "https://api.pagbank.com/charges";
$dominio  = "api.pagbank.com";

// ====== 1️⃣ Teste da extensão cURL ======
echo "<h3>1️⃣ Testando se cURL está ativo...</h3>";
if (function_exists('curl_version')) {
    echo "✅ cURL está habilitado no PHP.<br>";
} else {
    echo "❌ cURL NÃO está habilitado. Peça ao suporte para ativar.<br>";
    exit;
}

// ====== 2️⃣ Teste de resolução DNS ======
echo "<h3>2️⃣ Testando resolução de DNS ($dominio)...</h3>";
$ip = gethostbyname($dominio);
if ($ip === $dominio) {
    echo "❌ DNS não conseguiu resolver o domínio ($dominio).<br>";
    echo "👉 Isso indica que o servidor não tem acesso externo liberado.<br>";
    echo "➡️ Peça ao suporte da Locaweb para liberar o domínio <b>$dominio</b> para saída HTTPS (porta 443).<br>";
    exit;
} else {
    echo "✅ DNS resolvido com sucesso: $ip<br>";
}

// ====== 3️⃣ Teste de conexão HTTPS externa ======
echo "<h3>3️⃣ Testando conexão HTTPS com PagBank...</h3>";

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);

if ($response === false) {
    echo "❌ Erro ao tentar conectar: <b>" . curl_error($ch) . "</b><br>";
    echo "➡️ Isso normalmente significa que o servidor está bloqueando a saída HTTPS.<br>";
    echo "Peça a liberação de saída cURL para <b>$dominio</b> no suporte da Locaweb.<br>";
    curl_close($ch);
    exit;
} else {
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "✅ Conexão estabelecida. Código HTTP retornado: <b>$http</b><br>";
    if ($http == 401) {
        echo "ℹ️ 401 indica que o domínio está acessível, mas o token não foi informado (ou é inválido).<br>";
    }
}
curl_close($ch);

// ====== 4️⃣ Teste opcional do token PagBank (se existir config.php) ======
if (file_exists('config.php')) {
    include 'config.php';
    if (!empty($TOKEN)) {
        echo "<h3>4️⃣ Testando autenticação com token do PagBank...</h3>";

        $dados = [
            "reference_id" => uniqid("teste_"),
            "description" => "Teste de autenticação PagBank",
            "amount" => ["value" => 100, "currency" => "BRL"],
            "payment_method" => ["type" => "PIX"]
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $TOKEN",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            echo "❌ Erro ao enviar requisição autenticada: " . curl_error($ch) . "<br>";
        } else {
            echo "✅ Requisição autenticada enviada. HTTP: <b>$http</b><br>";
            echo "<details><summary>Ver resposta bruta</summary><pre>" . htmlspecialchars($response) . "</pre></details>";
        }
    } else {
        echo "<h3>4️⃣ Teste de token ignorado:</h3> variável <b>\$TOKEN</b> não encontrada no config.php.<br>";
    }
} else {
    echo "<h3>4️⃣ Teste de token ignorado:</h3> arquivo <b>config.php</b> não encontrado.<br>";
}

echo "<hr><b>✅ Diagnóstico concluído.</b><br>";
echo "Se o item 3 ou 4 falhar com erro de DNS ou SSL, solicite a liberação de saída HTTPS na Locaweb para o domínio <b>$dominio</b>.";
?>

