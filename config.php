<?php
// Detectar ambiente
$is_production = ($_SERVER['SERVER_ADDR'] ?? '') === '172.24.1.50' || ($_SERVER['HTTP_HOST'] ?? '') === '172.24.1.50';

if ($is_production) {
    // Configurações do banco de dados - PRODUÇÃO
    // Usando localhost pois o PHP e o MySQL provavelmente estão no mesmo servidor.
    // Conectar via IP da rede (172.24.1.50) pode falhar se o MySQL não estiver configurado para aceitar conexões externas.
    define('DB_HOST', 'localhost');
    define('DB_USER', 'inventario');
    define('DB_PASS', 'fA9-A@BLn_PiHsR0');
    define('DB_NAME', 'gestao_materiais_terceirizados');
    
    // Configurações da aplicação
    define('APP_ENV', 'production');
    define('APP_DEBUG', false);
    define('APP_URL', 'localhost/gestao-materiais');
    
    // Configurações de erro (produção)
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
    
} else {
    // Configurações do banco de dados - AMBIENTE LOCAL
    define('DB_HOST', 'localhost');
    define('DB_USER', 'inventario');
    define('DB_PASS', 'fA9-A@BLn_PiHsR0');
    define('DB_NAME', 'gestao_materiais_terceirizados');
    
    // Configurações da aplicação
    define('APP_ENV', 'local');
    define('APP_DEBUG', true);
    define('APP_URL', 'http://localhost/gestao-materiais');
    
    // Configurações de erro (local)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Configurações comuns
date_default_timezone_set('America/Recife');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

// Função para obter conexão
function getDbConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception('Erro de conexão: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (Exception $e) {
        // Apenas logar e relançar a exceção para ser tratada pela API
        error_log('Erro de conexão DB: ' . $e->getMessage());
        throw $e;
    }
}
?>
