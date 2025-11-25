<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestão de Materiais</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0f172a;
            --accent-color: #2563eb;
            --accent-hover: #1d4ed8;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --bg-color: #f1f5f9;
            --radius: 0.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            display: flex;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 550px;
        }

        .login-sidebar {
            background: var(--primary-color);
            width: 45%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(15, 23, 42, 0) 100%);
            z-index: 1;
        }

        .sidebar-content {
            position: relative;
            z-index: 2;
            text-align: center;
            width: 100%;
        }

        .login-sidebar h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .login-sidebar p {
            color: #94a3b8;
            font-size: 1.1rem;
            margin-bottom: 40px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            color: #cbd5e1;
        }

        .feature-item i {
            margin-right: 15px;
            color: var(--accent-color);
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-main {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .logo-mobile {
            display: none;
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h1 {
            font-size: 1.75rem;
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .login-header p {
            color: var(--text-secondary);
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        input, textarea {
            width: 100%;
            padding: 12px 16px 12px 45px;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 0.95rem;
            color: var(--text-primary);
            transition: all 0.2s;
            font-family: inherit;
        }
        
        textarea {
            padding: 12px 16px;
            resize: vertical;
            min-height: 100px;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: var(--accent-color);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: white;
            color: var(--text-primary);
            border: 1px solid #e2e8f0;
            margin-top: 15px;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .alert {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 25px;
            font-size: 0.9rem;
            display: none;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--radius);
            width: 90%;
            max-width: 500px;
            padding: 30px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .modal-header h2 {
            font-size: 1.25rem;
            color: var(--primary-color);
        }

        .close {
            font-size: 1.5rem;
            color: var(--text-secondary);
            cursor: pointer;
            transition: color 0.2s;
        }

        .close:hover {
            color: var(--text-primary);
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(37, 99, 235, 0.3);
            border-radius: 50%;
            border-top-color: var(--accent-color);
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                max-width: 450px;
            }

            .login-sidebar {
                display: none;
            }

            .logo-mobile {
                display: block;
            }

            .login-main {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Sidebar (Desktop) -->
        <div class="login-sidebar">
            <div class="sidebar-content">
                <img src="logo_ufrpe-uast.png" alt="Logo UFRPE UAST" style="max-width: 180px; margin-bottom: 30px;">
                <h2>Gestão de<br>Materiais</h2>
                <p style="margin-top: 10px; opacity: 0.8;">Sistema Integrado</p>
                <div style="margin-top: 40px;">
                    <div class="feature-item">
                        <i class="fas fa-boxes"></i>
                        <span>Controle total de estoque</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Relatórios detalhados</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Acesso seguro e auditável</span>
                    </div>
            </div>
        </div>
    </div>

        <!-- Main Content -->
        <div class="login-main">

            <div class="logo-mobile">
                <img src="logo_ufrpe-uast.png" alt="Logo UFRPE UAST" style="max-width: 120px; margin-bottom: 15px;">
                <h2 style="margin-top: 10px;">Gestão de Materiais</h2>
            </div>

            <div class="login-header">
                <h1>Bem-vindo de volta</h1>
                <p>Insira suas credenciais para acessar o sistema.</p>
            </div>

            <div id="alerta" class="alert"></div>

            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
                    </div>
                </div>

                <button type="submit" class="btn" id="btnLogin">
                    <span>Entrar no Sistema</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div> Verificando credenciais...
                </div>
            </form>

            <button type="button" class="btn btn-secondary" onclick="abrirModalCadastro()">
                Não tem conta? Solicite acesso
            </button>

            <div class="footer" style="display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap; margin-top: 40px;">
                <img src="logo-devops.png" alt="Logo STI" style="height: 30px;">
                <span>&copy; 2025 UAST/UFRPE - Desenvolvido pelo <a href="https://uast.ufrpe.br/sti" target="_blank" style="color: var(--accent-color); text-decoration: none; font-weight: 500;">STI-UAST</a></span>
            </div>
        </div>
    </div>

    <!-- Modal de Cadastro -->
    <div id="modalCadastro" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Solicitar Acesso</h2>
                <span class="close" onclick="fecharModalCadastro()">&times;</span>
            </div>
            
            <div id="alertaCadastro" class="alert"></div>
            
            <form id="cadastroForm">
                <div class="form-group">
                    <label for="cadastroNome">Nome Completo *</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="cadastroNome" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cadastroEmail">Email Institucional *</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="cadastroEmail" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="cadastroSenha">Senha *</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="cadastroSenha" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="cadastroDepartamento">Departamento</label>
                    <div class="input-group">
                        <i class="fas fa-building"></i>
                        <input type="text" id="cadastroDepartamento">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="cadastroJustificativa">Justificativa *</label>
                    <textarea id="cadastroJustificativa" placeholder="Explique por que precisa de acesso..." required></textarea>
                </div>
                
                <button type="submit" class="btn" id="btnCadastro">Enviar Solicitação</button>
                
                <div class="loading" id="loadingCadastro">
                    <div class="spinner"></div> Enviando solicitação...
                </div>
            </form>
        </div>
    </div>

    <script>
        const API_URL = './api.php';

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            
            if (!email || !senha) {
                mostrarAlerta('Preencha todos os campos', 'error');
                return;
            }

            mostrarLoading(true);

            try {
                const response = await fetch('./api_filtrada.php?tipo=auth&acao=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email, senha })
                });

                const resultado = await response.json();

                if (resultado.sucesso) {
                    mostrarAlerta('Login realizado com sucesso!', 'success');
                    
                    // Salvar dados do usuário na sessão
                    localStorage.setItem('usuario_logado', JSON.stringify(resultado.dados));
                    
                    // Redirecionar para o sistema
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    mostrarAlerta(resultado.erro, 'error');
                }
            } catch (error) {
                mostrarAlerta('Erro ao conectar com o servidor', 'error');
            }

            mostrarLoading(false);
        });

        function mostrarAlerta(mensagem, tipo) {
            const alerta = document.getElementById('alerta');
            alerta.className = `alert alert-${tipo}`;
            alerta.textContent = mensagem;
            alerta.style.display = 'block';
            
            setTimeout(() => {
                alerta.style.display = 'none';
            }, 5000);
        }

        function mostrarLoading(mostrar) {
            document.getElementById('loading').style.display = mostrar ? 'block' : 'none';
            document.getElementById('btnLogin').style.display = mostrar ? 'none' : 'flex';
        }

        // Funções do modal de cadastro
        function abrirModalCadastro() {
            document.getElementById('modalCadastro').style.display = 'flex';
        }

        function fecharModalCadastro() {
            document.getElementById('modalCadastro').style.display = 'none';
            document.getElementById('cadastroForm').reset();
            document.getElementById('alertaCadastro').style.display = 'none';
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('modalCadastro');
            if (event.target === modal) {
                fecharModalCadastro();
            }
        }

        // Form de cadastro
        document.getElementById('cadastroForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const dados = {
                nome: document.getElementById('cadastroNome').value,
                email: document.getElementById('cadastroEmail').value,
                senha: document.getElementById('cadastroSenha').value,
                departamento: document.getElementById('cadastroDepartamento').value,
                justificativa: document.getElementById('cadastroJustificativa').value
            };
            
            if (!dados.nome || !dados.email || !dados.senha || !dados.justificativa) {
                mostrarAlertaCadastro('Preencha todos os campos obrigatórios', 'error');
                return;
            }

            mostrarLoadingCadastro(true);

            try {
                const response = await fetch('./api_filtrada.php?tipo=auth&acao=cadastrar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dados)
                });

                const resultado = await response.json();

                if (resultado.sucesso) {
                    mostrarAlertaCadastro(resultado.mensagem, 'success');
                    setTimeout(() => {
                        fecharModalCadastro();
                    }, 2000);
                } else {
                    mostrarAlertaCadastro(resultado.erro, 'error');
                }
            } catch (error) {
                mostrarAlertaCadastro('Erro ao conectar com o servidor', 'error');
            }

            mostrarLoadingCadastro(false);
        });

        function mostrarAlertaCadastro(mensagem, tipo) {
            const alerta = document.getElementById('alertaCadastro');
            alerta.className = `alert alert-${tipo}`;
            alerta.textContent = mensagem;
            alerta.style.display = 'block';
        }

        function mostrarLoadingCadastro(mostrar) {
            document.getElementById('loadingCadastro').style.display = mostrar ? 'block' : 'none';
            document.getElementById('btnCadastro').style.display = mostrar ? 'none' : 'block';
        }

        // Verificar se já está logado
        window.addEventListener('load', () => {
            const usuarioLogado = localStorage.getItem('usuario_logado');
            if (usuarioLogado) {
                window.location.href = 'index.php';
            }
        });
    </script>
</body>
</html>