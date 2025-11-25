<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Diagnóstico de Conexão MySQL</h2>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .success { color: green; } .error { color: red; } .warning { color: orange; }</style>";

// Configurações do banco
$servername = '172.24.1.50';
$username = 'inventario';
$password = 'fA9-A@BLn_PiHsR0';
$database = 'gestao_materiais_terceirizados';

echo "<h3>Configurações Atuais:</h3>";
echo "<ul>";
echo "<li><strong>Servidor:</strong> $servername</li>";
echo "<li><strong>Usuário:</strong> $username</li>";
echo "<li><strong>Banco:</strong> $database</li>";
echo "</ul>";

echo "<h3>Testes de Conectividade:</h3>";

// 1. Teste básico de conectividade TCP
echo "<h4>1. Teste de Conexão TCP (porta 3306):</h4>";
$connection = @fsockopen($servername, 3306, $errno, $errstr, 5);
if ($connection) {
    echo "<div class='success'>✅ Porta 3306 está aberta e acessível</div>";
    fclose($connection);
} else {
    echo "<div class='error'>❌ Porta 3306 não está acessível: $errstr ($errno)</div>";
    echo "<div class='warning'>💡 Possíveis causas:<br>";
    echo "- Servidor MySQL não está rodando<br>";
    echo "- Firewall bloqueando a porta 3306<br>";
    echo "- Endereço IP incorreto<br>";
    echo "- Rede não permite conexão</div>";
}

// 2. Teste de resolução DNS
echo "<h4>2. Teste de Resolução DNS:</h4>";
$ip = gethostbyname($servername);
if ($ip !== $servername) {
    echo "<div class='success'>✅ DNS resolvido: $servername → $ip</div>";
} else {
    echo "<div class='error'>❌ Falha na resolução DNS para $servername</div>";
}

// 3. Teste de conexão MySQL
echo "<h4>3. Teste de Conexão MySQL:</h4>";
try {
    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        $error_code = $conn->connect_errno;
        $error_msg = $conn->connect_error;

        echo "<div class='error'>❌ Erro de conexão MySQL: $error_msg (Código: $error_code)</div>";

        // Diagnóstico específico baseado no código de erro
        switch ($error_code) {
            case 2002:
                echo "<div class='warning'>💡 Código 2002: Servidor não encontrado ou porta incorreta</div>";
                break;
            case 2003:
                echo "<div class='warning'>💡 Código 2003: Servidor rejeitou a conexão</div>";
                break;
            case 1045:
                echo "<div class='warning'>💡 Código 1045: Acesso negado (usuário/senha incorretos)</div>";
                break;
            case 1049:
                echo "<div class='warning'>💡 Código 1049: Banco de dados não existe</div>";
                break;
            case 2054:
                echo "<div class='warning'>💡 Código 2054: Método de autenticação incompatível (MySQL 8.0+)</div>";
                break;
            default:
                echo "<div class='warning'>💡 Código $error_code: Consulte documentação MySQL</div>";
        }
    } else {
        echo "<div class='success'>✅ Conexão MySQL estabelecida com sucesso!</div>";

        // Testar banco de dados
        if ($conn->select_db($database)) {
            echo "<div class='success'>✅ Banco de dados '$database' selecionado</div>";

            // Listar tabelas
            $result = $conn->query("SHOW TABLES");
            if ($result) {
                $tables = [];
                while ($row = $result->fetch_array()) {
                    $tables[] = $row[0];
                }
                echo "<div class='success'>✅ Tabelas encontradas: " . count($tables) . "</div>";
                echo "<div><strong>Tabelas:</strong> " . implode(", ", $tables) . "</div>";
            }
        } else {
            echo "<div class='error'>❌ Banco de dados '$database' não encontrado</div>";
        }

        $conn->close();
    }

} catch (Exception $e) {
    echo "<div class='error'>❌ Exceção: " . $e->getMessage() . "</div>";
}

echo "<h3>Soluções Sugeridas:</h3>";
echo "<ol>";
echo "<li><strong>Verifique se o MySQL está rodando:</strong> No servidor, execute <code>sudo systemctl status mysql</code> ou <code>sudo service mysql status</code></li>";
echo "<li><strong>Verifique permissões do usuário:</strong> No MySQL, execute:<br><code>GRANT ALL PRIVILEGES ON gestao_materiais_terceirizados.* TO 'inventario'@'%' IDENTIFIED BY 'fA9-A@BLn_PiHsR0';</code><br><code>FLUSH PRIVILEGES;</code></li>";
echo "<li><strong>Verifique firewall:</strong> Certifique-se de que a porta 3306 está aberta no firewall do servidor</li>";
echo "<li><strong>Teste conexão local:</strong> No servidor MySQL, teste: <code>mysql -h localhost -u inventario -p gestao_materiais_terceirizados</code></li>";
echo "<li><strong>Verifique bind-address:</strong> No my.cnf, certifique-se de que <code>bind-address = 0.0.0.0</code> ou o IP específico</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>Data do diagnóstico:</strong> " . date('d/m/Y H:i:s') . "</p>";
?>