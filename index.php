<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Materiais Terceirizados - Universidade</title>
    <!-- Fontes e Ícones -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- jsPDF e AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script>
        // Configuração inicial ou scripts essenciais
        // Configuração inicial ou scripts essenciais

        // Função para mostrar alertas (Modal Popup Dinâmico)
        function exibirNotificacaoSistema(msg, tipo = 'success') {
            // Remover modal anterior se existir
            const modalAnterior = document.getElementById('modal-alerta-sistema');
            if (modalAnterior) {
                modalAnterior.remove();
            }

            // Configurar ícone e cores
            let icone = '';
            let titulo = '';
            let corTitulo = '';

            if (tipo === 'success') {
                icone = '✅';
                titulo = 'Sucesso!';
                corTitulo = '#10b981';
            } else if (tipo === 'error') {
                icone = '❌';
                titulo = 'Erro!';
                corTitulo = '#ef4444';
            } else if (tipo === 'warning') {
                icone = '⚠️';
                titulo = 'Atenção!';
                corTitulo = '#f59e0b';
            }

            // Criar elementos do modal
            const modal = document.createElement('div');
            modal.id = 'modal-alerta-sistema';

            // Estilos forçados com !important para garantir sobreposição
            console.log('🚀 NOVA VERSÃO DO MODAL CHAMADA! 🚀');
            modal.style.cssText = `
                display: flex !important;
                position: fixed !important;
                z-index: 2147483647 !important; /* Máximo z-index possível */
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: 100% !important;
                background-color: rgba(15, 23, 42, 0.85) !important; /* Fundo Azul Escuro */
                justify-content: center !important;
                align-items: center !important;
                font-family: 'Inter', sans-serif !important;
            `;

            const conteudo = document.createElement('div');
            conteudo.style.cssText = `
                background-color: white !important;
                padding: 40px !important;
                border-radius: 16px !important;
                max-width: 500px !important;
                width: 90% !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
                text-align: center !important;
                position: relative !important;
                animation: fadeInScale 0.3s ease-out !important;
            `;

            // HTML interno
            conteudo.innerHTML = `
                <div style="font-size: 4rem; margin-bottom: 20px;">${icone}</div>
                <h3 style="margin-bottom: 15px; font-size: 1.6rem; font-weight: 600; color: ${corTitulo};">${titulo}</h3>
                <p style="color: #64748b; margin-bottom: 30px; font-size: 1.1rem; line-height: 1.6;">${msg}</p>
                <button id="btn-fechar-modal" style="
                    background-color: #0f172a;
                    color: white;
                    border: none;
                    padding: 12px 30px;
                    border-radius: 8px;
                    font-size: 1rem;
                    cursor: pointer;
                    transition: background 0.2s;
                ">OK</button>
                <style>
                    @keyframes fadeInScale {
                        from { opacity: 0; transform: scale(0.9); }
                        to { opacity: 1; transform: scale(1); }
                    }
                    #btn-fechar-modal:hover { background-color: #1e293b !important; }
                </style>
            `;

            modal.appendChild(conteudo);
            document.body.appendChild(modal);

            // Eventos de fechar
            const btnFechar = modal.querySelector('#btn-fechar-modal');
            btnFechar.onclick = fecharModalNotificacao;

            modal.onclick = function(e) {
                if (e.target === modal) fecharModalNotificacao();
            };

            // Focar no botão
            btnFechar.focus();
        }
        
        function fecharModalNotificacao() {
            const modal = document.getElementById('modal-alerta-sistema');
            if (modal) {
                modal.style.opacity = '0';
                modal.style.transition = 'opacity 0.2s';
                setTimeout(() => modal.remove(), 200);
            }
        }
        
        // Remover listener antigo de window click pois agora é tratado no próprio elemento
    </script>
</head>
<body>
    <div class="container">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="sidebar-header" style="flex-direction: column; text-align: center; gap: 15px;">
                <img src="logo_ufrpe-uast.png" alt="UFRPE UAST" style="max-width: 140px;">
                <h2 style="font-size: 1.1rem;">Gestão de Materiais</h2>
            </div>
            
            <div class="sidebar-menu">
                <a class="menu-item active" onclick="mostrarSecao('dashboard')">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
                <a class="menu-item" id="menu-empresas" onclick="mostrarSecao('empresas')" style="display: none;">
                    <i class="fas fa-building"></i> Empresas
                </a>
                <a class="menu-item" onclick="mostrarSecao('materiais')">
                    <i class="fas fa-boxes"></i> Materiais
                </a>
                <a class="menu-item" onclick="mostrarSecao('locais')">
                    <i class="fas fa-map-marker-alt"></i> Locais
                </a>
                <a class="menu-item" id="menu-categorias" onclick="mostrarSecao('categorias')" style="display: none;">
                    <i class="fas fa-tags"></i> Categorias
                </a>
                <a class="menu-item" id="menu-entrada" onclick="mostrarSecao('entrada')">
                    <i class="fas fa-arrow-circle-down"></i> Entrada
                </a>
                <a class="menu-item" onclick="mostrarSecao('saida')">
                    <i class="fas fa-arrow-circle-up"></i> Saída
                </a>
                <a class="menu-item" onclick="mostrarSecao('alertas')">
                    <i class="fas fa-exclamation-triangle"></i> Alertas
                </a>
                <a class="menu-item" onclick="mostrarSecao('relatorios')">
                    <i class="fas fa-file-alt"></i> Relatórios
                </a>
                <a class="menu-item" id="menu-usuarios" onclick="mostrarSecao('usuarios')">
                    <i class="fas fa-users"></i> Usuários
                </a>
                <a class="menu-item" id="menu-usuarios-pendentes" onclick="window.location.href='gerenciar_usuarios.php'" style="display: none;">
                    <i class="fas fa-user-clock"></i> Pendentes
                </a>
            </div>
            
            <div class="sidebar-footer">
                <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5);">
                    &copy; 2025 - UFRPE/UAST
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- HEADER -->
            <div class="header">
                <div>
                    <h1>Dashboard</h1>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Visão geral do sistema</p>
                </div>
                <div class="user-info">
                    <div style="text-align: right;">
                        <div id="usuario-nome" style="font-weight: 600; cursor: pointer; color: var(--primary);" onclick="mostrarSecao('perfil')" title="Clique para editar seu perfil">Usuário</div>
                        <div id="usuario-perfil" class="user-badge">Carregando...</div>
                    </div>
                    <button class="btn btn-secondary" onclick="logout()" style="padding: 8px 16px;">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </div>
            </div>

            <!-- SEÇÃO: DASHBOARD -->
            <section id="dashboard" class="section active">
                <!-- CARDS -->
                <div class="dashboard-cards" id="dashboard-cards-container">
                    <!-- Cards serão injetados via JS -->
                    <div class="loading"><div class="spinner"></div> Carregando dados...</div>
                </div>

                <!-- GRÁFICOS -->
                <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <!-- Tendência -->
                    <div class="card" style="grid-column: span 2;">
                        <h3>Tendência de Movimentações (30 Dias)</h3>
                        <div style="height: 300px;">
                            <canvas id="chart-trend"></canvas>
                        </div>
                    </div>
                    
                    <!-- Composição -->
                    <div class="card">
                        <h3>Composição de Estoque</h3>
                        <div style="height: 300px;">
                            <canvas id="chart-composition"></canvas>
                        </div>
                    </div>
                    
                    <!-- Top 5 -->
                    <div class="card">
                        <h3>Top 5 Materiais (Saídas)</h3>
                        <div style="height: 300px;">
                            <canvas id="chart-top5"></canvas>
                        </div>
                    </div>
                </div>

                <!-- TABELA: ESTOQUE DETALHADO -->
                <div class="table-container" style="margin-top: 30px;">
                    <div class="table-header" onclick="toggleEstoqueDetalhado()" style="cursor: pointer; user-select: none; display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                            Estoque Detalhado
                            <i id="icon-toggle-estoque" class="fas fa-chevron-down" style="font-size: 0.8em; transition: transform 0.3s;"></i>
                        </h2>
                    </div>
                    <div id="container-tabela-estoque" class="table-responsive" style="display: none; transition: all 0.3s ease-in-out;">
                        <table class="table-hover" id="tabela-estoque-dashboard">
                            <thead>
                                <tr>
                                    <th onclick="event.stopPropagation(); ordenarTabelaEstoque('codigo_sku')" style="cursor: pointer;">SKU <i class="fas fa-sort"></i></th>
                                    <th onclick="event.stopPropagation(); ordenarTabelaEstoque('nome')" style="cursor: pointer;">Material <i class="fas fa-sort"></i></th>
                                    <th onclick="event.stopPropagation(); ordenarTabelaEstoque('categoria_nome')" style="cursor: pointer;">Categoria <i class="fas fa-sort"></i></th>
                                    <th onclick="event.stopPropagation(); ordenarTabelaEstoque('empresa_nome')" style="cursor: pointer;">Empresa <i class="fas fa-sort"></i></th>
                                    <th onclick="event.stopPropagation(); ordenarTabelaEstoque('estoque_atual')" style="cursor: pointer; text-align: right;">Qtd. <i class="fas fa-sort"></i></th>
                                    <th onclick="event.stopPropagation(); ordenarTabelaEstoque('unidade_simbolo')" style="cursor: pointer;">Unid. <i class="fas fa-sort"></i></th>
                                    <th onclick="event.stopPropagation(); ordenarTabelaEstoque('status')" style="cursor: pointer;">Status <i class="fas fa-sort"></i></th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-estoque-dashboard">
                                <!-- Dados via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABELA: ÚLTIMAS MOVIMENTAÇÕES -->
                <div class="table-container">
                    <div class="table-header" onclick="toggleUltimasMovimentacoes()" style="cursor: pointer; user-select: none; display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                            Últimas Movimentações 
                            <i id="icon-toggle-movimentacoes" class="fas fa-chevron-down" style="font-size: 0.8em; transition: transform 0.3s;"></i>
                        </h2>
                        <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); mostrarSecao('relatorios')">Ver Completo</button>
                    </div>
                    <div id="container-tabela-movimentacoes" class="table-responsive" style="display: none; transition: all 0.3s ease-in-out;">
                        <table class="table-hover">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Data</th>
                                    <th>Material</th>
                                    <th>Origem/Destino</th>
                                    <th>Qtd.</th>
                                    <th>Responsável</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-ultimas-movimentacoes">
                                <!-- Dados via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- SEÇÃO: EMPRESAS -->
            <section id="empresas" class="section">
                <div class="form-container">
                    <h2 style="margin-bottom: 20px;">Cadastrar Empresa</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nome da Empresa</label>
                            <input type="text" id="emp-nome" placeholder="Nome completo">
                        </div>
                        <div class="form-group">
                            <label>Tipo de Serviço</label>
                            <input type="text" id="emp-tipo" placeholder="Ex: Limpeza, Manutenção, Segurança...">
                        </div>
                        <div class="form-group">
                            <label>Número do Contrato</label>
                            <input type="text" id="emp-contrato" placeholder="Ex: CT-2024-001">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>CNPJ</label>
                            <input type="text" id="emp-cnpj" placeholder="00.000.000/0000-00">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" id="emp-telefone" placeholder="(11) 9xxxx-xxxx">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" id="emp-email" placeholder="email@empresa.com.br">
                        </div>
                    </div>
                    <button class="btn btn-primary" id="btn-salvar-empresa" onclick="salvarEmpresa()">Cadastrar Empresa</button>
                </div>

                <div class="table-container">
                    <h2 style="margin-bottom: 20px;">Empresas Cadastradas</h2>
                    <div id="lista-empresas">
                        <div class="loading"><div class="spinner"></div> Carregando...</div>
                    </div>
                </div>
            </section>

            <!-- SEÇÃO: CATEGORIAS -->
            <section id="categorias" class="section">
                <div class="form-container">
                    <h2 style="margin-bottom: 20px;">Cadastrar Categoria</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nome da Categoria</label>
                            <input type="text" id="cat-nome" placeholder="Ex: Limpeza, Ferramentas...">
                        </div>
                        <div class="form-group">
                            <label>Descrição</label>
                            <input type="text" id="cat-descricao" placeholder="Descrição da categoria">
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="salvarCategoria()">Cadastrar Categoria</button>
                </div>

                <div class="table-container">
                    <h2 style="margin-bottom: 20px;">Categorias Cadastradas</h2>
                    <div id="lista-categorias">
                        <div class="loading"><div class="spinner"></div> Carregando...</div>
                    </div>
                </div>
            </section>

            <!-- SEÇÃO: LOCAIS -->
            <section id="locais" class="section">
                <div class="form-container">
                    <h2 style="margin-bottom: 20px;">Cadastrar Local</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nome do Local</label>
                            <input type="text" id="loc-nome" placeholder="Ex: Almoxarifado Central">
                            <input type="hidden" id="loc-id">
                        </div>
                        <div class="form-group">
                            <label>Descrição</label>
                            <input type="text" id="loc-descricao" placeholder="Descrição do local">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Empresas Vinculadas</label>
                            <div style="margin-bottom: 8px;">
                                <button type="button" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px; margin-right: 5px;" onclick="selecionarTodasEmpresas('loc-empresas', true)">Selecionar Todas</button>
                                <button type="button" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;" onclick="selecionarTodasEmpresas('loc-empresas', false)">Desmarcar Todas</button>
                            </div>
                            <div id="loc-empresas-checkboxes" class="empresas-checkbox-container">
                                <!-- Checkboxes serão carregados aqui -->
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-primary" onclick="salvarLocal()">Salvar Local</button>
                        <button class="btn btn-secondary" onclick="limparFormLocal()" style="display: none;" id="btn-cancelar-local">Cancelar Edição</button>
                    </div>
                </div>

                <div class="table-container">
                    <h2 style="margin-bottom: 20px;">Locais Cadastrados</h2>
                    <div id="lista-locais">
                        <div class="loading"><div class="spinner"></div> Carregando...</div>
                    </div>
                </div>
            </section>

            <!-- SEÇÃO: MATERIAIS -->
            <section id="materiais" class="section">
                <div class="form-container" id="form-cadastro-material" style="display: none;">
                    <h2 style="margin-bottom: 20px;">Cadastrar Material</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nome do Material</label>
                            <input type="text" id="mat-nome" placeholder="Nome do material">
                        </div>
                        <div class="form-group">
                            <label>Categoria</label>
                            <select id="mat-categoria" onchange="limparSKU()">
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Empresa Responsável</label>
                            <select id="mat-empresa" onchange="limparSKU()"></select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">

                        <div class="form-group">
                            <label>Unidade de Medida</label>
                            <select id="mat-unidade">
                                <option value="1">Unidade</option>
                                <option value="2">Litro</option>
                                <option value="3">Kg</option>
                                <option value="4">Caixa</option>
                                <option value="5">Pacote</option>
                                <option value="6">Resma</option>
                                <option value="7">Rolo</option>
                                <option value="8">Lata</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Código SKU</label>
                            <div style="display: flex; gap: 5px;">
                                <input type="text" id="mat-sku" placeholder="Clique em 'Gerar' após preencher categoria e empresa" style="flex: 1;" readonly>
                                <button type="button" class="btn btn-secondary" onclick="gerarSKU()" style="padding: 10px 15px;">Gerar</button>
                            </div>
                            <small>Formato: CATEG + EMPRE + 0001 (ex: LIMPEM0001)</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ponto de Reposição</label>
                            <input type="number" id="mat-reposicao" placeholder="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Estoque Máximo</label>
                            <input type="number" id="mat-maximo" placeholder="0" step="0.01">
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="salvarMaterial()">Cadastrar Material</button>
                </div>

                <div class="table-container">
                    <h2 style="margin-bottom: 20px;">Materiais Cadastrados</h2>
                    <div class="search-bar">
                        <select id="filtro-empresa-material" onchange="filtrarMateriais()" style="margin-right: 10px; width: 200px;">
                            <option value="">Todas as empresas</option>
                        </select>
                        <input type="text" id="busca-material" placeholder="Buscar material..." onkeyup="filtrarMateriais()">
                    </div>
                    <div id="lista-materiais">
                        <div class="loading"><div class="spinner"></div> Carregando...</div>
                    </div>
                </div>
            </section>

            <!-- SEÇÃO: ENTRADA -->
            <section id="entrada" class="section">
                <div class="form-container" id="form-entrada">
                    <h2 style="margin-bottom: 20px;">Registrar Entrada de Material</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Data da Entrada</label>
                            <input type="date" id="ent-data">
                        </div>
                        <div class="form-group">
                            <label>Empresa *</label>
                            <select id="ent-empresa" onchange="carregarMateriaisPorEmpresa('ent-material', this.value)">
                                <option value="">Selecione a empresa primeiro...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Material</label>
                            <div style="position: relative;">
                                <input type="text" id="ent-material-busca" placeholder="Digite para buscar material..." autocomplete="off">
                                <input type="hidden" id="ent-material" value="">
                                <div id="ent-material-lista" class="material-autocomplete"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Quantidade</label>
                            <input type="number" id="ent-quantidade" placeholder="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Nota Fiscal</label>
                            <input type="text" id="ent-nf" placeholder="NF-12345">
                        </div>
                        <div class="form-group">
                            <label>Responsável <small style="color: #999;">(Opcional)</small></label>
                            <select id="ent-responsavel"></select>
                        </div>
                        <div class="form-group">
                            <label>Local Destino</label>
                            <select id="ent-local"></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Observações</label>
                        <textarea id="ent-obs" placeholder="Deixe suas observações" rows="3"></textarea>
                    </div>
                    
                    <div style="margin-bottom: 20px; text-align: right;">
                        <button class="btn btn-secondary" onclick="adicionarItemEntrada()" style="margin-right: 10px;">+ Adicionar à Lista</button>
                    </div>

                    <!-- Tabela de Itens Temporários -->
                    <div id="container-lista-entrada" style="display: none; margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 10px; background: #f8fafc;">
                        <h3 style="font-size: 1rem; margin-bottom: 10px; color: #334155;">Itens a Registrar</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #e2e8f0; text-align: left;">
                                    <th style="padding: 8px;">Material</th>
                                    <th style="padding: 8px;">Qtd</th>
                                    <th style="padding: 8px;">Local</th>
                                    <th style="padding: 8px;">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-lista-entrada"></tbody>
                        </table>
                    </div>

                    <button class="btn btn-primary" onclick="registrarEntrada()">Registrar Entrada(s)</button>
                </div>

                <div class="table-container">
                    <h2 style="margin-bottom: 20px;">Histórico de Entradas</h2>
                    <div id="lista-entradas">
                        <div class="loading"><div class="spinner"></div> Carregando...</div>
                    </div>
                </div>
            </section>

            <!-- SEÇÃO: SAÍDA -->
            <section id="saida" class="section">
                <div class="form-container">
                    <h2 style="margin-bottom: 20px;">Registrar Saída de Material</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Data da Saída</label>
                            <input type="date" id="sai-data">
                        </div>
                        <div class="form-group">
                            <label>Empresa *</label>
                            <select id="sai-empresa" onchange="carregarMateriaisSaida(this.value)">
                                <option value="">Selecione a empresa...</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Material *</label>
                            <div style="position: relative;">
                                <input type="text" id="sai-material-busca" placeholder="Selecione a empresa primeiro..." disabled autocomplete="off">
                                <input type="hidden" id="sai-material" value="">
                                <div id="sai-material-lista" class="material-autocomplete"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Quantidade Total *</label>
                            <input type="number" id="sai-quantidade-total" placeholder="0" step="0.01">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Locais Disponíveis para Saída</label>
                            <div id="sai-locais-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 10px; background: #f8fafc;">
                                <p style="color: #64748b; text-align: center; padding: 20px;">Selecione um material para ver os locais disponíveis</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Observações / Finalidade *</label>
                        <textarea id="sai-obs" placeholder="Descreva a finalidade da saída (ex: Manutenção, Limpeza, Uso Administrativo...)" rows="3"></textarea>
                    </div>
                    <button class="btn btn-primary" onclick="registrarSaida()">Registrar Saída</button>
                </div>

                <div class="table-container">
                    <h2 style="margin-bottom: 20px;">Histórico de Saídas</h2>
                    <div id="lista-saidas">
                        <div class="loading"><div class="spinner"></div> Carregando...</div>
                    </div>
                </div>
            </section>

            <!-- SEÇÃO: ALERTAS -->
            <section id="alertas" class="section">
                <div class="table-container">
                    <h2 style="margin-bottom: 20px;">⚠️ Alertas de Estoque</h2>
                    

    <!-- Modal Editar Entrada -->
                    <!-- Filtro por Empresa -->
                    <div class="form-row" style="margin-bottom: 20px;">
                        <div class="form-group">
                            <label>Filtrar por Empresa:</label>
                            <select id="filtro-empresa-alertas" onchange="carregarAlertas()">
                                <option value="">Todas as empresas</option>
                            </select>
                        </div>
                    </div>

                    <!-- Alertas de Estoque Baixo -->
                    <h3 style="color: #dc2626; margin: 20px 0 10px 0;">🔴 Estoque Baixo</h3>
                    <div id="lista-alertas-baixo">
                        <div class="loading"><div class="spinner"></div> Carregando...</div>
                    </div>
                    
                    <!-- Alertas de Sobressalência (Estoque Alto) -->
                    <h3 style="color: #f59e0b; margin: 30px 0 10px 0;">🟡 Sobressalência (Estoque Alto)</h3>
                    <div id="lista-alertas-alto">
                        <div class="loading"><div class="spinner"></div> Carregando...</div>
                    </div>
                </div>
            </section>


            <!-- SEÇÃO: RELATÓRIOS -->
            <section id="relatorios" class="section">
                <h2 style="margin-bottom: 20px;">📈 Relatórios e Análises</h2>
                
                <!-- Abas de Relatórios -->
                <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb;">
                    <button class="tab-relatorio active" onclick="trocarRelatorio('estoque')">📊 Estoque por Empresa</button>
                    <button class="tab-relatorio" onclick="trocarRelatorio('movimentacoes')">📦 Movimentações</button>
                    <button class="tab-relatorio" onclick="trocarRelatorio('consumo')">📉 Consumo por Empresa</button>
                    <button class="tab-relatorio" onclick="trocarRelatorio('inventario')">📋 Inventário Completo</button>
                    <button class="tab-relatorio" onclick="trocarRelatorio('baixoestoque')">⚠️ Baixo Estoque</button>
                </div>
                
                <!-- Relatório: Estoque por Empresa -->
                <div id="rel-estoque" class="relatorio-content active">
                    <div class="table-container">
                        <h3 style="margin-bottom: 15px;">Resumo de Estoque por Empresa</h3>
                        <div id="relatorio-estoque-empresa">
                            <div class="loading"><div class="spinner"></div> Carregando...</div>
                        </div>
                    </div>
                </div>
                
                <!-- Relatório: Movimentações -->
                <div id="rel-movimentacoes" class="relatorio-content">
                    <div class="table-container">
                        <h3 style="margin-bottom: 15px;">Histórico de Movimentações</h3>
                        
                        <div class="form-row" style="margin-bottom: 20px;">
                            <div class="form-group">
                                <label>Tipo:</label>
                                <select id="rel-mov-tipo" onchange="carregarRelatorios()">
                                    <option value="">Todas</option>
                                    <option value="entrada">Entradas</option>
                                    <option value="saida">Saídas</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Empresa:</label>
                                <select id="rel-mov-empresa" onchange="carregarRelatorios()">
                                    <option value="">Todas as empresas</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Período:</label>
                                <select id="rel-mov-periodo" onchange="carregarRelatorios()">
                                    <option value="7">Últimos 7 dias</option>
                                    <option value="30" selected>Últimos 30 dias</option>
                                    <option value="90">Últimos 90 dias</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="relatorio-movimentacoes">
                            <div class="loading"><div class="spinner"></div> Carregando...</div>
                        </div>
                    </div>
                </div>
                
                <!-- Relatório: Consumo por Empresa -->
                <div id="rel-consumo" class="relatorio-content">
                    <div class="table-container">
                        <h3 style="margin-bottom: 15px;">Consumo de Materiais por Empresa</h3>
                        <p style="color: #666; margin-bottom: 15px;">Análise de saídas por empresa nos últimos 30 dias</p>
                        <div id="relatorio-consumo-empresa">
                            <div class="loading"><div class="spinner"></div> Carregando...</div>
                        </div>
                    </div>
                </div>
                
                <!-- Relatório: Inventário Completo -->
                <div id="rel-inventario" class="relatorio-content">
                    <div class="table-container">
                        <h3 style="margin-bottom: 15px;">Inventário Completo de Materiais</h3>
                        
                        <div class="form-row" style="margin-bottom: 20px;">
                            <div class="form-group">
                                <label>Filtrar por Empresa:</label>
                                <select id="filtro-empresa-inv" onchange="carregarRelatorioInventario()">
                                    <option value="">Todas as empresas</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Buscar Material:</label>
                                <input type="text" id="busca-inventario" placeholder="Nome ou SKU..." onkeyup="filtrarInventario()">
                            </div>
                        </div>
                        
                        <div id="relatorio-inventario">
                            <div class="loading"><div class="spinner"></div> Carregando...</div>
                        </div>
                    </div>
                </div>
                
                <!-- Relatório: Materiais com Baixo Estoque -->
                <div id="rel-baixoestoque" class="relatorio-content">
                    <div class="table-container">
                        <h3 style="margin-bottom: 15px;">Materiais Abaixo do Ponto de Reposição</h3>
                        
                        <div class="form-row" style="margin-bottom: 20px;">
                            <div class="form-group">
                                <label>Filtrar por Empresa:</label>
                                <select id="filtro-empresa-baixo" onchange="carregarRelatorioBaixoEstoque()">
                                    <option value="">Todas as empresas</option>
                                </select>
                            </div>
                        </div>

                        <div id="relatorio-baixo-estoque">
                            <div class="loading"><div class="spinner"></div> Carregando...</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- PERFIL DO USUÁRIO -->
            <section id="perfil" class="section">
                <h2 style="margin-bottom: 20px;">👤 Meu Perfil</h2>
                
                <div class="form-container">
                    <h3 style="margin-bottom: 20px;">Informações Pessoais</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nome Completo *</label>
                            <input type="text" id="perfil-nome" placeholder="Seu nome completo">
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" id="perfil-email" placeholder="seu@email.com">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Departamento</label>
                            <input type="text" id="perfil-departamento" placeholder="Ex: TI, Administração" readonly style="background-color: #f3f4f6;">
                        </div>
                        <div class="form-group">
                            <label>Perfil de Acesso</label>
                            <input type="text" id="perfil-perfil" readonly style="background-color: #f3f4f6;">
                        </div>
                    </div>
                    
                    <hr style="margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;">
                    
                    <h3 style="margin-bottom: 20px;">🔒 Alterar Senha</h3>
                    <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 15px;">
                        Deixe em branco se não deseja alterar a senha
                    </p>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Senha Atual *</label>
                            <input type="password" id="perfil-senha-atual" placeholder="Digite sua senha atual">
                            <small style="color: #64748b;">Obrigatório para salvar qualquer alteração</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nova Senha</label>
                            <input type="password" id="perfil-nova-senha" placeholder="Digite a nova senha (opcional)">
                        </div>
                        <div class="form-group">
                            <label>Confirmar Nova Senha</label>
                            <input type="password" id="perfil-confirma-senha" placeholder="Confirme a nova senha">
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button class="btn btn-primary" onclick="salvarPerfil()">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <button class="btn btn-secondary" onclick="cancelarEdicaoPerfil()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </div>
            </section>

            <style>
                .tab-relatorio {
                    padding: 10px 20px;
                    border: none;
                    background: transparent;
                    cursor: pointer;
                    font-size: 14px;
                    color: #666;
                    border-bottom: 3px solid transparent;
                    transition: all 0.3s;
                }
                
                .tab-relatorio:hover {
                    color: #2563eb;
                    background: #f3f4f6;
                }
                
                .tab-relatorio.active {
                    color: #2563eb;
                    border-bottom-color: #2563eb;
                    font-weight: 600;
                }
                
                .relatorio-content {
                    display: none;
                }
                
                .relatorio-content.active {
                    display: block;
                }
            </style>


            <!-- SEÇÃO: USUÁRIOS -->
            <section id="usuarios" class="section">
                <div class="alert alert-warning" id="aviso-nao-admin" style="display: none;">
                    <strong>⚠️ Acesso Restrito:</strong> Apenas administradores podem cadastrar e gerenciar usuários do sistema.
                </div>
                
                <div class="form-container" id="form-cadastro-usuario" style="display: none;">
                    <h2 style="margin-bottom: 20px;">Cadastrar Usuário</h2>
                    <form>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nome Completo</label>
                                <input type="text" id="usr-nome" placeholder="Nome completo do usuário">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" id="usr-email" placeholder="email@universidade.edu.br">
                            </div>
                            <div class="form-group">
                                <label>Perfil de Acesso</label>
                                <select id="usr-perfil">
                                    <option value="">Selecione...</option>
                                    <option value="1">Administrador</option>
                                    <option value="2">Gestor</option>
                                    <option value="3">Operador</option>
                                    <option value="4">Consulta</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Senha</label>
                                <input type="password" id="usr-senha" placeholder="Senha de acesso">
                            </div>
                            <div class="form-group">
                                <label>Confirmar Senha</label>
                                <input type="password" id="usr-confirma-senha" placeholder="Confirme a senha">
                            </div>
                            <div class="form-group">
                                <label>Departamento</label>
                                <input type="text" id="usr-departamento" placeholder="Ex: TI, Administração">
                            </div>
                        </div>
                        <div class="form-row" id="empresas-vinculo" style="display: none;">
                            <div class="form-group">
                                <label>Empresas Vinculadas <span id="empresas-obrigatorio" style="color: red; display: none;">*</span></label>
                                <div style="margin-bottom: 8px;">
                                    <button type="button" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px; margin-right: 5px;" onclick="selecionarTodasEmpresas('usr-empresas', true)">Selecionar Todas</button>
                                    <button type="button" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;" onclick="selecionarTodasEmpresas('usr-empresas', false)">Desmarcar Todas</button>
                                </div>
                                <div id="usr-empresas-checkboxes" class="empresas-checkbox-container">
                                    <!-- Checkboxes serão carregados aqui -->
                                </div>
                                <small>Selecione uma ou mais empresas que este usuário poderá gerenciar</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="salvarUsuario()">Cadastrar Usuário</button>
                    </form>
                </div>

                <div class="table-container">
                    <h2 style="margin-bottom: 20px;">Usuários Cadastrados</h2>
                    <div id="lista-usuarios">
                        <div class="loading"><div class="spinner"></div> Carregando...</div>
                    </div>
                </div>





                <!-- MODAL DE EDIÇÃO -->
                <div id="modal-editar-usuario" class="modal">
                    <div class="modal-content" style="max-width: 800px;">
                        <div class="modal-header">Editar Usuário</div>
                        <div style="padding: 30px;">
                            <form>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Nome Completo</label>
                                        <input type="text" id="edit-usr-nome">
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" id="edit-usr-email">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Perfil de Acesso</label>
                                        <select id="edit-usr-perfil">
                                            <option value="1">Administrador</option>
                                            <option value="2">Gestor</option>
                                            <option value="3">Operador</option>
                                            <option value="4">Consulta</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Departamento</label>
                                        <input type="text" id="edit-usr-departamento">
                                    </div>
                                </div>
                                <div class="form-group" id="edit-empresas-vinculo">
                                    <label>Empresas Vinculadas</label>
                                    <div style="margin-bottom: 8px;">
                                        <button type="button" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px; margin-right: 5px;" onclick="selecionarTodasEmpresas('edit-usr-empresas', true)">Selecionar Todas</button>
                                        <button type="button" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;" onclick="selecionarTodasEmpresas('edit-usr-empresas', false)">Desmarcar Todas</button>
                                    </div>
                                    <div id="edit-usr-empresas-checkboxes" class="empresas-checkbox-container">
                                        <!-- Checkboxes serão carregados aqui -->
                                    </div>
                                </div>
                            </form>
                            <div class="modal-footer" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                                <button class="btn btn-secondary" onclick="fecharModalEdicao()">Cancelar</button>
                                <button class="btn btn-primary" onclick="salvarEdicaoUsuario()">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <h2 style="margin-bottom: 20px;">Perfis de Acesso</h2>
                    <div id="lista-perfis">
                        <table>
                            <thead>
                                <tr><th>Perfil</th><th>Descrição</th><th>Permissões</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Administrador</strong></td>
                                    <td>Acesso total ao sistema</td>
                                    <td>Criar, editar, excluir todos os dados</td>
                                </tr>
                                <tr>
                                    <td><strong>Gestor</strong></td>
                                    <td>Gerenciamento operacional</td>
                                    <td>Criar e editar materiais, movimentações e relatórios</td>
                                </tr>
                                <tr>
                                    <td><strong>Operador</strong></td>
                                    <td>Operações básicas</td>
                                    <td>Registrar entradas e saídas de materiais</td>
                                </tr>
                                <tr>
                                    <td><strong>Consulta</strong></td>
                                    <td>Apenas visualização</td>
                                    <td>Visualizar relatórios e consultar estoque</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>


            <!-- MODAL DETALHES MATERIAL -->


            <div class="footer" style="display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap; margin-top: auto; padding: 20px; border-top: 1px solid #e2e8f0;">
                <img src="logo-devops.png" alt="Logo STI" style="height: 25px;">
                <span style="font-size: 0.85rem; color: #64748b;">&copy; 2025 UAST/UFRPE - Desenvolvido pela <a href="https://uast.ufrpe.br/sti" target="_blank" style="color: var(--accent-color); text-decoration: none; font-weight: 500;">Seção de Tecnologia da Informação STI-UAST</a></span>
            </div>
        </div>
    </div>

    <!-- MODAL: EDITAR MATERIAL -->
    <div id="modal-editar-material" class="modal">
        <div class="modal-content" style="width: 800px; max-width: 90%;">
            <div class="modal-header">
                <h2>Editar Material</h2>
                <span class="close" onclick="fecharModal('modal-editar-material')">&times;</span>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-mat-id">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nome do Material</label>
                        <input type="text" id="edit-mat-nome">
                    </div>
                    <div class="form-group">
                        <label>Código SKU</label>
                        <input type="text" id="edit-mat-sku" readonly style="background-color: #f0f0f0; cursor: not-allowed;">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Categoria</label>
                        <select id="edit-mat-categoria"></select>
                    </div>
                    <div class="form-group">
                        <label>Empresa Responsável</label>
                        <select id="edit-mat-empresa"></select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Unidade de Medida</label>
                        <select id="edit-mat-unidade">
                            <option value="1">Unidade</option>
                            <option value="2">Litro</option>
                            <option value="3">Kg</option>
                            <option value="4">Caixa</option>
                            <option value="5">Pacote</option>
                            <option value="6">Resma</option>
                            <option value="7">Rolo</option>
                            <option value="8">Lata</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Ponto de Reposição</label>
                        <input type="number" id="edit-mat-reposicao">
                    </div>
                    <div class="form-group">
                        <label>Estoque Máximo</label>
                        <input type="number" id="edit-mat-maximo">
                    </div>
                </div>
                <div class="form-actions" style="margin-top: 20px; text-align: right;">
                    <button class="btn btn-secondary" onclick="fecharModal('modal-editar-material')">Cancelar</button>
                    <button class="btn btn-primary" onclick="salvarEdicaoMaterial()">Salvar Alterações</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // =====================================================================
        // CONFIGURAÇÃO DA API
        // =====================================================================
        const API_URL = './api_filtrada.php';

        // =====================================================================
        // VARIÁVEIS GLOBAIS DE PAGINAÇÃO
        // =====================================================================
        let offsetEntrada = 0;
        const limitEntrada = 50;
        let carregandoEntrada = false;
        let fimEntrada = false;

        let offsetSaida = 0;
        const limitSaida = 50;
        let carregandoSaida = false;
        let fimSaida = false;

        // Observer para Lazy Loading
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const entradaObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !carregandoEntrada && !fimEntrada) {
                carregarEntradas(true);
            }
        }, observerOptions);

        const saidaObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !carregandoSaida && !fimSaida) {
                carregarSaidas(true);
            }
        }, observerOptions);

        // =====================================================================
        // FUNÇÕES AUXILIARES
        // =====================================================================
        
        async function chamarAPI(tipo, acao, dados = null, parametrosExtras = '') {
            try {
                // Adicionar & antes dos parâmetros extras se eles existirem
                const extras = parametrosExtras ? `&${parametrosExtras}` : '';
                const url = `${API_URL}?tipo=${tipo}&acao=${acao}${extras}`;
                const opcoes = {
                    method: dados ? 'POST' : 'GET',
                    headers: { 'Content-Type': 'application/json' }
                };
                
                if (dados) opcoes.body = JSON.stringify(dados);
                
                const resposta = await fetch(url, opcoes);
                const texto = await resposta.text();
                
                // Debug: mostrar resposta no console
                console.log('Resposta da API:', texto);
                
                try {
                    return JSON.parse(texto);
                } catch (jsonError) {
                    console.error('Erro ao parsear JSON:', jsonError);
                    console.error('Texto recebido:', texto);
                    return { sucesso: false, erro: 'Resposta inválida do servidor' };
                }
                
            } catch (erro) {
                console.error('Erro na requisição:', erro);
                return { sucesso: false, erro: 'Erro de conexão' };
            }
        }

        function mostrarSecao(secao) {
            // Atualizar título do Header
            const titulos = {
                'dashboard': 'Dashboard',
                'empresas': 'Gestão de Empresas',
                'materiais': 'Gestão de Materiais',
                'locais': 'Locais de Armazenamento',
                'categorias': 'Categorias de Materiais',
                'entrada': 'Registrar Entrada',
                'saida': 'Registrar Saída',
                'alertas': 'Alertas de Estoque',
                'relatorios': 'Relatórios e Análises',
                'usuarios': 'Gestão de Usuários'
            };
            
            const subtitulos = {
                'dashboard': 'Visão geral do sistema',
                'empresas': 'Cadastre e gerencie empresas terceirizadas',
                'materiais': 'Controle de inventário e estoque',
                'locais': 'Gerencie onde os materiais estão guardados',
                'categorias': 'Organize os materiais por tipos',
                'entrada': 'Adicione novos itens ao estoque',
                'saida': 'Registre o consumo de materiais',
                'alertas': 'Itens com estoque baixo ou excessivo',
                'relatorios': 'Visualize dados e exporte PDF',
                'usuarios': 'Controle de acesso ao sistema'
            };
            
            const headerH1 = document.querySelector('.header h1');
            const headerP = document.querySelector('.header p');
            
            if (headerH1) headerH1.textContent = titulos[secao] || 'Sistema de Gestão';
            if (headerP) headerP.textContent = subtitulos[secao] || '';

            // Gerenciar abas
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            const secaoEl = document.getElementById(secao);
            if (secaoEl) secaoEl.classList.add('active');
            
            // Gerenciar menu ativo
            document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
            
            // Encontrar o item de menu correto
            // Se foi clicado, usa o event.target
            if (event && event.target) {
                let item = event.target;
                // Subir até achar o .menu-item (caso tenha clicado no ícone)
                while (item && item.classList && !item.classList.contains('menu-item') && item.tagName !== 'BODY') {
                    item = item.parentElement;
                }
                if (item && item.classList && item.classList.contains('menu-item')) {
                    item.classList.add('active');
                } else {
                    // Se não achou pelo clique (ex: chamado via código), tenta achar pelo onclick
                    // Isso é difícil pois o onclick é uma string.
                    // Vamos buscar pelo texto ou atributo onclick
                    const menus = document.querySelectorAll('.menu-item');
                    menus.forEach(m => {
                        if (m.getAttribute('onclick') && m.getAttribute('onclick').includes(`'${secao}'`)) {
                            m.classList.add('active');
                        }
                    });
                }
            }

            // Carregar dados quando muda de seção
            if (secao === 'dashboard') carregarDashboard();
            else if (secao === 'empresas') setTimeout(carregarEmpresas, 100);
            else if (secao === 'categorias') setTimeout(carregarCategorias, 100);
            else if (secao === 'materiais') setTimeout(carregarMateriais, 100);
            else if (secao === 'entrada') setTimeout(carregarEntradas, 100);
            else if (secao === 'saida') setTimeout(carregarSaidas, 100);
            else if (secao === 'alertas') setTimeout(carregarAlertas, 100);
            else if (secao === 'relatorios') setTimeout(carregarRelatorios, 100);
            else if (secao === 'usuarios') setTimeout(carregarUsuarios, 100);
            else if (secao === 'locais') setTimeout(carregarLocais, 100);
        }



        function formatarData(data) {
            return new Date(data).toLocaleDateString('pt-BR');
        }

        function getStatusClass(estoque, pontoreposicao) {
            if (estoque < pontoreposicao * 0.5) return 'status-critico';
            if (estoque < pontoreposicao) return 'status-atencao';
            return 'status-adequado';
        }

        function getStatusTexto(estoque, pontoreposicao) {
            if (estoque < pontoreposicao * 0.5) return 'Crítico';
            if (estoque < pontoreposicao) return 'Atenção';
            return 'Adequado';
        }

        // =====================================================================
        // DASHBOARD
        // =====================================================================
        let chartTrend, chartComposition, chartTop5;

        async function carregarDashboard() {
            try {
                // Buscar dados do dashboard
                const resultado = await chamarAPI('dashboard', 'stats');
                
                if (!resultado.sucesso) {
                    exibirNotificacaoSistema('Erro ao carregar dashboard: ' + (resultado.erro || 'Erro desconhecido'), 'error');
                    return;
                }
                
                const dados = resultado.dados;
                const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
                const perfilId = usuarioLogado.perfil_id;
                
                // 1. RENDERIZAR CARDS (dinâmico por perfil)
                renderizarCards(dados, perfilId);
                
                // 2. RENDERIZAR GRÁFICOS
                renderizarGraficos(dados);
                
                // 3. CARREGAR ÚLTIMAS MOVIMENTAÇÕES
                await carregarUltimasMovimentacoes();

                // 4. CARREGAR ESTOQUE DETALHADO
                await carregarTabelaEstoqueDashboard();
                
            } catch (error) {
                console.error('Erro ao carregar dashboard:', error);
                exibirNotificacaoSistema('Erro ao carregar dashboard', 'error');
            }
        }

        function renderizarCards(dados, perfilId) {
            const container = document.getElementById('dashboard-cards-container');
            let html = '';
            
            // Admin (ID 1): Todos os cards
            if (perfilId == 1) {
                html += `
                    <div class="card">
                        <h3>Total de Empresas</h3>
                        <div class="number">${dados.total_empresas || 0}</div>
                        <i class="fas fa-building" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                    <div class="card">
                        <h3>Total de Materiais</h3>
                        <div class="number">${dados.total_materiais || 0}</div>
                        <i class="fas fa-boxes" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                    <div class="card alert">
                        <h3>Estoque Baixo</h3>
                        <div class="number">${dados.estoque_baixo || 0}</div>
                        <i class="fas fa-exclamation-triangle" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                    <div class="card success">
                        <h3>Valor em Estoque</h3>
                        <div class="number">R$ ${(dados.valor_total_estoque || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
                        <i class="fas fa-dollar-sign" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                `;
            }
            // Gestor (ID 2): Cards vinculados
            else if (perfilId == 2) {
                html += `
                    <div class="card">
                        <h3>Total de Materiais</h3>
                        <div class="number">${dados.total_materiais || 0}</div>
                        <i class="fas fa-boxes" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                    <div class="card alert">
                        <h3>Estoque Baixo</h3>
                        <div class="number">${dados.estoque_baixo || 0}</div>
                        <i class="fas fa-exclamation-triangle" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                    <div class="card success">
                        <h3>Valor em Estoque</h3>
                        <div class="number">R$ ${(dados.valor_total_estoque || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
                        <i class="fas fa-dollar-sign" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                    <div class="card">
                        <h3>Total Movimentações</h3>
                        <div class="number">${dados.total_movimentacoes || 0}</div>
                        <i class="fas fa-exchange-alt" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                `;
            }
            // Operador (ID 3): Cards específicos
            else if (perfilId == 3) {
                html += `
                    <div class="card alert">
                        <h3>Estoque Baixo</h3>
                        <div class="number">${dados.estoque_baixo || 0}</div>
                        <i class="fas fa-exclamation-triangle" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                    <div class="card">
                        <h3>Total Itens Disponíveis</h3>
                        <div class="number">${dados.total_itens || 0}</div>
                        <i class="fas fa-boxes" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                    <div class="card">
                        <h3>Total Movimentações</h3>
                        <div class="number">${dados.total_movimentacoes || 0}</div>
                        <i class="fas fa-exchange-alt" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                `;
            }
            // Consulta (ID 4): Sem valor em estoque
            else if (perfilId == 4) {
                html += `
                    <div class="card">
                        <h3>Total de Materiais</h3>
                        <div class="number">${dados.total_materiais || 0}</div>
                        <i class="fas fa-boxes" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                    <div class="card alert">
                        <h3>Estoque Baixo</h3>
                        <div class="number">${dados.estoque_baixo || 0}</div>
                        <i class="fas fa-exclamation-triangle" style="position: absolute; right: 20px; top: 20px; font-size: 2rem; opacity: 0.1;"></i>
                    </div>
                `;
            }
            
            container.innerHTML = html;
        }

        function renderizarGraficos(dados) {
            const charts = dados.charts || {};
            
            // Destruir gráficos existentes
            if (chartTrend) chartTrend.destroy();
            if (chartComposition) chartComposition.destroy();
            if (chartTop5) chartTop5.destroy();
            
            // 1. Gráfico de Tendência (Linha)
            if (charts.trend) {
                const ctxTrend = document.getElementById('chart-trend').getContext('2d');
                chartTrend = new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: charts.trend.labels.map(d => new Date(d).toLocaleDateString('pt-BR', {day: '2-digit', month: '2-digit'})),
                        datasets: [
                            {
                                label: 'Entradas',
                                data: charts.trend.entradas,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'Saídas',
                                data: charts.trend.saidas,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                tension: 0.4,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // 2. Gráfico de Composição (Pizza)
            if (charts.composition && charts.composition.length > 0) {
                const ctxComp = document.getElementById('chart-composition').getContext('2d');
                chartComposition = new Chart(ctxComp, {
                    type: 'doughnut',
                    data: {
                        labels: charts.composition.map(c => c.categoria),
                        datasets: [{
                            data: charts.composition.map(c => c.qtd),
                            backgroundColor: [
                                '#2563eb',
                                '#10b981',
                                '#f59e0b',
                                '#ef4444',
                                '#8b5cf6',
                                '#06b6d4'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
            }
            
            // 3. Gráfico Top 5 (Barra)
            if (charts.top5 && charts.top5.length > 0) {
                const ctxTop5 = document.getElementById('chart-top5').getContext('2d');
                chartTop5 = new Chart(ctxTop5, {
                    type: 'bar',
                    data: {
                        labels: charts.top5.map(m => m.nome),
                        datasets: [{
                            label: 'Quantidade Saída',
                            data: charts.top5.map(m => m.total_saida),
                            backgroundColor: '#2563eb'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }

        async function carregarUltimasMovimentacoes() {
            try {
                const resultado = await chamarAPI('relatorios', 'movimentacoes', null, 'periodo=7');
                
                const tbody = document.getElementById('tabela-ultimas-movimentacoes');
                if (!tbody) {
                    console.error('Elemento tabela-ultimas-movimentacoes não encontrado');
                    return;
                }
                
                if (!resultado.sucesso) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #ef4444;">Erro ao carregar movimentações: ' + (resultado.erro || 'Erro desconhecido') + '</td></tr>';
                    return;
                }
                
                if (!resultado.dados || resultado.dados.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #64748b;">Nenhuma movimentação nos últimos 7 dias</td></tr>';
                    return;
                }
                
                let html = '';
                resultado.dados.slice(0, 10).forEach(mov => {
                    const isEntrada = mov.tipo === 'Entrada';
                    const rowClass = isEntrada ? 'style="background-color: #f0fdf4;"' : 'style="background-color: #fef2f2;"';
                    const icon = isEntrada ? '<i class="fas fa-arrow-down" style="color: #10b981;"></i>' : '<i class="fas fa-arrow-up" style="color: #ef4444;"></i>';
                    
                    html += `
                        <tr ${rowClass}>
                            <td>${icon} ${mov.tipo}</td>
                            <td>${formatarData(mov.data)}</td>
                            <td><a href="#" onclick="abrirDetalhesMaterial(${mov.material_id}); return false;" style="font-weight: bold; color: #2563eb; text-decoration: none;">${mov.material}</a></td>
                            <td>${mov.empresa || '-'}</td>
                            <td><strong>${mov.quantidade}</strong></td>
                            <td>${mov.responsavel || '-'}</td>
                        </tr>
                    `;
                });
                
                tbody.innerHTML = html;
            } catch (error) {
                console.error('Erro ao carregar movimentações:', error);
                const tbody = document.getElementById('tabela-ultimas-movimentacoes');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #ef4444;">Erro ao carregar movimentações</td></tr>';
                }
            }
        }

        // =====================================================================
        // EMPRESAS
        // =====================================================================
        async function carregarEmpresas() {
            document.getElementById('lista-empresas').innerHTML = '<p>Carregando empresas...</p>';
            const resultado = await chamarAPI('empresas', 'listar');
            
            if (resultado.sucesso && resultado.dados) {
                const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
                const isAdmin = usuarioLogado.perfil_id == 1;
                let html = '<table><thead><tr><th>Nome</th><th>Tipo</th><th>Contrato</th>';
                if (isAdmin) {
                    html += '<th>Ações</th>';
                }
                html += '</tr></thead><tbody>';
                resultado.dados.forEach(emp => {
                    html += `<tr><td><a href="materiais_empresa.php?empresa_id=${emp.id}&empresa_nome=${encodeURIComponent(emp.nome)}" style="font-weight: bold; text-decoration: none; color: #2563eb;">${emp.nome || ''}</a></td><td>${emp.tipo_servico || ''}</td><td>${emp.numero_contrato || '-'}</td>`;
                    if (isAdmin) {
                        html += `<td>
                            <button class="btn btn-secondary btn-sm" onclick="editarEmpresa(${emp.id})" style="margin-right: 5px;">Editar</button>
                            <button class="btn btn-danger btn-sm" onclick="excluirEmpresa(${emp.id}, '${emp.nome}')">Excluir</button>
                        </td>`;
                    }
                    html += '</tr>';
                });
                html += '</tbody></table>';
                document.getElementById('lista-empresas').innerHTML = html;
            } else {
                document.getElementById('lista-empresas').innerHTML = '<p>Nenhuma empresa encontrada</p>';
            }
        }

        async function salvarEmpresa() {
            console.log('=== INICIANDO SALVAR EMPRESA ===');
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            const responsavelId = usuarioLogado ? usuarioLogado.id : 1;

            const dados = {
                nome: document.getElementById('emp-nome').value,
                tipo_servico: document.getElementById('emp-tipo').value,
                numero_contrato: document.getElementById('emp-contrato').value,
                cnpj: document.getElementById('emp-cnpj').value,
                telefone: document.getElementById('emp-telefone').value,
                email: document.getElementById('emp-email').value,
                responsavel_id: responsavelId
            };

            console.log('Dados do formulário:', dados);
            console.log('Empresa editando ID:', empresaEditando);

            if (!dados.nome || !dados.tipo_servico) {
                exibirNotificacaoSistema('Preencha todos os campos obrigatórios', 'warning');
                return;
            }

            let acao = 'criar';
            let mensagemSucesso = 'Empresa cadastrada com sucesso!';
            if (empresaEditando) {
                acao = 'atualizar';
                dados.id = empresaEditando;
                mensagemSucesso = 'Empresa atualizada com sucesso!';
            }

            console.log('Ação:', acao, 'Dados completos:', dados);

            const resultado = await chamarAPI('empresas', acao, dados);
            console.log('Resultado da API:', resultado);
            
            if (resultado.sucesso) {
                exibirNotificacaoSistema(mensagemSucesso, 'success');
                // Limpar formulário
                document.getElementById('emp-nome').value = '';
                document.getElementById('emp-tipo').value = '';
                document.getElementById('emp-contrato').value = '';
                document.getElementById('emp-cnpj').value = '';
                document.getElementById('emp-telefone').value = '';
                document.getElementById('emp-email').value = '';
                document.getElementById('btn-salvar-empresa').textContent = 'Cadastrar Empresa';
                empresaEditando = null;
                carregarEmpresas();
            } else {
                console.error('ERRO AO SALVAR EMPRESA:', resultado.erro);
                exibirNotificacaoSistema('Erro ao salvar empresa: ' + (resultado.erro || 'Erro desconhecido'), 'error');
            }
        }

        async function excluirEmpresa(id, nome) {
            if (!confirm(`Tem certeza que deseja excluir a empresa "${nome}"?\n\nAtenção: Só é possível excluir empresas que não possuem materiais associados.`)) {
                return;
            }

            const resultado = await chamarAPI('empresas', 'excluir', { id: id });
            if (resultado.sucesso) {
                exibirNotificacaoSistema('Empresa excluída com sucesso!', 'success');
                carregarEmpresas();
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }

        let empresaEditando = null;

        async function editarEmpresa(id) {
            const resultado = await chamarAPI('empresas', 'listar');
            if (resultado.sucesso && resultado.dados) {
                const empresa = resultado.dados.find(emp => emp.id == id);
                if (empresa) {
                    empresaEditando = id;
                    document.getElementById('emp-nome').value = empresa.nome || '';
                    document.getElementById('emp-tipo').value = empresa.tipo_servico || '';
                    document.getElementById('emp-contrato').value = empresa.numero_contrato || '';
                    document.getElementById('emp-cnpj').value = empresa.cnpj || '';
                    document.getElementById('emp-telefone').value = empresa.telefone || '';
                    document.getElementById('emp-email').value = empresa.email || '';
                    document.getElementById('btn-salvar-empresa').textContent = 'Atualizar Empresa';
                    document.getElementById('emp-nome').focus();
                }
            }
        }

        // =====================================================================
        // CATEGORIAS
        // =====================================================================
        async function carregarCategorias() {
            document.getElementById('lista-categorias').innerHTML = '<p>Carregando categorias...</p>';
            
            const resultado = await chamarAPI('categorias', 'listar');
            if (resultado.sucesso && resultado.dados) {
                let html = '<table><thead><tr><th>Nome</th><th>Descrição</th></tr></thead><tbody>';
                resultado.dados.forEach(cat => {
                    html += `<tr><td>${cat.nome || ''}</td><td>${cat.descricao || ''}</td></tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('lista-categorias').innerHTML = html;
            } else {
                document.getElementById('lista-categorias').innerHTML = '<p>Nenhuma categoria encontrada</p>';
            }
        }

        async function salvarCategoria() {
            const dados = {
                nome: document.getElementById('cat-nome').value,
                descricao: document.getElementById('cat-descricao').value
            };

            if (!dados.nome) {
                exibirNotificacaoSistema('Preencha o nome da categoria', 'warning');
                return;
            }

            const resultado = await chamarAPI('categorias', 'criar', dados);
            if (resultado.sucesso) {
                exibirNotificacaoSistema('Categoria cadastrada com sucesso!', 'success');
                document.getElementById('cat-nome').value = '';
                document.getElementById('cat-descricao').value = '';
                carregarCategorias();
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }

        // =====================================================================
        // LOCAIS
        // =====================================================================
        async function carregarLocais() {
            document.getElementById('lista-locais').innerHTML = '<p>Carregando locais...</p>';
            
            // Carregar checkboxes de empresas
            await carregarEmpresasSelectLocais();

            const resultado = await chamarAPI('locais', 'listar');
            if (resultado.sucesso && resultado.dados) {
                let html = '<table><thead><tr><th>Nome</th><th>Descrição</th><th>Empresas Vinculadas</th><th>Ações</th></tr></thead><tbody>';
                resultado.dados.forEach(loc => {
                    const empresas = loc.empresas_nomes || '<span style="color: #999; font-style: italic;">Nenhuma</span>';
                    html += `<tr>
                        <td><a href="materiais_local.php?local_id=${loc.id}&local_nome=${encodeURIComponent(loc.nome)}" style="font-weight: bold; text-decoration: none; color: #2563eb;">${loc.nome || ''}</a></td>
                        <td>${loc.descricao || ''}</td>
                        <td>${empresas}</td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="editarLocal(${loc.id}, '${loc.nome}', '${loc.descricao}', '${loc.empresas_ids || ''}');" style="margin-right: 5px;">Editar</button>
                            <button class="btn btn-danger btn-sm" onclick="excluirLocal(${loc.id}, '${loc.nome}');">Excluir</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('lista-locais').innerHTML = html;
            } else {
                document.getElementById('lista-locais').innerHTML = '<p>Nenhum local encontrado</p>';
            }
        }

        async function carregarEmpresasSelectLocais() {
            const empresas = await chamarAPI('empresas', 'listar');
            if (empresas.sucesso && empresas.dados) {
                let html = '';
                empresas.dados.forEach(emp => {
                    html += `
                        <div class="empresas-checkbox-item">
                            <label>
                                <input type="checkbox" name="loc-empresas" value="${emp.id}">
                                ${emp.nome}
                            </label>
                        </div>`;
                });
                document.getElementById('loc-empresas-checkboxes').innerHTML = html;
            }
        }

        async function salvarLocal() {
            const id = document.getElementById('loc-id').value;
            
            // Capturar empresas selecionadas
            const checkboxes = document.querySelectorAll('input[name="loc-empresas"]:checked');
            const empresasVinculadas = Array.from(checkboxes).map(cb => parseInt(cb.value));

            const dados = {
                nome: document.getElementById('loc-nome').value,
                descricao: document.getElementById('loc-descricao').value,
                empresas: empresasVinculadas
            };

            if (!dados.nome) {
                exibirNotificacaoSistema('Preencha o nome do local', 'warning');
                return;
            }

            if (dados.empresas.length === 0) {
                exibirNotificacaoSistema('Selecione pelo menos uma empresa vinculada', 'warning');
                return;
            }

            let acao = 'criar';
            if (id) {
                acao = 'atualizar';
                dados.id = parseInt(id);
            }

            const resultado = await chamarAPI('locais', acao, dados);
            if (resultado.sucesso) {
                exibirNotificacaoSistema(id ? 'Local atualizado com sucesso!' : 'Local cadastrado com sucesso!', 'success');
                limparFormLocal();
                carregarLocais();
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }

        function editarLocal(id, nome, descricao, empresasIds) {
            document.getElementById('loc-id').value = id;
            document.getElementById('loc-nome').value = nome;
            document.getElementById('loc-descricao').value = descricao;
            
            // Marcar checkboxes
            const ids = empresasIds ? empresasIds.split(',') : [];
            document.querySelectorAll('input[name="loc-empresas"]').forEach(cb => {
                cb.checked = ids.includes(cb.value);
            });

            document.getElementById('btn-cancelar-local').style.display = 'inline-block';
            document.querySelector('#locais .btn-primary').textContent = 'Atualizar Local';
            document.getElementById('loc-nome').focus();
        }

        function limparFormLocal() {
            document.getElementById('loc-id').value = '';
            document.getElementById('loc-nome').value = '';
            document.getElementById('loc-descricao').value = '';
            
            // Desmarcar checkboxes
            document.querySelectorAll('input[name="loc-empresas"]').forEach(cb => cb.checked = false);

            document.getElementById('btn-cancelar-local').style.display = 'none';
            document.querySelector('#locais .btn-primary').textContent = 'Salvar Local';
        }

        async function excluirLocal(id, nome) {
            if (!confirm(`Tem certeza que deseja excluir o local "${nome}"?`)) {
                return;
            }

            const resultado = await chamarAPI('locais', 'excluir', { id: id });
            if (resultado.sucesso) {
                exibirNotificacaoSistema('Local excluído com sucesso!', 'success');
                carregarLocais();
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }

        // =====================================================================
        // MATERIAIS
        // =====================================================================
        
        function limparSKU() {
            document.getElementById('mat-sku').value = '';
        }
        
        async function gerarSKU() {
            const categoria = document.getElementById('mat-categoria').value;
            const empresa = document.getElementById('mat-empresa').value;
            
            if (!categoria) {
                exibirNotificacaoSistema('Selecione uma categoria primeiro', 'warning');
                return;
            }
            
            if (!empresa) {
                exibirNotificacaoSistema('Selecione uma empresa primeiro', 'warning');
                return;
            }
            
            const resultado = await chamarAPI('materiais', 'gerar_sku', {
                categoria_id: parseInt(categoria),
                empresa_id: parseInt(empresa)
            });
            
            if (resultado.sucesso) {
                document.getElementById('mat-sku').value = resultado.sku;
                document.getElementById('mat-sku').readOnly = false;
                exibirNotificacaoSistema('SKU gerado: ' + resultado.sku, 'success');
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }
        async function carregarComboBoxes() {
            try {
                // Carregar categorias
                const categorias = await chamarAPI('categorias', 'listar');
                if (categorias.sucesso && categorias.dados) {
                    let htmlCat = '<option value="">Selecione a categoria...</option>';
                    categorias.dados.forEach(cat => {
                        htmlCat += `<option value="${cat.id}">${cat.nome}</option>`;
                    });
                    if (document.getElementById('mat-categoria')) {
                        document.getElementById('mat-categoria').innerHTML = htmlCat;
                    }
                }
                
                // Carregar empresas
                const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
                let html = '';
                
                if (usuarioLogado.perfil_id == 1) {
                    // Administrador: carregar todas as empresas
                    const empresas = await chamarAPI('empresas', 'listar');
                    if (empresas.sucesso && empresas.dados) {
                        html = '<option value="">Selecione a empresa...</option>';
                        empresas.dados.forEach(emp => {
                            html += `<option value="${emp.id}">${emp.nome}</option>`;
                        });
                    }
                } else {
                    // Gestor/Operador: usar empresas vinculadas
                    const empresasVinculadas = usuarioLogado.empresas_vinculadas || [];
                    
                    if (empresasVinculadas.length == 1) {
                        html = `<option value="${empresasVinculadas[0].id}" selected>${empresasVinculadas[0].nome}</option>`;
                    } else if (empresasVinculadas.length > 1) {
                        html = '<option value="">Selecione a empresa...</option>';
                        empresasVinculadas.forEach(emp => {
                            html += `<option value="${emp.id}">${emp.nome}</option>`;
                        });
                    }
                }
                
                if (document.getElementById('mat-empresa')) {
                    document.getElementById('mat-empresa').innerHTML = html;
                }
                
                // Carregar locais dinamicamente
                const locais = await chamarAPI('locais', 'listar');
                let locaisHtml = '<option value="">Selecione o local...</option>';

                if (locais.sucesso && locais.dados) {
                    locais.dados.forEach(local => {
                        locaisHtml += `<option value="${local.id}">${local.nome}</option>`;
                    });
                } else {
                    // Caso não tenha locais, mostrar mensagem
                    locaisHtml += '<option value="">Nenhum local encontrado</option>';
                }

                if (document.getElementById('mat-local')) {
                    document.getElementById('mat-local').innerHTML = locaisHtml;
                }
                
            } catch (e) {
                console.log('Erro ao carregar combos:', e);
            }
        }

        async function carregarMateriais() {
            document.getElementById('lista-materiais').innerHTML = '<p>Carregando materiais...</p>';
            
            // Verificar se pode cadastrar materiais (admin ou gestor)
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            const podeGerenciar = usuarioLogado.perfil_id == 1 || usuarioLogado.perfil_id == 2;
            
            if (podeGerenciar) {
                document.getElementById('form-cadastro-material').style.display = 'block';
            } else {
                document.getElementById('form-cadastro-material').style.display = 'none';
            }
            
            // Configurar filtro de empresa
            await configurarFiltroEmpresa();
            
            // Carregar materiais (inicialmente todos)
            await filtrarMateriais();
            
            // Carregar combos simples
            carregarComboBoxes();
        }
        
        async function configurarFiltroEmpresa() {
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            const filtroSelect = document.getElementById('filtro-empresa-material');
            
            if (usuarioLogado.perfil_id == 1) {
                // Administrador: carregar todas as empresas
                const empresas = await chamarAPI('empresas', 'listar');
                if (empresas.sucesso && empresas.dados) {
                    let html = '<option value="">Todas as empresas</option>';
                    empresas.dados.forEach(emp => {
                        html += `<option value="${emp.id}">${emp.nome}</option>`;
                    });
                    filtroSelect.innerHTML = html;
                }
            } else {
                // Gestor/Operador: usar empresas vinculadas
                const empresasVinculadas = usuarioLogado.empresas_vinculadas || [];
                
                if (empresasVinculadas.length == 1) {
                    // Uma empresa: pré-selecionar
                    filtroSelect.innerHTML = `<option value="${empresasVinculadas[0].id}" selected>${empresasVinculadas[0].nome}</option>`;
                    filtroSelect.disabled = true;
                } else if (empresasVinculadas.length > 1) {
                    // Múltiplas empresas: permitir escolha
                    let html = '<option value="">Todas as empresas</option>';
                    empresasVinculadas.forEach(emp => {
                        html += `<option value="${emp.id}">${emp.nome}</option>`;
                    });
                    filtroSelect.innerHTML = html;
                } else {
                    filtroSelect.innerHTML = '<option value="">Nenhuma empresa vinculada</option>';
                }
            }
        }

        async function filtrarMateriais() {
            const empresaId = document.getElementById('filtro-empresa-material').value;
            const busca = document.getElementById('busca-material').value.trim();
            
            document.getElementById('lista-materiais').innerHTML = '<p>Carregando materiais...</p>';
            
            let parametrosExtras = '';
            if (empresaId) parametrosExtras += `&empresa_id=${empresaId}`;
            if (busca) parametrosExtras += `&busca=${encodeURIComponent(busca)}`;
            
            const resultado = await chamarAPI('materiais', 'listar', null, parametrosExtras);
            
            if (resultado.sucesso) {
                if (resultado.dados && resultado.dados.length > 0) {
                    let html = '<table><thead><tr><th>Material</th><th>Código</th><th>Estoque</th><th>Empresa</th><th>Ações</th></tr></thead><tbody>';
                    resultado.dados.forEach(mat => {
                        const nomeEscaped = (mat.nome || '').replace(/'/g, "\\'");
                        html += `<tr>
                            <td><a href="#" onclick="abrirDetalhesMaterial(${mat.id}); return false;" style="font-weight: bold; color: #2563eb; text-decoration: none;">${mat.nome || ''}</a></td>
                            <td>${mat.codigo_sku || ''}</td>
                            <td>${mat.estoque_atual || 0}</td>
                            <td>${mat.empresa_nome || ''}</td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="verEstoquePorLocal(${mat.id}, '${nomeEscaped}')" style="margin-right: 5px; padding: 5px 10px; font-size: 12px; background-color: #0ea5e9; color: white; border: none;">📍 Locais</button>
                                <button class="btn btn-secondary btn-sm" onclick="editarMaterial(${mat.id})" style="margin-right: 5px; padding: 5px 10px; font-size: 12px;">✏️ Editar</button>
                                <button class="btn btn-danger btn-sm" onclick="excluirMaterialGeral(${mat.id}, '${nomeEscaped}', ${mat.estoque_atual || 0})" style="padding: 5px 10px; font-size: 12px;">🗑️ Excluir</button>
                            </td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('lista-materiais').innerHTML = html;
                } else {
                    document.getElementById('lista-materiais').innerHTML = '<p>Nenhum material encontrado.</p>';
                }
            } else {
                document.getElementById('lista-materiais').innerHTML = '<p>Erro ao carregar materiais: ' + (resultado.erro || 'Erro desconhecido') + '</p>';
            }
        }

        async function salvarMaterial() {
            const nome = document.getElementById('mat-nome').value;
            const categoria = document.getElementById('mat-categoria').value;
            const empresa = document.getElementById('mat-empresa').value;
            const sku = document.getElementById('mat-sku').value;
            
            if (!nome) {
                exibirNotificacaoSistema('Preencha o nome do material', 'warning');
                return;
            }
            
            if (!categoria) {
                exibirNotificacaoSistema('Selecione uma categoria', 'warning');
                return;
            }
            
            if (!empresa) {
                exibirNotificacaoSistema('Selecione uma empresa', 'warning');
                return;
            }
            
            if (!sku) {
                exibirNotificacaoSistema('Gere o código SKU antes de salvar', 'warning');
                return;
            }
            
            const dados = {
                nome: nome,
                codigo_sku: sku,
                descricao: '',
                categoria_id: parseInt(categoria),
                unidade_medida_id: parseInt(document.getElementById('mat-unidade').value),
                empresa_id: parseInt(empresa),
                local_id: null, // Local removido do cadastro inicial
                ponto_reposicao: parseFloat(document.getElementById('mat-reposicao').value) || 0,
                estoque_maximo: parseFloat(document.getElementById('mat-maximo').value) || 0
            };

            const resultado = await chamarAPI('materiais', 'criar', dados);
            if (resultado.sucesso) {
                exibirNotificacaoSistema('Material cadastrado com sucesso!', 'success');
                document.getElementById('mat-nome').value = '';
                document.getElementById('mat-sku').value = '';
                document.getElementById('mat-sku').readOnly = true;
                document.getElementById('mat-categoria').value = '';
                document.getElementById('mat-empresa').value = '';
                document.getElementById('mat-reposicao').value = '';
                document.getElementById('mat-maximo').value = '';
                carregarMateriais();
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }



        async function excluirMaterialGeral(id, nome, estoque) {
            if (estoque > 0) {
                if (!confirm(`O material "${nome}" possui estoque atual de ${estoque} unidades.\n\nNÃO É POSSÍVEL EXCLUIR materiais com estoque.\n\nVocê precisa zerar o estoque antes de excluir.`)) {
                    return;
                }
                exibirNotificacaoSistema('Não é possível excluir material com estoque. Estoque atual: ' + estoque, 'error');
                return;
            }
            
            if (!confirm(`Tem certeza que deseja excluir o material "${nome}"?\n\nEsta ação não pode ser desfeita.`)) {
                return;
            }

            console.log('Excluindo material ID:', id);
            const resultado = await chamarAPI('materiais', 'excluir', { id: id });
            console.log('Resultado:', resultado);
            
            if (resultado.sucesso) {
                exibirNotificacaoSistema(resultado.mensagem || 'Material excluído com sucesso!', 'success');
                filtrarMateriais(); // Recarregar lista
            } else {
                exibirNotificacaoSistema(resultado.erro || 'Erro ao excluir material', 'error');
            }
        }

        // =====================================================================
        // ENTRADA DE MATERIAIS
        // =====================================================================
        let materiaisEmpresa = [];
        
        async function carregarMateriaisPorEmpresa(inputId, empresaId) {
            console.log('Carregando materiais para empresa:', empresaId);
            const inputBusca = document.getElementById('ent-material-busca');
            const inputHidden = document.getElementById('ent-material');
            
            if (!empresaId) {
                inputBusca.placeholder = 'Selecione a empresa primeiro';
                inputBusca.disabled = true;
                inputHidden.value = '';
                materiaisEmpresa = [];
                return;
            }
            
            inputBusca.placeholder = 'Carregando materiais...';
            inputBusca.disabled = true;
            
            try {
                const resultado = await chamarAPI('materiais', 'por_empresa', null, `&empresa_id=${empresaId}`);
                console.log('Resultado API por_empresa:', resultado);
                
                if (resultado.sucesso && resultado.dados) {
                    materiaisEmpresa = resultado.dados;
                    console.log('Materiais carregados:', materiaisEmpresa.length);
                    inputBusca.placeholder = 'Digite para buscar material...';
                    inputBusca.disabled = false;
                    inputBusca.value = '';
                    inputHidden.value = '';
                } else {
                    console.log('Nenhum material encontrado ou erro:', resultado.erro);
                    inputBusca.placeholder = 'Nenhum material encontrado';
                    materiaisEmpresa = [];
                    inputBusca.disabled = true;
                }

                // Carregar usuários responsáveis para a empresa
                await carregarUsuariosPorEmpresa(empresaId);
                
            } catch (error) {
                console.log('Erro ao carregar materiais:', error);
                inputBusca.placeholder = 'Erro ao carregar materiais';
                materiaisEmpresa = [];
                inputBusca.disabled = true;
            }
        }
        
        function buscarMateriais(termo) {
            const lista = document.getElementById('ent-material-lista');
            
            if (!termo || termo.length < 2) {
                lista.style.display = 'none';
                return;
            }
            
            const termoLower = termo.toLowerCase();
            const materiaisFiltrados = materiaisEmpresa.filter(mat => 
                mat.nome.toLowerCase().includes(termoLower) || 
                mat.codigo_sku.toLowerCase().includes(termoLower)
            );
            
            if (materiaisFiltrados.length === 0) {
                lista.innerHTML = '<div class="material-item">Nenhum material encontrado</div>';
            } else {
                let html = '';
                materiaisFiltrados.forEach(mat => {
                    html += `
                        <div class="material-item" onclick="selecionarMaterial(${mat.id}, '${mat.nome.replace(/'/g, "\\'")}', '${mat.codigo_sku}', ${mat.estoque_atual})">
                            <div class="material-nome">${mat.nome}</div>
                            <div class="material-info">Código: ${mat.codigo_sku} | Estoque: ${mat.estoque_atual}</div>
                        </div>
                    `;
                });
                lista.innerHTML = html;
            }
            
            lista.style.display = 'block';
        }
        
        function selecionarMaterial(id, nome, codigo, estoque) {
            document.getElementById('ent-material-busca').value = `${nome} (${codigo})`;
            document.getElementById('ent-material').value = id;
            document.getElementById('ent-material-lista').style.display = 'none';
        }
        
        async function carregarUsuariosPorEmpresa(empresaId) {
            const selectResponsavel = document.getElementById('ent-responsavel');
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            
            // Administrador: pode escolher qualquer usuário
            if (usuarioLogado.perfil_id == 1) {
                try {
                    const resultado = await chamarAPI('usuarios', 'listar');
                    
                    if (resultado.sucesso && resultado.dados && resultado.dados.length > 0) {
                        let html = `<option value="${usuarioLogado.id}" selected>${usuarioLogado.nome} (Você - Padrão)</option>`;
                        
                        resultado.dados.forEach(user => {
                            if (user.id != usuarioLogado.id) {
                                html += `<option value="${user.id}">${user.nome} - ${user.perfil_nome || 'Usuário'}</option>`;
                            }
                        });
                        
                        selectResponsavel.innerHTML = html;
                        selectResponsavel.disabled = false;
                    }
                } catch (error) {
                    // Se falhar, mantém usuário logado
                    selectResponsavel.innerHTML = `<option value="${usuarioLogado.id}" selected>${usuarioLogado.nome} (Você)</option>`;
                }
            }
            // Gestor: pode escolher entre Gestores e Operadores
            else if (usuarioLogado.perfil_id == 2) {
                try {
                    const resultado = await chamarAPI('usuarios', 'listar');
                    
                    if (resultado.sucesso && resultado.dados && resultado.dados.length > 0) {
                        // Filtrar apenas Gestores (2) e Operadores (3)
                        const usuariosFiltrados = resultado.dados.filter(u => u.perfil_id == 2 || u.perfil_id == 3);
                        
                        let html = `<option value="${usuarioLogado.id}" selected>${usuarioLogado.nome} (Você - Padrão)</option>`;
                        
                        usuariosFiltrados.forEach(user => {
                            if (user.id != usuarioLogado.id) {
                                const perfilNome = user.perfil_id == 2 ? 'Gestor' : 'Operador';
                                html += `<option value="${user.id}">${user.nome} - ${perfilNome}</option>`;
                            }
                        });
                        
                        selectResponsavel.innerHTML = html;
                        selectResponsavel.disabled = false;
                    }
                } catch (error) {
                    selectResponsavel.innerHTML = `<option value="${usuarioLogado.id}" selected>${usuarioLogado.nome} (Você)</option>`;
                }
            }
            // Outros perfis: somente o próprio usuário
            else {
                selectResponsavel.innerHTML = `<option value="${usuarioLogado.id}" selected>${usuarioLogado.nome} (Você)</option>`;
                selectResponsavel.disabled = false;
            }
        }
        
        async function carregarLocaisEntrada() {
            const selectLocal = document.getElementById('ent-local');
            
            try {
                const resultado = await chamarAPI('locais', 'listar');
                
                if (resultado.sucesso && resultado.dados && resultado.dados.length > 0) {
                    let html = '<option value="">Selecione o local...</option>';
                    resultado.dados.forEach(local => {
                        html += `<option value="${local.id}">${local.nome}</option>`;
                    });
                    selectLocal.innerHTML = html;
                    selectLocal.disabled = false;
                } else {
                    selectLocal.innerHTML = '<option value="">Nenhum local cadastrado</option>';
                    selectLocal.disabled = true;
                }
            } catch (error) {
                selectLocal.innerHTML = '<option value="">Erro ao carregar locais</option>';
                selectLocal.disabled = true;
            }
        }
        
        async function carregarEmpresasParaEntrada() {
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            const selectEmpresa = document.getElementById('ent-empresa');
            
            if (usuarioLogado.perfil_id == 1) {
                // Administrador: todas as empresas
                const empresas = await chamarAPI('empresas', 'listar');
                if (empresas.sucesso && empresas.dados) {
                    let html = '<option value="">Selecione a empresa...</option>';
                    empresas.dados.forEach(emp => {
                        html += `<option value="${emp.id}">${emp.nome}</option>`;
                    });
                    selectEmpresa.innerHTML = html;
                }
            } else {
                // Outros perfis: empresas vinculadas
                const empresasVinculadas = usuarioLogado.empresas_vinculadas || [];
                
                if (empresasVinculadas.length == 1) {
                    // Uma empresa: pré-selecionar e carregar materiais
                    selectEmpresa.innerHTML = `<option value="${empresasVinculadas[0].id}" selected>${empresasVinculadas[0].nome}</option>`;
                    carregarMateriaisPorEmpresa('ent-material', empresasVinculadas[0].id);
                } else if (empresasVinculadas.length > 1) {
                    // Múltiplas empresas
                    let html = '<option value="">Selecione a empresa...</option>';
                    empresasVinculadas.forEach(emp => {
                        html += `<option value="${emp.id}">${emp.nome}</option>`;
                    });
                    selectEmpresa.innerHTML = html;
                } else {
                    selectEmpresa.innerHTML = '<option value="">Nenhuma empresa vinculada</option>';
                }
            }
            
            // Adicionar event listener para busca de materiais
            const inputBusca = document.getElementById('ent-material-busca');
            if (inputBusca) {
                inputBusca.addEventListener('input', function() {
                    buscarMateriais(this.value);
                });
            }
            
            // Fechar lista ao clicar fora
            document.addEventListener('click', function(e) {
                const lista = document.getElementById('ent-material-lista');
                if (lista && !e.target.closest('.form-group')) {
                    lista.style.display = 'none';
                }
            });
        }
        
        async function carregarEntradas(append = false) {
            if (carregandoEntrada) return;
            carregandoEntrada = true;

            if (!append) {
                // Resetar paginação se não for append
                offsetEntrada = 0;
                fimEntrada = false;
                // Carregar dependências apenas na primeira carga
                await carregarEmpresasParaEntrada();
                await carregarLocaisEntrada();
            }

            const loadingIndicator = document.getElementById('loading-entrada');
            if (loadingIndicator) loadingIndicator.style.display = 'block';
            
            try {
                const resultado = await chamarAPI('entrada', 'listar', null, `&limit=${limitEntrada}&offset=${offsetEntrada}`);
                
                if (loadingIndicator) loadingIndicator.style.display = 'none';

                if (resultado.sucesso) {
                    const listaContainer = document.getElementById('lista-entradas');
                    
                    if (!append) {
                        listaContainer.innerHTML = `
                            <table>
                                <thead>
                                    <tr>
                                        <th>Data</th><th>Material</th><th>Local</th><th>Quantidade</th><th>Nota Fiscal</th><th>Responsável</th><th>Observação</th><th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-entradas"></tbody>
                            </table>
                            <div id="sentinela-entrada" style="height: 20px; margin-top: 10px;"></div>
                            <div id="loading-entrada" style="display: none; text-align: center; padding: 10px;">Carregando mais...</div>
                        `;
                        // Iniciar observador
                        const sentinela = document.getElementById('sentinela-entrada');
                        if (sentinela) entradaObserver.observe(sentinela);
                    }

                    const tbody = document.getElementById('tbody-entradas');
                    
                    if (resultado.dados.length < limitEntrada) {
                        fimEntrada = true;
                        if (document.getElementById('sentinela-entrada')) {
                            entradaObserver.unobserve(document.getElementById('sentinela-entrada'));
                            document.getElementById('sentinela-entrada').style.display = 'none';
                        }
                    }

                    if (resultado.dados.length === 0 && !append) {
                        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">Nenhuma entrada registrada</td></tr>';
                    } else {
                        let html = '';
                        resultado.dados.forEach(ent => {
                            const materialEscaped = (ent.material_nome || '').replace(/'/g, "\\'");
                            const obsEscaped = (ent.observacao || '').replace(/'/g, "\\'");
                            const nfEscaped = (ent.nota_fiscal || '').replace(/'/g, "\\'");
                            
                            html += `<tr>
                                <td>${formatarData(ent.data_entrada)}</td>
                                <td><a href="#" onclick="abrirDetalhesMaterial(${ent.material_id}); return false;" style="color: #2563eb; text-decoration: none;">${ent.material_nome}</a></td>
                                <td>${ent.local_nome || '-'}</td>
                                <td>${ent.quantidade}</td>
                                <td>${ent.nota_fiscal}</td>
                                <td>${ent.responsavel_nome || '-'}</td>
                                <td>${ent.observacao || '-'}</td>
                                <td>
                                    <button class="btn btn-secondary btn-sm" onclick="editarMovimentacao(${ent.id}, 'Entrada', '${materialEscaped}', ${ent.quantidade}, '${nfEscaped}', '${obsEscaped}', ${ent.local_destino_id || ''})" style="margin-right: 5px; padding: 5px 10px; font-size: 12px;">✏️ Editar</button>
                                    <button class="btn btn-danger btn-sm" onclick="excluirEntrada(${ent.id}, '${materialEscaped}', ${ent.quantidade})" style="padding: 5px 10px; font-size: 12px;">🗑️ Excluir</button>
                                </td>
                            </tr>`;
                        });
                        tbody.insertAdjacentHTML('beforeend', html);
                        offsetEntrada += resultado.dados.length;
                    }
                }
            } catch (error) {
                console.error('Erro ao carregar entradas:', error);
                if (loadingIndicator) loadingIndicator.style.display = 'none';
            } finally {
                carregandoEntrada = false;
            }

            if (!append) {
                document.getElementById('ent-data').valueAsDate = new Date();
            }
        }

        async function excluirEntrada(id, material, qtd) {
            if (!confirm(`Tem certeza que deseja excluir a entrada de ${qtd} unidades do material "${material}"?\n\nO estoque será reduzido automaticamente.`)) {
                return;
            }

            const resultado = await chamarAPI('entrada', 'excluir', { id: id });
            
            if (resultado.sucesso) {
                exibirNotificacaoSistema(resultado.mensagem || 'Entrada excluída com sucesso!', 'success');
                carregarEntradas();
                // Atualizar dashboard se estiver visível
                if (document.getElementById('dashboard').classList.contains('active')) {
                    carregarResumoGeral();
                }
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }


        let itensEntrada = [];

        function adicionarItemEntrada() {
            const materialId = parseInt(document.getElementById('ent-material').value);
            const materialNome = document.getElementById('ent-material-busca').value;
            const quantidade = parseFloat(document.getElementById('ent-quantidade').value);
            const localId = parseInt(document.getElementById('ent-local').value);
            const localNome = document.getElementById('ent-local').options[document.getElementById('ent-local').selectedIndex]?.text || '-';
            
            if (!materialId || !quantidade || quantidade <= 0) {
                exibirNotificacaoSistema('Selecione um material e uma quantidade válida', 'warning');
                return;
            }

            // Adicionar ao array
            itensEntrada.push({
                material_id: materialId,
                material_nome: materialNome,
                quantidade: quantidade,
                local_destino_id: localId,
                local_nome: localNome
            });

            atualizarTabelaItensEntrada();
            
            // Limpar campos de item
            document.getElementById('ent-material').value = '';
            document.getElementById('ent-material-busca').value = '';
            document.getElementById('ent-quantidade').value = '';
            // Manter local selecionado para facilitar
        }

        function removerItemEntrada(index) {
            itensEntrada.splice(index, 1);
            atualizarTabelaItensEntrada();
        }

        function atualizarTabelaItensEntrada() {
            const tbody = document.getElementById('tbody-lista-entrada');
            const container = document.getElementById('container-lista-entrada');
            
            if (itensEntrada.length === 0) {
                container.style.display = 'none';
                tbody.innerHTML = '';
                return;
            }

            container.style.display = 'block';
            let html = '';
            
            itensEntrada.forEach((item, index) => {
                html += `
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 8px;">${item.material_nome}</td>
                        <td style="padding: 8px;">${item.quantidade}</td>
                        <td style="padding: 8px;">${item.local_nome}</td>
                        <td style="padding: 8px;">
                            <button class="btn btn-danger btn-sm" onclick="removerItemEntrada(${index})" style="padding: 2px 8px; font-size: 12px;">X</button>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }

        async function registrarEntrada() {
            // Dados comuns
            const dataEntrada = document.getElementById('ent-data').value;
            const notaFiscal = document.getElementById('ent-nf').value;
            const responsavelId = parseInt(document.getElementById('ent-responsavel').value) || null;
            const observacao = document.getElementById('ent-obs').value;

            // Se houver itens na lista, usar modo múltiplo
            if (itensEntrada.length > 0) {
                const dados = {
                    itens: itensEntrada.map(item => ({
                        ...item,
                        data_entrada: dataEntrada,
                        nota_fiscal: notaFiscal,
                        responsavel_id: responsavelId,
                        observacao: observacao
                    }))
                };

                const resultado = await chamarAPI('entrada', 'criar_multipla', dados);
                if (resultado.sucesso) {
                    exibirNotificacaoSistema(resultado.mensagem || 'Entradas registradas com sucesso!', 'success');
                    // Limpar tudo
                    itensEntrada = [];
                    atualizarTabelaItensEntrada();
                    document.getElementById('ent-nf').value = '';
                    document.getElementById('ent-obs').value = '';
                    carregarEntradas();
                } else {
                    exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
                }
            } 
            // Se não houver itens na lista, tentar registrar o item dos campos (modo simples)
            else {
                const dados = {
                    data_entrada: dataEntrada,
                    material_id: parseInt(document.getElementById('ent-material').value),
                    quantidade: parseFloat(document.getElementById('ent-quantidade').value),
                    nota_fiscal: notaFiscal,
                    responsavel_id: responsavelId,
                    local_destino_id: parseInt(document.getElementById('ent-local').value),
                    observacao: observacao
                };

                if (!dados.material_id || !dados.quantidade) {
                    exibirNotificacaoSistema('Adicione itens à lista ou preencha material e quantidade', 'warning');
                    return;
                }

                const resultado = await chamarAPI('entrada', 'criar', dados);
                if (resultado.sucesso) {
                    exibirNotificacaoSistema('Entrada registrada com sucesso!', 'success');
                    document.getElementById('ent-quantidade').value = '';
                    document.getElementById('ent-nf').value = '';
                    document.getElementById('ent-obs').value = '';
                    document.getElementById('ent-material').value = '';
                    document.getElementById('ent-material-busca').value = '';
                    carregarEntradas();
                } else {
                    exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
                }
            }
        }

        // =====================================================================
        // SAÍDA DE MATERIAIS
        // =====================================================================
        async function carregarSaidas(append = false) {
            if (carregandoSaida) return;
            carregandoSaida = true;

            if (!append) {
                offsetSaida = 0;
                fimSaida = false;
                await carregarEmpresasParaSaida();
            }

            const loadingIndicator = document.getElementById('loading-saida');
            if (loadingIndicator) loadingIndicator.style.display = 'block';
            
            try {
                const resultado = await chamarAPI('saida', 'listar', null, `&limit=${limitSaida}&offset=${offsetSaida}`);
                
                if (loadingIndicator) loadingIndicator.style.display = 'none';

                if (resultado.sucesso) {
                    const listaContainer = document.getElementById('lista-saidas');
                    
                    if (!append) {
                        listaContainer.innerHTML = `
                            <table>
                                <thead>
                                    <tr>
                                        <th>Data</th><th>Material</th><th>Quantidade</th><th>Empresa</th><th>Finalidade/Observação</th><th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-saidas"></tbody>
                            </table>
                            <div id="sentinela-saida" style="height: 20px; margin-top: 10px;"></div>
                            <div id="loading-saida" style="display: none; text-align: center; padding: 10px;">Carregando mais...</div>
                        `;
                        const sentinela = document.getElementById('sentinela-saida');
                        if (sentinela) saidaObserver.observe(sentinela);
                    }

                    const tbody = document.getElementById('tbody-saidas');

                    if (resultado.dados.length < limitSaida) {
                        fimSaida = true;
                        if (document.getElementById('sentinela-saida')) {
                            saidaObserver.unobserve(document.getElementById('sentinela-saida'));
                            document.getElementById('sentinela-saida').style.display = 'none';
                        }
                    }

                    if (resultado.dados.length === 0 && !append) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Nenhuma saída registrada</td></tr>';
                    } else {
                        let html = '';
                        resultado.dados.forEach(sai => {
                            const materialEscaped = (sai.material_nome || '').replace(/'/g, "\\'");
                            const obsEscaped = (sai.observacao || '').replace(/'/g, "\\'");
                            
                            html += `<tr>
                                <td>${formatarData(sai.data_saida)}</td>
                                <td><a href="#" onclick="abrirDetalhesMaterial(${sai.material_id}); return false;" style="color: #2563eb; text-decoration: none;">${sai.material_nome}</a></td>
                                <td>${sai.quantidade}</td>
                                <td>${sai.empresa_nome || '-'}</td>
                                <td>${sai.finalidade || '-'} / ${sai.observacao || '-'}</td>
                                <td>
                                    <button class="btn btn-secondary btn-sm" onclick="editarMovimentacao(${sai.id}, 'Saída', '${materialEscaped}', ${sai.quantidade}, '', '${obsEscaped}', ${sai.local_origem_id || ''})" style="margin-right: 5px; padding: 5px 10px; font-size: 12px;">✏️ Editar</button>
                                </td>
                            </tr>`;
                        });
                        tbody.insertAdjacentHTML('beforeend', html);
                        offsetSaida += resultado.dados.length;
                    }
                }
            } catch (error) {
                console.error('Erro ao carregar saídas:', error);
                if (loadingIndicator) loadingIndicator.style.display = 'none';
            } finally {
                carregandoSaida = false;
            }

            if (!append) {
                document.getElementById('sai-data').valueAsDate = new Date();
            }
        }

        
        async function carregarEmpresasParaSaida() {
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            const selectEmpresa = document.getElementById('sai-empresa');
            
            if (usuarioLogado.perfil_id == 1) {
                // Administrador: todas as empresas
                const empresas = await chamarAPI('empresas', 'listar');
                if (empresas.sucesso && empresas.dados) {
                    let html = '<option value="">Selecione a empresa...</option>';
                    empresas.dados.forEach(emp => {
                        html += `<option value="${emp.id}">${emp.nome}</option>`;
                    });
                    selectEmpresa.innerHTML = html;
                }
            } else {
                // Outros perfis: empresas vinculadas
                const empresasVinculadas = usuarioLogado.empresas_vinculadas || [];
                
                if (empresasVinculadas.length == 1) {
                    // Uma empresa: pré-selecionar e carregar materiais
                    selectEmpresa.innerHTML = `<option value="${empresasVinculadas[0].id}" selected>${empresasVinculadas[0].nome}</option>`;
                    await carregarMateriaisSaida(empresasVinculadas[0].id);
                } else if (empresasVinculadas.length > 1) {
                    // Múltiplas empresas
                    let html = '<option value="">Selecione a empresa...</option>';
                    empresasVinculadas.forEach(emp => {
                        html += `<option value="${emp.id}">${emp.nome}</option>`;
                    });
                    selectEmpresa.innerHTML = html;
                } else {
                    selectEmpresa.innerHTML = '<option value="">Nenhuma empresa vinculada</option>';
                }
            }

            // Adicionar event listener para busca de materiais (Saída)
            const inputBuscaSaida = document.getElementById('sai-material-busca');
            // Remover listener anterior para evitar duplicação (cloneNode)
            if (inputBuscaSaida) {
                const novoInput = inputBuscaSaida.cloneNode(true);
                inputBuscaSaida.parentNode.replaceChild(novoInput, inputBuscaSaida);
                
                novoInput.addEventListener('input', function() {
                    buscarMateriaisSaida(this.value);
                });
                
                // Re-adicionar foco se necessário (opcional, mas clone perde foco)
            }
            
            // Fechar lista ao clicar fora
            document.addEventListener('click', function(e) {
                const listaSaida = document.getElementById('sai-material-lista');
                if (listaSaida && !e.target.closest('.form-group')) {
                    listaSaida.style.display = 'none';
                }
            });
        }
        
        let materiaisSaida = [];

        async function carregarMateriaisSaida(empresaId) {
            const inputBusca = document.getElementById('sai-material-busca');
            const inputHidden = document.getElementById('sai-material');
            const locaisContainer = document.getElementById('sai-locais-container');

            if (!empresaId) {
                inputBusca.placeholder = 'Selecione a empresa primeiro';
                inputBusca.disabled = true;
                inputBusca.value = '';
                inputHidden.value = '';
                materiaisSaida = [];
                // Limpar também o container de locais
                if (locaisContainer) {
                    locaisContainer.innerHTML = '<p style="color: #64748b; text-align: center; padding: 20px;">Selecione um material para ver os locais disponíveis</p>';
                }
                return;
            }

            inputBusca.placeholder = 'Carregando materiais...';
            inputBusca.disabled = true;

            try {
                const resultado = await chamarAPI('materiais', 'por_empresa', null, `&empresa_id=${empresaId}`);

                if (resultado.sucesso && resultado.dados) {
                    materiaisSaida = resultado.dados;
                    inputBusca.placeholder = 'Digite para buscar material...';
                    inputBusca.disabled = false;
                    inputBusca.value = '';
                    inputHidden.value = '';
                } else {
                    materiaisSaida = [];
                    inputBusca.placeholder = 'Nenhum material encontrado';
                    inputBusca.disabled = true;
                }
            } catch (error) {
                materiaisSaida = [];
                inputBusca.placeholder = 'Erro ao carregar materiais';
                inputBusca.disabled = true;
            }
        }
        
        function buscarMateriaisSaida(termo) {
            const lista = document.getElementById('sai-material-lista');
            
            if (!termo || termo.length < 2) {
                lista.style.display = 'none';
                return;
            }
            
            const termoLower = termo.toLowerCase();
            const materiaisFiltrados = materiaisSaida.filter(mat => 
                mat.nome.toLowerCase().includes(termoLower) || 
                mat.codigo_sku.toLowerCase().includes(termoLower)
            );
            
            if (materiaisFiltrados.length === 0) {
                lista.innerHTML = '<div class="material-autocomplete-item">Nenhum material encontrado</div>';
                lista.style.display = 'block';
                return;
            }
            
            let html = '';
            materiaisFiltrados.forEach(mat => {
                const estoqueClass = mat.estoque_atual <= 0 ? 'style="color: red;"' : '';
                html += `<div class="material-autocomplete-item" onclick="selecionarMaterialSaida(${mat.id}, '${mat.nome.replace(/'/g, "\\'")}', '${mat.codigo_sku}', ${mat.estoque_atual})">
                    <strong>${mat.nome}</strong><br>
                    <small>SKU: ${mat.codigo_sku} | <span ${estoqueClass}>Estoque: ${mat.estoque_atual}</span></small>
                </div>`;
            });
            
            lista.innerHTML = html;
            lista.style.display = 'block';
        }
        
        async function selecionarMaterialSaida(id, nome, codigo, estoque) {
            document.getElementById('sai-material-busca').value = `${nome} (${codigo})`;
            document.getElementById('sai-material').value = id;
            document.getElementById('sai-material-lista').style.display = 'none';

            // Carregar locais com estoque do material
            await carregarLocaisComEstoque(id);

            // Alertar se estoque baixo
            if (estoque <= 0) {
                exibirNotificacaoSistema('Atenção: Este material está sem estoque!', 'warning');
            }
        }

        async function carregarLocaisComEstoque(materialId) {
            const container = document.getElementById('sai-locais-container');

            if (!materialId) {
                container.innerHTML = '<p style="color: #64748b; text-align: center; padding: 20px;">Selecione um material para ver os locais disponíveis</p>';
                return;
            }

            try {
                // Carregar estoque por local para este material
                const resultado = await chamarAPI('materiais', 'estoque_por_local', null, `&material_id=${materialId}`);

                if (resultado.sucesso && resultado.dados && resultado.dados.length > 0) {
                    let html = '<div style="max-height: 150px; overflow-y: auto;">';

                    // Para cada local com estoque, criar um campo de quantidade
                    resultado.dados.forEach(local => {
                        if (local.estoque > 0) { // Mostrar apenas locais com estoque
                            html += `
                                <div class="local-estoque-item" style="display: flex; align-items: center; margin-bottom: 8px; padding: 8px; background: white; border-radius: 0.25rem; border: 1px solid #e2e8f0;">
                                    <div style="flex: 1;">
                                        <strong>${local.local_nome}</strong><br>
                                        <small>Disponível: ${local.estoque}</small>
                                    </div>
                                    <div style="width: 100px; margin-left: 10px;">
                                        <input type="number"
                                               class="saida-local-qtd"
                                               data-local-id="${local.local_id}"
                                               data-estoque-max="${local.estoque}"
                                               placeholder="Qtd"
                                               min="0"
                                               max="${local.estoque}"
                                               step="0.01"
                                               style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 0.25rem;">
                                    </div>
                                </div>
                            `;
                        }
                    });

                    html += '</div>';
                    container.innerHTML = html;

                    // Adicionar eventos para validar quantidades e atualizar total
                    document.querySelectorAll('.saida-local-qtd').forEach(input => {
                        input.addEventListener('input', function() {
                            const max = parseFloat(this.getAttribute('data-estoque-max'));
                            const valor = parseFloat(this.value) || 0;

                            if (valor > max) {
                                this.value = max;
                                exibirNotificacaoSistema(`Quantidade excede o estoque disponível (${max})`, 'warning');
                            } else if (valor < 0) {
                                this.value = 0;
                            }

                            // Calcular soma total
                            let somaTotal = 0;
                            document.querySelectorAll('.saida-local-qtd').forEach(inp => {
                                somaTotal += parseFloat(inp.value) || 0;
                            });
                            
                            // Atualizar campo de quantidade total
                            const campoTotal = document.getElementById('sai-quantidade-total');
                            if (campoTotal) {
                                campoTotal.value = somaTotal > 0 ? somaTotal : '';
                            }
                        });
                    });
                } else {
                    container.innerHTML = '<p style="color: #64748b; text-align: center; padding: 20px;">Este material não está em nenhum local ou está com estoque 0</p>';
                }
            } catch (error) {
                container.innerHTML = '<p style="color: #ef4444; text-align: center; padding: 20px;">Erro ao carregar locais com estoque</p>';
                console.error('Erro ao carregar locais com estoque:', error);
            }
        }

        async function registrarSaida() {
            const materialId = parseInt(document.getElementById('sai-material').value);
            const quantidadeTotal = parseFloat(document.getElementById('sai-quantidade-total').value);
            const empresaSolicitanteId = parseInt(document.getElementById('sai-empresa').value);
            const observacao = document.getElementById('sai-obs').value;

            if (!materialId || !quantidadeTotal || !observacao) {
                exibirNotificacaoSistema('Preencha todos os campos obrigatórios (Material, Quantidade Total e Finalidade)', 'warning');
                return;
            }

            if (!empresaSolicitanteId) {
                exibirNotificacaoSistema('Selecione a empresa', 'warning');
                return;
            }

            // Obter todas as quantidades especificadas nos locais
            const inputsQuantidade = document.querySelectorAll('.saida-local-qtd');
            const saidasLocais = [];
            let quantidadeTotalInformada = 0;

            inputsQuantidade.forEach(input => {
                const quantidade = parseFloat(input.value) || 0;
                if (quantidade > 0) {
                    const localId = parseInt(input.getAttribute('data-local-id'));
                    const estoqueMax = parseFloat(input.getAttribute('data-estoque-max'));

                    if (quantidade > estoqueMax) {
                        exibirNotificacaoSistema(`Quantidade para o local excede o estoque disponível (${estoqueMax})`, 'error');
                        return;
                    }

                    saidasLocais.push({
                        local_id: localId,
                        quantidade: quantidade
                    });
                    quantidadeTotalInformada += quantidade;
                }
            });

            if (saidasLocais.length === 0) {
                exibirNotificacaoSistema('Selecione pelo menos um local e quantidade para remover', 'warning');
                return;
            }

            if (Math.abs(quantidadeTotalInformada - quantidadeTotal) > 0.01) { // Tolerância para floats
                exibirNotificacaoSistema(`A soma das quantidades (${quantidadeTotalInformada}) deve ser igual à quantidade total informada (${quantidadeTotal})`, 'warning');
                return;
            }

            // Preparar dados para envio
            const dados = {
                data_saida: document.getElementById('sai-data').value,
                material_id: materialId,
                quantidade_total: quantidadeTotal,
                empresa_solicitante_id: empresaSolicitanteId,
                saidas_por_local: saidasLocais, // Array com as saídas por local
                observacao: observacao,
                finalidade: observacao
            };

            const resultado = await chamarAPI('saida', 'criar_multipla', dados);
            if (resultado.sucesso) {
                exibirNotificacaoSistema('Saída registrada com sucesso em múltiplos locais!', 'success');
                // Limpar formulário
                document.getElementById('sai-quantidade-total').value = '';
                document.getElementById('sai-obs').value = '';
                document.getElementById('sai-material').value = '';
                document.getElementById('sai-material-busca').value = '';
                const container = document.getElementById('sai-locais-container');
                container.innerHTML = '<p style="color: #64748b; text-align: center; padding: 20px;">Selecione um material para ver os locais disponíveis</p>';
                carregarSaidas();
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }

        // =====================================================================
        // ALERTAS
        // =====================================================================
        async function carregarAlertas() {
            // Carregar lista de empresas para o filtro
            await carregarEmpresasParaAlertas();
            
            const empresaFiltro = document.getElementById('filtro-empresa-alertas').value;
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            
            // Buscar todos os materiais (filtrados por empresa se necessário)
            let parametrosExtras = '';
            if (empresaFiltro) {
                parametrosExtras = `&empresa_id=${empresaFiltro}`;
            }
            
            
            const resultado = await chamarAPI('materiais', 'listar', null, parametrosExtras);
            
            if (resultado.sucesso && resultado.dados) {
                const materiais = resultado.dados;
                
                // A API já filtra automaticamente por empresas permitidas
                // Não precisa filtrar novamente aqui
                
                // Separar alertas de estoque baixo e alto
                const alertasBaixo = materiais.filter(m => {
                    return m.ponto_reposicao && m.estoque_atual < m.ponto_reposicao;
                });
                
                const alertasAlto = materiais.filter(m => {
                    return m.estoque_maximo && m.estoque_atual > m.estoque_maximo;
                });
                
                // Renderizar alertas de estoque baixo
                renderizarAlertasBaixo(alertasBaixo);
                
                // Renderizar alertas de sobressalência
                renderizarAlertasAlto(alertasAlto);
            } else {
                // Se falhar, mostrar erro
                document.getElementById('lista-alertas-baixo').innerHTML = '<p style="color: red;">Erro ao carregar alertas: ' + (resultado.erro || 'Erro desconhecido') + '</p>';
                document.getElementById('lista-alertas-alto').innerHTML = '<p style="color: red;">Erro ao carregar alertas</p>';
            }
        }
        
        async function carregarEmpresasParaAlertas() {
            const select = document.getElementById('filtro-empresa-alertas');
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            
            let html = '<option value="">Todas as empresas</option>';
            
            if (usuarioLogado.perfil_id == 1) {
                // Admin: todas as empresas
                const resultado = await chamarAPI('empresas', 'listar');
                if (resultado.sucesso && resultado.dados) {
                    resultado.dados.forEach(emp => {
                        html += `<option value="${emp.id}">${emp.nome}</option>`;
                    });
                }
            } else {
                // Outros: empresas vinculadas
                const empresasVinculadas = usuarioLogado.empresas_vinculadas || [];
                empresasVinculadas.forEach(emp => {
                    html += `<option value="${emp.id}">${emp.nome}</option>`;
                });
            }
            
            select.innerHTML = html;
        }
        
        function renderizarAlertasBaixo(alertas) {
            const container = document.getElementById('lista-alertas-baixo');
            
            if (alertas.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #10b981; padding: 20px;"><strong>✓ Nenhum material com estoque baixo!</strong></p>';
                return;
            }
            
            let html = '<table><thead><tr><th>Material</th><th>Empresa</th><th>Estoque Atual</th><th>Ponto Reposição</th><th>% Disponível</th><th>Status</th></tr></thead><tbody>';
            alertas.forEach(mat => {
                const percentual = ((mat.estoque_atual / mat.ponto_reposicao) * 100).toFixed(0);
                const statusClass = percentual <= 50 ? 'status status-critico' : 'status status-alerta';
                const statusTexto = percentual <= 50 ? 'CRÍTICO' : 'ALERTA';
                
                html += `<tr>
                    <td><strong>${mat.nome}</strong><br><small>SKU: ${mat.codigo_sku}</small></td>
                    <td>${mat.empresa_nome || '-'}</td>
                    <td style="color: #dc2626; font-weight: bold;">${mat.estoque_atual}</td>
                    <td>${mat.ponto_reposicao}</td>
                    <td>${percentual}%</td>
                    <td><span class="${statusClass}">${statusTexto}</span></td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        }
        
        function renderizarAlertasAlto(alertas) {
            const container = document.getElementById('lista-alertas-alto');
            
            if (alertas.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #10b981; padding: 20px;"><strong>✓ Nenhum material com estoque excedente!</strong></p>';
                return;
            }
            
            let html = '<table><thead><tr><th>Material</th><th>Empresa</th><th>Estoque Atual</th><th>Estoque Máximo</th><th>Excedente</th><th>Status</th></tr></thead><tbody>';
            alertas.forEach(mat => {
                const excedente = mat.estoque_atual - mat.estoque_maximo;
                const percentualExcesso = ((excedente / mat.estoque_maximo) * 100).toFixed(0);
                
                html += `<tr>
                    <td><strong>${mat.nome}</strong><br><small>SKU: ${mat.codigo_sku}</small></td>
                    <td>${mat.empresa_nome || '-'}</td>
                    <td style="color: #f59e0b; font-weight: bold;">${mat.estoque_atual}</td>
                    <td>${mat.estoque_maximo}</td>
                    <td style="color: #f59e0b;">+${excedente} (+${percentualExcesso}%)</td>
                    <td><span class="status status-alerta">⚠️ EXCESSO</span></td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        // =====================================================================
        // RELATÓRIOS
        // =====================================================================
        
        async function carregarEmpresasRelatorio() {
            const select = document.getElementById('rel-mov-empresa');
            if (!select) return;
            
            // Se já tiver opções além da padrão, não recarregar
            if (select.options.length > 1) return;
            
            const resultado = await chamarAPI('empresas', 'listar');
            if (resultado.sucesso && resultado.dados) {
                let html = '<option value="">Todas</option>';
                resultado.dados.forEach(emp => {
                    html += `<option value="${emp.id}">${emp.nome}</option>`;
                });
                select.innerHTML = html;
            }
        }

        async function carregarEmpresasRelatorioInventario() {
            const select = document.getElementById('filtro-empresa-inv');
            if (!select) return;
            
            // Se já tiver opções além da padrão, não recarregar
            if (select.options.length > 1) return;
            
            const resultado = await chamarAPI('empresas', 'listar');
            if (resultado.sucesso && resultado.dados) {
                let html = '<option value="">Todas as empresas</option>';
                resultado.dados.forEach(emp => {
                    html += `<option value="${emp.id}">${emp.nome}</option>`;
                });
                select.innerHTML = html;
            }
        }

        // Variável para controlar a aba ativa
        let relatorioAtivo = 'estoque';

        function trocarRelatorio(tipo) {
            relatorioAtivo = tipo;
            
            // Atualizar classes das abas
            document.querySelectorAll('.tab-relatorio').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Atualizar visibilidade dos conteúdos
            document.querySelectorAll('.relatorio-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(`rel-${tipo}`).classList.add('active');
            
            // Carregar dados do relatório selecionado
            carregarRelatorios();
        }

        async function carregarRelatorios() {
            const containerId = `relatorio-${relatorioAtivo}-empresa`; // Ajuste conforme IDs do HTML
            // Mapeamento de IDs de container para cada tipo
            let targetId = '';
            let apiAcao = '';
            
            switch(relatorioAtivo) {
                case 'estoque':
                    targetId = 'relatorio-estoque-empresa';
                    apiAcao = 'estoque_por_empresa';
                    break;
                case 'movimentacoes':
                    targetId = 'relatorio-movimentacoes'; // ID corrigido para corresponder ao HTML
                    apiAcao = 'movimentacoes';
                    await carregarEmpresasRelatorio();
                    break;
                case 'consumo':
                    targetId = 'relatorio-consumo-empresa';
                    apiAcao = 'consumo_por_empresa';
                    break;
                case 'inventario':
                    targetId = 'relatorio-inventario';
                    apiAcao = 'inventario';
                    await carregarEmpresasRelatorioInventario();
                    break;
                case 'baixoestoque':
                    targetId = 'relatorio-baixo-estoque';
                    apiAcao = 'estoque_baixo';
                    await carregarEmpresasRelatorioBaixoEstoque();
                    break;
                case 'sobressalencia':
                    targetId = 'relatorio-sobressalencia-lista';
                    apiAcao = 'sobressalencia';
                    break;
            }
            
            // Se for movimentações, pegar filtros
            let params = '';
            if (relatorioAtivo === 'movimentacoes') {
                const periodo = document.getElementById('rel-mov-periodo')?.value || 30;
                const tipoMov = document.getElementById('rel-mov-tipo')?.value || 'todos';
                const empresa = document.getElementById('rel-mov-empresa')?.value || '';
                params = `&periodo=${periodo}&tipo_mov=${tipoMov}&empresa_id=${empresa}`;
            } else if (relatorioAtivo === 'inventario') {
                const empresa = document.getElementById('filtro-empresa-inv')?.value || '';
                if (empresa) params = `&empresa_id=${empresa}`;
            } else if (relatorioAtivo === 'baixoestoque') {
                const empresa = document.getElementById('filtro-empresa-baixo')?.value || '';
                if (empresa) params = `&empresa_id=${empresa}`;
            }
            
            // Elemento alvo
            const container = document.getElementById(targetId);
            if (!container) return; // Se elemento não existir ainda
            
            container.innerHTML = '<div class="loading"><div class="spinner"></div> Carregando...</div>';
            
            try {
                const resultado = await chamarAPI('relatorios', apiAcao, null, params);
                
                if (resultado.sucesso) {
                    renderizarRelatorio(relatorioAtivo, resultado.dados, container);
                } else {
                    container.innerHTML = `<p class="error">Erro ao carregar relatório: ${resultado.erro || 'Erro desconhecido'}</p>`;
                }
            } catch (erro) {
                console.error('Erro ao carregar relatório:', erro);
                container.innerHTML = '<p style="color: red;">Erro ao carregar dados.</p>';
            }
        }
        
        // Cache global para inventário
        let dadosInventarioCache = [];
        
        function carregarRelatorioInventario() {
            const empresaId = document.getElementById('filtro-empresa-inv').value;
            const busca = document.getElementById('busca-inventario').value;
            
            if (document.getElementById('rel-inventario').classList.contains('active')) {
                const container = document.getElementById('relatorio-inventario');
                if (!container) return;
                
                // Se for APENAS busca (sem mudar empresa) e já temos dados, filtrar localmente
                // Mas como saber se mudou empresa? 
                // Vamos assumir que se chamou essa função, pode ser filtro ou busca.
                // Se o evento foi oninput da busca, ok. Se foi onchange da empresa, precisamos recarregar.
                // Para simplificar: Se temos cache E o cache contém dados compatíveis com o filtro atual... difícil saber.
                // Melhor abordagem: Se o usuário está digitando (busca), usamos cache. 
                // Se o usuário mudou a empresa, limpamos cache e buscamos de novo.
                // Como distinguir? Pelo evento? Não temos acesso fácil aqui.
                
                // Vamos confiar que se busca tem valor e cache tem valor, é refinamento.
                // Mas se o usuário mudou a empresa, o cache antigo (de outra empresa ou de todas) pode estar lá.
                // O ideal é guardar o 'estado' do cache (qual empresa_id ele representa).
                
                // Solução temporária robusta: Sempre buscar na API se mudar empresa.
                // Para detectar mudança de empresa, podemos comparar com uma variável global ou atributo.
                
                const empresaCache = container.getAttribute('data-empresa-cache');
                
                // Se a empresa mudou, invalidar cache
                if (empresaCache !== empresaId) {
                    dadosInventarioCache = [];
                    container.setAttribute('data-empresa-cache', empresaId);
                }
                
                if (dadosInventarioCache.length > 0 && busca) {
                    filtrarInventarioLocalmente(busca);
                    return;
                }
                
                let params = '';
                if (empresaId) params += `&empresa_id=${empresaId}`;
                
                container.innerHTML = '<div class="loading"><div class="spinner"></div> Carregando...</div>';
                
                console.log('Buscando inventário com params:', params);
                
                chamarAPI('relatorios', 'inventario', null, params).then(resultado => {
                    console.log('Resultado inventário:', resultado);
                    if (resultado.sucesso) {
                        dadosInventarioCache = resultado.dados;
                        if (busca) {
                            filtrarInventarioLocalmente(busca, container);
                        } else {
                            renderizarRelatorio('inventario', dadosInventarioCache, container);
                        }
                    } else {
                        container.innerHTML = `<p class="error">Erro ao carregar dados: ${resultado.erro || 'Erro desconhecido'}</p>`;
                    }
                }).catch(erro => {
                    console.error('Erro fatal no inventário:', erro);
                    container.innerHTML = `<p class="error">Erro de conexão: ${erro.message}</p>`;
                });
            }
        }
        
        function filtrarInventario() {
            const busca = document.getElementById('busca-inventario').value;
            const container = document.getElementById('relatorio-inventario');
            if (!container) return;

            if (dadosInventarioCache.length > 0) {
                filtrarInventarioLocalmente(busca, container);
            } else {
                carregarRelatorioInventario();
            }
        }
        
        function filtrarInventarioLocalmente(termo, container) {
            if (!container) container = document.getElementById('relatorio-inventario');
            if (!container) return;

            if (!termo) {
                renderizarRelatorio('inventario', dadosInventarioCache, container);
                return;
            }
            
            const termoLower = termo.toLowerCase();
            const dadosFiltrados = dadosInventarioCache.filter(d => 
                d.nome.toLowerCase().includes(termoLower) || 
                d.codigo_sku.toLowerCase().includes(termoLower)
            );
            renderizarRelatorio('inventario', dadosFiltrados, container);
        }
        
        // Funções do Modal de Detalhes
        function verDetalhesMaterial(id, nome, sku, estoque) {
            document.getElementById('detalhe-titulo').textContent = `Detalhes: ${nome}`;
            document.getElementById('detalhe-info').innerHTML = `
                <strong>SKU:</strong> ${sku} <br>
                <strong>Estoque Atual:</strong> ${estoque}
            `;
            
            const modal = document.getElementById('modal-detalhes-material');
            modal.classList.add('active');
            
            const containerHist = document.getElementById('detalhe-historico');
            containerHist.innerHTML = '<div class="loading"><div class="spinner"></div> Carregando histórico...</div>';
            
            // Carregar histórico
            chamarAPI('relatorios', 'movimentacoes', null, `&material_id=${id}`).then(res => {
                if (res.sucesso && res.dados) {
                    if (res.dados.length === 0) {
                        containerHist.innerHTML = '<p>Nenhuma movimentação registrada.</p>';
                        return;
                    }
                    
                    let html = '<table class="table-sm"><thead><tr><th>Data</th><th>Tipo</th><th>Qtd</th><th>Empresa/Resp.</th></tr></thead><tbody>';
                    res.dados.forEach(mov => {
                        const corTipo = mov.tipo === 'Entrada' ? 'text-green-600' : 'text-red-600';
                        const sinal = mov.tipo === 'Entrada' ? '+' : '-';
                        const responsavel = mov.tipo === 'Entrada' ? (mov.responsavel || '-') : (mov.empresa || '-');
                        
                        html += `<tr>
                            <td>${formatarData(mov.data)}</td>
                            <td class="${corTipo}" style="font-weight:bold;">${mov.tipo}</td>
                            <td>${sinal}${mov.quantidade}</td>
                            <td>${responsavel}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    containerHist.innerHTML = html;
                } else {
                    containerHist.innerHTML = '<p>Erro ao carregar histórico.</p>';
                }
            });
        }
        
        // Funções para Baixo Estoque
        async function carregarEmpresasRelatorioBaixoEstoque() {
            const select = document.getElementById('filtro-empresa-baixo');
            if (!select) return;
            
            if (select.options.length > 1) return;
            
            const resultado = await chamarAPI('empresas', 'listar');
            if (resultado.sucesso && resultado.dados) {
                let html = '<option value="">Todas as empresas</option>';
                resultado.dados.forEach(emp => {
                    html += `<option value="${emp.id}">${emp.nome}</option>`;
                });
                select.innerHTML = html;
            }
        }
        
        function carregarRelatorioBaixoEstoque() {
            const empresaId = document.getElementById('filtro-empresa-baixo').value;
            
            if (document.getElementById('rel-baixoestoque').classList.contains('active')) {
                const container = document.getElementById('relatorio-baixo-estoque');
                if (!container) return;
                
                let params = '';
                if (empresaId) params += `&empresa_id=${empresaId}`;
                
                container.innerHTML = '<div class="loading"><div class="spinner"></div> Carregando...</div>';
                
                chamarAPI('relatorios', 'estoque_baixo', null, params).then(resultado => {
                    if (resultado.sucesso) {
                        renderizarRelatorio('baixo_estoque', resultado.dados, container);
                    } else {
                        container.innerHTML = `<p class="error">Erro ao carregar dados: ${resultado.erro || 'Erro desconhecido'}</p>`;
                    }
                }).catch(erro => {
                    console.error('Erro em baixo estoque:', erro);
                    container.innerHTML = `<p class="error">Erro de conexão: ${erro.message}</p>`;
                });
            }
        }

        function renderizarRelatorio(tipo, dados, container) {
            if (!dados || dados.length === 0) {
                container.innerHTML = '<p style="padding: 20px; text-align: center; color: #666;">Nenhum dado encontrado para este relatório.</p>';
                return;
            }
            
            let html = '<table class="data-table"><thead><tr>';
            
            if (tipo === 'estoque') {
                html += '<th>Empresa</th><th>Qtd. Materiais</th><th>Total Itens</th><th>Valor Total (Est.)</th></tr></thead><tbody>';
                dados.forEach(d => {
                    const valorFormatado = parseFloat(d.valor_total || 0).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
                    html += `<tr>
                        <td>${d.nome}</td>
                        <td>${d.total_materiais}</td>
                        <td>${d.total_estoque}</td>
                        <td>${valorFormatado}</td>
                    </tr>`;
                });
            } else if (tipo === 'movimentacoes') {
                html += '<th>Data</th><th>Tipo</th><th>Material</th><th>Empresa</th><th>Qtd</th><th>Responsável</th><th>Ações</th></tr></thead><tbody>';
                dados.forEach(d => {
                    const classeTipo = d.tipo === 'Entrada' ? 'status-adequado' : 'status-critico'; // Verde para entrada, vermelho para saída
                    
                    // Preparar dados para edição (escapar aspas)
                    const materialEscaped = d.material ? d.material.replace(/'/g, "\\'") : '';
                    const nfEscaped = d.nota_fiscal ? d.nota_fiscal.replace(/'/g, "\\'") : '';
                    const obsEscaped = d.observacao ? d.observacao.replace(/'/g, "\\'") : '';
                    
                    html += `<tr>
                        <td>${formatarData(d.data)}</td>
                        <td><span class="${classeTipo}">${d.tipo}</span></td>
                        <td>${d.material}</td>
                        <td>${d.empresa || '-'}</td>
                        <td>${d.quantidade}</td>
                        <td>${d.responsavel || '-'}</td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="editarMovimentacao(${d.id}, '${d.tipo}', '${materialEscaped}', ${d.quantidade}, '${nfEscaped}', '${obsEscaped}')" title="Editar" style="padding: 2px 6px;">
                                ✏️
                            </button>
                        </td>
                    </tr>`;
                });
            } else if (tipo === 'consumo') {
                html += '<th>Empresa</th><th>Total de Saídas</th><th>Itens Consumidos (30 dias)</th></tr></thead><tbody>';
                dados.forEach(d => {
                    html += `<tr>
                        <td>${d.empresa}</td>
                        <td>${d.total_saidas}</td>
                        <td>${d.total_itens}</td>
                    </tr>`;
                });
            } else if (tipo === 'inventario') {
                html += '<th>Material</th><th>SKU</th><th>Categoria</th><th>Empresa</th><th>Local</th><th>Estoque</th><th>Ações</th></tr></thead><tbody>';
                dados.forEach(d => {
                    html += `<tr>
                        <td><a href="#" onclick="abrirDetalhesMaterial(${d.id}); return false;" style="font-weight: bold; color: #2563eb; text-decoration: none;">${d.nome}</a></td>
                        <td>${d.codigo_sku}</td>
                        <td>${d.categoria || '-'}</td>
                        <td>${d.empresa || '-'}</td>
                        <td>${d.local || '-'}</td>
                        <td style="font-weight: bold;">${d.estoque_atual}</td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="abrirDetalhesMaterial(${d.id})">
                                📋 Detalhes
                            </button>
                        </td>
                    </tr>`;
                });
            } else if (tipo === 'baixoestoque' || tipo === 'baixo_estoque') {
                html += '<th>Material</th><th>Empresa</th><th>Estoque</th><th>Ponto Reposição</th><th>Nível</th></tr></thead><tbody>';
                dados.forEach(d => {
                    html += `<tr>
                        <td><strong>${d.nome}</strong><br><small>${d.codigo_sku}</small></td>
                        <td>${d.empresa_nome || '-'}</td>
                        <td style="color: #dc2626; font-weight: bold;">${d.estoque_atual}</td>
                        <td>${d.ponto_reposicao}</td>
                        <td>
                            <div style="width: 100px; background: #e5e7eb; height: 10px; border-radius: 5px; overflow: hidden;">
                                <div style="width: ${Math.min(d.percentual_ponto, 100)}%; background: #dc2626; height: 100%;"></div>
                            </div>
                            <small>${d.percentual_ponto}%</small>
                        </td>
                    </tr>`;
                });
            } else if (tipo === 'sobressalencia') {
                html += '<th>Material</th><th>Empresa</th><th>Estoque</th><th>Estoque Máximo</th><th>Excesso</th></tr></thead><tbody>';
                dados.forEach(d => {
                    html += `<tr>
                        <td><strong>${d.nome}</strong><br><small>${d.codigo_sku}</small></td>
                        <td>${d.empresa_nome || '-'}</td>
                        <td style="color: #f59e0b; font-weight: bold;">${d.estoque_atual}</td>
                        <td>${d.estoque_maximo}</td>
                        <td>${d.percentual_maximo}%</td>
                    </tr>`;
                });
            }
            
            html += '</tbody></table>';
            
            // Adicionar botão de exportar PDF se houver dados
            html += `<div style="margin-top: 15px; text-align: right;">
                        <button class="btn btn-secondary" onclick="exportarPDF('${tipo}')">📄 Exportar PDF</button>
                     </div>`;
                     
            container.innerHTML = html;
        }

        function exportarPDF(tipo) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            let titulo = 'Relatório';
            let colunas = [];
            let linhas = [];
            
            // Capturar dados da tabela visível
            // Nota: Em uma implementação ideal, usaríamos os dados brutos (variável 'dados' do escopo anterior),
            // mas como eles não estão acessíveis aqui facilmente sem refatoração maior, 
            // vamos extrair da tabela HTML gerada que já contém os dados formatados.
            
            const tabela = document.querySelector(`#rel-${tipo} table`);
            if (!tabela) {
                alert('Tabela não encontrada para exportação.');
                return;
            }
            
            // Cabeçalhos
            tabela.querySelectorAll('thead th').forEach(th => {
                colunas.push(th.innerText);
            });
            
            // Linhas
            tabela.querySelectorAll('tbody tr').forEach(tr => {
                let linha = [];
                tr.querySelectorAll('td').forEach(td => {
                    linha.push(td.innerText.replace(/\n/g, ' ')); // Remove quebras de linha para ficar limpo
                });
                linhas.push(linha);
            });
            
            // Definir título baseado no tipo
            switch(tipo) {
                case 'estoque': titulo = 'Relatório de Estoque por Empresa'; break;
                case 'movimentacoes': titulo = 'Histórico de Movimentações'; break;
                case 'consumo': titulo = 'Consumo por Empresa'; break;
                case 'inventario': titulo = 'Inventário Completo'; break;
                case 'baixo_estoque': titulo = 'Relatório de Baixo Estoque'; break;
                case 'sobressalencia': titulo = 'Relatório de Sobressalência'; break;
            }
            
            // Adicionar Título e Data
            doc.setFontSize(18);
            doc.text(titulo, 14, 22);
            
            doc.setFontSize(11);
            doc.setTextColor(100);
            doc.text(`Gerado em: ${new Date().toLocaleString('pt-BR')}`, 14, 30);
            
            // Gerar Tabela
            doc.autoTable({
                head: [colunas],
                body: linhas,
                startY: 40,
                theme: 'grid',
                styles: { fontSize: 8, cellPadding: 2 },
                headStyles: { fillColor: [30, 64, 175] }, // Azul do tema (#1e40af)
                alternateRowStyles: { fillColor: [240, 242, 245] }
            });
            
            // Salvar
            doc.save(`relatorio_${tipo}_${new Date().toISOString().slice(0,10)}.pdf`);
        }

        // =====================================================================
        // USUÁRIOS
        // =====================================================================
        
        function selecionarTodasEmpresas(nomeCheckbox, selecionar) {
            document.querySelectorAll(`input[name="${nomeCheckbox}"]`).forEach(checkbox => {
                checkbox.checked = selecionar;
            });
        }
        async function carregarUsuarios() {
            document.getElementById('lista-usuarios').innerHTML = '<p>Carregando usuários...</p>';
            
            // Verificar se é administrador
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            const isAdmin = usuarioLogado.perfil_id == 1;
            
            // Carregar empresas para os selects apenas se for admin
            if (isAdmin) {
                await carregarEmpresasSelect();
            }
            
            const resultado = await chamarAPI('usuarios', 'listar_completo');
            if (resultado.sucesso && resultado.dados) {
                let html = '<table><thead><tr><th>Nome</th><th>Email</th><th>Perfil</th><th>Empresas</th><th>Status</th>';
                
                // Mostrar coluna de ações apenas para administradores
                if (isAdmin) {
                    html += '<th>Ações</th>';
                }
                
                html += '</tr></thead><tbody>';
                
                resultado.dados.forEach(usr => {
                    const statusClass = usr.ativo == 1 ? 'status-adequado' : 'status-critico';
                    const statusTexto = usr.ativo == 1 ? 'Ativo' : 'Inativo';
                    const empresasTexto = usr.empresas_nomes || (usr.perfil_id == 1 ? 'Todas' : 'Nenhuma');
                    html += `<tr>
                        <td>${usr.nome || ''}</td>
                        <td>${usr.email || ''}</td>
                        <td>${usr.perfil_nome || ''}</td>
                        <td>${empresasTexto}</td>
                        <td><span class="${statusClass}">${statusTexto}</span></td>`;
                    
                    // Botões de ação apenas para administradores
                    if (isAdmin) {
                        html += `<td>
                            <button class="btn btn-secondary" onclick="editarUsuario(${usr.id})">Editar</button>
                            <button class="btn btn-danger" onclick="toggleUsuario(${usr.id}, ${usr.ativo})">${usr.ativo == 1 ? 'Desativar' : 'Ativar'}</button>
                            <button class="btn btn-danger" onclick="excluirUsuario(${usr.id}, '${usr.nome.replace(/'/g, "\\'")}')">Excluir</button>
                        </td>`;
                    }
                    
                    html += '</tr>';
                });
                html += '</tbody></table>';
                document.getElementById('lista-usuarios').innerHTML = html;
            } else {
                document.getElementById('lista-usuarios').innerHTML = '<p>Erro: ' + (resultado.erro || 'Nenhum usuário') + '</p>';
            }
        }

        async function salvarUsuario() {
            // Verificar se é administrador
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            if (usuarioLogado.perfil_id != 1) {
                exibirNotificacaoSistema('Acesso negado! Apenas administradores podem cadastrar usuários.', 'error');
                return;
            }
            
            const senha = document.getElementById('usr-senha').value;
            const confirmaSenha = document.getElementById('usr-confirma-senha').value;
            const perfilId = parseInt(document.getElementById('usr-perfil').value);
            
            if (senha !== confirmaSenha) {
                exibirNotificacaoSistema('As senhas não coincidem!', 'error');
                return;
            }

            // Verificar empresas vinculadas para perfis não-admin
            let empresasVinculadas = [];
            if (perfilId > 1) { // Não é administrador
                const checkboxes = document.querySelectorAll('input[name="usr-empresas"]:checked');
                empresasVinculadas = Array.from(checkboxes).map(cb => parseInt(cb.value));
                
                if (empresasVinculadas.length === 0) {
                    exibirNotificacaoSistema('Selecione pelo menos uma empresa para este perfil', 'warning');
                    return;
                }
            }

            const dados = {
                nome: document.getElementById('usr-nome').value,
                email: document.getElementById('usr-email').value,
                senha: senha,
                perfil_id: perfilId,
                departamento: document.getElementById('usr-departamento').value,
                empresas_vinculadas: empresasVinculadas
            };

            if (!dados.nome || !dados.email || !dados.senha || !dados.perfil_id) {
                exibirNotificacaoSistema('Preencha todos os campos obrigatórios', 'warning');
                return;
            }

            const resultado = await chamarAPI('usuarios', 'criar', dados);
            if (resultado.sucesso) {
                exibirNotificacaoSistema('Usuário cadastrado com sucesso!', 'success');
                document.getElementById('usr-nome').value = '';
                document.getElementById('usr-email').value = '';
                document.getElementById('usr-senha').value = '';
                document.getElementById('usr-confirma-senha').value = '';
                document.getElementById('usr-perfil').value = '';
                document.getElementById('usr-departamento').value = '';
                document.querySelectorAll('input[name="usr-empresas"]').forEach(cb => cb.checked = false);
                document.getElementById('empresas-vinculo').style.display = 'none';
                document.getElementById('usr-perfil').value = '';
                carregarUsuarios();
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }

        async function toggleUsuario(id, statusAtual) {
            // Verificar se é administrador
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            if (usuarioLogado.perfil_id != 1) {
                exibirNotificacaoSistema('Acesso negado! Apenas administradores podem alterar status de usuários.', 'error');
                return;
            }
            
            const novoStatus = statusAtual == 1 ? 0 : 1;
            const acao = novoStatus == 1 ? 'ativar' : 'desativar';
            
            if (confirm(`Deseja ${acao} este usuário?`)) {
                const resultado = await chamarAPI('usuarios', 'toggle_status', {id: id, ativo: novoStatus});
                if (resultado.sucesso) {
                                       exibirNotificacaoSistema(`Usuário ${acao}do com sucesso!`, 'success');
                    carregarUsuarios();
                } else {
                    exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
                }
            }
        }

        async function editarUsuario(id) {
            // Verificar se é administrador
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            if (usuarioLogado.perfil_id != 1) {
                exibirNotificacaoSistema('Acesso negado! Apenas administradores podem editar usuários.', 'error');
                return;
            }
            
            const resultado = await chamarAPI('usuarios', 'buscar', {id: id});
            if (resultado.sucesso && resultado.dados) {
                const usr = resultado.dados;
                
                document.getElementById('edit-usr-nome').value = usr.nome;
                document.getElementById('edit-usr-email').value = usr.email;
                document.getElementById('edit-usr-perfil').value = usr.perfil_id;
                document.getElementById('edit-usr-departamento').value = usr.departamento || '';
                
                // Carregar empresas vinculadas
                const empresasVinculadas = usr.empresas_ids ? usr.empresas_ids.split(',') : [];
                document.querySelectorAll('input[name="edit-usr-empresas"]').forEach(checkbox => {
                    checkbox.checked = empresasVinculadas.includes(checkbox.value);
                });
                
                // Mostrar/ocultar campo empresas
                document.getElementById('edit-empresas-vinculo').style.display = usr.perfil_id == 1 ? 'none' : 'block';
                
                // Armazenar ID para edição
                document.getElementById('modal-editar-usuario').dataset.userId = id;
                document.getElementById('modal-editar-usuario').classList.add('active');
            }
        }
        
        function fecharModalEdicao() {
            document.getElementById('modal-editar-usuario').classList.remove('active');
        }
        
        async function salvarEdicaoUsuario() {
            // Verificar se é administrador
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            if (usuarioLogado.perfil_id != 1) {
                exibirNotificacaoSistema('Acesso negado! Apenas administradores podem editar usuários.', 'error');
                return;
            }
            
            const userId = document.getElementById('modal-editar-usuario').dataset.userId;
            const perfilId = parseInt(document.getElementById('edit-usr-perfil').value);
            
            let empresasVinculadas = [];
            if (perfilId > 1) {
                const checkboxes = document.querySelectorAll('input[name="edit-usr-empresas"]:checked');
                empresasVinculadas = Array.from(checkboxes).map(cb => parseInt(cb.value));
                
                if (empresasVinculadas.length === 0) {
                    exibirNotificacaoSistema('Selecione pelo menos uma empresa para este perfil', 'warning');
                    return;
                }
            }
            
            const dados = {
                id: parseInt(userId),
                nome: document.getElementById('edit-usr-nome').value,
                email: document.getElementById('edit-usr-email').value,
                perfil_id: perfilId,
                departamento: document.getElementById('edit-usr-departamento').value,
                empresas_vinculadas: empresasVinculadas
            };
            
            const resultado = await chamarAPI('usuarios', 'atualizar', dados);
            if (resultado.sucesso) {
                exibirNotificacaoSistema('Usuário atualizado com sucesso!', 'success');
                fecharModalEdicao();
                carregarUsuarios();
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }

        async function excluirUsuario(id, nome) {
            // Verificar se é administrador
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            if (usuarioLogado.perfil_id != 1) {
                exibirNotificacaoSistema('Acesso negado! Apenas administradores podem excluir usuários.', 'error');
                return;
            }

            // Impedir que o administrador se exclua a si mesmo
            if (id == usuarioLogado.id) {
                exibirNotificacaoSistema('Você não pode excluir sua própria conta.', 'error');
                return;
            }

            if (confirm(`Tem certeza que deseja excluir permanentemente o usuário "${nome}"?\n\nEsta ação não pode ser desfeita.`)) {
                const resultado = await chamarAPI('usuarios', 'excluir', {id: id});
                if (resultado.sucesso) {
                    exibirNotificacaoSistema(`Usuário "${nome}" excluído com sucesso!`, 'success');
                    carregarUsuarios();
                } else {
                    exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
                }
            }
        }

        // =====================================================================
        // DETALHES DO MATERIAL E GRÁFICO
        // =====================================================================
        let chartMaterial = null;
        let materialIdAtual = null;

        async function abrirDetalhesMaterial(id) {
            console.log('Iniciando abertura do modal para material:', id);
            materialIdAtual = id;
            const modal = document.getElementById('modal-detalhes-material');
            
            if (modal) {
                // Usar classe active para consistência com CSS e animações
                modal.classList.add('active');
                console.log('Classe active adicionada ao modal.');
                
                // Resetar filtro para 30 dias
                const filtro = document.getElementById('filtro-dias-grafico');
                if (filtro) filtro.value = '30';
                
                await carregarHistoricoMaterial(id, 30);
            } else {
                console.error('ERRO CRÍTICO: Modal de detalhes não encontrado no DOM!');
                exibirNotificacaoSistema('Erro interno: Modal não encontrado', 'error');
            }
        }

        function fecharModalDetalhesMaterial() {
            const modal = document.getElementById('modal-detalhes-material');
            if (modal) modal.classList.remove('active');
            
            materialIdAtual = null;
            if (chartMaterial) {
                chartMaterial.destroy();
                chartMaterial = null;
            }
        }

        async function gerarRelatorioPDFMaterial() {
            if (!materialIdAtual) return;

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // 1. Cabeçalho
            doc.setFontSize(18);
            doc.setTextColor(15, 23, 42); // Primary color
            doc.text('Relatório de Detalhes do Material', 14, 20);
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text(`Gerado em: ${new Date().toLocaleString('pt-BR')}`, 14, 28);
            
            // 2. Informações do Material (Capturar do DOM)
            const infoBasica = document.getElementById('detalhe-info-basica').innerText.split('\n').filter(l => l.trim());
            const infoEstoque = document.getElementById('detalhe-info-estoque').innerText.split('\n').filter(l => l.trim());
            
            let yPos = 40;
            
            doc.setFontSize(14);
            doc.setTextColor(0);
            doc.text('Informações Gerais', 14, yPos);
            yPos += 8;
            
            doc.setFontSize(11);
            doc.setTextColor(60);
            
            // Info Básica
            infoBasica.forEach(line => {
                doc.text(line, 14, yPos);
                yPos += 6;
            });
            
            // Info Estoque (lado direito)
            let yPosRight = 48;
            infoEstoque.forEach(line => {
                doc.text(line, 120, yPosRight);
                yPosRight += 6;
            });
            
            yPos = Math.max(yPos, yPosRight) + 10;

            // 3. Gráfico
            const canvas = document.getElementById('graficoMaterial');
            if (canvas) {
                const imgData = canvas.toDataURL('image/png');
                doc.addImage(imgData, 'PNG', 14, yPos, 180, 80); // x, y, w, h
                yPos += 90;
            }

            // 4. Tabela de Movimentações
            doc.setFontSize(14);
            doc.setTextColor(0);
            doc.text('Histórico de Movimentações (Período Selecionado)', 14, yPos);
            yPos += 5;

            // Preparar dados da tabela
            const rows = [];
            document.querySelectorAll('#tbody-detalhe-movimentacoes tr').forEach(tr => {
                const rowData = [];
                tr.querySelectorAll('td').forEach(td => rowData.push(td.innerText));
                rows.push(rowData);
            });

            doc.autoTable({
                startY: yPos,
                head: [['Data', 'Tipo', 'Quantidade', 'Saldo']],
                body: rows,
                theme: 'striped',
                headStyles: { fillColor: [37, 99, 235] }, // Azul accent
                styles: { fontSize: 10 },
                alternateRowStyles: { fillColor: [241, 245, 249] }
            });

            // Salvar
            const nomeMaterial = infoBasica[0] || 'material';
            doc.save(`Relatorio_${nomeMaterial.replace(/[^a-z0-9]/gi, '_')}.pdf`);
        }

        async function atualizarGraficoMaterial() {
            if (!materialIdAtual) return;
            const dias = document.getElementById('filtro-dias-grafico').value;
            await carregarHistoricoMaterial(materialIdAtual, dias);
        }

        async function carregarHistoricoMaterial(id, dias) {
            const containerInfo = document.getElementById('detalhe-info-basica');
            const containerEstoque = document.getElementById('detalhe-info-estoque');
            const tbody = document.getElementById('tbody-detalhe-movimentacoes');
            
            containerInfo.innerHTML = '<div class="loading"><div class="spinner"></div> Carregando...</div>';
            
            try {
                const resultado = await chamarAPI('materiais', 'historico', null, `&material_id=${id}&dias=${dias}`);
                
                if (resultado.sucesso) {
                    const mat = resultado.material;
                    const movs = resultado.movimentacoes;
                    const grafico = resultado.grafico;

                    // Preencher Info Básica
                    containerInfo.innerHTML = `
                        <h3 style="margin: 0; color: #1e293b;">${mat.nome}</h3>
                        <p style="margin: 5px 0; color: #64748b;">SKU: ${mat.codigo_sku}</p>
                        <p style="margin: 0; color: #64748b;">Categoria: ${mat.categoria_nome || '-'}</p>
                        <p style="margin: 0; color: #64748b;">Empresa: ${mat.empresa_nome || '-'}</p>
                    `;

                    // Preencher Info Estoque
                    const statusClass = mat.estoque_atual <= mat.estoque_minimo ? 'status-inativo' : 'status-ativo';
                    const statusTexto = mat.estoque_atual <= mat.estoque_minimo ? 'Baixo Estoque' : 'Normal';
                    
                    containerEstoque.innerHTML = `
                        <div style="font-size: 2rem; font-weight: bold; color: #0f172a;">${mat.estoque_atual} <span style="font-size: 1rem; color: #64748b;">${mat.unidade_simbolo || 'un'}</span></div>
                        <div class="status-badge ${statusClass}" style="display: inline-block;">${statusTexto}</div>
                        <p style="margin: 5px 0; font-size: 0.9rem;">Mín: ${mat.estoque_minimo} | Máx: ${mat.estoque_maximo}</p>
                    `;

                    // Renderizar Gráfico
                    renderizarGraficoMaterial(grafico, mat.nome);

                    // Preencher Tabela de Movimentações
                    if (movs.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Nenhuma movimentação no período</td></tr>';
                    } else {
                        let html = '';
                        let saldoSimulado = 0; // Opcional: calcular saldo linha a linha se necessário, mas o gráfico já mostra
                        
                        // Vamos mostrar as movs do período (invertido para mais recente primeiro na tabela)
                        const movsInvertidas = [...movs].reverse();
                        
                        movsInvertidas.forEach(m => {
                            const tipoClass = m.tipo === 'entrada' ? 'text-success' : 'text-danger';
                            const tipoIcon = m.tipo === 'entrada' ? '⬇️ Entrada' : '⬆️ Saída';
                            const qtdSinal = m.tipo === 'entrada' ? `+${m.quantidade}` : `-${m.quantidade}`;
                            
                            html += `
                                <tr>
                                    <td>${formatarData(m.data)}</td>
                                    <td class="${tipoClass}">${tipoIcon}</td>
                                    <td class="${tipoClass}" style="font-weight: bold;">${qtdSinal}</td>
                                    <td>-</td> <!-- Saldo histórico é complexo de alinhar na tabela invertida sem recalcular tudo -->
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                    }

                } else {
                    containerInfo.innerHTML = `<p style="color: red;">Erro: ${resultado.erro}</p>`;
                }
            } catch (error) {
                console.error(error);
                containerInfo.innerHTML = '<p style="color: red;">Erro ao carregar dados.</p>';
            }
        }

        function renderizarGraficoMaterial(dados, nomeMaterial) {
            const ctx = document.getElementById('graficoMaterial').getContext('2d');
            
            if (chartMaterial) {
                chartMaterial.destroy();
            }

            const labels = dados.map(d => d.data);
            const saldos = dados.map(d => d.saldo);
            const entradas = dados.map(d => d.entradas);
            const saidas = dados.map(d => d.saidas);

            // Verificar se temos dados válidos para mostrar
            const temSaldo = saldos.some(s => s !== null && s !== undefined && !isNaN(s));
            const temMovimentacao = entradas.some(e => e !== null && e !== undefined && !isNaN(e)) ||
                                  saidas.some(s => s !== null && s !== undefined && !isNaN(s));

            // Configurar o gráfico normal com os dados
            chartMaterial = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Saldo em Estoque',
                            data: saldos,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            pointRadius: 4,
                            pointBackgroundColor: '#3b82f6',
                            fill: false,
                            tension: 0.3,
                            yAxisID: 'y',
                            order: 1
                        },
                        {
                            label: 'Entradas',
                            data: entradas,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderWidth: 2,
                            type: 'bar',
                            yAxisID: 'y1',
                            order: 2
                        },
                        {
                            label: 'Saídas',
                            data: saidas,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderWidth: 2,
                            type: 'bar',
                            yAxisID: 'y1',
                            order: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Evolução do Estoque - ' + nomeMaterial
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Saldo' },
                            // Adicionando padding para melhor visualização
                            suggestedMin: temSaldo ? Math.min(...saldos.filter(s => s !== null && !isNaN(s))) * 0.9 : 0,
                            suggestedMax: temSaldo ? Math.max(...saldos.filter(s => s !== null && !isNaN(s))) * 1.1 : 10
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: { display: true, text: 'Movimentação (Qtd)' },
                            grid: {
                                drawOnChartArea: false,
                            },
                            beginAtZero: true
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    animations: {
                        tension: {
                            duration: 1000,
                            easing: 'linear'
                        }
                    }
                }
            });
        }

        // Mostrar/ocultar campo de empresas baseado no perfil
        document.addEventListener('DOMContentLoaded', function() {
            const perfilSelect = document.getElementById('usr-perfil');
            const empresasObrigatorio = document.getElementById('empresas-obrigatorio');
            
            perfilSelect.addEventListener('change', function() {
                const perfilId = parseInt(this.value);
                const empresasVinculo = document.getElementById('empresas-vinculo');
                
                if (perfilId > 1) { // Não é administrador
                    empresasVinculo.style.display = 'block';
                    empresasObrigatorio.style.display = 'inline';
                } else {
                    empresasVinculo.style.display = 'none';
                    empresasObrigatorio.style.display = 'none';
                }
            });
            
            // Modal - controle de empresas na edição
            const editPerfilSelect = document.getElementById('edit-usr-perfil');
            editPerfilSelect.addEventListener('change', function() {
                const perfilId = parseInt(this.value);
                const editEmpresasDiv = document.getElementById('edit-empresas-vinculo');
                
                if (perfilId > 1) {
                    editEmpresasDiv.style.display = 'block';
                } else {
                    editEmpresasDiv.style.display = 'none';
                }
            });
        });
        
        async function carregarEmpresasSelect() {
            const empresas = await chamarAPI('empresas', 'listar');
            if (empresas.sucesso && empresas.dados) {
                // Checkboxes para cadastro
                let htmlCadastro = '';
                empresas.dados.forEach(emp => {
                    htmlCadastro += `
                        <div class="empresas-checkbox-item">
                            <label>
                                <input type="checkbox" name="usr-empresas" value="${emp.id}">
                                ${emp.nome}
                            </label>
                        </div>`;
                });
                const cadastroContainer = document.getElementById('usr-empresas-checkboxes');
                if (cadastroContainer) {
                    cadastroContainer.innerHTML = htmlCadastro;
                }
                
                // Checkboxes para edição
                let htmlEdicao = '';
                empresas.dados.forEach(emp => {
                    htmlEdicao += `
                        <div class="empresas-checkbox-item">
                            <label>
                                <input type="checkbox" name="edit-usr-empresas" value="${emp.id}">
                                ${emp.nome}
                            </label>
                        </div>`;
                });
                const edicaoContainer = document.getElementById('edit-usr-empresas-checkboxes');
                if (edicaoContainer) {
                    edicaoContainer.innerHTML = htmlEdicao;
                }
            }
        }

        // =====================================================================
        // AUTENTICAÇÃO
        // =====================================================================
        function verificarLogin() {
            const usuarioLogado = localStorage.getItem('usuario_logado');
            if (!usuarioLogado) {
                window.location.href = 'login.php';
                return false;
            }
            
            let usuario;
            try {
                usuario = JSON.parse(usuarioLogado);
                if (!usuario || !usuario.nome) throw new Error('Dados inválidos');
            } catch (e) {
                console.error('Erro ao ler dados do usuário:', e);
                localStorage.removeItem('usuario_logado');
                window.location.href = 'login.php';
                return false;
            }
            
            document.getElementById('usuario-nome').textContent = usuario.nome;
            document.getElementById('usuario-perfil').textContent = usuario.perfil_nome;
            
            console.log('Usuário logado:', usuario);
            console.log('Perfil ID (original):', usuario.perfil_id, 'Tipo:', typeof usuario.perfil_id);
            
            // Converter perfil_id para número (pode vir como string do backend)
            const perfilId = parseInt(usuario.perfil_id);
            console.log('Perfil ID (convertido):', perfilId, 'Tipo:', typeof perfilId);
            
            // Mostrar menu de empresas para administradores e gestores
            if (perfilId === 1 || perfilId === 2) {
                document.getElementById('menu-empresas').style.display = 'block';
                console.log('Menu empresas exibido para perfil:', perfilId);
            }
            
            // Apenas administradores podem acessar categorias e usuários
            if (perfilId === 1) {
                console.log('Usuário é ADMINISTRADOR - Exibindo menus admin');
                document.getElementById('menu-categorias').style.display = 'flex'; // Usar flex para manter o alinhamento do ícone
                document.getElementById('menu-usuarios').style.display = 'flex';
                document.getElementById('menu-usuarios-pendentes').style.display = 'flex';
                // Mostrar formulário de cadastro de usuários apenas para administradores
                const formCadastro = document.getElementById('form-cadastro-usuario');
                if (formCadastro) formCadastro.style.display = 'block';
                const avisoNaoAdmin = document.getElementById('aviso-nao-admin');
                if (avisoNaoAdmin) avisoNaoAdmin.style.display = 'none';
            } else {
                console.log('Usuário NÃO é administrador - Ocultando menus admin');
                document.getElementById('menu-categorias').style.display = 'none';
                document.getElementById('menu-usuarios').style.display = 'none';
                document.getElementById('menu-usuarios-pendentes').style.display = 'none';
                // Ocultar formulário de cadastro para não-administradores
                const formCadastro = document.getElementById('form-cadastro-usuario');
                if (formCadastro) formCadastro.style.display = 'none';
                const avisoNaoAdmin = document.getElementById('aviso-nao-admin');
                if (avisoNaoAdmin) avisoNaoAdmin.style.display = 'block';
            }
            
            // Ocultar entrada para Operadores (perfil 3) e Consulta (perfil 4)
            // Apenas Admin (1) e Gestor (2) podem fazer entradas
            if (usuario.perfil_id != 1 && usuario.perfil_id != 2) {
                document.getElementById('menu-entrada').style.display = 'none';
                const formEntrada = document.getElementById('form-entrada');
                if (formEntrada) formEntrada.style.display = 'none';
            } else {
                document.getElementById('menu-entrada').style.display = 'block';
                const formEntrada = document.getElementById('form-entrada');
                if (formEntrada) formEntrada.style.display = 'block';
            }
            
            return true;
        }

        function logout() {
            if (confirm('Deseja realmente sair do sistema?')) {
                localStorage.removeItem('usuario_logado');
                window.location.href = 'login.php';
            }
        }

        // =====================================================================
        // INICIALIZAÇÃO
        // =====================================================================
        window.addEventListener('load', () => {
            if (verificarLogin()) {
                carregarDashboard();
            }
        });

        // =====================================================================
        // FUNÇÕES DE PERFIL DO USUÁRIO
        // =====================================================================
        
        function carregarPerfil() {
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            if (!usuarioLogado) return;
            
            document.getElementById('perfil-nome').value = usuarioLogado.nome || '';
            document.getElementById('perfil-email').value = usuarioLogado.email || '';
            document.getElementById('perfil-departamento').value = usuarioLogado.departamento || 'Não informado';
            document.getElementById('perfil-perfil').value = usuarioLogado.perfil_nome || '';
            
            // Limpar campos de senha
            document.getElementById('perfil-senha-atual').value = '';
            document.getElementById('perfil-nova-senha').value = '';
            document.getElementById('perfil-confirma-senha').value = '';
        }
        
        async function salvarPerfil() {
            console.log('=== INICIANDO SALVAR PERFIL ===');
            const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
            if (!usuarioLogado) {
                exibirNotificacaoSistema('Erro: usuário não identificado', 'error');
                return;
            }
            
            console.log('Usuário logado:', usuarioLogado);
            
            const nome = document.getElementById('perfil-nome').value.trim();
            const email = document.getElementById('perfil-email').value.trim();
            const senhaAtual = document.getElementById('perfil-senha-atual').value;
            const novaSenha = document.getElementById('perfil-nova-senha').value;
            const confirmaSenha = document.getElementById('perfil-confirma-senha').value;
            
            console.log('Dados do formulário:', { nome, email, senhaAtual: '***', novaSenha: novaSenha ? '***' : '', confirmaSenha: confirmaSenha ? '***' : '' });
            
            // Validações
            if (!nome || !email) {
                exibirNotificacaoSistema('Nome e email são obrigatórios', 'warning');
                return;
            }
            
            if (!senhaAtual) {
                exibirNotificacaoSistema('Digite sua senha atual para confirmar as alterações', 'warning');
                return;
            }
            
            // Se está tentando mudar a senha
            if (novaSenha || confirmaSenha) {
                if (novaSenha !== confirmaSenha) {
                    exibirNotificacaoSistema('As senhas não coincidem', 'warning');
                    return;
                }
                
                if (novaSenha.length < 6) {
                    exibirNotificacaoSistema('A nova senha deve ter pelo menos 6 caracteres', 'warning');
                    return;
                }
            }
            
            const dados = {
                id: usuarioLogado.id,
                nome: nome,
                email: email,
                senha_atual: senhaAtual,
                nova_senha: novaSenha || null
            };
            
            console.log('Enviando dados para API:', { ...dados, senha_atual: '***', nova_senha: dados.nova_senha ? '***' : null });
            
            const resultado = await chamarAPI('usuarios', 'atualizar_perfil', dados);
            
            console.log('Resultado da API:', resultado);
            if (resultado.sucesso) {
                exibirNotificacaoSistema('Perfil atualizado com sucesso!', 'success');
                
                // Atualizar dados no localStorage
                usuarioLogado.nome = nome;
                usuarioLogado.email = email;
                localStorage.setItem('usuario_logado', JSON.stringify(usuarioLogado));
                
                // Atualizar nome no header
                document.getElementById('usuario-nome').textContent = nome;
                
                // Limpar campos de senha
                document.getElementById('perfil-senha-atual').value = '';
                document.getElementById('perfil-nova-senha').value = '';
                document.getElementById('perfil-confirma-senha').value = '';
                
                // Se mudou a senha, fazer logout após 2 segundos
                if (novaSenha) {
                    setTimeout(() => {
                        exibirNotificacaoSistema('Senha alterada! Faça login novamente.', 'success');
                        setTimeout(() => logout(), 1500);
                    }, 2000);
                }
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }
        
        function cancelarEdicaoPerfil() {
            mostrarSecao('dashboard');
        }
        
        // Carregar perfil quando a seção for aberta
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.target.id === 'perfil' && mutation.target.classList.contains('active')) {
                        carregarPerfil();
                    }
                });
            });
            
            const perfilSection = document.getElementById('perfil');
            if (perfilSection) {
                observer.observe(perfilSection, { attributes: true, attributeFilter: ['class'] });
            }
        });
    </script>
    <div id="modal-estoque-local" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">Estoque por Local: <span id="titulo-material-estoque" style="font-weight: normal;"></span></div>
            <div style="padding: 20px;">
                <table class="table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Local</th>
                            <th style="text-align: right;">Quantidade</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-estoque-local-body">
                        <!-- Dados aqui -->
                    </tbody>
                </table>
                <div style="margin-top: 20px; text-align: right;">
                    <button class="btn btn-secondary" onclick="fecharModalEstoque()">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fecharModalEstoque() {
            document.getElementById('modal-estoque-local').style.display = 'none';
        }

        async function verEstoquePorLocal(id, nome) {
            const modal = document.getElementById('modal-estoque-local');
            const titulo = document.getElementById('titulo-material-estoque');
            const tbody = document.getElementById('tabela-estoque-local-body');
            
            titulo.textContent = nome;
            tbody.innerHTML = '<tr><td colspan="2" style="text-align: center; padding: 20px;">Carregando...</td></tr>';
            modal.style.display = 'flex';
            
            try {
                const resultado = await chamarAPI('materiais', 'estoque_por_local', null, `material_id=${id}`);
                
                if (resultado.sucesso && resultado.dados && resultado.dados.length > 0) {
                    let html = '';
                    resultado.dados.forEach(item => {
                        html += `<tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">${item.local_nome}</td>
                            <td style="text-align: right; font-weight: bold; padding: 10px; border-bottom: 1px solid #eee;">${item.estoque}</td>
                        </tr>`;
                    });
                    tbody.innerHTML = html;
                } else {
                    tbody.innerHTML = '<tr><td colspan="2" style="text-align: center; padding: 20px; color: #666;">Nenhum estoque encontrado em locais específicos.</td></tr>';
                }
            } catch (e) {
                console.error(e);
                tbody.innerHTML = '<tr><td colspan="2" style="text-align: center; padding: 20px; color: red;">Erro ao carregar estoque.</td></tr>';
            }
        }

        function toggleUltimasMovimentacoes() {
            const container = document.getElementById('container-tabela-movimentacoes');
            const icon = document.getElementById('icon-toggle-movimentacoes');
            
            if (container.style.display === 'none') {
                container.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                container.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        // Variáveis globais para controle da tabela de estoque
        let dadosEstoqueDashboard = [];
        let ordemAtualEstoque = { coluna: 'nome', direcao: 'asc' };

        async function carregarTabelaEstoqueDashboard() {
            const tbody = document.getElementById('tbody-estoque-dashboard');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Carregando...</td></tr>';

            try {
                const resultado = await chamarAPI('materiais', 'listar', null, 'somente_com_estoque=true');
                
                if (resultado.sucesso && resultado.dados) {
                    dadosEstoqueDashboard = resultado.dados;
                    renderizarTabelaEstoque();
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Nenhum material com estoque encontrado.</td></tr>';
                }
            } catch (e) {
                console.error(e);
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: red;">Erro ao carregar dados.</td></tr>';
            }
        }

        function renderizarTabelaEstoque() {
            const tbody = document.getElementById('tbody-estoque-dashboard');
            
            // Ordenar dados
            dadosEstoqueDashboard.sort((a, b) => {
                let valA = a[ordemAtualEstoque.coluna];
                let valB = b[ordemAtualEstoque.coluna];

                // Tratar números
                if (ordemAtualEstoque.coluna === 'estoque_atual') {
                    valA = parseFloat(valA);
                    valB = parseFloat(valB);
                } else {
                    // Tratar strings (case insensitive)
                    valA = (valA || '').toString().toLowerCase();
                    valB = (valB || '').toString().toLowerCase();
                }

                if (valA < valB) return ordemAtualEstoque.direcao === 'asc' ? -1 : 1;
                if (valA > valB) return ordemAtualEstoque.direcao === 'asc' ? 1 : -1;
                return 0;
            });

            let html = '';
            dadosEstoqueDashboard.forEach(item => {
                // Definir status
                let status = '<span class="badge badge-success">OK</span>';
                const estoque = parseFloat(item.estoque_atual);
                const minimo = parseFloat(item.ponto_reposicao);
                const maximo = parseFloat(item.estoque_maximo);

                if (estoque <= minimo) {
                    status = '<span class="badge badge-danger">Baixo</span>';
                } else if (maximo > 0 && estoque > maximo) {
                    status = '<span class="badge badge-warning">Excesso</span>';
                }

                html += `<tr>
                    <td>${item.codigo_sku || '-'}</td>
                    <td><a href="#" onclick="abrirDetalhesMaterial(${item.id}); return false;" style="font-weight: bold; color: #2563eb; text-decoration: none;">${item.nome}</a></td>
                    <td>${item.categoria_nome || '-'}</td>
                    <td>${item.empresa_nome || '-'}</td>
                    <td style="text-align: right; font-weight: bold;">${estoque}</td>
                    <td>${item.unidade_simbolo || '-'}</td>
                    <td>${status}</td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick="editarMaterial(${item.id})" style="padding: 2px 6px; font-size: 11px;" title="Editar Material">✏️</button>
                        <button class="btn btn-info btn-sm" onclick="abrirDetalhesMaterial(${item.id})" style="padding: 2px 6px; font-size: 11px; margin-left: 5px;" title="Ver Detalhes">📋</button>
                    </td>
                </tr>`;
            });

            if (html === '') {
                html = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Nenhum material com estoque encontrado.</td></tr>';
            }
            
            tbody.innerHTML = html;
            atualizarIconesOrdenacao();
        }

        function ordenarTabelaEstoque(coluna) {
            if (ordemAtualEstoque.coluna === coluna) {
                ordemAtualEstoque.direcao = ordemAtualEstoque.direcao === 'asc' ? 'desc' : 'asc';
            } else {
                ordemAtualEstoque.coluna = coluna;
                ordemAtualEstoque.direcao = 'asc';
            }
            renderizarTabelaEstoque();
        }

        function atualizarIconesOrdenacao() {
            const headers = document.querySelectorAll('#tabela-estoque-dashboard th');
            headers.forEach(th => {
                const icon = th.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-sort'; // Reset
                    if (th.getAttribute('onclick').includes(ordemAtualEstoque.coluna)) {
                        icon.className = ordemAtualEstoque.direcao === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
                    }
                }
            });
        }

        function toggleEstoqueDetalhado() {
            const container = document.getElementById('container-tabela-estoque');
            const icon = document.getElementById('icon-toggle-estoque');
            
            if (container.style.display === 'none') {
                container.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                container.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        // =====================================================================
        // FUNÇÕES DE EDIÇÃO DE MATERIAL (MOVIDAS PARA FINAL DO ARQUIVO)
        // =====================================================================
        
        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        async function editarMaterial(id) {
            console.log('Abrindo edição para material ID:', id);
            try {
                // Carregar dados do material
                const resultado = await chamarAPI('materiais', 'obter', null, `id=${id}`);
                
                if (resultado.sucesso && resultado.dados) {
                    const mat = resultado.dados;
                    
                    // Preencher campos
                    document.getElementById('edit-mat-id').value = mat.id;
                    document.getElementById('edit-mat-nome').value = mat.nome;
                    document.getElementById('edit-mat-sku').value = mat.codigo_sku;
                    document.getElementById('edit-mat-reposicao').value = mat.ponto_reposicao;
                    document.getElementById('edit-mat-maximo').value = mat.estoque_maximo;
                    
                    // Carregar combos
                    await carregarCombosEdicao(mat.categoria_id, mat.empresa_id, mat.unidade_medida_id);
                    
                    // Abrir modal
                    document.getElementById('modal-editar-material').style.display = 'flex';
                } else {
                    exibirNotificacaoSistema('Erro ao carregar material: ' + (resultado.erro || 'Erro desconhecido'), 'error');
                }
            } catch (e) {
                console.error(e);
                exibirNotificacaoSistema('Erro ao abrir edição: ' + e.message, 'error');
            }
        }

        async function carregarCombosEdicao(catId, empId, unidId) {
            // Categorias
            const categorias = await chamarAPI('categorias', 'listar');
            let catHtml = '<option value="">Selecione...</option>';
            if (categorias.sucesso) {
                categorias.dados.forEach(c => {
                    catHtml += `<option value="${c.id}" ${c.id == catId ? 'selected' : ''}>${c.nome}</option>`;
                });
            }
            document.getElementById('edit-mat-categoria').innerHTML = catHtml;
            
            // Empresas
            const empresas = await chamarAPI('empresas', 'listar');
            let empHtml = '<option value="">Selecione...</option>';
            if (empresas.sucesso) {
                empresas.dados.forEach(e => {
                    empHtml += `<option value="${e.id}" ${e.id == empId ? 'selected' : ''}>${e.nome}</option>`;
                });
            }
            document.getElementById('edit-mat-empresa').innerHTML = empHtml;

            // Unidade de Medida
            const unidadeSelect = document.getElementById('edit-mat-unidade');
            if (unidadeSelect) {
                unidadeSelect.value = unidId;
            }
        }

        async function salvarEdicaoMaterial() {
            const id = document.getElementById('edit-mat-id').value;
            const nome = document.getElementById('edit-mat-nome').value;
            const categoria = document.getElementById('edit-mat-categoria').value;
            const empresa = document.getElementById('edit-mat-empresa').value;
            const unidade = document.getElementById('edit-mat-unidade').value;
            const reposicao = document.getElementById('edit-mat-reposicao').value;
            const maximo = document.getElementById('edit-mat-maximo').value;
            
            if (!nome || !categoria || !empresa || !unidade) {
                exibirNotificacaoSistema('Preencha todos os campos obrigatórios', 'warning');
                return;
            }
            
            const dados = {
                id: id,
                nome: nome,
                categoria_id: parseInt(categoria),
                unidade_medida_id: parseInt(unidade),
                empresa_id: parseInt(empresa),
                ponto_reposicao: parseFloat(reposicao) || 0,
                estoque_maximo: parseFloat(maximo) || 0,
                local_id: null
            };
            
            const resultado = await chamarAPI('materiais', 'atualizar', dados);
            
            if (resultado.sucesso) {
                exibirNotificacaoSistema('Material atualizado com sucesso!', 'success');
                fecharModal('modal-editar-material');
                
                // Atualizar tabelas onde o material pode estar visível
                if (document.getElementById('materiais').classList.contains('active')) {
                    filtrarMateriais();
                }
                if (document.getElementById('dashboard').classList.contains('active')) {
                    carregarTabelaEstoqueDashboard();
                }
            } else {
                exibirNotificacaoSistema('Erro ao atualizar: ' + resultado.erro, 'error');
            }
        }
    </script>
    <!-- MODAL: EDITAR ENTRADA -->
    <div id="modal-editar-entrada" class="modal">
        <div class="modal-content" style="width: 500px;">
            <div class="modal-header">
                <h2>Editar Entrada</h2>
                <span class="close" onclick="fecharModal('modal-editar-entrada')">&times;</span>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-ent-id">
                <div class="form-group">
                    <label>Material</label>
                    <input type="text" id="edit-ent-material" readonly style="background-color: #f0f0f0;">
                </div>
                <div class="form-group">
                    <label>Quantidade</label>
                    <input type="number" id="edit-ent-qtd" step="0.01">
                </div>
                <div class="form-group">
                    <label for="edit-ent-local">Local de Armazenamento</label>
                    <select id="edit-ent-local" required>
                        <option value="">Selecione um local</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nota Fiscal</label>
                    <input type="text" id="edit-ent-nf">
                </div>
                <div class="form-group">
                    <label>Observação</label>
                    <textarea id="edit-ent-obs" rows="3"></textarea>
                </div>
                <div class="form-actions" style="margin-top: 20px; text-align: right;">
                    <button class="btn btn-secondary" onclick="fecharModal('modal-editar-entrada')">Cancelar</button>
                    <button class="btn btn-primary" onclick="salvarEdicaoEntrada()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: EDITAR SAÍDA -->
    <div id="modal-editar-saida" class="modal">
        <div class="modal-content" style="width: 500px;">
            <div class="modal-header">
                <h2>Editar Saída</h2>
                <span class="close" onclick="fecharModal('modal-editar-saida')">&times;</span>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-sai-id">
                <div class="form-group">
                    <label>Material</label>
                    <input type="text" id="edit-sai-material" readonly style="background-color: #f0f0f0;">
                </div>
                <div class="form-group">
                    <label>Quantidade</label>
                    <input type="number" id="edit-sai-qtd" step="0.01">
                </div>
                <div class="form-group">
                    <label>Observação</label>
                    <textarea id="edit-sai-obs" rows="3"></textarea>
                </div>
                <div class="form-actions" style="margin-top: 20px; text-align: right;">
                    <button class="btn btn-secondary" onclick="fecharModal('modal-editar-saida')">Cancelar</button>
                    <button class="btn btn-primary" onclick="salvarEdicaoSaida()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editarMovimentacao(id, tipo, material, qtd, nf, obs, localId = null) {
            // Decodificar strings que podem ter aspas
            material = material.replace(/\\'/g, "'");
            obs = obs ? obs.replace(/\\'/g, "'") : '';
            nf = nf ? nf.replace(/\\'/g, "'") : '';

            if (tipo === 'Entrada') {
                document.getElementById('edit-ent-id').value = id;
                document.getElementById('edit-ent-material').value = material;
                document.getElementById('edit-ent-qtd').value = qtd;
                document.getElementById('edit-ent-nf').value = nf || '';
                document.getElementById('edit-ent-obs').value = obs || '';
                
                // Preencher select de locais (clonando do cadastro de entrada ou recarregando)
                const selectLocalOrigem = document.getElementById('ent-local');
                const selectLocalDestino = document.getElementById('edit-ent-local');
                
                if (selectLocalOrigem && selectLocalDestino) {
                    selectLocalDestino.innerHTML = selectLocalOrigem.innerHTML;
                    if (localId) selectLocalDestino.value = localId;
                }
                
                document.getElementById('modal-editar-entrada').style.display = 'flex';
            } else if (tipo === 'Saída') {
                document.getElementById('edit-sai-id').value = id;
                document.getElementById('edit-sai-material').value = material;
                document.getElementById('edit-sai-qtd').value = qtd;
                document.getElementById('edit-sai-obs').value = obs || '';
                document.getElementById('modal-editar-saida').style.display = 'flex';
            }
        }

        async function salvarEdicaoEntrada() {
            const id = document.getElementById('edit-ent-id').value;
            const qtd = document.getElementById('edit-ent-qtd').value;
            const nf = document.getElementById('edit-ent-nf').value;
            const obs = document.getElementById('edit-ent-obs').value;
            const localId = document.getElementById('edit-ent-local').value;

            if (!qtd || qtd <= 0) {
                exibirNotificacaoSistema('Quantidade inválida', 'warning');
                return;
            }

            const dados = { id: id, quantidade: qtd, nota_fiscal: nf, observacao: obs, local_destino_id: localId };
            const resultado = await chamarAPI('entrada', 'atualizar', dados);

            if (resultado.sucesso) {
                exibirNotificacaoSistema('Entrada atualizada!', 'success');
                fecharModal('modal-editar-entrada');
                carregarRelatorios(); // Recarregar tabela
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }

        async function salvarEdicaoSaida() {
            const id = document.getElementById('edit-sai-id').value;
            const qtd = document.getElementById('edit-sai-qtd').value;
            const obs = document.getElementById('edit-sai-obs').value;

            if (!qtd || qtd <= 0) {
                exibirNotificacaoSistema('Quantidade inválida', 'warning');
                return;
            }

            const dados = { id: id, quantidade: qtd, observacao: obs };
            const resultado = await chamarAPI('saida', 'atualizar', dados);

            if (resultado.sucesso) {
                exibirNotificacaoSistema('Saída atualizada!', 'success');
                fecharModal('modal-editar-saida');
                carregarRelatorios(); // Recarregar tabela
            } else {
                exibirNotificacaoSistema('Erro: ' + resultado.erro, 'error');
            }
        }
    </script>
    <!-- MODAL: DETALHES DO MATERIAL -->
    <div id="modal-detalhes-material" class="modal">
        <div class="modal-content" style="width: 800px; max-width: 95%;">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <h2>Detalhes do Material</h2>
                    <button class="btn btn-primary btn-sm" onclick="gerarRelatorioPDFMaterial()" title="Baixar PDF">
                        <i class="fas fa-file-pdf"></i> Gerar PDF
                    </button>
                </div>
                <span class="close" onclick="fecharModalDetalhesMaterial()">&times;</span>
            </div>
            <div class="modal-body">
                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div id="detalhe-info-basica" style="flex: 1; background: #f8fafc; padding: 15px; border-radius: 8px;">
                        <!-- Preenchido via JS -->
                    </div>
                    <div id="detalhe-info-estoque" style="flex: 1; background: #f0f9ff; padding: 15px; border-radius: 8px; text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <!-- Preenchido via JS -->
                    </div>
                </div>

                <div style="margin-bottom: 20px; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0; font-size: 1.1rem;">Histórico de Movimentação</h3>
                        <select id="filtro-dias-grafico" onchange="atualizarGraficoMaterial()" style="padding: 5px; border-radius: 4px; border: 1px solid #cbd5e1;">
                            <option value="7">Últimos 7 dias</option>
                            <option value="15">Últimos 15 dias</option>
                            <option value="30" selected>Últimos 30 dias</option>
                            <option value="60">Últimos 60 dias</option>
                            <option value="90">Últimos 90 dias</option>
                        </select>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="graficoMaterial"></canvas>
                    </div>
                </div>

                <div style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Saldo</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-detalhe-movimentacoes">
                            <!-- Preenchido via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
