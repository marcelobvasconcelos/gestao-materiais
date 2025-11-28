<?php
// test_user_update.php
// Script para testar a atualização de usuários e vínculos de empresas

// Configurar ambiente para simular admin
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_perfil'] = 1; // Admin
$_SESSION['empresas_permitidas'] = 'ALL';

require_once 'config.php';

// Função auxiliar para fazer requisição simulada
function testarAPI($dados) {
    global $conn; // Usar conexão global
    
    // Capturar saída
    ob_start();
    
    // Simular input JSON
    // Nota: Como api_filtrada lê php://input, não podemos injetar facilmente via include direto
    // se ela não tiver uma verificação. Vamos modificar a abordagem para usar cURL local ou
    // apenas testar a lógica se extrairmos para uma função.
    // Como não podemos refatorar agora, vamos fazer um POST real para o localhost
    
    $url = 'http://localhost/gestao-materiais/api_filtrada.php?tipo=usuarios&acao=atualizar';
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n" .
                         "Cookie: PHPSESSID=" . session_id() . "\r\n",
            'method'  => 'POST',
            'content' => json_encode($dados)
        ]
    ];
    
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    ob_end_clean();
    return json_decode($result, true);
}

echo "=== INICIANDO TESTE DE ATUALIZAÇÃO DE USUÁRIO ===\n\n";

// 1. Criar usuário de teste via banco direto para garantir estado inicial
$emailTeste = 'teste_bug_' . time() . '@teste.com';
$stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, ativo) VALUES ('Usuario Teste', ?, 'hash', 2, 'TI', 1)");
$stmt->bind_param('s', $emailTeste);
$stmt->execute();
$idUsuario = $conn->insert_id;

echo "[1] Usuário de teste criado: ID $idUsuario ($emailTeste)\n";

// 2. Inserir alguns vínculos iniciais (Empresas 1 e 2)
$conn->query("INSERT INTO usuarios_empresas (usuario_id, empresa_id) VALUES ($idUsuario, 1)");
$conn->query("INSERT INTO usuarios_empresas (usuario_id, empresa_id) VALUES ($idUsuario, 2)");

echo "[2] Vínculos iniciais definidos: Empresas 1 e 2\n";

// 3. Preparar dados para atualização (Mudar para Empresas 2 e 3)
$dadosAtualizacao = [
    'id' => $idUsuario,
    'nome' => 'Usuario Teste Atualizado',
    'email' => $emailTeste,
    'perfil_id' => 2, // Gestor (importante ser > 1 para ter vínculos)
    'departamento' => 'TI Atualizado',
    'empresas_vinculadas' => [2, 3] // Novos vínculos
];

echo "[3] Enviando requisição de atualização...\n";
echo "    Dados: " . json_encode($dadosAtualizacao) . "\n";

// Executar teste via cURL para garantir isolamento
$ch = curl_init('http://localhost/gestao-materiais/api_filtrada.php?tipo=usuarios&acao=atualizar');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dadosAtualizacao));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Cookie: PHPSESSID=' . session_id() // Passar sessão admin
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "    Resposta HTTP: $httpCode\n";
echo "    Corpo: $response\n\n";

// 4. Verificar resultados no banco
echo "[4] Verificando persistência no banco de dados...\n";

// Verificar dados básicos
$resUser = $conn->query("SELECT * FROM usuarios WHERE id = $idUsuario")->fetch_assoc();
echo "    Nome atualizado: " . ($resUser['nome'] === 'Usuario Teste Atualizado' ? "OK" : "FALHA ({$resUser['nome']})") . "\n";

// Verificar vínculos
$resLinks = $conn->query("SELECT empresa_id FROM usuarios_empresas WHERE usuario_id = $idUsuario");
$empresasFinais = [];
while ($row = $resLinks->fetch_assoc()) {
    $empresasFinais[] = $row['empresa_id'];
}
sort($empresasFinais);

echo "    Vínculos esperados: [2, 3]\n";
echo "    Vínculos encontrados: " . json_encode($empresasFinais) . "\n";

if ($empresasFinais === [2, 3]) {
    echo "\n>>> SUCESSO: A atualização funcionou corretamente! <<<\n";
} else {
    echo "\n>>> FALHA: Os vínculos de empresa não foram atualizados corretamente! <<<\n";
}

// 5. Limpeza
$conn->query("DELETE FROM usuarios_empresas WHERE usuario_id = $idUsuario");
$conn->query("DELETE FROM usuarios WHERE id = $idUsuario");
echo "\n[5] Dados de teste limpos.\n";
?>
