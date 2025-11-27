# 📋 Sistema de Gestão de Materiais Terceirizados

## 📖 Índice
1. [Visão Geral](#visão-geral)
2. [Estrutura do Banco de Dados](#estrutura-do-banco-de-dados)
3. [Arquitetura do Sistema](#arquitetura-do-sistema)
4. [API Endpoints](#api-endpoints)
5. [Controle de Acesso](#controle-de-acesso)
6. [Funcionalidades](#funcionalidades)
7. [Instalação](#instalação)
8. [Desenvolvimento](#desenvolvimento)

---

## 🎯 Visão Geral

Sistema web para controle de estoque de materiais de empresas terceirizadas em universidades. Permite gestão completa com controle de acesso por perfis, aprovação de usuários e filtros por empresa.

### Tecnologias Utilizadas
- **Backend**: PHP 7.4+, MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Servidor**: Apache (XAMPP)

---

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais

#### `perfis_acesso`
```sql
CREATE TABLE perfis_acesso (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    permissoes JSON,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
**Dados Padrão:**
- ID 1: Administrador (acesso total)
- ID 2: Gestor (gerenciamento operacional)
- ID 3: Operador (operações básicas)
- ID 4: Consulta (apenas visualização)

#### `usuarios`
```sql
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255),
    perfil_id INT DEFAULT 1,
    departamento VARCHAR(100),
    ativo TINYINT(1) DEFAULT 1,
    ultimo_acesso TIMESTAMP NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (perfil_id) REFERENCES perfis_acesso(id)
);
```
**Usuário Padrão:**
- Email: admin@universidade.edu.br
- Senha: admin123

#### `usuarios_pendentes`
```sql
CREATE TABLE usuarios_pendentes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    departamento VARCHAR(100),
    justificativa TEXT,
    status ENUM('Pendente', 'Aprovado', 'Rejeitado') DEFAULT 'Pendente',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aprovado_por INT NULL,
    data_aprovacao TIMESTAMP NULL,
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id)
);
```

#### `usuarios_empresas`
```sql
CREATE TABLE usuarios_empresas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    empresa_id INT NOT NULL,
    data_vinculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_id) REFERENCES empresas_terceirizadas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_empresa (usuario_id, empresa_id)
);
```

#### `empresas_terceirizadas`
```sql
CREATE TABLE empresas_terceirizadas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(200) NOT NULL,
    tipo_servico VARCHAR(100),
    numero_contrato VARCHAR(50),
    cnpj VARCHAR(20),
    responsavel_id INT,
    telefone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('Ativa', 'Inativa') DEFAULT 'Ativa',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
);
```

#### `categorias_materiais`
```sql
CREATE TABLE categorias_materiais (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
**Dados Padrão:**
- Limpeza, Ferramentas, Equipamentos, Escritório, Manutenção

#### `unidades_medida`
```sql
CREATE TABLE unidades_medida (
    id INT PRIMARY KEY AUTO_INCREMENT,
    descricao VARCHAR(100) NOT NULL,
    simbolo VARCHAR(20) NOT NULL
);
```
**Dados Padrão:**
- Unidade (un), Litro (L), Quilograma (kg), Caixa (cx), Pacote (pct), Resma (rsm), Rolo (rl), Lata (lt)

#### `locais_armazenamento`
```sql
CREATE TABLE locais_armazenamento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
**Dados Padrão:**
- Almoxarifado Central, Almoxarifado Limpeza, Almoxarifado Manutenção, Almoxarifado Escritório

#### `materiais`
```sql
CREATE TABLE materiais (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(200) NOT NULL,
    codigo_sku VARCHAR(50) UNIQUE NOT NULL,
    descricao TEXT,
    categoria_id INT NOT NULL,
    unidade_medida_id INT NOT NULL,
    empresa_id INT NOT NULL,
    local_id INT NOT NULL,
    estoque_atual DECIMAL(10,2) DEFAULT 0.00,
    ponto_reposicao DECIMAL(10,2) NOT NULL,
    estoque_maximo DECIMAL(10,2) NOT NULL,
    valor_unitario DECIMAL(10,2),
    observacoes TEXT,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_materiais(id),
    FOREIGN KEY (unidade_medida_id) REFERENCES unidades_medida(id),
    FOREIGN KEY (empresa_id) REFERENCES empresas_terceirizadas(id),
    FOREIGN KEY (local_id) REFERENCES locais_armazenamento(id)
);
```

#### `movimentacoes_entrada`
```sql
CREATE TABLE movimentacoes_entrada (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_entrada DATETIME NOT NULL,
    material_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    nota_fiscal VARCHAR(50),
    responsavel_id INT,
    local_destino_id INT,
    observacao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materiais(id),
    FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
    FOREIGN KEY (local_destino_id) REFERENCES locais_armazenamento(id)
);
```

#### `movimentacoes_saida`
```sql
CREATE TABLE movimentacoes_saida (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_saida DATETIME NOT NULL,
    material_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    empresa_solicitante_id INT,
    local_origem_id INT,
    finalidade VARCHAR(100),
    responsavel_autorizacao_id INT,
    local_destino VARCHAR(200),
    observacao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materiais(id),
    FOREIGN KEY (empresa_solicitante_id) REFERENCES empresas_terceirizadas(id),
    FOREIGN KEY (local_origem_id) REFERENCES locais_armazenamento(id),
    FOREIGN KEY (responsavel_autorizacao_id) REFERENCES usuarios(id)
);
```

---

## 🏗️ Arquitetura do Sistema

### Estrutura de Arquivos
```
gestao-materiais/
├── index.php                 # Interface principal (SPA)
├── login.php                 # Tela de login
├── api_filtrada.php          # API principal
├── gerenciar_usuarios.php    # Gestão de usuários pendentes
├── logout.php                # Logout
├── sessao_check.php          # Verificação de sessão
├── criar_tabelas_completas.sql
├── DOCUMENTACAO_COMPLETA.md
└── scripts_sql/
    ├── dados_basicos.sql
    ├── usuarios_pendentes.sql
    └── correcoes.sql
```

### Fluxo de Dados
1. **Frontend** (JavaScript) → **API** (PHP) → **MySQL**
2. **Sessões PHP** para controle de acesso
3. **JSON** para comunicação API
4. **Filtros automáticos** por empresa baseados no perfil

---

## 🔌 API Endpoints

### Autenticação
- `POST /api_filtrada.php?tipo=auth&acao=login`
- `POST /api_filtrada.php?tipo=auth&acao=cadastrar`

### Usuários (Admin apenas)
- `GET /api_filtrada.php?tipo=usuarios&acao=pendentes`
- `POST /api_filtrada.php?tipo=usuarios&acao=aprovar`
- `POST /api_filtrada.php?tipo=usuarios&acao=rejeitar`
- `GET /api_filtrada.php?tipo=usuarios&acao=listar_completo`

### Empresas (Admin apenas)
- `GET /api_filtrada.php?tipo=empresas&acao=listar`
- `POST /api_filtrada.php?tipo=empresas&acao=criar`

### Categorias (Admin apenas)
- `GET /api_filtrada.php?tipo=categorias&acao=listar`
- `POST /api_filtrada.php?tipo=categorias&acao=criar`

### Materiais
- `GET /api_filtrada.php?tipo=materiais&acao=listar`
- `GET /api_filtrada.php?tipo=materiais&acao=por_empresa&empresa_id=X`
- `POST /api_filtrada.php?tipo=materiais&acao=gerar_sku`
- `POST /api_filtrada.php?tipo=materiais&acao=criar`

### Relatórios
- `GET /api_filtrada.php?tipo=relatorios&acao=resumo_geral`
- `GET /api_filtrada.php?tipo=relatorios&acao=estoque_baixo`

---

## 🔐 Controle de Acesso

### Perfis e Permissões

| **Perfil** | **ID** | **Empresas** | **Materiais** | **Gerenciar Empresas** | **Gerenciar Usuários** |
|---|:---:|---|---|:---:|:---:|
| **Administrador** | 1 | ✅ Todas | ✅ Criar/Editar | ✅ Gerenciar | ✅ Criar |
| **Gestor** | 2 | 🔒 Vinculadas | ✅ Criar/Editar | ❌ | ❌ |
| **Operador** | 3 | 🔒 Vinculadas | ✅ Movimentar | ❌ | ❌ |
| **Consulta** | 4 | 🔒 Vinculadas | 👁️ Visualizar | ❌ | ❌ |

### 📊 Tabela Detalhada de Funcionalidades por Perfil

| Funcionalidade | Administrador<br>(ID 1) | Gestor<br>(ID 2) | Operador<br>(ID 3) | Consulta<br>(ID 4) |
|---|:---:|:---:|:---:|:---:|
| **📊 Dashboard** | ✅ Todas métricas | ✅ Empresas vinculadas | ✅ Métricas básicas | 👁️ Visualizar |
| **🏢 Empresas** | ✅ CRUD completo | ✅ Ver vinculadas | ❌ Sem acesso | ❌ Sem acesso |
| **📦 Materiais** | ✅ CRUD completo | ✅ CRUD vinculadas | 👁️ Visualizar | 👁️ Visualizar |
| **📍 Locais** | ✅ CRUD completo | ✅ CRUD completo | ✅ CRUD completo | 👁️ Visualizar |
| **🏷️ Categorias** | ✅ CRUD completo | ❌ Sem acesso | ❌ Sem acesso | ❌ Sem acesso |
| **📥 Entrada** | ✅ Todas empresas | ✅ Empresas vinculadas | ❌ Sem acesso | ❌ Sem acesso |
| **📤 Saída** | ✅ Todas empresas | ✅ Empresas vinculadas | ✅ Empresas vinculadas | ❌ Sem acesso |
| **⚠️ Alertas** | ✅ Todos alertas | ✅ Empresas vinculadas | ✅ Empresas vinculadas | 👁️ Visualizar |
| **📈 Relatórios** | ✅ Todos relatórios | ✅ Empresas vinculadas | ✅ Empresas vinculadas | 👁️ Visualizar |
| **👥 Usuários** | ✅ CRUD completo | 👁️ Visualizar | 👁️ Visualizar | 👁️ Visualizar |
| **⏳ Pendentes** | ✅ Aprovar/Rejeitar | ❌ Sem acesso | ❌ Sem acesso | ❌ Sem acesso |

#### Legenda:
- ✅ = Acesso completo
- 🔒 = Acesso restrito (somente empresas vinculadas)
- 👁️ = Somente visualização
- ❌ = Sem acesso

### Filtros Automáticos
```php
// Aplicado automaticamente em todas as consultas
function aplicarFiltroEmpresa($query, $alias = '') {
    if ($_SESSION['empresas_permitidas'] === 'ALL') {
        return $query; // Admin vê tudo
    }
    
    $empresas_str = implode(',', $_SESSION['empresas_permitidas']);
    return $query . " AND empresa_id IN ($empresas_str)";
}
```

---

## ⚙️ Funcionalidades

### 1. Sistema de Login
- Autenticação por email/senha
- Controle de sessão PHP
- Redirecionamento automático

### 2. Gestão de Usuários
- **Solicitação de acesso** via tela de login
- **Aprovação administrativa** com definição de perfil
- **Vínculo a empresas** específicas
- **Controle de status** (ativo/inativo)

### 3. Gestão de Empresas (Admin)
- Cadastro de empresas terceirizadas
- Controle de contratos e dados
- Vinculação com usuários

### 4. Gestão de Categorias (Admin)
- Criação de categorias de materiais
- Carregamento dinâmico nos formulários

### 5. Gestão de Materiais
- **Geração automática de SKU** (CATEG + EMPRE + 0001)
- **Busca com autocomplete** por nome ou código
- **Filtros por empresa** baseados no perfil
- **Controle de estoque** (atual, mínimo, máximo)

### 6. Movimentações
- **Entrada de materiais** com seleção por empresa
- **Saída de materiais** com controle de finalidade
- **Histórico completo** de movimentações

### 7. Relatórios e Alertas
- Dashboard com resumo geral
- Alertas de estoque baixo
- Relatórios por empresa

---

## 🚀 Instalação

### Pré-requisitos
- XAMPP (Apache + MySQL + PHP 7.4+)
- Navegador moderno

### Passos
1. **Clone/Copie** os arquivos para `C:\xampp\htdocs\gestao-materiais\`

2. **Execute o SQL** no phpMyAdmin:
```sql
source criar_tabelas_completas.sql;
```

3. **Insira dados básicos**:
```sql
source dados_basicos.sql;
```

4. **Acesse o sistema**:
   - URL: `http://localhost/gestao-materiais/`
   - Login: admin@universidade.edu.br
   - Senha: admin123

### Configuração do Banco
```php
// api_filtrada.php - linha 15
$conn = new mysqli('localhost', 'inventario', 'fA9-A@BLn_PiHsR0', 'gestao_materiais_terceirizados');
```

---

## 👨‍💻 Desenvolvimento

### Estrutura do Frontend
- **SPA (Single Page Application)** em JavaScript vanilla
- **Seções dinâmicas** controladas por `mostrarSecao()`
- **API calls** centralizadas em `chamarAPI()`
- **Controle de estado** via localStorage

### Padrões de Código

#### JavaScript
```javascript
// Função padrão para chamar API
async function chamarAPI(tipo, acao, dados = null, parametrosExtras = '') {
    const url = `${API_URL}?tipo=${tipo}&acao=${acao}${parametrosExtras}`;
    // ... implementação
}

// Padrão para carregar dados
async function carregarDados() {
    const resultado = await chamarAPI('tipo', 'acao');
    if (resultado.sucesso) {
        // Processar dados
    } else {
        mostrarAlerta(resultado.erro, 'error');
    }
}
```

#### PHP API
```php
// Padrão de endpoint
if ($tipo === 'entidade' && $acao === 'acao') {
    // Verificar permissões
    if (!temPermissao()) {
        echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
        exit;
    }
    
    // Processar dados
    $resultado = processarDados($dados);
    
    // Retornar resposta
    echo json_encode(['sucesso' => true, 'dados' => $resultado]);
    exit;
}
```

### Adicionando Novas Funcionalidades

#### 1. Nova Tabela
```sql
CREATE TABLE nova_tabela (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    -- outros campos
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 2. Novo Endpoint API
```php
// Em api_filtrada.php
if ($tipo === 'nova_entidade') {
    if ($acao === 'listar') {
        $result = $conn->query('SELECT * FROM nova_tabela');
        $dados = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['sucesso' => true, 'dados' => $dados]);
        exit;
    }
}
```

#### 3. Nova Seção Frontend
```html
<!-- Em index.php -->
<section id="nova_secao" class="section">
    <div class="form-container">
        <h2>Nova Funcionalidade</h2>
        <!-- Formulário -->
    </div>
</section>
```

```javascript
// Função para carregar
async function carregarNovaSecao() {
    const resultado = await chamarAPI('nova_entidade', 'listar');
    // Processar resultado
}
```

### Debugging
- **Console do navegador** (F12) para erros JavaScript
- **Logs PHP** em `C:\xampp\apache\logs\error.log`
- **Resposta da API** sempre logada no console

### Segurança
- ✅ **Prepared statements** para SQL
- ✅ **Password hashing** com `password_hash()`
- ✅ **Validação de sessão** em todas as APIs
- ✅ **Filtros automáticos** por empresa
- ✅ **Sanitização** de inputs

---

## 📞 Suporte

Para dúvidas sobre o sistema:
1. Consulte esta documentação
2. Verifique os logs de erro
3. Use as ferramentas de debug do navegador
4. Teste endpoints isoladamente

**Sistema desenvolvido para gestão eficiente de materiais terceirizados em ambiente universitário.**