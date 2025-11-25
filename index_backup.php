<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestão - Versão Simplificada</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #1e40af; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .menu { display: flex; gap: 10px; margin-bottom: 20px; }
        .menu button { padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .menu button:hover { background: #1e40af; }
        .section { display: none; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section.active { display: block; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        .btn { padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #1e40af; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f5f5f5; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏛️ Sistema de Gestão de Materiais</h1>
            <p>Versão Simplificada - Universidade</p>
        </div>

        <div class="menu">
            <button onclick="mostrarSecao('dashboard')">Dashboard</button>
            <button onclick="mostrarSecao('empresas')">Empresas</button>
            <button onclick="mostrarSecao('materiais')">Materiais</button>
            <button onclick="mostrarSecao('teste')">Teste Conexão</button>
        </div>

        <div id="alertas"></div>

        <!-- DASHBOARD -->
        <div id="dashboard" class="section active">
            <h2>Dashboard</h2>
            <p>Sistema funcionando corretamente!</p>
            <div id="resumo">
                <p>Total de Empresas: <span id="total-empresas">0</span></p>
                <p>Total de Materiais: <span id="total-materiais">0</span></p>
            </div>
        </div>

        <!-- EMPRESAS -->
        <div id="empresas" class="section">
            <h2>Cadastrar Empresa</h2>
            <div class="form-group">
                <label>Nome da Empresa</label>
                <input type="text" id="emp-nome" placeholder="Nome da empresa">
            </div>
            <div class="form-group">
                <label>Tipo de Serviço</label>
                <input type="text" id="emp-tipo" placeholder="Ex: Limpeza, Manutenção">
            </div>
            <div class="form-group">
                <label>Número do Contrato</label>
                <input type="text" id="emp-contrato" placeholder="Ex: CT-2024-001">
            </div>
            <button class="btn" onclick="salvarEmpresa()">Cadastrar</button>
            
            <div id="lista-empresas"></div>
        </div>

        <!-- MATERIAIS -->
        <div id="materiais" class="section">
            <h2>Cadastrar Material</h2>
            <div class="form-group">
                <label>Nome do Material</label>
                <input type="text" id="mat-nome" placeholder="Nome do material">
            </div>
            <div class="form-group">
                <label>Código SKU</label>
                <input type="text" id="mat-sku" placeholder="SKU-001">
            </div>
            <button class="btn" onclick="salvarMaterial()">Cadastrar</button>
            
            <div id="lista-materiais"></div>
        </div>

        <!-- TESTE -->
        <div id="teste" class="section">
            <h2>Teste de Conexão</h2>
            <button class="btn" onclick="testarConexao()">Testar API</button>
            <div id="resultado-teste"></div>
        </div>
    </div>

    <script>
        function mostrarSecao(secao) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.getElementById(secao).classList.add('active');
        }

        function mostrarAlerta(msg, tipo) {
            const alertas = document.getElementById('alertas');
            alertas.innerHTML = `<div class="alert alert-${tipo}">${msg}</div>`;
            setTimeout(() => alertas.innerHTML = '', 3000);
        }

        async function testarConexao() {
            try {
                const response = await fetch('./api.php?tipo=teste&acao=teste');
                const resultado = await response.json();
                document.getElementById('resultado-teste').innerHTML = 
                    `<p>Status: ${resultado.sucesso ? 'Sucesso' : 'Erro'}</p>
                     <p>Mensagem: ${resultado.mensagem || resultado.erro}</p>`;
            } catch (error) {
                document.getElementById('resultado-teste').innerHTML = 
                    `<p>Erro: ${error.message}</p>`;
            }
        }

        async function salvarEmpresa() {
            const dados = {
                nome: document.getElementById('emp-nome').value,
                tipo_servico: document.getElementById('emp-tipo').value,
                numero_contrato: document.getElementById('emp-contrato').value,
                responsavel_id: 1
            };

            if (!dados.nome) {
                mostrarAlerta('Preencha o nome da empresa', 'error');
                return;
            }

            try {
                const response = await fetch('./api.php?tipo=empresas&acao=criar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dados)
                });
                
                const resultado = await response.json();
                
                if (resultado.sucesso) {
                    mostrarAlerta('Empresa cadastrada!', 'success');
                    document.getElementById('emp-nome').value = '';
                    document.getElementById('emp-tipo').value = '';
                    document.getElementById('emp-contrato').value = '';
                } else {
                    mostrarAlerta('Erro: ' + resultado.erro, 'error');
                }
            } catch (error) {
                mostrarAlerta('Erro de conexão: ' + error.message, 'error');
            }
        }

        async function salvarMaterial() {
            const dados = {
                nome: document.getElementById('mat-nome').value,
                codigo_sku: document.getElementById('mat-sku').value,
                categoria_id: 1,
                unidade_medida_id: 1,
                empresa_id: 1,
                local_id: 1,
                estoque_atual: 0,
                ponto_reposicao: 0,
                estoque_maximo: 0
            };

            if (!dados.nome) {
                mostrarAlerta('Preencha o nome do material', 'error');
                return;
            }

            try {
                const response = await fetch('./api.php?tipo=materiais&acao=criar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dados)
                });
                
                const resultado = await response.json();
                
                if (resultado.sucesso) {
                    mostrarAlerta('Material cadastrado!', 'success');
                    document.getElementById('mat-nome').value = '';
                    document.getElementById('mat-sku').value = '';
                } else {
                    mostrarAlerta('Erro: ' + resultado.erro, 'error');
                }
            } catch (error) {
                mostrarAlerta('Erro de conexão: ' + error.message, 'error');
            }
        }
    </script>
</body>
</html>