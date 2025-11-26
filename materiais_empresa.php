<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$empresa_id = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : 0;
$empresa_nome = isset($_GET['empresa_nome']) ? htmlspecialchars($_GET['empresa_nome']) : 'Empresa';

if ($empresa_id === 0) {
    header('Location: index.php#empresas');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiais - <?php echo $empresa_nome; ?> | Gestão de Materiais</title>
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
            max-width: 1400px;
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

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .btn-danger {
            background: #c62828;
            color: white;
        }

        .btn-danger:hover {
            background: #b71c1c;
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

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
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
                <h1>🏢 Materiais da Empresa</h1>
                <small><?php echo $empresa_nome; ?></small>
            </div>
            <a href="index.php#empresas" class="btn btn-secondary">← Voltar para Empresas</a>
        </div>

        <div class="content">
            <div id="detalhes-empresa">
                <div class="loading">
                    <div class="spinner"></div>
                    Carregando informações da empresa...
                </div>
            </div>
            <div id="alerta"></div>
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
        const empresaId = <?php echo $empresa_id; ?>;

        function mostrarAlerta(mensagem, tipo = 'success') {
            const alerta = document.getElementById('alerta');
            alerta.className = `alert alert-${tipo}`;
            alerta.innerHTML = `<strong>${tipo === 'error' ? 'Erro:' : tipo === 'success' ? 'Sucesso:' : 'Atenção:'}</strong> ${mensagem}`;
            alerta.style.display = 'block';

            setTimeout(() => {
                alerta.style.display = 'none';
            }, 5000);
        }

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
                    console.error('Resposta:', texto);
                    return { sucesso: false, erro: 'Resposta inválida do servidor' };
                }

            } catch (erro) {
                console.error('Erro na requisição:', erro);
                return { sucesso: false, erro: 'Erro de conexão' };
            }
        }

        async function carregarDetalhesEmpresa() {
            const container = document.getElementById('detalhes-empresa');

            const resultado = await chamarAPI('empresas', 'detalhes', null, `&empresa_id=${empresaId}`);

            if (resultado.sucesso && resultado.dados) {
                const empresa = resultado.dados;

                let html = `
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <h2 style="margin-top: 0; color: #1e293b;">Informações da Empresa</h2>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div><strong>Nome:</strong> <span>${empresa.nome || '-'}</span></div>
                            <div><strong>Tipo de Serviço:</strong> <span>${empresa.tipo_servico || '-'}</span></div>
                            <div><strong>Número do Contrato:</strong> <span>${empresa.numero_contrato || '-'}</span></div>
                            <div><strong>CNPJ:</strong> <span>${empresa.cnpj || '-'}</span></div>
                            <div><strong>Telefone:</strong> <span>${empresa.telefone || '-'}</span></div>
                            <div><strong>Email:</strong> <span>${empresa.email || '-'}</span></div>
                            <div><strong>Status:</strong> <span style="color: ${empresa.status === 'Ativa' ? '#10b981' : '#ef4444'}; font-weight: bold;">${empresa.status || '-'}</span></div>
                        </div>
                    </div>
                `;

                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="alert alert-error">
                        <strong>Erro:</strong> ${resultado.erro || 'Não foi possível carregar as informações da empresa'}
                    </div>
                `;
            }
        }

        async function carregarMateriais() {
            const container = document.getElementById('lista-materiais');
            
            const resultado = await chamarAPI('materiais', 'listar', null, `&empresa_id=${empresaId}`);
            
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
                                    <th>Local</th>
                                    <th>Categoria</th>
                                    <th>Ponto Reposição</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    resultado.dados.forEach(mat => {
                        const estoqueClass = mat.estoque_atual < mat.ponto_reposicao ? 'color: #c62828; font-weight: bold;' : 'color: #667eea; font-weight: bold;';
                        const nomeEscaped = (mat.nome || '').replace(/'/g, "\\'");
                        html += `
                            <tr>
                                <td><strong>${mat.nome}</strong></td>
                                <td>${mat.codigo_sku}</td>
                                <td style="font-size: 16px; ${estoqueClass}">${mat.estoque_atual}</td>
                                <td>${mat.local_nome || '-'}</td>
                                <td>${mat.categoria_nome || '-'}</td>
                                <td>${mat.ponto_reposicao || '-'}</td>
                                <td>
                                    <button class="btn btn-secondary btn-sm" onclick="editarMaterial(${mat.id})" style="margin-right: 5px;">✏️ Editar</button>
                                    <button class="btn btn-danger btn-sm" onclick="excluirMaterial(${mat.id}, '${nomeEscaped}', ${mat.estoque_atual})">🗑️ Excluir</button>
                                </td>
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
                            <p>Esta empresa ainda não possui materiais cadastrados.</p>
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

        async function editarMaterial(id) {
            alert('Função de edição em desenvolvimento. Material ID: ' + id);
            // TODO: Implementar modal de edição
        }

        async function excluirMaterial(id, nome, estoque) {
            if (estoque > 0) {
                if (!confirm(`O material "${nome}" possui estoque atual de ${estoque} unidades.\n\nTem certeza que deseja excluir?`)) {
                    return;
                }
            } else {
                if (!confirm(`Tem certeza que deseja excluir o material "${nome}"?\n\nEsta ação não pode ser desfeita.`)) {
                    return;
                }
            }

            console.log('Excluindo material ID:', id);
            const resultado = await chamarAPI('materiais', 'excluir', { id: id });
            console.log('Resultado:', resultado);
            
            if (resultado.sucesso) {
                mostrarAlerta(resultado.mensagem || 'Material excluído com sucesso!', 'success');
                carregarMateriais();
            } else {
                mostrarAlerta(resultado.erro || 'Erro ao excluir material', 'error');
            }
        }

        // Carregar informações da empresa e materiais ao abrir a página
        window.addEventListener('load', async () => {
            await carregarDetalhesEmpresa();
            carregarMateriais();
        });
    </script>
</body>
</html>
