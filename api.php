<?php
// Limpar qualquer output anterior
ob_clean();

// Configuração de CORS e JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Desabilitar exibição de erros para não quebrar JSON
ini_set('display_errors', 0);
error_reporting(0);

// Configurações do banco de dados
$servername = 'localhost';
$username = 'inventario';
$password = 'fA9-A@BLn_PiHsR0'; // Padrão do XAMPP é vazio
$database = 'gestao_materiais_terceirizados';

// Criar conexão com timeout
$conn = new mysqli($servername, $username, $password, $database);
$conn->set_charset('utf8mb4');

// Configurar para resposta rápida
if (!$conn->connect_error) {
    $conn->query("SET SESSION sql_mode = ''");
}

// Verificar conexão
if ($conn->connect_error) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro de conexão']);
    exit;
}

// Obter ação solicitada
$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Obter dados POST
$dados = json_decode(file_get_contents('php://input'), true);

// ============================================================================
// FUNÇÕES DE RETORNO
// ============================================================================
function resposta_sucesso($mensagem, $dados = null) {
    return json_encode([
        'sucesso' => true,
        'mensagem' => $mensagem,
        'dados' => $dados
    ]);
}

function resposta_erro($mensagem) {
    return json_encode([
        'sucesso' => false,
        'erro' => $mensagem
    ]);
}

// ============================================================================
// ROTAS DA API
// ============================================================================

// TESTE DE CONEXÃO
if ($acao === 'teste' || ($tipo === '' && $acao === '')) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'API OK']);
    exit;
}

// LOCAIS DE ARMAZENAMENTO
if ($tipo === 'locais') {
    if ($acao === 'listar') {
        $result = $conn->query('SELECT * FROM locais_armazenamento WHERE ativo = 1 ORDER BY nome');
        if ($result) {
            $locais = $result->fetch_all(MYSQLI_ASSOC);
            echo resposta_sucesso('Locais carregados', $locais);
        } else {
            echo resposta_erro('Erro ao consultar locais: ' . $conn->error);
        }
        exit;
    }
}

// USUÁRIOS
if ($tipo === 'usuarios') {
    if ($acao === 'listar') {
        $result = $conn->query('SELECT * FROM usuarios WHERE ativo = 1 ORDER BY nome');
        $usuarios = $result->fetch_all(MYSQLI_ASSOC);
        echo resposta_sucesso('Usuários carregados', $usuarios);
    }
    elseif ($acao === 'listar_completo') {
        $query = 'SELECT u.*, p.nome as perfil_nome 
                  FROM usuarios u 
                  LEFT JOIN perfis_acesso p ON u.perfil_id = p.id 
                  ORDER BY u.nome';
        $result = $conn->query($query);
        $usuarios = $result->fetch_all(MYSQLI_ASSOC);
        echo resposta_sucesso('Usuários carregados', $usuarios);
    }
    elseif ($acao === 'criar') {
        // Verificar se email já existe
        $stmt_check = $conn->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt_check->bind_param('s', $dados['email']);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($result->num_rows > 0) {
            echo resposta_erro('Email já cadastrado no sistema');
            $stmt_check->close();
            exit;
        }
        $stmt_check->close();
        
        // Criptografar senha
        $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare('INSERT INTO usuarios (nome, email, senha, perfil_id, departamento) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssis', $dados['nome'], $dados['email'], $senha_hash, $dados['perfil_id'], $dados['departamento']);
        
        if ($stmt->execute()) {
            echo resposta_sucesso('Usuário cadastrado com sucesso', ['id' => $conn->insert_id]);
        } else {
            echo resposta_erro('Erro ao cadastrar usuário: ' . $stmt->error);
        }
        $stmt->close();
    }
    elseif ($acao === 'toggle_status') {
        $stmt = $conn->prepare('UPDATE usuarios SET ativo = ? WHERE id = ?');
        $stmt->bind_param('ii', $dados['ativo'], $dados['id']);
        
        if ($stmt->execute()) {
            $status = $dados['ativo'] == 1 ? 'ativado' : 'desativado';
            echo resposta_sucesso("Usuário {$status} com sucesso");
        } else {
            echo resposta_erro('Erro ao alterar status: ' . $stmt->error);
        }
        $stmt->close();
    }
    elseif ($acao === 'atualizar') {
        $stmt = $conn->prepare('UPDATE usuarios SET nome=?, email=?, perfil_id=?, departamento=? WHERE id=?');
        $stmt->bind_param('ssisi', $dados['nome'], $dados['email'], $dados['perfil_id'], $dados['departamento'], $dados['id']);
        
        if ($stmt->execute()) {
            echo resposta_sucesso('Usuário atualizado com sucesso');
        } else {
            echo resposta_erro('Erro ao atualizar usuário: ' . $stmt->error);
        }
        $stmt->close();
    }
}



// PERFIS DE ACESSO
if ($tipo === 'perfis') {
    if ($acao === 'listar') {
        $result = $conn->query('SELECT * FROM perfis_acesso ORDER BY id');
        $perfis = $result->fetch_all(MYSQLI_ASSOC);
        echo resposta_sucesso('Perfis carregados', $perfis);
    }
}

// EMPRESAS TERCEIRIZADAS
if ($tipo === 'empresas') {
    if ($acao === 'listar') {
        $result = $conn->query('SELECT id, nome, tipo_servico, numero_contrato FROM empresas_terceirizadas WHERE status = "Ativa" LIMIT 50');
        if ($result) {
            $empresas = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $empresas]);
        } else {
            echo json_encode(['sucesso' => true, 'dados' => []]);
        }
        exit;
    } 
    elseif ($acao === 'criar') {
        try {
            $stmt = $conn->prepare('INSERT INTO empresas_terceirizadas (nome, tipo_servico, numero_contrato, cnpj, responsavel_id, telefone, email) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssiss', $dados['nome'], $dados['tipo_servico'], $dados['numero_contrato'], $dados['cnpj'], $dados['responsavel_id'], $dados['telefone'], $dados['email']);
            
            if ($stmt->execute()) {
                echo json_encode(['sucesso' => true, 'mensagem' => 'Empresa cadastrada']);
            } else {
                echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar']);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro interno']);
        }
        exit;
    }
    elseif ($acao === 'atualizar') {
        $stmt = $conn->prepare('UPDATE empresas_terceirizadas SET nome=?, tipo_servico=?, numero_contrato=?, cnpj=?, responsavel_id=?, telefone=?, email=? WHERE id=?');
        $stmt->bind_param('ssssissi', $dados['nome'], $dados['tipo_servico'], $dados['numero_contrato'], $dados['cnpj'], $dados['responsavel_id'], $dados['telefone'], $dados['email'], $dados['id']);
        
        if ($stmt->execute()) {
            echo resposta_sucesso('Empresa atualizada com sucesso');
        } else {
            echo resposta_erro('Erro ao atualizar empresa: ' . $stmt->error);
        }
        $stmt->close();
    }
    elseif ($acao === 'deletar') {
        $stmt = $conn->prepare('UPDATE empresas_terceirizadas SET status="Inativa" WHERE id=?');
        $stmt->bind_param('i', $dados['id']);
        
        if ($stmt->execute()) {
            echo resposta_sucesso('Empresa deletada com sucesso');
        } else {
            echo resposta_erro('Erro ao deletar empresa: ' . $stmt->error);
        }
        $stmt->close();
    }
}

// MATERIAIS
if ($tipo === 'materiais') {
    if ($acao === 'listar') {
        $result = $conn->query('SELECT id, nome, codigo_sku, estoque_atual FROM materiais WHERE ativo = 1 LIMIT 50');
        if ($result) {
            $materiais = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $materiais]);
        } else {
            echo json_encode(['sucesso' => true, 'dados' => []]);
        }
        exit;
    }
    elseif ($acao === 'criar') {
        try {
            $stmt = $conn->prepare('INSERT INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, estoque_atual, ponto_reposicao, estoque_maximo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssiiiiddd', $dados['nome'], $dados['codigo_sku'], $dados['categoria_id'], $dados['unidade_medida_id'], $dados['empresa_id'], $dados['local_id'], $dados['estoque_atual'], $dados['ponto_reposicao'], $dados['estoque_maximo']);
            
            if ($stmt->execute()) {
                echo json_encode(['sucesso' => true, 'mensagem' => 'Material cadastrado']);
            } else {
                echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar']);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro interno']);
        }
        exit;
    }
}

// MOVIMENTAÇÕES ENTRADA
if ($tipo === 'entrada') {
    if ($acao === 'listar') {
        $query = 'SELECT me.*, m.nome as material_nome, u.nome as responsavel_nome
                  FROM movimentacoes_entrada me
                  LEFT JOIN materiais m ON me.material_id = m.id
                  LEFT JOIN usuarios u ON me.responsavel_id = u.id
                  ORDER BY me.data_entrada DESC LIMIT 100';
        $result = $conn->query($query);
        $entradas = $result->fetch_all(MYSQLI_ASSOC);
        echo resposta_sucesso('Entradas carregadas', $entradas);
    }
    elseif ($acao === 'criar') {
        // Inserir entrada
        $stmt = $conn->prepare('INSERT INTO movimentacoes_entrada (data_entrada, material_id, quantidade, nota_fiscal, responsavel_id, local_destino_id, observacao) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $data_entrada = $dados['data_entrada'] . ' ' . date('H:i:s');
        $stmt->bind_param('sidisii', $data_entrada, $dados['material_id'], $dados['quantidade'], $dados['nota_fiscal'], $dados['responsavel_id'], $dados['local_destino_id'], $dados['observacao']);
        
        if ($stmt->execute()) {
            // Atualizar estoque do material
            $stmt2 = $conn->prepare('UPDATE materiais SET estoque_atual = estoque_atual + ? WHERE id = ?');
            $stmt2->bind_param('di', $dados['quantidade'], $dados['material_id']);
            $stmt2->execute();
            
            echo resposta_sucesso('Entrada registrada e estoque atualizado com sucesso', ['id' => $conn->insert_id]);
        } else {
            echo resposta_erro('Erro ao registrar entrada: ' . $stmt->error);
        }
        $stmt->close();
    }
}

// MOVIMENTAÇÕES SAÍDA
if ($tipo === 'saida') {
    if ($acao === 'listar') {
        $query = 'SELECT ms.*, m.nome as material_nome, u.nome as responsavel_nome
                  FROM movimentacoes_saida ms
                  LEFT JOIN materiais m ON ms.material_id = m.id
                  LEFT JOIN usuarios u ON ms.responsavel_autorizacao_id = u.id
                  ORDER BY ms.data_saida DESC LIMIT 100';
        $result = $conn->query($query);
        $saidas = $result->fetch_all(MYSQLI_ASSOC);
        echo resposta_sucesso('Saídas carregadas', $saidas);
    }
    elseif ($acao === 'criar') {
        // Verificar estoque disponível
        $stmt_check = $conn->prepare('SELECT estoque_atual FROM materiais WHERE id = ?');
        $stmt_check->bind_param('i', $dados['material_id']);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $material = $result->fetch_assoc();
        
        if ($material['estoque_atual'] < $dados['quantidade']) {
            echo resposta_erro('Estoque insuficiente. Disponível: ' . $material['estoque_atual']);
            $stmt_check->close();
            exit;
        }
        $stmt_check->close();
        
        // Inserir saída
        $stmt = $conn->prepare('INSERT INTO movimentacoes_saida (data_saida, material_id, quantidade, empresa_solicitante_id, finalidade, responsavel_autorizacao_id, local_destino, observacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $data_saida = $dados['data_saida'] . ' ' . date('H:i:s');
        $stmt->bind_param('sidisss', $data_saida, $dados['material_id'], $dados['quantidade'], $dados['empresa_solicitante_id'], $dados['finalidade'], $dados['responsavel_autorizacao_id'], $dados['local_destino'], $dados['observacao']);
        
        if ($stmt->execute()) {
            // Atualizar estoque do material
            $stmt2 = $conn->prepare('UPDATE materiais SET estoque_atual = estoque_atual - ? WHERE id = ?');
            $stmt2->bind_param('di', $dados['quantidade'], $dados['material_id']);
            $stmt2->execute();
            
            echo resposta_sucesso('Saída registrada e estoque atualizado com sucesso', ['id' => $conn->insert_id]);
        } else {
            echo resposta_erro('Erro ao registrar saída: ' . $stmt->error);
        }
        $stmt->close();
    }
}

// RELATÓRIOS
if ($tipo === 'relatorios') {
    if ($acao === 'resumo_geral') {
        // Total de empresas
        $result1 = $conn->query('SELECT COUNT(*) as total FROM empresas_terceirizadas WHERE status="Ativa"');
        $empresas_total = $result1->fetch_assoc()['total'];
        
        // Total de materiais
        $result2 = $conn->query('SELECT COUNT(*) as total FROM materiais WHERE ativo=1');
        $materiais_total = $result2->fetch_assoc()['total'];
        
        // Materiais com estoque baixo
        $result3 = $conn->query('SELECT COUNT(*) as total FROM materiais WHERE ativo=1 AND estoque_atual < ponto_reposicao');
        $estoque_baixo = $result3->fetch_assoc()['total'];
        
        // Valor total em estoque
        $result4 = $conn->query('SELECT SUM(estoque_atual * valor_unitario) as total FROM materiais WHERE ativo=1 AND valor_unitario IS NOT NULL');
        $valor_total = $result4->fetch_assoc()['total'] ?? 0;
        
        $resumo = [
            'total_empresas' => $empresas_total,
            'total_materiais' => $materiais_total,
            'estoque_baixo' => $estoque_baixo,
            'valor_total_estoque' => number_format($valor_total, 2, ',', '.')
        ];
        
        echo resposta_sucesso('Resumo carregado', $resumo);
    }
    elseif ($acao === 'estoque_baixo') {
        $query = 'SELECT m.*, e.nome as empresa_nome, l.nome as local_nome,
                        ROUND((m.estoque_atual / m.ponto_reposicao) * 100, 1) as percentual_ponto
                 FROM materiais m
                 LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
                 LEFT JOIN locais_armazenamento l ON m.local_id = l.id
                 WHERE m.ativo=1 AND m.estoque_atual < m.ponto_reposicao
                 ORDER BY m.estoque_atual ASC';
        $result = $conn->query($query);
        $alertas = $result->fetch_all(MYSQLI_ASSOC);
        echo resposta_sucesso('Alertas carregados', $alertas);
    }
    elseif ($acao === 'estoque_por_empresa') {
        $query = 'SELECT e.id, e.nome, SUM(m.estoque_atual) as total_estoque, COUNT(m.id) as quantidade_materiais
                 FROM empresas_terceirizadas e
                 LEFT JOIN materiais m ON e.id = m.empresa_id AND m.ativo=1
                 WHERE e.status="Ativa"
                 GROUP BY e.id, e.nome
                 ORDER BY e.nome';
        $result = $conn->query($query);
        $dados_relatorio = $result->fetch_all(MYSQLI_ASSOC);
        echo resposta_sucesso('Relatório carregado', $dados_relatorio);
    }
}

// AUTENTICAÇÃO
if ($tipo === 'auth') {
    if ($acao === 'login') {
        $email = $dados['email'];
        $senha = $dados['senha'];
        
        $stmt = $conn->prepare('SELECT u.*, p.nome as perfil_nome, p.permissoes FROM usuarios u LEFT JOIN perfis_acesso p ON u.perfil_id = p.id WHERE u.email = ? AND u.ativo = 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo resposta_erro('Email não encontrado ou usuário inativo');
            $stmt->close();
            exit;
        }
        
        $usuario = $result->fetch_assoc();
        
        if (password_verify($senha, $usuario['senha'])) {
            // Atualizar último acesso
            $stmt_update = $conn->prepare('UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?');
            $stmt_update->bind_param('i', $usuario['id']);
            $stmt_update->execute();
            $stmt_update->close();
            
            // Remover senha dos dados retornados
            unset($usuario['senha']);
            
            echo resposta_sucesso('Login realizado com sucesso', $usuario);
        } else {
            echo resposta_erro('Senha incorreta');
        }
        
        $stmt->close();
        exit;
    }
}

// Fechar conexão
$conn->close();

// Se nenhuma ação foi reconhecida
echo json_encode(['sucesso' => false, 'erro' => 'Ação não encontrada', 'debug' => ['tipo' => $tipo, 'acao' => $acao, 'get_params' => $_GET]]);
exit;
?>
```
