<?php
<?php
// pagbank_helper.php — seleciona base_url do PagBank/PagSeguro testando resolução DNS

function get_pagbank_base_url(): string {
    // Preferência: variável de ambiente
    $env = getenv('PAGBANK_BASE_URI');
    $candidates = [];
    if ($env) $candidates[] = rtrim($env, '/');
    // Lista de fallback conhecida (ordem de preferência)
    $candidates[] = 'https://api.pagbank.com.br';
    $candidates[] = 'https://api.pagseguro.com.br';
    $candidates[] = 'https://api.pagseguro.com';

    foreach ($candidates as $url) {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) continue;

        // Teste simples com gethostbyname
        $resolved = @gethostbyname($host);
        if ($resolved && $resolved !== $host) {
            error_log("pagbank_helper: DNS resolvido para {$host} => {$resolved}. Usando {$url}");
            return $url;
        }

        // Tentativa alternativa com dns_get_record
        $records = @dns_get_record($host, DNS_A + DNS_AAAA);
        if (!empty($records)) {
            error_log("pagbank_helper: dns_get_record encontrou registro para {$host}. Usando {$url}");
            return $url;
        }
    }

    // Se nenhum host resolveu, escolhe o primeiro candidato (fallback) e loga
    error_log("pagbank_helper: nenhum host resolveu; usando fallback {$candidates[0]} (pode falhar)");
    return $candidates[0];
}
?>