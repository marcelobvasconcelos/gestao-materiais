# Controle de Acesso - Gestão de Usuários

## Resumo
Implementado controle de acesso onde **somente administradores** podem cadastrar, editar e gerenciar usuários do sistema.

## Funcionalidades Implementadas

### 1. Restrições no Frontend (index.php)
- **Formulário de cadastro**: Visível apenas para administradores
- **Botões de ação**: Editar/Ativar/Desativar usuários apenas para administradores
- **Aviso visual**: Mensagem informativa para usuários não-administradores
- **Validação JavaScript**: Verificação de permissão antes de executar ações

### 2. Restrições no Backend (api_filtrada.php)
- **Endpoint /usuarios**: Acesso restrito apenas para perfil_id = 1 (Administrador)
- **Todas as ações**: criar, listar, editar, ativar/desativar protegidas
- **Mensagem de erro**: "Acesso negado! Apenas administradores podem gerenciar usuários."

### 3. Arquivo de Verificação (verificar_permissoes.php)
- **verificarPermissaoAdmin()**: Função para validar se usuário é administrador
- **verificarPermissao($acao)**: Função genérica para validar permissões por ação
- Pode ser usado em outras partes do sistema

## Perfis de Acesso

| Perfil | ID | Pode Gerenciar Usuários |
|--------|----|-----------------------|
| Administrador | 1 | ✅ Sim |
| Gestor | 2 | ❌ Não |
| Operador | 3 | ❌ Não |
| Consulta | 4 | ❌ Não |

## Comportamento por Perfil

### Administrador (perfil_id = 1)
- ✅ Vê formulário de cadastro de usuários
- ✅ Vê botões de editar/ativar/desativar usuários
- ✅ Pode executar todas as ações de usuários
- ✅ Acesso total ao sistema

### Outros Perfis (perfil_id > 1)
- ❌ Formulário de cadastro oculto
- ❌ Botões de ação ocultos
- ❌ API retorna erro de acesso negado
- ℹ️ Vê aviso de acesso restrito
- 👁️ Pode apenas visualizar lista de usuários

## Segurança

### Validações Implementadas
1. **Sessão**: Verificação se usuário está logado
2. **Perfil**: Validação do perfil_id na sessão
3. **Frontend**: Ocultação de elementos visuais
4. **Backend**: Bloqueio de endpoints da API
5. **JavaScript**: Validação antes de chamadas AJAX

### Proteções
- Mesmo que usuário tente acessar diretamente a API, será bloqueado
- Interface não mostra opções não permitidas
- Mensagens claras sobre restrições de acesso

## Uso

### Para verificar se usuário é admin:
```javascript
const usuarioLogado = JSON.parse(localStorage.getItem('usuario_logado'));
const isAdmin = usuarioLogado.perfil_id == 1;
```

### Para usar no PHP:
```php
require_once 'verificar_permissoes.php';

if (!verificarPermissaoAdmin()) {
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}
```

## Extensibilidade

O sistema pode ser facilmente estendido para:
- Adicionar mais permissões específicas
- Criar controles granulares por funcionalidade
- Implementar permissões por módulo
- Adicionar logs de tentativas de acesso