# Sistema de Gestão de Usuários e Perfis

## Como Configurar

### 1. Executar o Script SQL
Execute o arquivo `sql_usuarios_perfis.sql` no seu banco de dados MySQL:

```sql
-- No phpMyAdmin ou MySQL Workbench, execute o conteúdo do arquivo sql_usuarios_perfis.sql
```

### 2. Usuário Padrão Criado
- **Email**: admin@universidade.edu.br  
- **Senha**: admin123
- **Perfil**: Administrador

## Funcionalidades Implementadas

### Perfis de Acesso
1. **Administrador** - Acesso total ao sistema
2. **Gestor** - Gerenciamento operacional (sem exclusão)
3. **Operador** - Operações básicas (entrada/saída)
4. **Consulta** - Apenas visualização

### Gestão de Usuários
- ✅ Cadastro de novos usuários
- ✅ Listagem com perfis e status
- ✅ Ativar/Desativar usuários
- ✅ Validação de email único
- ✅ Criptografia de senhas
- ✅ Controle por departamento

### Segurança
- Senhas criptografadas com `password_hash()`
- Validação de email único
- Controle de status ativo/inativo
- Perfis com permissões específicas

## Como Usar

### 1. Fazer Login
- Acesse `login.php` no seu navegador
- Use as credenciais padrão ou crie novos usuários
- O sistema redirecionará automaticamente após o login

### 2. Gerenciar Usuários
1. Acesse a seção "👥 Usuários" no menu lateral
2. Cadastre novos usuários preenchendo todos os campos
3. Gerencie usuários existentes (ativar/desativar)
4. Visualize os perfis de acesso e suas permissões

### 3. Segurança
- O sistema verifica automaticamente se o usuário está logado
- Redireciona para login se não autenticado
- Botão "Sair" no canto superior direito

## Arquivos do Sistema de Login

- `login.php` - Tela de autenticação
- `logout.php` - Encerramento de sessão
- API expandida com rotas de autenticação
- Verificação automática no sistema principal

## Próximos Passos (Opcional)

- ✅ Sistema de login implementado
- ✅ Controle de sessões
- Logs de auditoria
- Recuperação de senha
- Edição completa de usuários