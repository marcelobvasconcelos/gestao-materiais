<?php
// Arquivo de configuração para produção
// Este arquivo contém as configurações específicas do ambiente de produção

// Configurações do banco de dados - PRODUÇÃO
define('DB_HOST', '172.24.1.50');
define('DB_USER', 'inventario');
define('DB_PASS', 'fA9-A@BLn_PiHsR0');
define('DB_NAME', 'gestao_materiais_terceirizados');

// Configurações da aplicação
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('APP_URL', 'http://172.24.1.50/gestao-materiais'); // Ajuste conforme necessário

// Configurações de sessão
ini_set('session.cookie_secure', 0); // 0 para desenvolvimento, 1 para produção HTTPS
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

// Timezone
date_default_timezone_set('America/Recife');

// Configurações de erro (produção)
if (!APP_DEBUG) {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
}
?>