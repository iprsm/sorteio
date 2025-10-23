<?php
// config.php

// === TOKEN DO PAGBANK ===
// Coloque o token da sua conta PagBank (sandbox ou produção)
define('PAGBANK_TOKEN', 'c0c7c542-0150-441f-80a3-afda883eb6fa490cd9da4e38a18feda184884d6fa2dd4773-a291-4cda-a84b-d63de60c739a');

// === AMBIENTE ===
// Use "sandbox" para testes ou "producao" para o site real
define('PAGBANK_ENV', 'sandbox'); // ou 'sandbox'

// === URL base conforme ambiente ===
if (PAGBANK_ENV === 'sandbox') {
    define('PAGBANK_API', 'https://sandbox.api.pagseguro.com');
} else {
    define('PAGBANK_API', 'https://api.pagseguro.com');
}
?>

