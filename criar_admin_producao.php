<?php
$conn = new mysqli('172.24.1.50', 'inventario', 'fA9-A@BLn_PiHsR0', 'gestao_materiais_terceirizados');
if ($conn->connect_error) {
    die('Erro de conexão: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

// Inserir perfis se não existirem
$conn->query("INSERT IGNORE INTO perfis_acesso (id, nome, descricao, ativo) VALUES
(1, 'Administrador', 'Acesso total ao sistema', 1),
(2, 'Gestor', 'Gerenciamento operacional', 1),
(3, 'Operador', 'Operações básicas', 1),
(4, 'Consulta', 'Apenas visualização', 1)");

// Inserir usuário administrador
$senha_hash = password_hash('123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO usuarios (id, nome, email, senha, perfil_id, ativo) VALUES
(1, 'Marcelo', 'adm.ti.uast@ufrpe.br', '$senha_hash', 1, 1)");

echo "Usuário administrador criado com sucesso!";
echo "<br>Email: adm.ti.uast@ufrpe.br";
echo "<br>Senha: 123";
echo "<br><br><strong>IMPORTANTE:</strong> Altere a senha após o primeiro login.";

$conn->close();
?>