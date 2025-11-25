<?php
// Script de Diagnóstico de Ambiente
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Diagnóstico de Ambiente</h1>";

echo "<h2>Variáveis de Servidor</h2>";
echo "<pre>";
echo "SERVER_ADDR: " . ($_SERVER['SERVER_ADDR'] ?? 'Não definido') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'Não definido') . "\n";
echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'Não definido') . "\n";
echo "</pre>";

require_once 'config.php';

echo "<h2>Configuração Detectada</h2>";
echo "<pre>";
echo "Ambiente: " . (defined('APP_ENV') ? APP_ENV : 'Não definido') . "\n";
echo "DB Host: " . (defined('DB_HOST') ? DB_HOST : 'Não definido') . "\n";
echo "DB User: " . (defined('DB_USER') ? DB_USER : 'Não definido') . "\n";
echo "</pre>";

echo "<h2>Teste de Conexão</h2>";
try {
    $conn = getDbConnection();
    echo "<div style='color:green'>✅ Conexão bem sucedida!</div>";
    $conn->close();
} catch (Exception $e) {
    echo "<div style='color:red'>❌ Erro de conexão: " . $e->getMessage() . "</div>";
}
?>
