<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$local_id = isset($_GET['local_id']) ? intval($_GET['local_id']) : 0;
$local_nome = isset($_GET['local_nome']) ? htmlspecialchars($_GET['local_nome']) : 'Local';

if ($local_id === 0) {
    header('Location: index.php#locais');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiais - <?php echo $local_nome; ?> | Gestão de Materiais</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }

        .header small {
            display: block;
            margin-top: 5px;
            opacity: 0.9;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.3);
        }

        .content {
            padding: 30px;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-info {
            background: #e3f2fd;
            color: #1976d2;
            border-left: 4px solid #1976d2;
        }

        .alert-warning {
            background: #fff3e0;
            color: #f57c00;
            border-left: 4px solid #f57c00;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background: #f5f5f5;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            font-weight: 600;
            color: #333;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>📦 Materiais no Local</h1>
                <small><?php echo $local_nome; ?></small>
            </div>
            <a href="index.php#locais" class="btn btn-secondary">← Voltar para Locais</a>
        </div>

        <div class="content">
            <div id="lista-materiais">
                <div class="loading">
                    <div class="spinner"></div>
                    Carregando materiais...
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = './api_filtrada.php';
        const localId = <?php echo $local_id; ?>;

        async function chamarAPI(tipo, acao, dados = null, parametrosExtras = '') {
            try {
                const url = `${API_URL}?tipo=${tipo}&acao=${acao}${parametrosExtras}`;
                const opcoes = {
                    method: dados ? 'POST' : 'GET',
                    headers: { 'Content-Type': 'application/json' }
                };
                
                if (dados) opcoes.body = JSON.stringify(dados);
                
                const resposta = await fetch(url, opcoes);
                const texto = await resposta.text();
                
                try {
                    return JSON.parse(texto);
                } catch (jsonError) {
                    console.error('Erro ao parsear JSON:', jsonError);
                    return { sucesso: false, erro: 'Resposta inválida do servidor' };
                }
                
            } catch (erro) {
                console.error('Erro na requisição:', erro);
                return { sucesso: false, erro: 'Erro de conexão' };
            }
        }

        async function carregarMateriais() {
            const container = document.getElementById('lista-materiais');
            
            const resultado = await chamarAPI('materiais', 'listar', null, `&local_id=${localId}`);
            
            if (resultado.sucesso) {
                if (resultado.dados && resultado.dados.length > 0) {
                    let html = `
                        <div class="alert alert-info">
                            <strong>Total de materiais:</strong> ${resultado.dados.length}
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Código SKU</th>
                                    <th>Estoque Atual</th>
                                    <th>Unidade</th>
                                    <th>Empresa</th>
                                    <th>Categoria</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    resultado.dados.forEach(mat => {
                        html += `
                            <tr>
                                <td><strong>${mat.nome}</strong></td>
                                <td>${mat.codigo_sku}</td>
                                <td style="font-size: 16px; font-weight: bold; color: #667eea;">${mat.estoque_atual}</td>
                                <td>${mat.unidade_nome || '-'}</td>
                                <td>${mat.empresa_nome || '-'}</td>
                                <td>${mat.categoria_nome || '-'}</td>
                            </tr>
                        `;
                    });
                    
                    html += '</tbody></table>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <h3>Nenhum material encontrado</h3>
                            <p>Este local ainda não possui materiais cadastrados.</p>
                        </div>
                    `;
                }
            } else {
                container.innerHTML = `
                    <div class="alert alert-error">
                        <strong>Erro:</strong> ${resultado.erro || 'Não foi possível carregar os materiais'}
                    </div>
                `;
            }
        }

        // Carregar materiais ao abrir a página
        window.addEventListener('load', carregarMateriais);
    </script>
</body>
</html>
