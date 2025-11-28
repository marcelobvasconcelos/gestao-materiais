<?php
ob_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

session_start();

$tipo = $_GET['tipo'] ?? '';
$acao = $_GET['acao'] ?? '';
$dados = json_decode(file_get_contents('php://input'), true);

try {
    $conn = getDbConnection();
} catch (Exception $e) {
    ob_clean();
    // DEBUG: Exibindo erro detalhado para identificar o problema em produção
    $erro_msg = 'Erro de conexão: ' . $e->getMessage();
    if (defined('DB_HOST')) {
        $erro_msg .= ' | Tentando conectar em: ' . DB_HOST;
    }
    echo json_encode(['sucesso' => false, 'erro' => $erro_msg]);
    exit;
}

ob_clean();

// TESTE
if ($tipo === 'teste' || ($tipo === '' && $acao === '')) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'API OK']);
    exit;
}

// Função para aplicar filtro de empresa
function aplicarFiltroEmpresa($query, $alias = '', $coluna = 'empresa_id') {
    if (!isset($_SESSION['empresas_permitidas']) || $_SESSION['empresas_permitidas'] === 'ALL') {
        return $query;
    }
    
    if (empty($_SESSION['empresas_permitidas'])) {
        return $query . " AND 1=0";
    }
    
    $empresas_str = implode(',', array_map('intval', $_SESSION['empresas_permitidas']));
    $campo_empresa = $alias ? $alias . '.' . $coluna : $coluna;
    
    return $query . " AND $campo_empresa IN ($empresas_str)";
}

// Função auxiliar para calcular estoque em um local específico
function calcularEstoqueLocal($conn, $material_id, $local_id) {
    // Somar entradas neste local
    $stmt = $conn->prepare("SELECT SUM(quantidade) as total FROM movimentacoes_entrada WHERE material_id = ? AND local_destino_id = ?");
    $stmt->bind_param("ii", $material_id, $local_id);
    $stmt->execute();
    $entradas = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

    // Somar saídas deste local
    $stmt = $conn->prepare("SELECT SUM(quantidade) as total FROM movimentacoes_saida WHERE material_id = ? AND local_origem_id = ?");
    $stmt->bind_param("ii", $material_id, $local_id);
    $stmt->execute();
    $saidas = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

    return $entradas - $saidas;
}

// DASHBOARD
if ($tipo === 'dashboard') {
    if ($acao === 'stats') {
        $stats = [];
        
        // 1. CARDS DATA
        // Total Empresas (Admin only)
        if (!isset($_SESSION['empresas_permitidas']) || $_SESSION['empresas_permitidas'] === 'ALL') {
            $stats['total_empresas'] = $conn->query('SELECT COUNT(*) as total FROM empresas_terceirizadas WHERE status = "Ativa"')->fetch_assoc()['total'];
        }
        
        // Total Materiais (Scoped)
        $query_mat = 'SELECT COUNT(*) as total FROM materiais WHERE ativo = 1';
        $query_mat = aplicarFiltroEmpresa($query_mat);
        $stats['total_materiais'] = $conn->query($query_mat)->fetch_assoc()['total'];
        
        // Estoque Baixo (Scoped)
        $query_baixo = 'SELECT COUNT(*) as total FROM materiais WHERE ativo = 1 AND estoque_atual < ponto_reposicao';
        $query_baixo = aplicarFiltroEmpresa($query_baixo);
        $stats['estoque_baixo'] = $conn->query($query_baixo)->fetch_assoc()['total'];
        
        // Valor Total em Estoque (Scoped)
        $query_valor = 'SELECT SUM(estoque_atual * valor_unitario) as total FROM materiais WHERE ativo = 1';
        $query_valor = aplicarFiltroEmpresa($query_valor);
        $stats['valor_total_estoque'] = $conn->query($query_valor)->fetch_assoc()['total'] ?? 0;
        
        // Total Itens Disponíveis (Scoped - para Operador)
        $query_itens = 'SELECT SUM(estoque_atual) as total FROM materiais WHERE ativo = 1';
        $query_itens = aplicarFiltroEmpresa($query_itens);
        $stats['total_itens'] = $conn->query($query_itens)->fetch_assoc()['total'] ?? 0;

        // Total Movimentações (Últimos 30 dias - Scoped)
        $data_inicio = date('Y-m-d', strtotime("-30 days"));
        
        // Entradas
        $query_ent = "SELECT COUNT(*) as total FROM movimentacoes_entrada me 
                      JOIN materiais m ON me.material_id = m.id 
                      WHERE me.data_entrada >= '$data_inicio'";
        $query_ent = aplicarFiltroEmpresa($query_ent, 'm');
        $total_entradas = $conn->query($query_ent)->fetch_assoc()['total'];
        
        // Saídas
        $query_sai = "SELECT COUNT(*) as total FROM movimentacoes_saida ms 
                      JOIN materiais m ON ms.material_id = m.id 
                      WHERE ms.data_saida >= '$data_inicio'";
        $query_sai = aplicarFiltroEmpresa($query_sai, 'm');
        $total_saidas = $conn->query($query_sai)->fetch_assoc()['total'];
        
        $stats['total_movimentacoes'] = $total_entradas + $total_saidas;

        // 2. CHARTS DATA
        
        // Chart: Trend (Entrada vs Saída - Últimos 30 dias)
        $trend_data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $trend_data[$date] = ['entrada' => 0, 'saida' => 0];
        }
        
        // Dados de Entrada diária
        $query_trend_ent = "SELECT DATE(me.data_entrada) as data, SUM(me.quantidade) as qtd 
                            FROM movimentacoes_entrada me 
                            JOIN materiais m ON me.material_id = m.id 
                            WHERE me.data_entrada >= '$data_inicio'";
        $query_trend_ent = aplicarFiltroEmpresa($query_trend_ent, 'm');
        $query_trend_ent .= " GROUP BY DATE(me.data_entrada)";
        $res_ent = $conn->query($query_trend_ent);
        while ($row = $res_ent->fetch_assoc()) {
            if (isset($trend_data[$row['data']])) {
                $trend_data[$row['data']]['entrada'] = (int)$row['qtd'];
            }
        }
        
        // Dados de Saída diária
        $query_trend_sai = "SELECT DATE(ms.data_saida) as data, SUM(ms.quantidade) as qtd 
                            FROM movimentacoes_saida ms 
                            JOIN materiais m ON ms.material_id = m.id 
                            WHERE ms.data_saida >= '$data_inicio'";
        $query_trend_sai = aplicarFiltroEmpresa($query_trend_sai, 'm');
        $query_trend_sai .= " GROUP BY DATE(ms.data_saida)";
        $res_sai = $conn->query($query_trend_sai);
        while ($row = $res_sai->fetch_assoc()) {
            if (isset($trend_data[$row['data']])) {
                $trend_data[$row['data']]['saida'] = (int)$row['qtd'];
            }
        }
        
        $stats['charts']['trend'] = [
            'labels' => array_keys($trend_data),
            'entradas' => array_column($trend_data, 'entrada'),
            'saidas' => array_column($trend_data, 'saida')
        ];
        
        // Chart: Stock Composition (Por Categoria)
        $query_comp = "SELECT c.nome as categoria, COUNT(m.id) as qtd 
                       FROM materiais m 
                       JOIN categorias_materiais c ON m.categoria_id = c.id 
                       WHERE m.ativo = 1";
        $query_comp = aplicarFiltroEmpresa($query_comp, 'm');
        $query_comp .= " GROUP BY c.id";
        $res_comp = $conn->query($query_comp);
        $stats['charts']['composition'] = $res_comp->fetch_all(MYSQLI_ASSOC);
        
        // Chart: Top 5 Materials (Saídas - Últimos 30 dias)
        $query_top = "SELECT m.nome, SUM(ms.quantidade) as total_saida 
                      FROM movimentacoes_saida ms 
                      JOIN materiais m ON ms.material_id = m.id 
                      WHERE ms.data_saida >= '$data_inicio'";
        $query_top = aplicarFiltroEmpresa($query_top, 'm');
        $query_top .= " GROUP BY m.id ORDER BY total_saida DESC LIMIT 5";
        $res_top = $conn->query($query_top);
        $stats['charts']['top5'] = $res_top->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['sucesso' => true, 'dados' => $stats]);
        exit;
    }
}

// RELATÓRIOS
if ($tipo === 'relatorios') {
    // 1. Resumo Geral (Dashboard)
    if ($acao === 'resumo_geral') {
        $total_empresas = $conn->query('SELECT COUNT(*) as total FROM empresas_terceirizadas WHERE status = "Ativa"')->fetch_assoc()['total'];
        $total_materiais = $conn->query('SELECT COUNT(*) as total FROM materiais WHERE ativo = 1')->fetch_assoc()['total'];
        $estoque_baixo = $conn->query('SELECT COUNT(*) as total FROM materiais WHERE ativo = 1 AND estoque_atual < ponto_reposicao')->fetch_assoc()['total'];
        
        // Calcular valor total do estoque
        $valor_total = $conn->query('SELECT SUM(estoque_atual * valor_unitario) as total FROM materiais WHERE ativo = 1')->fetch_assoc()['total'];
        
        echo json_encode([
            'sucesso' => true,
            'dados' => [
                'total_empresas' => $total_empresas,
                'total_materiais' => $total_materiais,
                'estoque_baixo' => $estoque_baixo,
                'valor_total_estoque' => 'R$ ' . number_format($valor_total ?? 0, 2, ',', '.')
            ]
        ]);
        exit;
    }

    // 2. Estoque por Empresa
    if ($acao === 'estoque_por_empresa') {
        $query = 'SELECT e.nome, 
                         COUNT(m.id) as total_materiais, 
                         SUM(m.estoque_atual) as total_estoque,
                         SUM(m.estoque_atual * m.valor_unitario) as valor_total
                  FROM empresas_terceirizadas e
                  LEFT JOIN materiais m ON e.id = m.empresa_id AND m.ativo = 1
                  WHERE e.status = "Ativa"';
        
        $query = aplicarFiltroEmpresa($query, 'e', 'id');
        $query .= ' GROUP BY e.id ORDER BY e.nome';
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }

    // 3. Movimentações (Entradas e Saídas)
    if ($acao === 'movimentacoes') {
        $periodo = $_GET['periodo'] ?? 30; // Dias
        $tipo_mov = $_GET['tipo_mov'] ?? 'todos';
        $empresa_id = $_GET['empresa_id'] ?? '';
        
        $data_inicio = date('Y-m-d', strtotime("-$periodo days"));
        
        $dados = [];
        
        // Entradas
        if ($tipo_mov === 'todos' || $tipo_mov === 'entrada') {
            $query = "SELECT 'Entrada' as tipo, me.data_entrada as data, m.nome as material, e.nome as empresa, me.quantidade, u.nome as responsavel
                      FROM movimentacoes_entrada me
                      JOIN materiais m ON me.material_id = m.id
                      LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
                      LEFT JOIN usuarios u ON me.responsavel_id = u.id
                      WHERE me.data_entrada >= '$data_inicio'";
            
            if ($empresa_id) $query .= " AND m.empresa_id = " . intval($empresa_id);
            $query = aplicarFiltroEmpresa($query, 'm');
            
            $result = $conn->query($query);
            if ($result) $dados = array_merge($dados, $result->fetch_all(MYSQLI_ASSOC));
        }
        
        // Saídas
        if ($tipo_mov === 'todos' || $tipo_mov === 'saida') {
            $query = "SELECT 'Saída' as tipo, ms.data_saida as data, m.nome as material, e.nome as empresa, ms.quantidade, '-' as responsavel
                      FROM movimentacoes_saida ms
                      JOIN materiais m ON ms.material_id = m.id
                      LEFT JOIN empresas_terceirizadas e ON ms.empresa_solicitante_id = e.id
                      WHERE ms.data_saida >= '$data_inicio'";
            
            if ($empresa_id) $query .= " AND ms.empresa_solicitante_id = " . intval($empresa_id);
            // Nota: Saída pode ser vista por quem tem acesso à empresa solicitante OU dona do material? 
            // Simplificação: filtro pela empresa dona do material (m.empresa_id)
            $query = aplicarFiltroEmpresa($query, 'm'); 
            
            $result = $conn->query($query);
            if ($result) $dados = array_merge($dados, $result->fetch_all(MYSQLI_ASSOC));
        }
        
        // Ordenar por data decrescente
        usort($dados, function($a, $b) {
            return strtotime($b['data']) - strtotime($a['data']);
        });
        
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }

    // 4. Consumo por Empresa (Saídas)
    if ($acao === 'consumo_por_empresa') {
        $query = "SELECT e.nome as empresa, COUNT(ms.id) as total_saidas, SUM(ms.quantidade) as total_itens
                  FROM movimentacoes_saida ms
                  JOIN empresas_terceirizadas e ON ms.empresa_solicitante_id = e.id
                  WHERE ms.data_saida >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        // Filtrar pelas empresas que o usuário tem acesso (usando o ID da empresa solicitante)
        $query = aplicarFiltroEmpresa($query, 'e', 'id');
        $query .= " GROUP BY e.id ORDER BY total_itens DESC";
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }

    // 5. Inventário Completo
    if ($acao === 'inventario_completo') {
        $query = "SELECT m.nome, m.codigo_sku, e.nome as empresa, c.nome as categoria, 
                         m.estoque_atual, m.unidade_medida_id, l.nome as local
                  FROM materiais m
                  LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
                  LEFT JOIN categorias_materiais c ON m.categoria_id = c.id
                  LEFT JOIN locais_armazenamento l ON m.local_id = l.id
                  WHERE m.ativo = 1";
        
        $query = aplicarFiltroEmpresa($query, 'm');
        $query .= " ORDER BY e.nome, m.nome";
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }

    // 6. Baixo Estoque (já existente, mas ajustado para filtro)
    if ($acao === 'estoque_baixo') {
        $query = "SELECT m.nome, m.codigo_sku, e.nome as empresa_nome, m.estoque_atual, m.ponto_reposicao,
                         ROUND((m.estoque_atual / m.ponto_reposicao) * 100, 1) as percentual_ponto
                  FROM materiais m
                  LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
                  WHERE m.ativo = 1 AND m.estoque_atual < m.ponto_reposicao";
        
        $query = aplicarFiltroEmpresa($query, 'm');
        
        if (isset($_GET['empresa_id']) && !empty($_GET['empresa_id'])) {
            $query .= " AND m.empresa_id = " . intval($_GET['empresa_id']);
        }
        
        $query .= " ORDER BY percentual_ponto ASC";
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }

    // 7. Sobressalência (Estoque Alto)
    if ($acao === 'sobressalencia') {
        $query = "SELECT m.nome, m.codigo_sku, e.nome as empresa_nome, m.estoque_atual, m.estoque_maximo,
                         ROUND((m.estoque_atual / m.estoque_maximo) * 100, 1) as percentual_maximo
                  FROM materiais m
                  LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
                  WHERE m.ativo = 1 AND m.estoque_atual > m.estoque_maximo";
        
        $query = aplicarFiltroEmpresa($query, 'm');
        
        if (isset($_GET['empresa_id']) && !empty($_GET['empresa_id'])) {
            $query .= " AND m.empresa_id = " . intval($_GET['empresa_id']);
        }
        
        $query .= " ORDER BY percentual_maximo DESC";
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }
}

// Função para gerar código SKU automático
function gerarCodigoSKU($conn, $categoria_id, $empresa_id) {
    try {
        // Buscar categoria
        $stmt = $conn->prepare('SELECT nome FROM categorias_materiais WHERE id = ?');
        $stmt->bind_param('i', $categoria_id);
        $stmt->execute();
        $categoria = $stmt->get_result()->fetch_assoc();
        
        if (!$categoria) {
            throw new Exception('Categoria não encontrada');
        }
        
        // Buscar empresa
        $stmt = $conn->prepare('SELECT nome FROM empresas_terceirizadas WHERE id = ?');
        $stmt->bind_param('i', $empresa_id);
        $stmt->execute();
        $empresa = $stmt->get_result()->fetch_assoc();
        
        if (!$empresa) {
            throw new Exception('Empresa não encontrada');
        }
        
        // Criar prefixo: 3 letras da categoria + 2 letras da empresa
        $nome_cat = preg_replace('/[^A-Za-z]/', '', $categoria['nome']);
        $nome_emp = preg_replace('/[^A-Za-z]/', '', $empresa['nome']);
        
        $prefixo_cat = strtoupper(substr($nome_cat ?: 'MAT', 0, 3));
        $prefixo_emp = strtoupper(substr($nome_emp ?: 'EMP', 0, 2));
        
        // Buscar próximo número sequencial
        $prefixo = $prefixo_cat . $prefixo_emp;
        
        // Verificar se tabela materiais existe
        $result = $conn->query("SHOW TABLES LIKE 'materiais'");
        if ($result->num_rows == 0) {
            // Se não existe, começar do 1
            $proximo_num = 1;
        } else {
            $stmt = $conn->prepare('SELECT MAX(CAST(SUBSTRING(codigo_sku, 6) AS UNSIGNED)) as ultimo_num FROM materiais WHERE codigo_sku LIKE ?');
            $like_pattern = $prefixo . '%';
            $stmt->bind_param('s', $like_pattern);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $proximo_num = ($result['ultimo_num'] ?? 0) + 1;
        }
        
        return $prefixo . str_pad($proximo_num, 4, '0', STR_PAD_LEFT);
        
    } catch (Exception $e) {
        error_log('Erro ao gerar SKU: ' . $e->getMessage());
        return 'MAT' . date('His') . rand(10, 99);
    }
}

// CADASTRO DE USUÁRIO
if ($tipo === 'auth' && $acao === 'cadastrar') {
    $stmt = $conn->prepare('SELECT id FROM usuarios_pendentes WHERE email = ?');
    $stmt->bind_param('s', $dados['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['sucesso' => false, 'erro' => 'Email já cadastrado']);
        exit;
    }
    
    $stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ?');
    $stmt->bind_param('s', $dados['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['sucesso' => false, 'erro' => 'Email já existe no sistema']);
        exit;
    }
    
    $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO usuarios_pendentes (nome, email, senha, departamento, justificativa) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $dados['nome'], $dados['email'], $senha_hash, $dados['departamento'], $dados['justificativa']);
    
    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Solicitação enviada! Aguarde aprovação do administrador.']);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar solicitação']);
    }
    exit;
}

// LISTAR USUÁRIOS PENDENTES
if ($tipo === 'usuarios' && $acao === 'pendentes') {
    if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 1) {
        echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
        exit;
    }
    
    $result = $conn->query('SELECT id, nome, email, departamento, justificativa, data_solicitacao FROM usuarios_pendentes WHERE status = "Pendente" ORDER BY data_solicitacao DESC');
    $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    echo json_encode(['sucesso' => true, 'dados' => $dados]);
    exit;
}

// APROVAR USUÁRIO
if ($tipo === 'usuarios' && $acao === 'aprovar') {
    if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 1) {
        echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
        exit;
    }
    
    $stmt = $conn->prepare('SELECT * FROM usuarios_pendentes WHERE id = ? AND status = "Pendente"');
    $stmt->bind_param('i', $dados['id']);
    $stmt->execute();
    $usuario_pendente = $stmt->get_result()->fetch_assoc();
    
    if (!$usuario_pendente) {
        echo json_encode(['sucesso' => false, 'erro' => 'Usuário não encontrado']);
        exit;
    }
    
    
    $conn->begin_transaction();
    try {
        // Criar usuário com TODOS os campos necessários
        $stmt = $conn->prepare('INSERT INTO usuarios (nome, email, senha, perfil_id, departamento, cargo, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)');
        if (!$stmt) {
            throw new Exception('Erro ao preparar statement: ' . $conn->error);
        }
        
        // Definir cargo padrão se não existir na tabela pendentes
        $cargo = isset($usuario_pendente['cargo']) ? $usuario_pendente['cargo'] : 'Colaborador';
        $departamento = $usuario_pendente['departamento'] ?? 'Não informado';
        
        $stmt->bind_param('sssiss', 
            $usuario_pendente['nome'], 
            $usuario_pendente['email'], 
            $usuario_pendente['senha'], 
            $dados['perfil_id'], 
            $departamento,
            $cargo
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao inserir usuário: ' . $stmt->error);
        }
        
        $novo_usuario_id = $conn->insert_id;
        
        if (!$novo_usuario_id) {
            throw new Exception('Erro: ID do novo usuário não foi gerado');
        }
        
        // Vincular empresas se fornecidas
        if (!empty($dados['empresas']) && is_array($dados['empresas'])) {
            $stmt_empresa = $conn->prepare('INSERT INTO usuarios_empresas (usuario_id, empresa_id) VALUES (?, ?)');
            if (!$stmt_empresa) {
                throw new Exception('Erro ao preparar statement de empresas: ' . $conn->error);
            }
            
            foreach ($dados['empresas'] as $empresa_id) {
                $stmt_empresa->bind_param('ii', $novo_usuario_id, $empresa_id);
                if (!$stmt_empresa->execute()) {
                    throw new Exception('Erro ao vincular empresa: ' . $stmt_empresa->error);
                }
            }
        }
        
        // Atualizar status do pendente
        $stmt = $conn->prepare('UPDATE usuarios_pendentes SET status = "Aprovado", aprovado_por = ?, data_aprovacao = NOW() WHERE id = ?');
        $stmt->bind_param('ii', $_SESSION['usuario_id'], $dados['id']);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar status pendente: ' . $stmt->error);
        }
        
        $conn->commit();
        echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário aprovado e criado com sucesso', 'usuario_id' => $novo_usuario_id]);
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Erro ao aprovar usuário: ' . $e->getMessage());
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao aprovar usuário: ' . $e->getMessage()]);
    }
    exit;
}

// GESTÃO DE LOCAIS
if ($tipo === 'locais') {
    if ($acao === 'listar') {
        $sql = "SELECT l.*, 
                GROUP_CONCAT(e.id) as empresas_ids,
                GROUP_CONCAT(e.nome SEPARATOR ', ') as empresas_nomes
                FROM locais_armazenamento l
                LEFT JOIN locais_empresas le ON l.id = le.local_id
                LEFT JOIN empresas_terceirizadas e ON le.empresa_id = e.id
                WHERE l.ativo = 1 
                GROUP BY l.id
                ORDER BY l.nome";
        $result = $conn->query($sql);
        $locais = [];
        while ($row = $result->fetch_assoc()) {
            $locais[] = $row;
        }
        echo json_encode(['sucesso' => true, 'dados' => $locais]);
        exit;
    }

    if ($acao === 'criar') {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO locais_armazenamento (nome, descricao, ativo) VALUES (?, ?, 1)");
            $stmt->bind_param('ss', $dados['nome'], $dados['descricao']);
            $stmt->execute();
            $local_id = $conn->insert_id;

            if (!empty($dados['empresas']) && is_array($dados['empresas'])) {
                $stmt_emp = $conn->prepare("INSERT INTO locais_empresas (local_id, empresa_id) VALUES (?, ?)");
                foreach ($dados['empresas'] as $empresa_id) {
                    $stmt_emp->bind_param('ii', $local_id, $empresa_id);
                    $stmt_emp->execute();
                }
            }

            $conn->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Local criado com sucesso']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao criar local: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($acao === 'atualizar') {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE locais_armazenamento SET nome = ?, descricao = ? WHERE id = ?");
            $stmt->bind_param('ssi', $dados['nome'], $dados['descricao'], $dados['id']);
            $stmt->execute();

            // Atualizar vínculos: remover todos e inserir novos
            $stmt_del = $conn->prepare("DELETE FROM locais_empresas WHERE local_id = ?");
            $stmt_del->bind_param('i', $dados['id']);
            $stmt_del->execute();

            if (!empty($dados['empresas']) && is_array($dados['empresas'])) {
                $stmt_emp = $conn->prepare("INSERT INTO locais_empresas (local_id, empresa_id) VALUES (?, ?)");
                foreach ($dados['empresas'] as $empresa_id) {
                    $stmt_emp->bind_param('ii', $dados['id'], $empresa_id);
                    $stmt_emp->execute();
                }
            }

            $conn->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Local atualizado com sucesso']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar local: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($acao === 'excluir') {
        $id = (int)$_GET['id'];

        // Verificar se o local tem vínculos antes de excluir
        $check_stmt = $conn->prepare("
            SELECT
                (SELECT COUNT(*) FROM materiais WHERE local_id = ?) as materiais_count,
                (SELECT COUNT(*) FROM movimentacoes_entrada WHERE local_destino_id = ?) as entradas_count,
                (SELECT COUNT(*) FROM movimentacoes_saida WHERE local_origem_id = ?) as saidas_count
        ");
        $check_stmt->bind_param('iii', $id, $id, $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $vinculos = $result->fetch_assoc();

        if ($vinculos['materiais_count'] > 0 || $vinculos['entradas_count'] > 0 || $vinculos['saidas_count'] > 0) {
            $msg_erro = "Não é possível excluir o local. Vínculos encontrados:\n";
            if ($vinculos['materiais_count'] > 0) $msg_erro .= "- {$vinculos['materiais_count']} material(is) com localização definida\n";
            if ($vinculos['entradas_count'] > 0) $msg_erro .= "- {$vinculos['entradas_count']} movimentação(ões) de entrada\n";
            if ($vinculos['saidas_count'] > 0) $msg_erro .= "- {$vinculos['saidas_count']} movimentação(ões) de saída\n";

            $msg_erro .= "\nPara excluir o local, remova os vínculos primeiro.";

            echo json_encode(['sucesso' => false, 'erro' => $msg_erro]);
            exit;
        }

        // Soft delete
        $stmt = $conn->prepare("UPDATE locais_armazenamento SET ativo = 0 WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Local excluído com sucesso']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao excluir local: ' . $stmt->error]);
        }
        exit;
    }
}

// REJEITAR USUÁRIO
if ($tipo === 'usuarios' && $acao === 'rejeitar') {
    if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 1) {
        echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
        exit;
    }
    
    $stmt = $conn->prepare('UPDATE usuarios_pendentes SET status = "Rejeitado", aprovado_por = ?, data_aprovacao = NOW() WHERE id = ? AND status = "Pendente"');
    $stmt->bind_param('ii', $_SESSION['usuario_id'], $dados['id']);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário rejeitado']);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Usuário não encontrado']);
    }
    exit;
}

// LOGIN
if ($tipo === 'auth' && $acao === 'login') {
    $stmt = $conn->prepare('SELECT u.*, p.nome as perfil_nome FROM usuarios u LEFT JOIN perfis_acesso p ON u.perfil_id = p.id WHERE u.email = ? AND u.ativo = 1');
    $stmt->bind_param('s', $dados['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['sucesso' => false, 'erro' => 'Email não encontrado']);
        exit;
    }
    
    $usuario = $result->fetch_assoc();
    
    if (password_verify($dados['senha'], $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_perfil'] = $usuario['perfil_id'];
        
        // Definir empresas permitidas
        if ($usuario['perfil_id'] == 1) {
            $_SESSION['empresas_permitidas'] = 'ALL';
            $usuario['empresas_vinculadas'] = 'ALL';
        } else {
            $stmt2 = $conn->prepare('SELECT ue.empresa_id, e.nome FROM usuarios_empresas ue JOIN empresas_terceirizadas e ON ue.empresa_id = e.id WHERE ue.usuario_id = ?');
            $stmt2->bind_param('i', $usuario['id']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            
            $empresas = [];
            $empresas_info = [];
            while ($row = $result2->fetch_assoc()) {
                $empresas[] = $row['empresa_id'];
                $empresas_info[] = ['id' => $row['empresa_id'], 'nome' => $row['nome']];
            }
            $_SESSION['empresas_permitidas'] = $empresas;
            $usuario['empresas_vinculadas'] = $empresas_info;
        }
        
        // Garantir que perfil_id seja retornado como inteiro
        $usuario['perfil_id'] = (int)$usuario['perfil_id'];
        $usuario['id'] = (int)$usuario['id'];
        
        unset($usuario['senha']);
        echo json_encode(['sucesso' => true, 'dados' => $usuario]);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Senha incorreta']);
    }
    exit;
}

// EMPRESAS
if ($tipo === 'empresas') {
    if ($acao === 'detalhes') {
        $empresa_id = $_GET['empresa_id'] ?? 0;

        if (!$empresa_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID da empresa é obrigatório']);
            exit;
        }

        // Verificar permissão de empresa
        if ($_SESSION['empresas_permitidas'] !== 'ALL' && !in_array($empresa_id, $_SESSION['empresas_permitidas'])) {
            echo json_encode(['sucesso' => false, 'erro' => 'Sem permissão para visualizar esta empresa']);
            exit;
        }

        $stmt = $conn->prepare('SELECT id, nome, tipo_servico, numero_contrato, cnpj, telefone, email, status FROM empresas_terceirizadas WHERE id = ?');
        $stmt->bind_param('i', $empresa_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $empresa = $result->fetch_assoc();
            echo json_encode(['sucesso' => true, 'dados' => $empresa]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Empresa não encontrada']);
        }
        exit;
    }

    if ($acao === 'listar') {
        $query = 'SELECT id, nome, tipo_servico, numero_contrato FROM empresas_terceirizadas WHERE status = "Ativa"';
        $query = aplicarFiltroEmpresa($query);
        $query .= ' LIMIT 50';

        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }
    
    if ($acao === 'criar') {
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 1) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores podem cadastrar empresas']);
            exit;
        }

        // Inclusão dos campos adicionais que podem ter sido enviados
        $cnpj = $dados['cnpj'] ?? '';
        $telefone = $dados['telefone'] ?? '';
        $email = $dados['email'] ?? '';
        $responsavel_id = $_SESSION['usuario_id'] ?? 1; // Usar ID do usuário logado ou padrão 1

        $stmt = $conn->prepare('INSERT INTO empresas_terceirizadas (nome, tipo_servico, numero_contrato, cnpj, telefone, email, responsavel_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, "Ativa")');
        $stmt->bind_param('ssssssi', $dados['nome'], $dados['tipo_servico'], $dados['numero_contrato'], $cnpj, $telefone, $email, $responsavel_id);

        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Empresa cadastrada']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar: ' . $stmt->error]);
        }
        exit;
    }
    
    if ($acao === 'excluir') {
        // Debug: Log da sessão
        error_log("DEBUG Excluir Empresa - Sessão completa: " . json_encode($_SESSION));
        error_log("DEBUG - usuario_perfil: " . ($_SESSION['usuario_perfil'] ?? 'não definido'));
        error_log("DEBUG - perfil_id: " . ($_SESSION['perfil_id'] ?? 'não definido'));
        
        // Verificar ambas as possibilidades de nome da variável de sessão
        $perfil = $_SESSION['usuario_perfil'] ?? $_SESSION['perfil_id'] ?? null;
        
        if (!$perfil || $perfil != 1) {
            $msg_erro = 'Apenas administradores podem excluir empresas';
            $msg_erro .= ' (Perfil detectado: ' . ($perfil ?? 'nenhum') . ')';
            error_log("DEBUG - NEGADO: " . $msg_erro);
            echo json_encode(['sucesso' => false, 'erro' => $msg_erro]);
            exit;
        }
        
        $empresa_id = $dados['id'] ?? 0;
        if (!$empresa_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID da empresa não informado']);
            exit;
        }
        
        // Verificar se há materiais associados (ATIVOS OU INATIVOS)
        $stmt = $conn->prepare('SELECT id, nome, codigo_sku, ativo FROM materiais WHERE empresa_id = ?');
        $stmt->bind_param('i', $empresa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $materiais = $result->fetch_all(MYSQLI_ASSOC);
        
        if (count($materiais) > 0) {
            $msg_erro = 'Não é possível excluir empresa. Existem ' . count($materiais) . ' material(is) vinculado(s):\n\n';
            
            foreach ($materiais as $mat) {
                $status = $mat['ativo'] == 1 ? 'ATIVO' : 'INATIVO (excluído logicamente)';
                $msg_erro .= "- {$mat['nome']} ({$mat['codigo_sku']}) - Status: {$status}\n";
            }
            
            $msg_erro .= "\n⚠️ IMPORTANTE: Materiais 'excluídos' ainda existem no banco (apenas marcados como inativos).\n";
            $msg_erro .= "Para excluir a empresa, você precisa DELETAR permanentemente esses materiais do banco de dados.";
            
            echo json_encode(['sucesso' => false, 'erro' => $msg_erro]);
            exit;
        }
        
        // Verificar se há usuários vinculados e desvincular
        $stmt = $conn->prepare('DELETE FROM usuarios_empresas WHERE empresa_id = ?');
        $stmt->bind_param('i', $empresa_id);
        $stmt->execute();
        
        // Excluir a empresa
        $stmt = $conn->prepare('DELETE FROM empresas_terceirizadas WHERE id = ?');
        $stmt->bind_param('i', $empresa_id);
        
        if ($stmt->execute()) {
            error_log("DEBUG - Empresa $empresa_id excluída com sucesso");
            echo json_encode(['sucesso' => true, 'mensagem' => 'Empresa excluída com sucesso']);
        } else {
            $erro_mysql = $stmt->error;
            error_log("DEBUG - Erro MySQL ao excluir empresa: $erro_mysql");
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao excluir empresa: ' . $erro_mysql]);
        }
        exit;
    }
    
    if ($acao === 'atualizar') {
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 1) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores podem editar empresas']);
            exit;
        }
        
        $empresa_id = $dados['id'] ?? 0;
        if (!$empresa_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID da empresa não informado']);
            exit;
        }
        
        // Log dos dados recebidos
        error_log("Atualizando empresa ID: $empresa_id");
        error_log("Dados: " . json_encode($dados));
        
        $stmt = $conn->prepare('UPDATE empresas_terceirizadas SET nome=?, tipo_servico=?, numero_contrato=?, cnpj=?, telefone=?, email=? WHERE id=?');
        
        if (!$stmt) {
            $erro = $conn->error;
            error_log("Erro ao preparar statement: $erro");
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao preparar atualização: ' . $erro]);
            exit;
        }
        
        $stmt->bind_param('ssssssi', $dados['nome'], $dados['tipo_servico'], $dados['numero_contrato'], $dados['cnpj'], $dados['telefone'], $dados['email'], $empresa_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['sucesso' => true, 'mensagem' => 'Empresa atualizada com sucesso']);
            } else {
                echo json_encode(['sucesso' => false, 'erro' => 'Nenhuma alteração foi feita ou empresa não encontrada']);
            }
        } else {
            $erro = $stmt->error;
            error_log("Erro ao executar UPDATE: $erro");
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar empresa: ' . $erro]);
        }
        exit;
    }
}

// CATEGORIAS (apenas admin)
if ($tipo === 'categorias') {
    if ($acao === 'listar') {
        $result = $conn->query('SELECT id, nome, descricao FROM categorias_materiais ORDER BY nome');
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }
    
    if ($acao === 'criar') {
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 1) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores podem cadastrar categorias']);
            exit;
        }
        
        $stmt = $conn->prepare('INSERT INTO categorias_materiais (nome, descricao) VALUES (?, ?)');
        $stmt->bind_param('ss', $dados['nome'], $dados['descricao']);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Categoria cadastrada']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar: ' . $conn->error]);
        }
        exit;
    }
}

// MATERIAIS
if ($tipo === 'materiais') {
    if ($acao === 'obter') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID não informado']);
            exit;
        }
        
        $query = 'SELECT m.*, e.nome as empresa_nome, c.nome as categoria_nome 
                  FROM materiais m 
                  LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id 
                  LEFT JOIN categorias_materiais c ON m.categoria_id = c.id 
                  WHERE m.id = ?';
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $dados = $result->fetch_assoc();
            
            // Verificar permissão
            if (isset($_SESSION['empresas_permitidas']) && $_SESSION['empresas_permitidas'] !== 'ALL') {
                if (!in_array($dados['empresa_id'], $_SESSION['empresas_permitidas'])) {
                    echo json_encode(['sucesso' => false, 'erro' => 'Sem permissão para visualizar este material']);
                    exit;
                }
            }
            
            echo json_encode(['sucesso' => true, 'dados' => $dados]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Material não encontrado']);
        }
        exit;
    }

    if ($acao === 'listar') {
        try {
            $query = 'SELECT m.*, e.nome as empresa_nome, c.nome as categoria_nome, u.simbolo as unidade_simbolo 
                      FROM materiais m 
                      LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id 
                      LEFT JOIN categorias_materiais c ON m.categoria_id = c.id 
                      LEFT JOIN unidades_medida u ON m.unidade_medida_id = u.id 
                      WHERE m.ativo = 1';
            
            // Aplicar filtro apenas se usuário não for admin
            if (isset($_SESSION['empresas_permitidas']) && $_SESSION['empresas_permitidas'] !== 'ALL') {
                if (!empty($_SESSION['empresas_permitidas'])) {
                    $empresas_str = implode(',', array_map('intval', $_SESSION['empresas_permitidas']));
                    $query .= " AND m.empresa_id IN ($empresas_str)";
                } else {
                    $query .= " AND 1=0"; // Nenhuma empresa permitida
                }
            }
            
            // Filtro adicional por empresa específica
            if (isset($_GET['empresa_id']) && !empty($_GET['empresa_id'])) {
                $empresa_filtro = intval($_GET['empresa_id']);
                if ($_SESSION['empresas_permitidas'] === 'ALL' || in_array($empresa_filtro, $_SESSION['empresas_permitidas'])) {
                    $query .= " AND m.empresa_id = $empresa_filtro";
                }
            }
            
            // Filtro de busca por nome ou código SKU
            if (isset($_GET['busca']) && !empty($_GET['busca'])) {
                $busca = $conn->real_escape_string($_GET['busca']);
                $query .= " AND (m.nome LIKE '%$busca%' OR m.codigo_sku LIKE '%$busca%')";
            }

            if (isset($_GET['local_id']) && !empty($_GET['local_id'])) {
                $local_id = intval($_GET['local_id']);
                $query .= " AND m.local_id = $local_id";
            }

            if (isset($_GET['somente_com_estoque']) && $_GET['somente_com_estoque'] === 'true') {
                $query .= " AND m.estoque_atual > 0";
            }
            
            $query .= ' ORDER BY m.nome LIMIT 50';
            
            $result = $conn->query($query);
            if (!$result) {
                throw new Exception('Erro na consulta: ' . $conn->error);
            }
            
            $dados = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $dados, 'query' => $query]);
            
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao listar materiais: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($acao === 'por_empresa') {
        $empresa_id = $_GET['empresa_id'] ?? 0;
        
        if (!$empresa_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID da empresa é obrigatório']);
            exit;
        }
        
        $query = 'SELECT m.id, m.nome, m.codigo_sku, m.estoque_atual FROM materiais m WHERE m.ativo = 1 AND m.empresa_id = ?';
        
        // Aplicar filtro de empresas permitidas
        if (isset($_SESSION['empresas_permitidas']) && $_SESSION['empresas_permitidas'] !== 'ALL') {
            if (!in_array($empresa_id, $_SESSION['empresas_permitidas'])) {
                echo json_encode(['sucesso' => false, 'erro' => 'Empresa não autorizada']);
                exit;
            }
        }
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $empresa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }
    
    if ($acao === 'gerar_sku') {
        try {
            if (empty($dados['categoria_id']) || empty($dados['empresa_id'])) {
                echo json_encode(['sucesso' => false, 'erro' => 'Selecione categoria e empresa antes de gerar o SKU']);
                exit;
            }
            
            $categoria_id = intval($dados['categoria_id']);
            $empresa_id = intval($dados['empresa_id']);
            
            // Verificar se categoria existe
            $stmt = $conn->prepare('SELECT nome FROM categorias_materiais WHERE id = ?');
            $stmt->bind_param('i', $categoria_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                echo json_encode(['sucesso' => false, 'erro' => 'Categoria não encontrada']);
                exit;
            }
            
            // Verificar se empresa existe
            $stmt = $conn->prepare('SELECT nome FROM empresas_terceirizadas WHERE id = ?');
            $stmt->bind_param('i', $empresa_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                echo json_encode(['sucesso' => false, 'erro' => 'Empresa não encontrada']);
                exit;
            }
            
            $sku = gerarCodigoSKU($conn, $categoria_id, $empresa_id);
            
            if ($sku) {
                echo json_encode(['sucesso' => true, 'sku' => $sku]);
            } else {
                echo json_encode(['sucesso' => false, 'erro' => 'Erro ao gerar SKU']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($acao === 'criar') {
        if (!isset($_SESSION['usuario_perfil']) || ($_SESSION['usuario_perfil'] != 1 && $_SESSION['usuario_perfil'] != 2)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores e gestores podem cadastrar materiais']);
            exit;
        }
        
        // Verificar se empresa está permitida
        if ($_SESSION['empresas_permitidas'] !== 'ALL' && !in_array($dados['empresa_id'], $_SESSION['empresas_permitidas'])) {
            echo json_encode(['sucesso' => false, 'erro' => 'Empresa não autorizada']);
            exit;
        }
        
        // Gerar SKU se não fornecido
        $codigo_sku = $dados['codigo_sku'];
        if (empty($codigo_sku)) {
            $codigo_sku = gerarCodigoSKU($conn, $dados['categoria_id'], $dados['empresa_id']);
        }
        
        // Verificar se SKU já existe
        $stmt_check = $conn->prepare('SELECT id FROM materiais WHERE codigo_sku = ?');
        $stmt_check->bind_param('s', $codigo_sku);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Código SKU já existe']);
            exit;
        }
        
        // Garantir que as dependências existam (com estrutura correta)
        $conn->query("INSERT IGNORE INTO categorias_materiais (id, nome, descricao) VALUES (1, 'Limpeza', 'Produtos de limpeza')");
        $conn->query("INSERT IGNORE INTO unidades_medida (id, descricao, simbolo) VALUES (1, 'Unidade', 'un')");
        $conn->query("INSERT IGNORE INTO locais_armazenamento (id, nome, descricao, ativo) VALUES (1, 'Almoxarifado Central', 'Depósito principal', 1)");
        
        // Verificar se empresa existe
        $result = $conn->query("SELECT COUNT(*) as total FROM empresas_terceirizadas WHERE id = {$dados['empresa_id']}");
        if ($result->fetch_assoc()['total'] == 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Empresa não encontrada']);
            exit;
        }
        
        // Preparar valores (baseado na estrutura real)
        $categoria_id = $dados['categoria_id'] ?? 2; // Ferramentas existe
        $unidade_id = $dados['unidade_medida_id'] ?? 1;
        // CORREÇÃO: Usar ID 1 (Almoxarifado Central) como padrão se não informado, pois a coluna é NOT NULL
        $local_id = (!empty($dados['local_id']) && $dados['local_id'] > 0) ? intval($dados['local_id']) : 1;
        $ponto_reposicao = $dados['ponto_reposicao'] ?? 0;
        $estoque_maximo = $dados['estoque_maximo'] ?? 0;

        // Inserir material (campos obrigatórios baseados na estrutura)
        $stmt = $conn->prepare('INSERT INTO materiais (nome, codigo_sku, categoria_id, unidade_medida_id, empresa_id, local_id, ponto_reposicao, estoque_maximo, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)');
        $stmt->bind_param('ssiiiddd',
            $dados['nome'],
            $codigo_sku,
            $categoria_id,
            $unidade_id,
            $dados['empresa_id'],
            $local_id,
            $ponto_reposicao,
            $estoque_maximo
        );
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Material cadastrado', 'sku_gerado' => $codigo_sku]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar: ' . $conn->error]);
        }
        exit;
    }
    
    if ($acao === 'atualizar') {
        if (!isset($_SESSION['usuario_perfil']) || ($_SESSION['usuario_perfil'] != 1 && $_SESSION['usuario_perfil'] != 2)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores e gestores podem editar materiais']);
            exit;
        }
        
        $material_id = $dados['id'] ?? 0;
        if (!$material_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID do material não informado']);
            exit;
        }
        
        // Verificar se material existe e buscar local_id atual
        $stmt = $conn->prepare('SELECT empresa_id, local_id FROM materiais WHERE id = ? AND ativo = 1');
        $stmt->bind_param('i', $material_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Material não encontrado']);
            exit;
        }
        
        $material = $result->fetch_assoc();
        
        // Verificar permissão de empresa
        if ($_SESSION['empresas_permitidas'] !== 'ALL' && !in_array($material['empresa_id'], $_SESSION['empresas_permitidas'])) {
            echo json_encode(['sucesso' => false, 'erro' => 'Sem permissão para editar material desta empresa']);
            exit;
        }
        
        // Tratamento de dados
        $unidade_id = isset($dados['unidade_medida_id']) ? intval($dados['unidade_medida_id']) : 1;

        // CORREÇÃO: Removido local_id da atualização para manter o valor original sem disparar erro de FK
        $stmt = $conn->prepare('UPDATE materiais SET nome=?, categoria_id=?, empresa_id=?, unidade_medida_id=?, ponto_reposicao=?, estoque_maximo=? WHERE id=?');
        $stmt->bind_param('siiiddi', 
            $dados['nome'],
            $dados['categoria_id'],
            $dados['empresa_id'],
            $unidade_id,
            $dados['ponto_reposicao'],
            $dados['estoque_maximo'],
            $material_id
        );
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Material atualizado com sucesso']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar material: ' . $stmt->error]);
        }
        exit;
    }
    
    if ($acao === 'excluir') {
        if (!isset($_SESSION['usuario_perfil']) || ($_SESSION['usuario_perfil'] != 1 && $_SESSION['usuario_perfil'] != 2)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores e gestores podem excluir materiais']);
            exit;
        }

        $material_id = $dados['id'] ?? 0;
        if (!$material_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID do material não informado']);
            exit;
        }

        // Verificar se material existe e obter informações
        $stmt = $conn->prepare('SELECT nome, estoque_atual, empresa_id FROM materiais WHERE id = ? AND ativo = 1');
        $stmt->bind_param('i', $material_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Material não encontrado']);
            exit;
        }

        $material = $result->fetch_assoc();

        // Verificar permissão de empresa
        if ($_SESSION['empresas_permitidas'] !== 'ALL' && !in_array($material['empresa_id'], $_SESSION['empresas_permitidas'])) {
            echo json_encode(['sucesso' => false, 'erro' => 'Sem permissão para excluir material desta empresa']);
            exit;
        }

        // VALIDAÇÃO: Não permitir excluir material com estoque
        if ($material['estoque_atual'] > 0) {
            echo json_encode([
                'sucesso' => false,
                'erro' => 'Não é possível excluir material com estoque. Estoque atual: ' . $material['estoque_atual']
            ]);
            exit;
        }

        // Verificar se há movimentações de entrada - estas precisam ser excluídas primeiro devido à chave estrangeira
        $stmt = $conn->prepare('SELECT COUNT(*) as total FROM movimentacoes_entrada WHERE material_id = ?');
        $stmt->bind_param('i', $material_id);
        $stmt->execute();
        $result_entrada = $stmt->get_result()->fetch_assoc();

        $stmt = $conn->prepare('SELECT COUNT(*) as total FROM movimentacoes_saida WHERE material_id = ?');
        $stmt->bind_param('i', $material_id);
        $stmt->execute();
        $result_saida = $stmt->get_result()->fetch_assoc();

        $total_movimentacoes_entrada = (int)$result_entrada['total'];
        $total_movimentacoes_saida = (int)$result_saida['total'];
        $total_movimentacoes = $total_movimentacoes_entrada + $total_movimentacoes_saida;

        // Transação para garantir consistência
        $conn->begin_transaction();
        try {
            // Excluir primeiro as movimentações de entrada relacionadas (devido à chave estrangeira)
            if ($total_movimentacoes_entrada > 0) {
                $stmt = $conn->prepare('DELETE FROM movimentacoes_entrada WHERE material_id = ?');
                $stmt->bind_param('i', $material_id);
                $stmt->execute();
                error_log("Excluídas $total_movimentacoes_entrada movimentações de entrada para o material ID $material_id");
            }

            // Excluir movimentações de saída relacionadas
            if ($total_movimentacoes_saida > 0) {
                $stmt = $conn->prepare('DELETE FROM movimentacoes_saida WHERE material_id = ?');
                $stmt->bind_param('i', $material_id);
                $stmt->execute();
                error_log("Excluídas $total_movimentacoes_saida movimentações de saída para o material ID $material_id");
            }

            // Agora excluir o material
            $stmt = $conn->prepare('DELETE FROM materiais WHERE id = ?');
            $stmt->bind_param('i', $material_id);

            if ($stmt->execute()) {
                $conn->commit();
                error_log("HARD DELETE - Material ID $material_id ({$material['nome']}) deletado permanentemente com $total_movimentacoes movimentações relacionadas");
                echo json_encode(['sucesso' => true, 'mensagem' => "Material excluído permanentemente do banco de dados e $total_movimentacoes movimentações associadas removidas"]);
            } else {
                $conn->rollback();
                echo json_encode(['sucesso' => false, 'erro' => 'Erro ao excluir material: ' . $stmt->error]);
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro na transação: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($acao === 'historico') {
        $material_id = intval($dados['material_id'] ?? $_GET['material_id'] ?? 0);
        $dias = intval($dados['dias'] ?? $_GET['dias'] ?? 30);
        
        if (!$material_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID do material não informado']);
            exit;
        }

        try {
            // 1. Dados do Material
            $stmt = $conn->prepare('SELECT m.*, c.nome as categoria_nome, u.simbolo as unidade_simbolo, e.nome as empresa_nome 
                                    FROM materiais m 
                                    LEFT JOIN categorias_materiais c ON m.categoria_id = c.id 
                                    LEFT JOIN unidades_medida u ON m.unidade_medida_id = u.id 
                                    LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id 
                                    WHERE m.id = ?');
            $stmt->bind_param('i', $material_id);
            $stmt->execute();
            $material = $stmt->get_result()->fetch_assoc();

            if (!$material) {
                throw new Exception('Material não encontrado');
            }

            // 2. Calcular intervalo
            $data_fim = date('Y-m-d');
            $data_inicio = date('Y-m-d', strtotime("-$dias days"));

            // 3. Calcular estoque inicial do período
            // Entradas no período (até hoje)
            $stmt = $conn->prepare("SELECT SUM(quantidade) as total FROM movimentacoes_entrada WHERE material_id = ? AND data_entrada >= ?");
            $stmt->bind_param('is', $material_id, $data_inicio);
            $stmt->execute();
            $entradas_periodo = floatval($stmt->get_result()->fetch_assoc()['total'] ?? 0);

            // Saídas no período (até hoje)
            $stmt = $conn->prepare("SELECT SUM(quantidade) as total FROM movimentacoes_saida WHERE material_id = ? AND data_saida >= ?");
            $stmt->bind_param('is', $material_id, $data_inicio);
            $stmt->execute();
            $saidas_periodo = floatval($stmt->get_result()->fetch_assoc()['total'] ?? 0);

            $estoque_inicial_periodo = floatval($material['estoque_atual']) - $entradas_periodo + $saidas_periodo;

            // 4. Buscar movimentações diárias para o gráfico
            $movimentacoes = [];
            
            // Entradas
            $stmt = $conn->prepare("SELECT 'entrada' as tipo, data_entrada as data, quantidade, id 
                                    FROM movimentacoes_entrada 
                                    WHERE material_id = ? AND data_entrada >= ? 
                                    ORDER BY data_entrada ASC");
            $stmt->bind_param('is', $material_id, $data_inicio);
            $stmt->execute();
            $res_ent = $stmt->get_result();
            while ($row = $res_ent->fetch_assoc()) {
                $movimentacoes[] = $row;
            }

            // Saídas
            $stmt = $conn->prepare("SELECT 'saida' as tipo, data_saida as data, quantidade, id 
                                    FROM movimentacoes_saida 
                                    WHERE material_id = ? AND data_saida >= ? 
                                    ORDER BY data_saida ASC");
            $stmt->bind_param('is', $material_id, $data_inicio);
            $stmt->execute();
            $res_sai = $stmt->get_result();
            while ($row = $res_sai->fetch_assoc()) {
                $movimentacoes[] = $row;
            }

            // Ordenar todas por data
            usort($movimentacoes, function($a, $b) {
                return strtotime($a['data']) - strtotime($b['data']);
            });

            // 5. Construir dados do gráfico (dia a dia)
            $grafico = [];
            $estoque_corrente = $estoque_inicial_periodo;
            $data_atual_loop = $data_inicio;
            $mov_idx = 0;
            $total_movs = count($movimentacoes);

            // Loop de data_inicio até hoje
            while (strtotime($data_atual_loop) <= strtotime($data_fim)) {
                // Processar movimentações deste dia
                $entradas_dia = 0;
                $saidas_dia = 0;

                // Avançar nas movimentações até encontrar as do dia atual
                while ($mov_idx < $total_movs) {
                    $data_mov = date('Y-m-d', strtotime($movimentacoes[$mov_idx]['data']));
                    
                    if ($data_mov < $data_atual_loop) {
                        // Movimentação anterior ao dia atual (não deveria acontecer se ordenado, mas por segurança)
                        $mov_idx++;
                        continue;
                    }
                    
                    if ($data_mov > $data_atual_loop) {
                        // Movimentação futura, parar e ir para próximo dia do loop
                        break;
                    }
                    
                    // Se chegou aqui, é do dia atual
                    if ($movimentacoes[$mov_idx]['tipo'] == 'entrada') {
                        $entradas_dia += $movimentacoes[$mov_idx]['quantidade'];
                        $estoque_corrente += $movimentacoes[$mov_idx]['quantidade'];
                    } else {
                        $saidas_dia += $movimentacoes[$mov_idx]['quantidade'];
                        $estoque_corrente -= $movimentacoes[$mov_idx]['quantidade'];
                    }
                    $mov_idx++;
                }

                $grafico[] = [
                    'data' => date('d/m', strtotime($data_atual_loop)),
                    'data_full' => $data_atual_loop,
                    'saldo' => $estoque_corrente,
                    'entradas' => $entradas_dia,
                    'saidas' => $saidas_dia
                ];

                $data_atual_loop = date('Y-m-d', strtotime($data_atual_loop . ' +1 day'));
            }

            echo json_encode([
                'sucesso' => true, 
                'material' => $material,
                'grafico' => $grafico,
                'movimentacoes' => $movimentacoes
            ]);

        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao buscar histórico: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($acao === 'estoque_por_local') {
        $material_id = $_GET['material_id'] ?? 0;

        if (!$material_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID do material é obrigatório']);
            exit;
        }

        // Verificar se material existe e pertence à empresa autorizada
        $stmt = $conn->prepare('SELECT empresa_id FROM materiais WHERE id = ?');
        $stmt->bind_param('i', $material_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Material não encontrado']);
            exit;
        }

        $material = $result->fetch_assoc();

        // Verificar permissão de empresa
        if ($_SESSION['empresas_permitidas'] !== 'ALL' && !in_array($material['empresa_id'], $_SESSION['empresas_permitidas'])) {
            echo json_encode(['sucesso' => false, 'erro' => 'Sem permissão para visualizar estoque deste material']);
            exit;
        }

        // Buscar estoque por local considerando entradas e saídas (Corrigido para evitar produto cartesiano)
        $query = "SELECT
                    l.id as local_id,
                    l.nome as local_nome,
                    COALESCE(entradas.total, 0) as entrada_no_local,
                    COALESCE(saidas.total, 0) as saida_do_local,
                    (COALESCE(entradas.total, 0) - COALESCE(saidas.total, 0)) as estoque_no_local
                  FROM locais_armazenamento l
                  LEFT JOIN (
                      SELECT local_destino_id, SUM(quantidade) as total
                      FROM movimentacoes_entrada
                      WHERE material_id = ?
                      GROUP BY local_destino_id
                  ) entradas ON l.id = entradas.local_destino_id
                  LEFT JOIN (
                      SELECT local_origem_id, SUM(quantidade) as total
                      FROM movimentacoes_saida
                      WHERE material_id = ?
                      GROUP BY local_origem_id
                  ) saidas ON l.id = saidas.local_origem_id
                  WHERE l.ativo = 1
                  HAVING estoque_no_local > 0
                  ORDER BY l.nome";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $material_id, $material_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $estoques = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['estoque_no_local'] > 0) {
                $estoques[] = [
                    'local_id' => $row['local_id'],
                    'local_nome' => $row['local_nome'],
                    'estoque' => $row['estoque_no_local']
                ];
            }
        }

        echo json_encode(['sucesso' => true, 'dados' => $estoques]);
        exit;
    }

    if ($acao === 'hard_delete') {
        // HARD DELETE: Exclusão permanente do banco de dados (apenas para materiais inativos)
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 1) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores podem deletar permanentemente materiais']);
            exit;
        }
        
        $material_id = $dados['id'] ?? 0;
        if (!$material_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID do material não informado']);
            exit;
        }
        
        // Verificar se material existe e se está INATIVO
        $stmt = $conn->prepare('SELECT nome, ativo, estoque_atual, empresa_id FROM materiais WHERE id = ?');
        $stmt->bind_param('i', $material_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Material não encontrado']);
            exit;
        }
        
        $material = $result->fetch_assoc();
        
        // Verificar se está INATIVO (segurança: só permite deletar permanentemente materiais já "excluídos")
        if ($material['ativo'] == 1) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas materiais inativos podem ser deletados permanentemente. Faça uma exclusão normal primeiro.']);
            exit;
        }
        
        // Verificar se tem estoque
        if ($material['estoque_atual'] > 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Material possui estoque (' . $material['estoque_atual'] . '). Não é possível deletar.']);
            exit;
        }
        
        // Verificar se há movimentações (entrada ou saída)
        $stmt = $conn->prepare('SELECT COUNT(*) as total FROM movimentacoes_entrada WHERE material_id = ?');
        $stmt->bind_param('i', $material_id);
        $stmt->execute();
        $result_entrada = $stmt->get_result()->fetch_assoc();
        
        $stmt = $conn->prepare('SELECT COUNT(*) as total FROM movimentacoes_saida WHERE material_id = ?');
        $stmt->bind_param('i', $material_id);
        $stmt->execute();
        $result_saida = $stmt->get_result()->fetch_assoc();
        
        $total_movimentacoes = $result_entrada['total'] + $result_saida['total'];
        
        if ($total_movimentacoes > 0) {
            echo json_encode([
                'sucesso' => false, 
                'erro' => 'Material possui histórico de movimentações (' . $total_movimentacoes . ' registros). Por segurança, não é possível deletar permanentemente materiais com histórico.'
            ]);
            exit;
        }
        
        // DELETE permanente do banco de dados
        $stmt = $conn->prepare('DELETE FROM materiais WHERE id = ?');
        $stmt->bind_param('i', $material_id);
        
        if ($stmt->execute()) {
            error_log("HARD DELETE - Material ID $material_id ({$material['nome']}) deletado permanentemente por admin");
            echo json_encode(['sucesso' => true, 'mensagem' => 'Material "' . $material['nome'] . '" deletado permanentemente do banco de dados']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao deletar material: ' . $stmt->error]);
        }
        exit;
    }
}

// ATUALIZAR PRÓPRIO PERFIL (qualquer usuário logado pode fazer)
if ($tipo === 'usuarios' && $acao === 'atualizar_perfil') {
    // Verificar se está logado
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['sucesso' => false, 'erro' => 'Usuário não autenticado']);
        exit;
    }
    
    // Verificar se está tentando atualizar seu próprio perfil
    if ($_SESSION['usuario_id'] != $dados['id']) {
        echo json_encode(['sucesso' => false, 'erro' => 'Você só pode atualizar seu próprio perfil']);
        exit;
    }
    
    // Buscar usuário atual
    $stmt = $conn->prepare('SELECT senha, email FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $dados['id']);
    $stmt->execute();
    $usuario_atual = $stmt->get_result()->fetch_assoc();
    
    if (!$usuario_atual) {
        echo json_encode(['sucesso' => false, 'erro' => 'Usuário não encontrado']);
        exit;
    }
    
    // Verificar senha atual
    if (!password_verify($dados['senha_atual'], $usuario_atual['senha'])) {
        echo json_encode(['sucesso' => false, 'erro' => 'Senha atual incorreta']);
        exit;
    }
    
    // Verificar se email já está em uso por outro usuário
    if ($dados['email'] != $usuario_atual['email']) {
        $stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
        $stmt->bind_param('si', $dados['email'], $dados['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Este email já está em uso']);
            exit;
        }
    }
    
    // Atualizar dados
    if ($dados['nova_senha']) {
        // Atualizar com nova senha
        $nova_senha_hash = password_hash($dados['nova_senha'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?');
        $stmt->bind_param('sssi', $dados['nome'], $dados['email'], $nova_senha_hash, $dados['id']);
    } else {
        // Atualizar sem mudar senha
        $stmt = $conn->prepare('UPDATE usuarios SET nome = ?, email = ? WHERE id = ?');
        $stmt->bind_param('ssi', $dados['nome'], $dados['email'], $dados['id']);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Perfil atualizado com sucesso']);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar perfil']);
    }
    exit;
}

// USUÁRIOS (apenas para admin)
if ($tipo === 'usuarios') {
    if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 1) {
        echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado! Apenas administradores podem gerenciar usuários.']);
        exit;
    }
    
    if ($acao === 'listar' || $acao === 'listar_completo') {
        $query = 'SELECT u.id, u.nome, u.email, u.ativo, u.departamento, p.nome as perfil_nome, p.id as perfil_id,
                         GROUP_CONCAT(e.nome SEPARATOR ", ") as empresas_nomes 
                  FROM usuarios u 
                  LEFT JOIN perfis_acesso p ON u.perfil_id = p.id 
                  LEFT JOIN usuarios_empresas ue ON u.id = ue.usuario_id 
                  LEFT JOIN empresas_terceirizadas e ON ue.empresa_id = e.id 
                  GROUP BY u.id ORDER BY u.nome';
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }
    
    if ($acao === 'criar') {
        $stmt = $conn->prepare('INSERT INTO usuarios (nome, email, senha, perfil_id, departamento) VALUES (?, ?, ?, ?, ?)');
        $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $stmt->bind_param('sssis', $dados['nome'], $dados['email'], $senha_hash, $dados['perfil_id'], $dados['departamento']);
        
        if ($stmt->execute()) {
            $usuario_id = $conn->insert_id;
            
            // Vincular empresas se não for admin
            if ($dados['perfil_id'] > 1 && !empty($dados['empresas_vinculadas'])) {
                foreach ($dados['empresas_vinculadas'] as $empresa_id) {
                    $stmt2 = $conn->prepare('INSERT INTO usuarios_empresas (usuario_id, empresa_id) VALUES (?, ?)');
                    $stmt2->bind_param('ii', $usuario_id, $empresa_id);
                    $stmt2->execute();
                }
            }
            
            echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário cadastrado']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar']);
        }
        exit;
    }
    
    if ($acao === 'toggle_status') {
        $stmt = $conn->prepare('UPDATE usuarios SET ativo = ? WHERE id = ?');
        $stmt->bind_param('ii', $dados['ativo'], $dados['id']);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Status atualizado']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar']);
        }
        exit;
    }
    
    if ($acao === 'buscar') {
        $query = 'SELECT u.*, p.nome as perfil_nome, GROUP_CONCAT(ue.empresa_id) as empresas_ids
                  FROM usuarios u 
                  LEFT JOIN perfis_acesso p ON u.perfil_id = p.id 
                  LEFT JOIN usuarios_empresas ue ON u.id = ue.usuario_id 
                  WHERE u.id = ? GROUP BY u.id';
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $dados['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            echo json_encode(['sucesso' => true, 'dados' => $usuario]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Usuário não encontrado']);
        }
        exit;
    }
    
    if ($acao === 'atualizar') {
        // Atualizar dados básicos
        $stmt = $conn->prepare('UPDATE usuarios SET nome=?, email=?, perfil_id=?, departamento=? WHERE id=?');
        $stmt->bind_param('ssisi', $dados['nome'], $dados['email'], $dados['perfil_id'], $dados['departamento'], $dados['id']);
        
        if ($stmt->execute()) {
            // Remover vínculos antigos
            $stmt2 = $conn->prepare('DELETE FROM usuarios_empresas WHERE usuario_id = ?');
            $stmt2->bind_param('i', $dados['id']);
            $stmt2->execute();
            
            // Adicionar novos vínculos se não for admin
            if ($dados['perfil_id'] > 1 && !empty($dados['empresas_vinculadas'])) {
                foreach ($dados['empresas_vinculadas'] as $empresa_id) {
                    $stmt3 = $conn->prepare('INSERT INTO usuarios_empresas (usuario_id, empresa_id) VALUES (?, ?)');
                    $stmt3->bind_param('ii', $dados['id'], $empresa_id);
                    $stmt3->execute();
                }
            }
            
            echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário atualizado']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar']);
        }
        exit;
    }

    if ($acao === 'excluir') {
        // Verificar se é administrador
        if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 1) {
            echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado! Apenas administradores podem excluir usuários.']);
            exit;
        }

        $usuario_id = $dados['id'] ?? 0;
        if (!$usuario_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID do usuário não informado']);
            exit;
        }

        // Impedir que o administrador se exclua a si mesmo
        if ($usuario_id == $_SESSION['usuario_id']) {
            echo json_encode(['sucesso' => false, 'erro' => 'Você não pode excluir sua própria conta']);
            exit;
        }

        // Começar transação para garantir consistência
        $conn->begin_transaction();
        try {
            // Remover vínculos com empresas
            $stmt = $conn->prepare('DELETE FROM usuarios_empresas WHERE usuario_id = ?');
            $stmt->bind_param('i', $usuario_id);
            $stmt->execute();

            // Remover o usuário
            $stmt = $conn->prepare('DELETE FROM usuarios WHERE id = ?');
            $stmt->bind_param('i', $usuario_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $conn->commit();
                echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário excluído com sucesso']);
            } else {
                $conn->rollback();
                echo json_encode(['sucesso' => false, 'erro' => 'Erro ao excluir usuário ou usuário não encontrado']);
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro na exclusão: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($acao === 'por_empresa') {
        $empresa_id = $_GET['empresa_id'] ?? 0;
        
        if (!$empresa_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID da empresa é obrigatório']);
            exit;
        }
        
        // Buscar usuários vinculados à empresa (operadores, gestores e administradores)
        $query = 'SELECT u.id, u.nome, u.email 
                  FROM usuarios u 
                  JOIN usuarios_empresas ue ON u.id = ue.usuario_id 
                  WHERE ue.empresa_id = ? AND u.ativo = 1 AND u.perfil_id IN (1, 2, 3)
                  ORDER BY u.nome';
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $empresa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }
}

// RELATÓRIOS
if ($tipo === 'relatorios') {
    // 1. Resumo Geral (Dashboard)
    if ($acao === 'resumo_geral') {
        $total_empresas = $conn->query('SELECT COUNT(*) as total FROM empresas_terceirizadas WHERE status = "Ativa"')->fetch_assoc()['total'];
        $total_materiais = $conn->query('SELECT COUNT(*) as total FROM materiais WHERE ativo = 1')->fetch_assoc()['total'];
        $estoque_baixo = $conn->query('SELECT COUNT(*) as total FROM materiais WHERE ativo = 1 AND estoque_atual < ponto_reposicao')->fetch_assoc()['total'];
        
        // Calcular valor total do estoque
        $valor_total = $conn->query('SELECT SUM(estoque_atual * valor_unitario) as total FROM materiais WHERE ativo = 1')->fetch_assoc()['total'];
        
        echo json_encode([
            'sucesso' => true,
            'dados' => [
                'total_empresas' => $total_empresas,
                'total_materiais' => $total_materiais,
                'estoque_baixo' => $estoque_baixo,
                'valor_total_estoque' => 'R$ ' . number_format($valor_total ?? 0, 2, ',', '.')
            ]
        ]);
        exit;
    }

    // 2. Estoque por Empresa
    if ($acao === 'estoque_por_empresa') {
        $query = 'SELECT e.nome, 
                         COUNT(m.id) as total_materiais, 
                         SUM(m.estoque_atual) as total_estoque,
                         SUM(m.estoque_atual * m.valor_unitario) as valor_total
                  FROM empresas_terceirizadas e
                  LEFT JOIN materiais m ON e.id = m.empresa_id AND m.ativo = 1
                  WHERE e.status = "Ativa"';
        
        $query = aplicarFiltroEmpresa($query, 'e');
        $query .= ' GROUP BY e.id ORDER BY e.nome';
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }

    // 3. Movimentações (Entradas e Saídas)
    if ($acao === 'movimentacoes') {
        $periodo = $_GET['periodo'] ?? 30; // Dias
        $tipo_mov = $_GET['tipo_mov'] ?? 'todos';
        $empresa_id = $_GET['empresa_id'] ?? '';
        $material_id = $_GET['material_id'] ?? '';
        
        // Se for filtro por material, pegar histórico completo (ignorar periodo padrão de 30 dias se não especificado explicitamente)
        if ($material_id && !isset($_GET['periodo'])) {
            $data_inicio = '2000-01-01';
        } else {
            $data_inicio = date('Y-m-d', strtotime("-$periodo days"));
        }
        
        $dados = [];
        
        // Entradas
        if ($tipo_mov === 'todos' || $tipo_mov === 'entrada') {
            $query = "SELECT 'Entrada' as tipo, me.id, me.data_entrada as data, m.nome as material, e.nome as empresa, me.quantidade, u.nome as responsavel, me.nota_fiscal, me.observacao
                      FROM movimentacoes_entrada me
                      JOIN materiais m ON me.material_id = m.id
                      LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
                      LEFT JOIN usuarios u ON me.responsavel_id = u.id
                      WHERE me.data_entrada >= '$data_inicio'";
            
            if ($empresa_id) $query .= " AND m.empresa_id = " . intval($empresa_id);
            if ($material_id) $query .= " AND me.material_id = " . intval($material_id);
            
            $query = aplicarFiltroEmpresa($query, 'm');
            
            $result = $conn->query($query);
            if ($result) $dados = array_merge($dados, $result->fetch_all(MYSQLI_ASSOC));
        }
        
        // Saídas
        if ($tipo_mov === 'todos' || $tipo_mov === 'saida') {
            $query = "SELECT 'Saída' as tipo, ms.id, ms.data_saida as data, m.nome as material, e.nome as empresa, ms.quantidade, '-' as responsavel, '' as nota_fiscal, ms.observacao
                      FROM movimentacoes_saida ms
                      JOIN materiais m ON ms.material_id = m.id
                      LEFT JOIN empresas_terceirizadas e ON ms.empresa_solicitante_id = e.id
                      WHERE ms.data_saida >= '$data_inicio'";
            
            if ($empresa_id) $query .= " AND ms.empresa_solicitante_id = " . intval($empresa_id);
            if ($material_id) $query .= " AND ms.material_id = " . intval($material_id);
            
            // Nota: Saída pode ser vista por quem tem acesso à empresa solicitante OU dona do material? 
            // Simplificação: filtro pela empresa dona do material (m.empresa_id)
            $query = aplicarFiltroEmpresa($query, 'm'); 
            
            $result = $conn->query($query);
            if ($result) $dados = array_merge($dados, $result->fetch_all(MYSQLI_ASSOC));
        }
        
        // Ordenar por data decrescente
        usort($dados, function($a, $b) {
            return strtotime($b['data']) - strtotime($a['data']);
        });
        
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }

    // 4. Consumo por Empresa (Saídas)
    if ($acao === 'consumo_por_empresa') {
        $query = "SELECT e.nome as empresa, COUNT(ms.id) as total_saidas, SUM(ms.quantidade) as total_itens
                  FROM movimentacoes_saida ms
                  JOIN empresas_terceirizadas e ON ms.empresa_solicitante_id = e.id
                  WHERE ms.data_saida >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $query = aplicarFiltroEmpresa($query, 'ms');
        $query .= " GROUP BY e.id ORDER BY total_itens DESC";
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }

    // 5. Inventário Completo
    if ($acao === 'inventario') {
        $query = "SELECT m.id, m.nome, m.codigo_sku, e.nome as empresa, c.nome as categoria, 
                         m.estoque_atual, m.unidade_medida_id, l.nome as local,
                         m.valor_unitario, (m.estoque_atual * m.valor_unitario) as valor_total
                  FROM materiais m
                  LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
                  LEFT JOIN categorias_materiais c ON m.categoria_id = c.id
                  LEFT JOIN locais_armazenamento l ON m.local_id = l.id
                  WHERE m.ativo = 1";
        
        $query = aplicarFiltroEmpresa($query, 'm');
        
        if (isset($_GET['empresa_id']) && !empty($_GET['empresa_id'])) {
            $query .= " AND m.empresa_id = " . intval($_GET['empresa_id']);
        }
        
        $query .= " ORDER BY e.nome, m.nome";
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }

    // 6. Baixo Estoque (já existente, mas ajustado para filtro)
    if ($acao === 'estoque_baixo') {
        $query = "SELECT m.nome, m.codigo_sku, e.nome as empresa_nome, m.estoque_atual, m.ponto_reposicao,
                         ROUND((m.estoque_atual / m.ponto_reposicao) * 100, 1) as percentual_ponto
                  FROM materiais m
                  LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
                  WHERE m.ativo = 1 AND m.estoque_atual < m.ponto_reposicao";
        
        $query = aplicarFiltroEmpresa($query, 'm');
        
        if (isset($_GET['empresa_id']) && !empty($_GET['empresa_id'])) {
            $query .= " AND m.empresa_id = " . intval($_GET['empresa_id']);
        }
        
        $query .= " ORDER BY percentual_ponto ASC";
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }

    // 7. Sobressalência (Estoque Alto)
    if ($acao === 'sobressalencia') {
        $query = "SELECT m.nome, m.codigo_sku, e.nome as empresa_nome, m.estoque_atual, m.estoque_maximo,
                         ROUND((m.estoque_atual / m.estoque_maximo) * 100, 1) as percentual_maximo
                  FROM materiais m
                  LEFT JOIN empresas_terceirizadas e ON m.empresa_id = e.id
                  WHERE m.ativo = 1 AND m.estoque_atual > m.estoque_maximo";
        
        $query = aplicarFiltroEmpresa($query, 'm');
        
        if (isset($_GET['empresa_id']) && !empty($_GET['empresa_id'])) {
            $query .= " AND m.empresa_id = " . intval($_GET['empresa_id']);
        }
        
        $query .= " ORDER BY percentual_maximo DESC";
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }


}

// ENTRADA
if ($tipo === 'entrada') {
    if ($acao === 'listar') {
        $query = 'SELECT me.*, m.nome as material_nome, u.nome as responsavel_nome, l.nome as local_nome 
                  FROM movimentacoes_entrada me 
                  LEFT JOIN materiais m ON me.material_id = m.id 
                  LEFT JOIN usuarios u ON me.responsavel_id = u.id 
                  LEFT JOIN locais_armazenamento l ON me.local_destino_id = l.id 
                  ORDER BY me.data_entrada DESC';
        
        // Paginação
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        $query .= " LIMIT $limit OFFSET $offset";
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }
    
    if ($acao === 'criar_multipla') {
        if (!isset($_SESSION['usuario_perfil']) || ($_SESSION['usuario_perfil'] != 1 && $_SESSION['usuario_perfil'] != 2)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores e gestores podem registrar entradas']);
            exit;
        }

        $itens = $dados['itens'] ?? [];
        if (empty($itens) || !is_array($itens)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Nenhum item informado']);
            exit;
        }

        $conn->begin_transaction();
        try {
            $stmtInsert = $conn->prepare('INSERT INTO movimentacoes_entrada (data_entrada, material_id, quantidade, nota_fiscal, responsavel_id, local_destino_id, observacao) VALUES (?, ?, ?, ?, ?, ?, ?)');
            
            foreach ($itens as $item) {
                $material_id = intval($item['material_id']);
                $quantidade = floatval($item['quantidade']);
                
                if (!$material_id || $quantidade <= 0) {
                    throw new Exception('Material e quantidade inválidos em um dos itens');
                }

                // Verificar material
                $stmtMat = $conn->prepare('SELECT id, estoque_atual FROM materiais WHERE id = ? AND ativo = 1');
                $stmtMat->bind_param('i', $material_id);
                $stmtMat->execute();
                $material = $stmtMat->get_result()->fetch_assoc();

                if (!$material) {
                    throw new Exception("Material ID $material_id não encontrado ou inativo");
                }

                // Inserir movimentação
                // data(s), material(i), qtd(d), nota(s), resp(i), local(i), obs(s)
                $stmtInsert->bind_param('sidisis', 
                    $item['data_entrada'], 
                    $material_id, 
                    $quantidade, 
                    $item['nota_fiscal'], 
                    $item['responsavel_id'], 
                    $item['local_destino_id'], 
                    $item['observacao']
                );
                $stmtInsert->execute();

                // Atualizar estoque
                $novo_estoque = $material['estoque_atual'] + $quantidade;
                $local_destino_id = $item['local_destino_id'] ?? null;

                if ($local_destino_id) {
                    $stmtUpd = $conn->prepare('UPDATE materiais SET estoque_atual = ?, local_id = ? WHERE id = ?');
                    $stmtUpd->bind_param('dii', $novo_estoque, $local_destino_id, $material_id);
                } else {
                    $stmtUpd = $conn->prepare('UPDATE materiais SET estoque_atual = ? WHERE id = ?');
                    $stmtUpd->bind_param('di', $novo_estoque, $material_id);
                }
                $stmtUpd->execute();
            }

            $conn->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => count($itens) . ' entradas registradas com sucesso']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao registrar entradas: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($acao === 'criar') {
        if (!isset($_SESSION['usuario_perfil']) || ($_SESSION['usuario_perfil'] != 1 && $_SESSION['usuario_perfil'] != 2)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores e gestores podem registrar entradas']);
            exit;
        }
        
        $material_id = intval($dados['material_id']);
        $quantidade = floatval($dados['quantidade']);
        
        if (!$material_id || $quantidade <= 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Material e quantidade obrigatórios']);
            exit;
        }
        
        // Verificar se material existe e está ativo
        $stmt = $conn->prepare('SELECT id, estoque_atual FROM materiais WHERE id = ? AND ativo = 1');
        $stmt->bind_param('i', $material_id);
        $stmt->execute();
        $material = $stmt->get_result()->fetch_assoc();
        
        if (!$material) {
            echo json_encode(['sucesso' => false, 'erro' => 'Material não encontrado']);
            exit;
        }
        
        $conn->begin_transaction();
        try {
            // Inserir movimentação
            $stmt = $conn->prepare('INSERT INTO movimentacoes_entrada (data_entrada, material_id, quantidade, nota_fiscal, responsavel_id, local_destino_id, observacao) VALUES (?, ?, ?, ?, ?, ?, ?)');
            // s=string, i=int, d=double
            // data(s), material(i), qtd(d), nota(s), resp(i), local(i), obs(s)
            $stmt->bind_param('sidisis', $dados['data_entrada'], $material_id, $quantidade, $dados['nota_fiscal'], $dados['responsavel_id'], $dados['local_destino_id'], $dados['observacao']);
            $stmt->execute();
            
            // Atualizar estoque e local do material
            $novo_estoque = $material['estoque_atual'] + $quantidade;
            $local_destino_id = $dados['local_destino_id'] ?? null;

            if ($local_destino_id) {
                // Atualizar estoque e local do material
                $stmt = $conn->prepare('UPDATE materiais SET estoque_atual = ?, local_id = ? WHERE id = ?');
                $stmt->bind_param('dii', $novo_estoque, $local_destino_id, $material_id);
            } else {
                // Atualizar apenas estoque se não for especificado local
                $stmt = $conn->prepare('UPDATE materiais SET estoque_atual = ? WHERE id = ?');
                $stmt->bind_param('di', $novo_estoque, $material_id);
            }
            $stmt->execute();
            
            $conn->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Entrada registrada e estoque atualizado']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao registrar entrada: ' . $e->getMessage()]);
        }
        exit;
    }
    if ($acao === 'atualizar') {
        if (!isset($_SESSION['usuario_perfil']) || ($_SESSION['usuario_perfil'] != 1 && $_SESSION['usuario_perfil'] != 2)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores e gestores podem editar entradas']);
            exit;
        }

        $id = intval($dados['id']);
        $nova_quantidade = floatval($dados['quantidade']);
        
        if (!$id || $nova_quantidade <= 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID e quantidade válida são obrigatórios']);
            exit;
        }

        $conn->begin_transaction();
        try {
            // 1. Buscar dados atuais da movimentação e do material
            $stmt = $conn->prepare('SELECT me.quantidade as qtd_antiga, me.material_id, m.estoque_atual, m.nome as material_nome 
                                    FROM movimentacoes_entrada me 
                                    JOIN materiais m ON me.material_id = m.id 
                                    WHERE me.id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $atual = $stmt->get_result()->fetch_assoc();

            if (!$atual) {
                throw new Exception('Movimentação não encontrada');
            }

            // 2. Calcular diferença
            $diferenca = $nova_quantidade - $atual['qtd_antiga'];

            // 3. Validar se o estoque suporta a redução (se houver)
            // 3. Validar se o estoque suporta a redução (se houver)
            // Se diferença for negativa (ex: era 10, virou 8 => diff -2), estoque deve ser >= 2
            if ($diferenca < 0 && ($atual['estoque_atual'] + $diferenca) < 0) {
                throw new Exception("Estoque insuficiente para esta alteração. Estoque total: {$atual['estoque_atual']}, Redução necessária: " . abs($diferenca));
            }
            
            // Validar estoque local se houver redução ou mudança de local
            $stmt = $conn->prepare('SELECT local_destino_id FROM movimentacoes_entrada WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $local_antigo_id = $stmt->get_result()->fetch_assoc()['local_destino_id'];
            $local_novo_id = isset($dados['local_destino_id']) ? intval($dados['local_destino_id']) : $local_antigo_id;
            
            // Se mudou de local, o local antigo perde toda a quantidade antiga.
            if ($local_antigo_id != $local_novo_id) {
                if ($local_antigo_id) {
                    $estoque_local_antigo = calcularEstoqueLocal($conn, $atual['material_id'], $local_antigo_id);
                    if ($estoque_local_antigo < $atual['qtd_antiga']) {
                         throw new Exception("Não é possível mudar o local. O local de origem (ID $local_antigo_id) ficaria negativo. Saldo: $estoque_local_antigo, Necessário: {$atual['qtd_antiga']}");
                    }
                }
            } 
            // Se não mudou de local, mas reduziu quantidade
            elseif ($diferenca < 0 && $local_antigo_id) {
                $estoque_local = calcularEstoqueLocal($conn, $atual['material_id'], $local_antigo_id);
                // O estoque local já inclui essa entrada. Se vamos reduzir X, precisamos ter X disponível? 
                // Não, o estoque local ATUAL inclui a entrada. Se reduzirmos a entrada em 2, o estoque local cai 2.
                // O problema é se o estoque local for MENOR que a redução (impossível matematicamente se a entrada faz parte do saldo, a menos que o saldo já esteja errado/negativo por outros motivos).
                // Mas se o usuário já consumiu itens dessa entrada, o saldo local pode ser menor que a quantidade da entrada.
                // Ex: Entrada 10. Saída 8. Saldo 2.
                // Editar entrada para 5 (redução de 5). Novo saldo seria 2 - 5 = -3. ERRO.
                
                if (($estoque_local + $diferenca) < 0) {
                     throw new Exception("Estoque insuficiente no local ID $local_antigo_id para esta redução. Saldo: $estoque_local, Redução: " . abs($diferenca));
                }
            }

            // 4. Atualizar movimentação
            $stmt = $conn->prepare('UPDATE movimentacoes_entrada SET quantidade = ?, local_destino_id = ?, nota_fiscal = ?, observacao = ? WHERE id = ?');
            $local_destino_id = isset($dados['local_destino_id']) ? intval($dados['local_destino_id']) : null;
            $stmt->bind_param('dissi', $nova_quantidade, $local_destino_id, $dados['nota_fiscal'], $dados['observacao'], $id);
            $stmt->execute();

            // 5. Atualizar estoque do material
            if ($diferenca != 0) {
                $novo_estoque = $atual['estoque_atual'] + $diferenca;
                $stmt = $conn->prepare('UPDATE materiais SET estoque_atual = ? WHERE id = ?');
                $stmt->bind_param('di', $novo_estoque, $atual['material_id']);
                $stmt->execute();
            }

            $conn->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Entrada atualizada com sucesso']);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($acao === 'excluir') {
        if (!isset($_SESSION['usuario_perfil']) || ($_SESSION['usuario_perfil'] != 1 && $_SESSION['usuario_perfil'] != 2)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores e gestores podem excluir entradas']);
            exit;
        }

        $id = intval($dados['id']);
        if (!$id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID da entrada não informado']);
            exit;
        }

        $conn->begin_transaction();
        try {
            // 1. Buscar dados da entrada para saber quanto estornar
            $stmt = $conn->prepare('SELECT me.quantidade, me.material_id, m.estoque_atual, m.nome as material_nome 
                                    FROM movimentacoes_entrada me 
                                    JOIN materiais m ON me.material_id = m.id 
                                    WHERE me.id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $entrada = $stmt->get_result()->fetch_assoc();

            if (!$entrada) {
                throw new Exception('Entrada não encontrada');
            }

            // 2. Verificar se há estoque suficiente para remover a entrada (Global e Local)
            if ($entrada['estoque_atual'] < $entrada['quantidade']) {
                throw new Exception("Não é possível excluir esta entrada pois o estoque total ({$entrada['estoque_atual']}) é menor que a quantidade da entrada ({$entrada['quantidade']}).");
            }
            
            // Buscar local da entrada
            $stmt = $conn->prepare('SELECT local_destino_id FROM movimentacoes_entrada WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $local_id = $stmt->get_result()->fetch_assoc()['local_destino_id'];
            
            if ($local_id) {
                $estoque_local = calcularEstoqueLocal($conn, $entrada['material_id'], $local_id);
                if ($estoque_local < $entrada['quantidade']) {
                    throw new Exception("Não é possível excluir esta entrada pois o estoque no local ID $local_id ($estoque_local) é menor que a quantidade da entrada ({$entrada['quantidade']}).");
                }
            }

            // 3. Excluir a movimentação
            $stmt = $conn->prepare('DELETE FROM movimentacoes_entrada WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();

            // 4. Atualizar o estoque (subtrair)
            $novo_estoque = $entrada['estoque_atual'] - $entrada['quantidade'];
            $stmt = $conn->prepare('UPDATE materiais SET estoque_atual = ? WHERE id = ?');
            $stmt->bind_param('di', $novo_estoque, $entrada['material_id']);
            $stmt->execute();

            $conn->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Entrada excluída e estoque estornado com sucesso']);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
        exit;
    }
}

// SAÍDA DE MATERIAIS
if ($tipo === 'saida') {
    if ($acao === 'listar') {
        $query = 'SELECT ms.*, m.nome as material_nome, e.nome as empresa_nome
                  FROM movimentacoes_saida ms 
                  LEFT JOIN materiais m ON ms.material_id = m.id 
                  LEFT JOIN empresas_terceirizadas e ON ms.empresa_solicitante_id = e.id
                  ORDER BY ms.data_saida DESC';
        
        // Paginação
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        $query .= " LIMIT $limit OFFSET $offset";
        
        $result = $conn->query($query);
        $dados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }
    
    if ($acao === 'criar') {
        $material_id = intval($dados['material_id']);
        $quantidade = floatval($dados['quantidade']);
        
        if (!$material_id || $quantidade <= 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Material e quantidade obrigatórios']);
            exit;
        }
        
        $conn->begin_transaction();
        try {
            // Verificar estoque disponível e local do material
            $stmt = $conn->prepare('SELECT estoque_atual, local_id FROM materiais WHERE id = ?');
            $stmt->bind_param('i', $material_id);
            $stmt->execute();
            $material = $stmt->get_result()->fetch_assoc();

            if (!$material) {
                throw new Exception('Material não encontrado');
            }

            if ($material['estoque_atual'] < $quantidade) {
                throw new Exception('Estoque insuficiente. Disponível: ' . $material['estoque_atual']);
            }

            // Verificar se o local de origem coincide com o local atual do material (se local de origem for especificado)
            if (!empty($dados['local_origem_id'])) {
                $local_origem_id = intval($dados['local_origem_id']);
                
                // Validação 1: O material está no local? (Lógica antiga, talvez redundante com a nova, mas mantendo por segurança)
                if ($material['local_id'] != $local_origem_id) {
                     // Opcional: remover esta validação se o sistema permitir saídas de locais diferentes do "principal"
                     // throw new Exception('O material não está no local de origem selecionado. Local atual: ' . ($material['local_id'] ?? 'Não definido'));
                }
                
                // Validação 2: Tem saldo neste local?
                $estoque_local = calcularEstoqueLocal($conn, $material_id, $local_origem_id);
                if ($estoque_local < $quantidade) {
                    throw new Exception("Estoque insuficiente no local ID $local_origem_id. Disponível: $estoque_local");
                }
            }

            // Registrar saída
            $stmt = $conn->prepare('INSERT INTO movimentacoes_saida
                (data_saida, material_id, quantidade, empresa_solicitante_id, local_origem_id, finalidade, observacao)
                VALUES (?, ?, ?, ?, ?, ?, ?)');

            $data_saida = $dados['data_saida'];
            $empresa_id = isset($dados['empresa_solicitante_id']) ? intval($dados['empresa_solicitante_id']) : null;
            $local_origem_id = isset($dados['local_origem_id']) ? intval($dados['local_origem_id']) : null;
            $local_origem_id = isset($dados['local_origem_id']) ? intval($dados['local_origem_id']) : null;
            // Truncar finalidade para evitar erro de banco (limite provável de 50 chars)
            $finalidade = isset($dados['finalidade']) ? substr($dados['finalidade'], 0, 50) : '';
            $observacao = $dados['observacao'] ?? '';
            $observacao = $dados['observacao'] ?? '';

            // data(s), material(i), qtd(d), empresa(i), local(i), fin(s), obs(s)
            $stmt->bind_param('sidisss',
                $data_saida,
                $material_id,
                $quantidade,
                $empresa_id,
                $local_origem_id,
                $finalidade,
                $observacao
            );
            $stmt->execute();

            // Atualizar estoque
            $novo_estoque = $material['estoque_atual'] - $quantidade;
            $stmt = $conn->prepare('UPDATE materiais SET estoque_atual = ? WHERE id = ?');
            $stmt->bind_param('di', $novo_estoque, $material_id);
            $stmt->execute();
            
            $conn->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Saída registrada e estoque atualizado']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao registrar saída: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($acao === 'atualizar') {
        if (!isset($_SESSION['usuario_perfil']) || ($_SESSION['usuario_perfil'] != 1 && $_SESSION['usuario_perfil'] != 2)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores e gestores podem editar saídas']);
            exit;
        }

        $id = intval($dados['id']);
        $nova_quantidade = floatval($dados['quantidade']);
        
        if (!$id || $nova_quantidade <= 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID e quantidade válida são obrigatórios']);
            exit;
        }

        $conn->begin_transaction();
        try {
            // 1. Buscar dados atuais
            $stmt = $conn->prepare('SELECT ms.quantidade as qtd_antiga, ms.material_id, m.estoque_atual, m.nome as material_nome 
                                    FROM movimentacoes_saida ms 
                                    JOIN materiais m ON ms.material_id = m.id 
                                    WHERE ms.id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $atual = $stmt->get_result()->fetch_assoc();

            if (!$atual) {
                throw new Exception('Movimentação não encontrada');
            }

            // 2. Calcular diferença
            // Se nova=15, antiga=10 => diff=5 (saiu +5, estoque diminui 5)
            // Se nova=5, antiga=10 => diff=-5 (saiu -5, estoque aumenta 5)
            $diferenca = $nova_quantidade - $atual['qtd_antiga'];

            // 3. Validar estoque
            // Se diff > 0 (estamos tirando mais do estoque), verificar se tem saldo
            if ($diferenca > 0 && ($atual['estoque_atual'] - $diferenca) < 0) {
                throw new Exception("Estoque insuficiente para aumentar a saída. Estoque atual: {$atual['estoque_atual']}, Necessário: $diferenca");
            }

            // 4. Atualizar movimentação
            $stmt = $conn->prepare('UPDATE movimentacoes_saida SET quantidade = ?, local_origem_id = ?, observacao = ? WHERE id = ?');
            $local_origem_id = isset($dados['local_origem_id']) ? intval($dados['local_origem_id']) : null;
            $stmt->bind_param('disi', $nova_quantidade, $local_origem_id, $dados['observacao'], $id);
            $stmt->execute();

            // 5. Atualizar estoque do material
            if ($diferenca != 0) {
                $novo_estoque = $atual['estoque_atual'] - $diferenca;
                $stmt = $conn->prepare('UPDATE materiais SET estoque_atual = ? WHERE id = ?');
                $stmt->bind_param('di', $novo_estoque, $atual['material_id']);
                $stmt->execute();
            }

            $conn->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Saída atualizada com sucesso']);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($acao === 'criar_multipla') {
        if (!isset($_SESSION['usuario_perfil']) || ($_SESSION['usuario_perfil'] != 1 && $_SESSION['usuario_perfil'] != 2)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Apenas administradores e gestores podem registrar saídas']);
            exit;
        }

        $material_id = intval($dados['material_id']);
        $quantidade_total = floatval($dados['quantidade_total']);
        $saidas_por_local = $dados['saidas_por_local'] ?? [];

        if (!$material_id || $quantidade_total <= 0 || empty($saidas_por_local)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Material, quantidade total e saídas por local são obrigatórios']);
            exit;
        }

        $conn->begin_transaction();
        try {
            // Verificar estoque total disponível e consolidado
            $stmt = $conn->prepare('
                SELECT
                    m.estoque_atual,
                    m.empresa_id,
                    COALESCE(SUM(me.quantidade), 0) as entrada_total,
                    COALESCE(SUM(
                        CASE
                            WHEN ms.local_origem_id IS NOT NULL THEN ms.quantidade
                            ELSE 0
                        END
                    ), 0) as saida_total
                FROM materiais m
                LEFT JOIN movimentacoes_entrada me ON m.id = me.material_id
                LEFT JOIN movimentacoes_saida ms ON m.id = ms.material_id
                WHERE m.id = ?
                GROUP BY m.id, m.estoque_atual, m.empresa_id
            ');
            $stmt->bind_param('i', $material_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $material = $result->fetch_assoc();

            if (!$material) {
                throw new Exception('Material não encontrado');
            }

            $estoque_disponivel = $material['entrada_total'] - $material['saida_total'];

            if ($estoque_disponivel < $quantidade_total) {
                throw new Exception("Estoque insuficiente. Disponível: {$estoque_disponivel}, solicitado: {$quantidade_total}");
            }

            // Verificar se as quantidades por local são válidas
            $quantidade_total_informada = 0;
            foreach ($saidas_por_local as $saida_local) {
                $local_id = intval($saida_local['local_id']);
                $quantidade_local = floatval($saida_local['quantidade']);

                if ($quantidade_local <= 0) {
                    throw new Exception("Quantidade inválida para o local ID {$local_id}");
                }

                $quantidade_total_informada += $quantidade_local;

                // Verificar estoque específico para este local (Corrigido para evitar produto cartesiano)
                $stmt = $conn->prepare('
                    SELECT
                        COALESCE(entradas.total, 0) as entrada_local,
                        COALESCE(saidas.total, 0) as saida_local
                    FROM (SELECT 1) as dummy
                    LEFT JOIN (
                        SELECT SUM(quantidade) as total
                        FROM movimentacoes_entrada
                        WHERE material_id = ? AND local_destino_id = ?
                    ) entradas ON 1=1
                    LEFT JOIN (
                        SELECT SUM(quantidade) as total
                        FROM movimentacoes_saida
                        WHERE material_id = ? AND local_origem_id = ?
                    ) saidas ON 1=1
                ');
                $stmt->bind_param('iiii', $material_id, $local_id, $material_id, $local_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $estoque_local = $result->fetch_assoc();

                $estoque_disponivel_local = $estoque_local['entrada_local'] - $estoque_local['saida_local'];

                if ($estoque_disponivel_local < $quantidade_local) {
                    throw new Exception("Estoque insuficiente no local. Local: {$local_id}, disponível: {$estoque_disponivel_local}, solicitado: {$quantidade_local}");
                }
            }

            if (abs($quantidade_total_informada - $quantidade_total) > 0.01) { // Tolerância para floats
                throw new Exception("A soma das quantidades por local ({$quantidade_total_informada}) não corresponde à quantidade total ({$quantidade_total})");
            }

            // Registrar todas as saídas por local
            foreach ($saidas_por_local as $saida_local) {
                $local_origem_id = intval($saida_local['local_id']);
                $quantidade_local = floatval($saida_local['quantidade']);

                // Registrar saída individual
                $stmt = $conn->prepare('INSERT INTO movimentacoes_saida
                    (data_saida, material_id, quantidade, empresa_solicitante_id, local_origem_id, finalidade, observacao)
                    VALUES (?, ?, ?, ?, ?, ?, ?)');

                $data_saida = $dados['data_saida'];
                $empresa_id = isset($dados['empresa_solicitante_id']) ? intval($dados['empresa_solicitante_id']) : null;
                $empresa_id = isset($dados['empresa_solicitante_id']) ? intval($dados['empresa_solicitante_id']) : null;
                // Truncar finalidade para evitar erro de banco
                $finalidade = isset($dados['finalidade']) ? substr($dados['finalidade'], 0, 50) : '';
                $observacao = $dados['observacao'] ?? '';
                $observacao = $dados['observacao'] ?? '';

                $stmt->bind_param('sidisss',
                    $data_saida,
                    $material_id,
                    $quantidade_local,
                    $empresa_id,
                    $local_origem_id,
                    $finalidade,
                    $observacao
                );
                $stmt->execute();
            }

            // Atualizar estoque global do material
            $novo_estoque = $material['estoque_atual'] - $quantidade_total;
            $stmt = $conn->prepare('UPDATE materiais SET estoque_atual = ? WHERE id = ?');
            $stmt->bind_param('di', $novo_estoque, $material_id);
            $stmt->execute();

            $conn->commit();
            echo json_encode(['sucesso' => true, 'mensagem' => 'Saída registrada com sucesso em múltiplos locais']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao registrar saída múltipla: ' . $e->getMessage()]);
        }
        exit;
    }
}

$conn->close();
error_log("API Debug - Tipo: $tipo, Ação: $acao");
echo json_encode(['sucesso' => false, 'erro' => 'Ação não encontrada', 'debug' => ['tipo' => $tipo, 'acao' => $acao, 'get_params' => $_GET]]);
?>