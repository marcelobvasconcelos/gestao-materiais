(1, 'Limpeza', 'Produtos de limpeza e higiene', 1),
(2, 'Ferramentas', 'Ferramentas e equipamentos', 1),
(3, 'Equipamentos', 'Equipamentos diversos', 1),
(4, 'Escritório', 'Material de escritório', 1),
(5, 'Manutenção', 'Materiais para manutenção', 1)");

// Inserir unidades se não existirem
$conn->query("INSERT IGNORE INTO unidades_medida (id, descricao, simbolo) VALUES
(1, 'Unidade', 'un'),
(2, 'Litro', 'L'),
(3, 'Quilograma', 'kg'),
(4, 'Caixa', 'cx'),
(5, 'Pacote', 'pct')");

// Inserir locais se não existirem
$conn->query("INSERT IGNORE INTO locais_armazenamento (id, nome, descricao, ativo) VALUES
(1, 'Almoxarifado Central', 'Depósito principal', 1),
(2, 'Almoxarifado Limpeza', 'Produtos de limpeza', 1),
(3, 'Almoxarifado Manutenção', 'Materiais de manutenção', 1)");

// Inserir empresa de teste
$conn->query("INSERT IGNORE INTO empresas_terceirizadas (id, nome, tipo_servico, numero_contrato, status, responsavel_id) VALUES
(1, 'Empresa Teste', 'Limpeza', 'CT-2024-001', 'Ativa', 1)");

// Inserir usuário admin se não existir
$senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO usuarios (id, nome, email, senha, perfil_id, ativo) VALUES
(1, 'Administrador', 'admin@universidade.edu.br', '$senha_hash', 1, 1)");

// Inserir perfil admin se não existir
$conn->query("INSERT IGNORE INTO perfis_acesso (id, nome, descricao, ativo) VALUES
(1, 'Administrador', 'Acesso total ao sistema', 1),
(2, 'Gestor', 'Gerenciamento operacional', 1),
(3, 'Operador', 'Operações básicas', 1),
(4, 'Consulta', 'Apenas visualização', 1)");

// Inserir usuário admin se não existir
$senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO usuarios (id, nome, email, senha, perfil_id, ativo) VALUES
(1, 'Administrador', 'admin@universidade.edu.br', '$senha_hash', 1, 1)");

// Inserir usuário gestor
$senha_hash = password_hash('gestor123', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO usuarios (id, nome, email, senha, perfil_id, ativo) VALUES
(2, 'Gestor Silva', 'gestor@universidade.edu.br', '$senha_hash', 2, 1)");

// Vincular gestor à empresa
$conn->query("INSERT IGNORE INTO usuarios_empresas (usuario_id, empresa_id) VALUES (2, 1)");

// Vincular admin à empresa
$conn->query("INSERT IGNORE INTO usuarios_empresas (usuario_id, empresa_id) VALUES (1, 1)");

// Inserir material de teste
$conn->query("INSERT IGNORE INTO materiais (id, nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ponto_reposicao, estoque_maximo, ativo) VALUES
(1, 'Sabão em pó', 'LIM001EMP', 1, 1, 1, 1, 10.00, 5.00, 50.00, 1)");

echo "Dados básicos inseridos com sucesso!";
$conn->close();
?>