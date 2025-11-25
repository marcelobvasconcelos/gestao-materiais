# Sistema de Gerenciamento de Código SKU

## Como Funciona

### Geração Automática de SKU
O sistema gera códigos SKU automaticamente seguindo o padrão:
**[CATEGORIA][EMPRESA][NÚMERO]**

### Formato do SKU
- **3 letras da categoria** (ex: LIM para Limpeza, FER para Ferramentas)
- **2 letras da empresa** (ex: EM para primeira empresa)
- **4 dígitos sequenciais** (0001, 0002, 0003...)

### Exemplos
- `LIMPEM0001` - Primeiro material de Limpeza da empresa "Empresa Modelo"
- `FEREM0001` - Primeiro material de Ferramentas da empresa "Empresa Modelo"
- `LIMPEM0002` - Segundo material de Limpeza da empresa "Empresa Modelo"

## Funcionalidades

### 1. Geração Manual
- Clique no botão "Gerar" ao lado do campo SKU
- Selecione categoria e empresa primeiro
- SKU é gerado automaticamente

### 2. Geração Automática
- Deixe o campo SKU vazio
- O sistema gera automaticamente ao salvar
- Baseado na categoria e empresa selecionadas

### 3. SKU Personalizado
- Digite um SKU personalizado
- Sistema verifica se já existe
- Deve ser único no sistema

## Validações

### Unicidade
- Cada SKU deve ser único no sistema
- Sistema verifica duplicatas antes de salvar
- Erro exibido se SKU já existir

### Formato
- SKUs gerados seguem padrão fixo
- SKUs manuais podem ter formato livre
- Recomendado seguir padrão para organização

## Vantagens do Sistema

### Organização
- Fácil identificação por categoria
- Agrupamento por empresa
- Sequência numérica clara

### Automação
- Reduz erros manuais
- Padronização automática
- Geração rápida e confiável

### Flexibilidade
- Permite SKUs personalizados
- Mantém controle de unicidade
- Adaptável a diferentes necessidades

## Interface

### Campo SKU
- Placeholder indica geração automática
- Botão "Gerar" para criação manual
- Texto explicativo do formato

### Validações Visuais
- Alerta se categoria/empresa não selecionadas
- Confirmação quando SKU é gerado
- Erro se SKU duplicado

## Implementação Técnica

### Função PHP: `gerarCodigoSKU()`
- Busca nomes de categoria e empresa
- Extrai primeiras letras (sem acentos/espaços)
- Calcula próximo número sequencial
- Retorna SKU formatado

### Função JavaScript: `gerarSKU()`
- Valida seleção de categoria/empresa
- Chama API para gerar SKU
- Atualiza campo na interface
- Exibe feedback ao usuário

### Validação de Duplicatas
- Verificação no banco antes de inserir
- Retorno de erro se SKU existir
- Prevenção de conflitos

## Manutenção

### Alteração de Padrão
- Modificar função `gerarCodigoSKU()` na API
- Ajustar validações se necessário
- Manter compatibilidade com SKUs existentes

### Migração de Dados
- SKUs existentes mantidos
- Novos seguem padrão atual
- Possível padronização posterior