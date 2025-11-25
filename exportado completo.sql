-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24/11/2025 às 13:57
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gestao_materiais_terceirizados`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias_materiais`
--

CREATE TABLE `categorias_materiais` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categorias_materiais`
--

INSERT INTO `categorias_materiais` (`id`, `nome`, `descricao`, `data_criacao`) VALUES
(1, 'Limpeza', 'Produtos de limpeza e higiene', '2025-11-17 20:20:44'),
(2, 'Ferramentas', 'Ferramentas e equipamentos', '2025-11-17 20:20:44'),
(3, 'Equipamentos', 'Equipamentos diversos', '2025-11-17 20:20:44'),
(4, 'Escritório', 'Material de escritório', '2025-11-17 20:20:44'),
(5, 'Manutenção', 'Materiais para manutenção', '2025-11-17 20:20:44'),
(6, 'Segurança', 'Equipamentos de segurança', '2025-11-17 20:20:44'),
(7, 'Informática', 'Materiais de informática', '2025-11-17 20:20:44'),
(8, 'Elétrica', 'Materiais elétricos', '2025-11-17 20:20:44'),
(9, 'Utensílios domésticos', 'Utilitários para a fazeres domesticos.', '2025-11-18 18:06:35');

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresas_terceirizadas`
--

CREATE TABLE `empresas_terceirizadas` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `tipo_servico` enum('Limpeza','Manutenção','Auxiliares Administrativos','Outros') NOT NULL,
  `numero_contrato` varchar(50) DEFAULT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `responsavel_id` int(11) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `status` enum('Ativa','Inativa','Suspensa') DEFAULT 'Ativa',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `empresas_terceirizadas`
--

INSERT INTO `empresas_terceirizadas` (`id`, `nome`, `tipo_servico`, `numero_contrato`, `cnpj`, `responsavel_id`, `telefone`, `email`, `endereco`, `status`, `data_criacao`, `data_atualizacao`) VALUES
(1, 'Lua - Limpeza', 'Limpeza', '25879', '', 1, '', '', NULL, 'Ativa', '2025-11-13 17:45:09', '2025-11-19 13:04:56'),
(8, 'Teste', 'Limpeza', '3654', NULL, 1, NULL, NULL, NULL, 'Ativa', '2025-11-13 20:22:46', '2025-11-13 20:22:46'),
(9, 'Segurança', '', '123456789', '8/52369741', 1, '879636996647', 'email@gmail.com', NULL, 'Ativa', '2025-11-14 16:40:51', '2025-11-19 13:03:55'),
(10, 'Empresa de prestação de serviço', '', '2321568', '216823', 1, '859354', 'tratadores@gmail.com', NULL, 'Ativa', '2025-11-17 20:23:10', '2025-11-19 13:04:38');

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_estoque`
--

CREATE TABLE `historico_estoque` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `estoque_anterior` decimal(10,2) DEFAULT NULL,
  `estoque_novo` decimal(10,2) DEFAULT NULL,
  `tipo_movimento` enum('Entrada','Saída','Ajuste','Inventário') NOT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `locais_armazenamento`
--

CREATE TABLE `locais_armazenamento` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `locais_armazenamento`
--

INSERT INTO `locais_armazenamento` (`id`, `nome`, `descricao`, `ativo`, `data_criacao`) VALUES
(1, 'Teste', 'Descrição teste', 1, '2025-11-18 17:00:05'),
(2, 'Almoxarifado Limpeza', 'Produtos de limpeza', 1, '2025-11-19 12:56:41'),
(3, 'Almoxarifado Manutenção', 'Materiais de manutenção', 1, '2025-11-19 12:56:41');

-- --------------------------------------------------------

--
-- Estrutura para tabela `materiais`
--

CREATE TABLE `materiais` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `codigo_sku` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria_id` int(11) NOT NULL,
  `unidade_medida_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `local_id` int(11) NOT NULL,
  `estoque_atual` decimal(10,2) DEFAULT 0.00,
  `ponto_reposicao` decimal(10,2) NOT NULL,
  `estoque_maximo` decimal(10,2) NOT NULL,
  `valor_unitario` decimal(10,2) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `materiais`
--

INSERT INTO `materiais` (`id`, `nome`, `codigo_sku`, `descricao`, `categoria_id`, `unidade_medida_id`, `empresa_id`, `local_id`, `estoque_atual`, `ponto_reposicao`, `estoque_maximo`, `valor_unitario`, `observacoes`, `ativo`, `data_criacao`, `data_atualizacao`) VALUES
(1, 'Sabão em pó', 'LIM001EMP', NULL, 1, 1, 1, 1, 10.00, 5.00, 50.00, NULL, NULL, 1, '2025-11-19 13:23:09', '2025-11-19 13:23:09'),
(13, 'Teste Material', 'TEST001', NULL, 1, 1, 1, 1, 10.00, 0.00, 0.00, NULL, NULL, 1, '2025-11-18 17:00:05', '2025-11-18 17:00:05'),
(18, 'Material Teste', 'TEST999', NULL, 1, 1, 1, 1, 10.00, 0.00, 0.00, NULL, NULL, 1, '2025-11-18 20:23:31', '2025-11-18 20:23:31'),
(20, 'papel higienico', 'LIMLU0001', NULL, 1, 5, 1, 1, 100.00, 10.00, 1000.00, NULL, NULL, 1, '2025-11-18 20:26:28', '2025-11-18 20:26:28'),
(21, 'chave de fenda', 'FERTE0001', NULL, 2, 5, 8, 1, 19.00, 20.00, 1000.00, NULL, NULL, 1, '2025-11-19 12:47:55', '2025-11-19 12:47:55'),
(22, 'capacete', 'ELTSE0001', NULL, 8, 1, 9, 2, 10.00, 20.00, 100.00, NULL, NULL, 1, '2025-11-19 13:13:23', '2025-11-19 13:13:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes_entrada`
--

CREATE TABLE `movimentacoes_entrada` (
  `id` int(11) NOT NULL,
  `data_entrada` datetime NOT NULL DEFAULT current_timestamp(),
  `material_id` int(11) NOT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `nota_fiscal` varchar(50) DEFAULT NULL,
  `responsavel_id` int(11) DEFAULT NULL,
  `local_destino_id` int(11) DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes_saida`
--

CREATE TABLE `movimentacoes_saida` (
  `id` int(11) NOT NULL,
  `data_saida` datetime NOT NULL DEFAULT current_timestamp(),
  `material_id` int(11) NOT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `empresa_solicitante_id` int(11) DEFAULT NULL,
  `finalidade` enum('Manutenção','Limpeza','Uso Administrativo','Outros') NOT NULL,
  `responsavel_autorizacao_id` int(11) DEFAULT NULL,
  `local_destino` varchar(200) DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `perfis_acesso`
--

CREATE TABLE `perfis_acesso` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `permissoes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissoes`)),
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `perfis_acesso`
--

INSERT INTO `perfis_acesso` (`id`, `nome`, `descricao`, `permissoes`, `ativo`, `data_criacao`) VALUES
(1, 'Administrador', 'Acesso total ao sistema', '{\"criar\": true, \"editar\": true, \"excluir\": true, \"relatorios\": true, \"usuarios\": true}', 1, '2025-11-13 17:19:17'),
(2, 'Gestor', 'Gerenciamento operacional', '{\"criar\": true, \"editar\": true, \"excluir\": false, \"relatorios\": true, \"usuarios\": false}', 1, '2025-11-13 17:19:17'),
(3, 'Operador', 'Operações básicas', '{\"criar\": true, \"editar\": false, \"excluir\": false, \"relatorios\": false, \"usuarios\": false}', 1, '2025-11-13 17:19:17'),
(4, 'Consulta', 'Apenas visualização', '{\"criar\": false, \"editar\": false, \"excluir\": false, \"relatorios\": true, \"usuarios\": false}', 1, '2025-11-13 17:19:17');

-- --------------------------------------------------------

--
-- Estrutura para tabela `unidades_medida`
--

CREATE TABLE `unidades_medida` (
  `id` int(11) NOT NULL,
  `simbolo` varchar(20) NOT NULL,
  `descricao` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `unidades_medida`
--

INSERT INTO `unidades_medida` (`id`, `simbolo`, `descricao`) VALUES
(1, 'un', 'Descrição teste'),
(2, 'L', 'Litro'),
(3, 'kg', 'Quilograma'),
(4, 'cx', 'Caixa'),
(5, 'pct', 'Pacote'),
(6, 'rsm', 'Resma'),
(7, 'rl', 'Rolo'),
(8, 'lt', 'Lata');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `cargo` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `perfil_id` int(11) DEFAULT 1,
  `senha` varchar(255) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `empresas_vinculadas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`empresas_vinculadas`)),
  `ultimo_acesso` timestamp NULL DEFAULT NULL,
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `cargo`, `telefone`, `ativo`, `data_criacao`, `perfil_id`, `senha`, `departamento`, `empresas_vinculadas`, `ultimo_acesso`, `data_atualizacao`) VALUES
(1, 'João Silva', 'joao.silva@universidade.edu.br', 'Gestor de Contratos', '(11) 98765-4321', 1, '2025-11-11 19:18:39', 3, NULL, 'logística', '[]', NULL, '2025-11-17 20:18:43'),
(2, 'Maria Santos', 'maria.santos@universidade.edu.br', 'Supervisora de Manutenção', '(11) 99876-5432', 0, '2025-11-11 19:18:39', 1, NULL, NULL, '[]', NULL, '2025-11-24 12:13:03'),
(3, 'Administrador', 'admin@universidade.edu.br', '', NULL, 1, '2025-11-13 17:19:17', 1, '$2y$10$3dGOCvQlFIAiQCYySgXdIu0AzOwWXzBMBmFXeUthm0v2VGG/lskri', 'TI', '[]', '2025-11-13 17:44:34', '2025-11-14 19:07:49'),
(4, 'teste01', 'asdf@asdfasdfas.com', '', NULL, 1, '2025-11-14 19:13:03', 2, '$2y$10$KbKKlJ4XqvwDwqBtzxbKa.J2djMVxqlxPPocfMvtrJQC8HUSERf.6', 'qualquerum', NULL, NULL, '2025-11-14 19:13:03'),
(5, 'teste02', 'teste01@gmail.com', '', NULL, 1, '2025-11-17 19:56:59', 3, '$2y$10$b2m3j1WjOsp7mi/4rykYtONYd4dKFqr6zFpSplHMZwJjDqNteHehS', 'teste', NULL, NULL, '2025-11-17 19:56:59'),
(6, 'Marcelo_teste', 'marcelo_teste@gmail.com', '', NULL, 1, '2025-11-18 16:42:27', 3, '$2y$10$MeXuAoFA9wp4G/Hhga1TpuMrNZTlLSqT8B4wnp13FHrnG290lQ1hO', 'teste', NULL, NULL, '2025-11-18 16:42:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_empresas`
--

CREATE TABLE `usuarios_empresas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `data_vinculo` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios_empresas`
--

INSERT INTO `usuarios_empresas` (`id`, `usuario_id`, `empresa_id`, `data_vinculo`) VALUES
(4, 1, 1, '2025-11-17 20:18:43'),
(5, 5, 1, '2025-11-17 20:38:11'),
(6, 5, 8, '2025-11-17 20:38:11'),
(7, 5, 9, '2025-11-17 20:38:11'),
(9, 6, 1, '2025-11-19 13:07:15'),
(10, 6, 8, '2025-11-19 13:07:15'),
(11, 2, 1, '2025-11-19 13:51:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_pendentes`
--

CREATE TABLE `usuarios_pendentes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `justificativa` text DEFAULT NULL,
  `status` enum('Pendente','Aprovado','Rejeitado') DEFAULT 'Pendente',
  `data_solicitacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `aprovado_por` int(11) DEFAULT NULL,
  `data_aprovacao` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios_pendentes`
--

INSERT INTO `usuarios_pendentes` (`id`, `nome`, `email`, `senha`, `departamento`, `justificativa`, `status`, `data_solicitacao`, `aprovado_por`, `data_aprovacao`) VALUES
(1, 'Marcelo_teste', 'marcelo_teste@gmail.com', '$2y$10$MeXuAoFA9wp4G/Hhga1TpuMrNZTlLSqT8B4wnp13FHrnG290lQ1hO', 'teste', 'Preciso cadastrar acessos', 'Aprovado', '2025-11-18 16:40:42', 3, '2025-11-18 16:42:27');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categorias_materiais`
--
ALTER TABLE `categorias_materiais`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `empresas_terceirizadas`
--
ALTER TABLE `empresas_terceirizadas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD UNIQUE KEY `cnpj` (`cnpj`),
  ADD KEY `responsavel_id` (`responsavel_id`);

--
-- Índices de tabela `historico_estoque`
--
ALTER TABLE `historico_estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_material` (`material_id`),
  ADD KEY `idx_data` (`data_criacao`);

--
-- Índices de tabela `locais_armazenamento`
--
ALTER TABLE `locais_armazenamento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `materiais`
--
ALTER TABLE `materiais`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_sku` (`codigo_sku`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `unidade_medida_id` (`unidade_medida_id`),
  ADD KEY `idx_empresa` (`empresa_id`),
  ADD KEY `idx_local` (`local_id`),
  ADD KEY `idx_codigo` (`codigo_sku`);

--
-- Índices de tabela `movimentacoes_entrada`
--
ALTER TABLE `movimentacoes_entrada`
  ADD PRIMARY KEY (`id`),
  ADD KEY `responsavel_id` (`responsavel_id`),
  ADD KEY `local_destino_id` (`local_destino_id`),
  ADD KEY `idx_material` (`material_id`),
  ADD KEY `idx_data` (`data_entrada`);

--
-- Índices de tabela `movimentacoes_saida`
--
ALTER TABLE `movimentacoes_saida`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_solicitante_id` (`empresa_solicitante_id`),
  ADD KEY `responsavel_autorizacao_id` (`responsavel_autorizacao_id`),
  ADD KEY `idx_material` (`material_id`),
  ADD KEY `idx_data` (`data_saida`);

--
-- Índices de tabela `perfis_acesso`
--
ALTER TABLE `perfis_acesso`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `unidades_medida`
--
ALTER TABLE `unidades_medida`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `simbolo` (`simbolo`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_usuarios_email` (`email`),
  ADD KEY `idx_usuarios_perfil` (`perfil_id`),
  ADD KEY `idx_usuarios_ativo` (`ativo`);

--
-- Índices de tabela `usuarios_empresas`
--
ALTER TABLE `usuarios_empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vinculo` (`usuario_id`,`empresa_id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices de tabela `usuarios_pendentes`
--
ALTER TABLE `usuarios_pendentes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `aprovado_por` (`aprovado_por`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categorias_materiais`
--
ALTER TABLE `categorias_materiais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `empresas_terceirizadas`
--
ALTER TABLE `empresas_terceirizadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `historico_estoque`
--
ALTER TABLE `historico_estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `locais_armazenamento`
--
ALTER TABLE `locais_armazenamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `materiais`
--
ALTER TABLE `materiais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `movimentacoes_entrada`
--
ALTER TABLE `movimentacoes_entrada`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `movimentacoes_saida`
--
ALTER TABLE `movimentacoes_saida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `perfis_acesso`
--
ALTER TABLE `perfis_acesso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `unidades_medida`
--
ALTER TABLE `unidades_medida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuarios_empresas`
--
ALTER TABLE `usuarios_empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `usuarios_pendentes`
--
ALTER TABLE `usuarios_pendentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `empresas_terceirizadas`
--
ALTER TABLE `empresas_terceirizadas`
  ADD CONSTRAINT `empresas_terceirizadas_ibfk_1` FOREIGN KEY (`responsavel_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `historico_estoque`
--
ALTER TABLE `historico_estoque`
  ADD CONSTRAINT `historico_estoque_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiais` (`id`),
  ADD CONSTRAINT `historico_estoque_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `materiais`
--
ALTER TABLE `materiais`
  ADD CONSTRAINT `materiais_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_materiais` (`id`),
  ADD CONSTRAINT `materiais_ibfk_2` FOREIGN KEY (`unidade_medida_id`) REFERENCES `unidades_medida` (`id`),
  ADD CONSTRAINT `materiais_ibfk_3` FOREIGN KEY (`empresa_id`) REFERENCES `empresas_terceirizadas` (`id`),
  ADD CONSTRAINT `materiais_ibfk_4` FOREIGN KEY (`local_id`) REFERENCES `locais_armazenamento` (`id`);

--
-- Restrições para tabelas `movimentacoes_entrada`
--
ALTER TABLE `movimentacoes_entrada`
  ADD CONSTRAINT `movimentacoes_entrada_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiais` (`id`),
  ADD CONSTRAINT `movimentacoes_entrada_ibfk_2` FOREIGN KEY (`responsavel_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `movimentacoes_entrada_ibfk_3` FOREIGN KEY (`local_destino_id`) REFERENCES `locais_armazenamento` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `movimentacoes_saida`
--
ALTER TABLE `movimentacoes_saida`
  ADD CONSTRAINT `movimentacoes_saida_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiais` (`id`),
  ADD CONSTRAINT `movimentacoes_saida_ibfk_2` FOREIGN KEY (`empresa_solicitante_id`) REFERENCES `empresas_terceirizadas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `movimentacoes_saida_ibfk_3` FOREIGN KEY (`responsavel_autorizacao_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_perfil` FOREIGN KEY (`perfil_id`) REFERENCES `perfis_acesso` (`id`);

--
-- Restrições para tabelas `usuarios_empresas`
--
ALTER TABLE `usuarios_empresas`
  ADD CONSTRAINT `usuarios_empresas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuarios_empresas_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresas_terceirizadas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `usuarios_pendentes`
--
ALTER TABLE `usuarios_pendentes`
  ADD CONSTRAINT `usuarios_pendentes_ibfk_1` FOREIGN KEY (`aprovado_por`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
