-- =====================================================================
-- banco.sql - Script de criação do banco "stardew_wiki" (MySQL/MariaDB)
-- Importe pelo phpMyAdmin (aba "Importar") ou: mysql -u root -p < banco.sql
-- =====================================================================
CREATE DATABASE IF NOT EXISTS stardew_wiki
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stardew_wiki;

-- Usuários do sistema. O campo "tipo" separa jogadores comuns de administradores.
CREATE TABLE IF NOT EXISTS usuarios (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome        VARCHAR(50)  NOT NULL,
  email       VARCHAR(120) NOT NULL UNIQUE,
  senha_hash  VARCHAR(255) NOT NULL,            -- resultado de password_hash()
  tipo        ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
  criado_em   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Registros da wiki (cultivos, peixes, moradores e itens).
CREATE TABLE IF NOT EXISTS itens_wiki (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome         VARCHAR(80)  NOT NULL,
  categoria    ENUM('cultivo','peixe','morador','item') NOT NULL,
  estacao      ENUM('Primavera','Verão','Outono','Inverno','Todas') NOT NULL DEFAULT 'Todas',
  preco_venda  INT UNSIGNED NULL,               -- em ouro (g); NULL para moradores
  descricao    TEXT NOT NULL,
  criado_por   INT UNSIGNED NULL,
  criado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_itens_usuario FOREIGN KEY (criado_por)
    REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Dados de exemplo para a wiki não começar vazia.
INSERT INTO itens_wiki (nome, categoria, estacao, preco_venda, descricao) VALUES
('Pastinaca', 'cultivo', 'Primavera', 35, 'Cultivo de primavera de crescimento rápido (4 dias). Ótimo para começar a fazenda.'),
('Mirtilo', 'cultivo', 'Verão', 50, 'Produz várias frutas por colheita, o que rende bom lucro ao longo do verão.'),
('Abóbora', 'cultivo', 'Outono', 320, 'Demora 13 dias para crescer, mas tem alto valor de venda. Também é usada no Festival de Outono.'),
('Carpa', 'peixe', 'Todas', 30, 'Peixe comum encontrado em lagos e na Floresta Cindersap.'),
('Robin', 'morador', 'Todas', NULL, 'Carpinteira da Vila dos Pelicanos. Constrói e melhora os prédios da sua fazenda.'),
('Willy', 'morador', 'Todas', NULL, 'Pescador que mora no cais da praia. Vende varas de pescar e iscas.'),
('Pedra', 'item', 'Todas', 2, 'Recurso básico, obtido ao quebrar rochas com a picareta. Usado em construções e artesanato.');
