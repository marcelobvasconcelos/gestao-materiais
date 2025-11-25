<?php
require_once 'config.php';

echo "=== DEBUG USUÁRIOS E EMPRESAS ===\n";
$conn = getDbConnection();

$sql = "SELECT u.id, u.nome, u.email, p.nome as perfil, 
        GROUP_CONCAT(e.nome SEPARATOR ', ') as empresas_vinculadas
        FROM usuarios u
        LEFT JOIN perfis_acesso p ON u.perfil_id = p.id
        LEFT JOIN usuarios_empresas ue ON u.id = ue.usuario_id
        LEFT JOIN empresas_terceirizadas e ON ue.empresa_id = e.id
        GROUP BY u.id";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Nome: " . $row['nome'] . " | Perfil: " . $row['perfil'] . "\n";
        echo "Empresas: " . ($row['empresas_vinculadas'] ?: 'NENHUMA') . "\n";
        echo "--------------------------------------------------\n";
    }
} else {
    echo "Erro SQL: " . $conn->error . "\n";
}
?>
