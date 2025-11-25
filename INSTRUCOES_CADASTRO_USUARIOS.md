# Sistema de Cadastro de Usuários

## Funcionalidades Implementadas

### 1. Botão "Solicitar Acesso" na Tela de Login
- Usuários podem solicitar acesso ao sistema
- Formulário com campos obrigatórios:
  - Nome completo
  - Email
  - Senha
  - Departamento (opcional)
  - Justificativa para acesso

### 2. Aprovação pelo Administrador
- Administradores podem acessar "Usuários Pendentes" no menu
- Visualizar todas as solicitações pendentes
- Aprovar ou rejeitar solicitações
- Definir perfil de acesso (Gestor, Operador, Consulta)
- Vincular usuário a empresas específicas

### 3. Fluxo Completo
1. **Usuário solicita acesso** → Tela de login
2. **Solicitação fica pendente** → Armazenada no banco
3. **Administrador aprova** → Define perfil e empresas
4. **Usuário pode fazer login** → Com as permissões definidas

## Como Usar

### Para Usuários (Solicitar Acesso)
1. Acesse a tela de login
2. Clique em "Solicitar Acesso"
3. Preencha todos os campos obrigatórios
4. Aguarde aprovação do administrador

### Para Administradores (Aprovar Usuários)
1. Faça login como administrador
2. Acesse "Usuários Pendentes" no menu
3. Clique em "Ver Detalhes" na solicitação
4. Escolha o perfil de acesso:
   - **Gestor**: Pode criar/editar materiais e movimentações
   - **Operador**: Pode registrar entradas/saídas
   - **Consulta**: Apenas visualização
5. Selecione as empresas que o usuário pode gerenciar
6. Clique em "Aprovar Usuário"

## Instalação

### 1. Execute o Script SQL
```sql
-- Execute no MySQL/phpMyAdmin:
source executar_tabelas_usuarios.sql;
```

### 2. Arquivos Criados/Modificados
- `login.php` - Adicionado botão e modal de cadastro
- `gerenciar_usuarios.php` - Nova página para administradores
- `api_filtrada.php` - Novas funcionalidades de API
- Tabelas: `usuarios_pendentes`, `usuarios_empresas`

### 3. Permissões por Perfil
- **Administrador (ID 1)**: Acesso total, pode aprovar usuários
- **Gestor (ID 2)**: Gerencia materiais das empresas vinculadas
- **Operador (ID 3)**: Registra movimentações das empresas vinculadas
- **Consulta (ID 4)**: Apenas visualização das empresas vinculadas

## Segurança
- Senhas são criptografadas com `password_hash()`
- Validação de email único
- Controle de acesso por perfil
- Filtro de empresas por usuário
- Logs de aprovação com data e responsável

## Testes
1. Acesse `login.php`
2. Clique em "Solicitar Acesso"
3. Preencha o formulário
4. Faça login como admin (admin@universidade.edu.br / admin123)
5. Acesse "Usuários Pendentes"
6. Aprove a solicitação
7. Teste o login com o novo usuário