<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar se é administrador
if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] != 1) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Sistema de Gestão</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }

        .btn-success { background: #10b981; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-primary { background: #3b82f6; color: white; }

        .btn:hover { opacity: 0.8; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f9fafb;
            font-weight: 600;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover { color: #000; }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fef2f2;
            color: #7f1d1d;
            border-left: 4px solid #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏛️ Gerenciar Usuários</h1>
            <p>Aprovar solicitações de acesso ao sistema</p>
            <button class="btn btn-primary" onclick="window.location.href='index.php'">← Voltar ao Sistema</button>
        </div>

        <div class="card">
            <h2>Solicitações Pendentes</h2>
            <div id="alerta" class="alert"></div>
            
            <div id="loading" style="text-align: center; padding: 20px;">
                <p>Carregando solicitações...</p>
            </div>
            
            <div id="tabelaContainer" style="display: none;">
                <table id="tabelaUsuarios">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Departamento</th>
                            <th>Data Solicitação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
            <div id="semDados" style="display: none; text-align: center; padding: 40px;">
                <p>Nenhuma solicitação pendente</p>
            </div>
        </div>
    </div>

    <!-- Modal de Aprovação -->
    <div id="modalAprovacao" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h2>Aprovar Usuário</h2>
            
            <div id="alertaModal" class="alert"></div>
            
            <form id="formAprovacao">
                <input type="hidden" id="usuarioId">
                
                <div class="form-group">
                    <label>Dados do Usuário:</label>
                    <div id="dadosUsuario" style="background: #f9fafb; padding: 15px; border-radius: 5px; margin-bottom: 15px;"></div>
                </div>
                
                <div class="form-group">
                    <label for="perfilId">Perfil de Acesso *</label>
                    <select id="perfilId" required>
                        <option value="">Selecione o perfil</option>
                        <option value="1">Administrador</option>
                        <option value="2">Gestor</option>
                        <option value="3">Operador</option>
                        <option value="4">Consulta</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Empresas Permitidas</label>
                    <p style="font-size: 12px; color: #666; margin-bottom: 10px;">
                        Deixe em branco para permitir todas as empresas (apenas para Administradores)
                    </p>
                    <div id="empresasContainer" class="checkbox-group"></div>
                </div>
                
                <button type="submit" class="btn btn-success">Aprovar Usuário</button>
                <button type="button" class="btn btn-danger" onclick="rejeitarUsuario()">Rejeitar</button>
                <button type="button" class="btn" onclick="fecharModal()" style="background: #6b7280; color: white;">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        let usuariosPendentes = [];
        let empresas = [];
        let usuarioSelecionado = null;

        // Carregar dados iniciais
        window.addEventListener('load', async () => {
            await carregarEmpresas();
            await carregarUsuariosPendentes();
            
            // Adicionar listener para ocultar empresas quando Administrador for selecionado
            document.getElementById('perfilId').addEventListener('change', function() {
                const perfilId = parseInt(this.value);
                const empresasContainer = document.querySelector('.form-group:has(#empresasContainer)');
                
                if (perfilId === 1) {
                    // Administrador: ocultar campo de empresas
                    if (empresasContainer) empresasContainer.style.display = 'none';
                } else {
                    // Outros perfis: mostrar campo de empresas
                    if (empresasContainer) empresasContainer.style.display = 'block';
                }
            });
        });

        async function carregarUsuariosPendentes() {
            try {
                const response = await fetch('./api_filtrada.php?tipo=usuarios&acao=pendentes');
                const resultado = await response.json();

                if (resultado.sucesso) {
                    usuariosPendentes = resultado.dados;
                    renderizarTabela();
                } else {
                    mostrarAlerta(resultado.erro, 'error');
                }
            } catch (error) {
                mostrarAlerta('Erro ao carregar solicitações', 'error');
            }
            
            document.getElementById('loading').style.display = 'none';
        }

        async function carregarEmpresas() {
            try {
                const response = await fetch('./api_filtrada.php?tipo=empresas&acao=listar');
                const resultado = await response.json();

                if (resultado.sucesso) {
                    empresas = resultado.dados;
                }
            } catch (error) {
                console.error('Erro ao carregar empresas:', error);
            }
        }

        function renderizarTabela() {
            const tbody = document.querySelector('#tabelaUsuarios tbody');
            
            if (usuariosPendentes.length === 0) {
                document.getElementById('semDados').style.display = 'block';
                return;
            }

            document.getElementById('tabelaContainer').style.display = 'block';
            
            tbody.innerHTML = usuariosPendentes.map(usuario => `
                <tr>
                    <td>${usuario.nome}</td>
                    <td>${usuario.email}</td>
                    <td>${usuario.departamento || '-'}</td>
                    <td>${new Date(usuario.data_solicitacao).toLocaleDateString('pt-BR')}</td>
                    <td>
                        <button class="btn btn-primary" onclick="abrirModalAprovacao(${usuario.id})">
                            Ver Detalhes
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function abrirModalAprovacao(usuarioId) {
            usuarioSelecionado = usuariosPendentes.find(u => u.id == usuarioId);
            
            document.getElementById('usuarioId').value = usuarioId;
            document.getElementById('dadosUsuario').innerHTML = `
                <strong>Nome:</strong> ${usuarioSelecionado.nome}<br>
                <strong>Email:</strong> ${usuarioSelecionado.email}<br>
                <strong>Departamento:</strong> ${usuarioSelecionado.departamento || 'Não informado'}<br>
                <strong>Justificativa:</strong> ${usuarioSelecionado.justificativa}
            `;
            
            // Renderizar empresas
            const container = document.getElementById('empresasContainer');
            container.innerHTML = empresas.map(empresa => `
                <div class="checkbox-item">
                    <input type="checkbox" id="empresa_${empresa.id}" value="${empresa.id}">
                    <label for="empresa_${empresa.id}">${empresa.nome}</label>
                </div>
            `).join('');
            
            document.getElementById('modalAprovacao').style.display = 'block';
        }

        function fecharModal() {
            document.getElementById('modalAprovacao').style.display = 'none';
            document.getElementById('formAprovacao').reset();
            document.getElementById('alertaModal').style.display = 'none';
        }

        document.getElementById('formAprovacao').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const empresasSelecionadas = Array.from(document.querySelectorAll('#empresasContainer input:checked'))
                .map(cb => parseInt(cb.value));
            
            const dados = {
                id: parseInt(document.getElementById('usuarioId').value),
                perfil_id: parseInt(document.getElementById('perfilId').value),
                empresas: empresasSelecionadas
            };

            try {
                const response = await fetch('./api_filtrada.php?tipo=usuarios&acao=aprovar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dados)
                });

                const resultado = await response.json();

                if (resultado.sucesso) {
                    mostrarAlerta(resultado.mensagem, 'success');
                    fecharModal();
                    await carregarUsuariosPendentes();
                } else {
                    mostrarAlertaModal(resultado.erro, 'error');
                }
            } catch (error) {
                mostrarAlertaModal('Erro ao aprovar usuário', 'error');
            }
        });

        async function rejeitarUsuario() {
            if (!confirm('Tem certeza que deseja rejeitar esta solicitação?')) return;

            const dados = {
                id: parseInt(document.getElementById('usuarioId').value)
            };

            try {
                const response = await fetch('./api_filtrada.php?tipo=usuarios&acao=rejeitar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dados)
                });

                const resultado = await response.json();

                if (resultado.sucesso) {
                    mostrarAlerta(resultado.mensagem, 'success');
                    fecharModal();
                    await carregarUsuariosPendentes();
                } else {
                    mostrarAlertaModal(resultado.erro, 'error');
                }
            } catch (error) {
                mostrarAlertaModal('Erro ao rejeitar usuário', 'error');
            }
        }

        function mostrarAlerta(mensagem, tipo) {
            const alerta = document.getElementById('alerta');
            alerta.className = `alert alert-${tipo}`;
            alerta.textContent = mensagem;
            alerta.style.display = 'block';
            
            setTimeout(() => {
                alerta.style.display = 'none';
            }, 5000);
        }

        function mostrarAlertaModal(mensagem, tipo) {
            const alerta = document.getElementById('alertaModal');
            alerta.className = `alert alert-${tipo}`;
            alerta.textContent = mensagem;
            alerta.style.display = 'block';
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('modalAprovacao');
            if (event.target === modal) {
                fecharModal();
            }
        }
    </script>
</body>
</html>