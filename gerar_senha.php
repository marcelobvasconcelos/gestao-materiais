<?php
// Script para gerar hash da senha e atualizar no banco

$servername = 'localhost';
$username = 'inventario';
$password = 'fA9-A@BLn_PiHsR0';
$database = 'gestao_materiais_terceirizados';

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Gerar hash para a senha admin123
$senha = 'admin123';
$hash = password_hash($senha, PASSWORD_DEFAULT);

echo "Hash gerado para 'admin123': " . $hash . "<br><br>";

// Atualizar no banco
$stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = 'admin@universidade.edu.br'");
$stmt->bind_param('s', $hash);

if ($stmt->execute()) {
    echo "Senha atualizada com sucesso no banco!<br>";
    echo "Agora você pode fazer login com:<br>";
    echo "Email: admin@universidade.edu.br<br>";
    echo "Senha: admin123<br>";
} else {
    echo "Erro ao atualizar senha: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>