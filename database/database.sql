CREATE DATABASE tsf;

USE tsf;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE usuario(
    pk_user INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    user_name VARCHAR(90),
    user_mail VARCHAR(100),
    user_password VARCHAR(255),
    user_adm BOOLEAN DEFAULT FALSE NOT NULL
);

CREATE TABLE alertas(
    pk_alerta INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    fk_user_id INT Not NULL,
    fk_user_name VARCHAR(90),
    fk_user_mail VARCHAR(100),
    alerta_texto VARCHAR(3000),
    alerta_data DATETIME DEFAULT CURRENT_TIMESTAMP,
    alerta_titulo VARCHAR(100),
    alerta_tipo ENUM('rota', 'estacao', 'sistema') DEFAULT 'sistema'
);

CREATE TABLE estacoes (
  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  nome varchar(100) NOT NULL,
  latitude decimal(10,8) NOT NULL,
  longitude decimal(11,8) NOT NULL,
  endereco text DEFAULT NULL,
  data_criacao timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO estacoes (`id`, `nome`, `latitude`, `longitude`, `endereco`, `data_criacao`) VALUES
(1, 'Estação 1', -26.30400000, -48.84600000, 'Joinville', '2025-10-27 11:11:09'),
(2, 'Estação 2', -3.73045100, -38.52179900, 'Fortaleza', '2025-10-27 11:13:07'),
(3, 'Estação 1.1', -25.42770000, -49.27310000, 'Curitiba', '2025-10-27 14:58:37'),
(4, 'Estação 1.2', -23.59140000, -48.05310000, 'Itapetininga', '2025-10-27 14:59:50'),
(5, 'Estação 1.3', -19.92270000, -43.94510000, 'Belo Horizonte', '2025-10-27 15:00:29'),
(6, 'Estação 1.4', -18.85000000, -41.94000000, 'Governador Valadares', '2025-10-27 15:01:26'),
(7, 'Estação 1.5', -9.39000000, -40.50000000, 'Petrolina', '2025-10-27 15:02:21'),
(8, 'Estação 1.6', -7.21300000, -39.31500000, 'Juazeiro do Norte', '2025-10-27 15:03:02'),
(9, 'Estação 1.7', -7.23000000, -35.88000000, 'Campina Grande', '2025-10-27 15:04:08'),
(10, 'Estação 1.8', -5.96166700, -35.20888900, 'Natal', '2025-10-27 15:05:12'),
(11, 'Estação 1.9', -5.18412850, -37.34778050, 'Mossoró', '2025-10-27 15:05:57');

CREATE TABLE rotas (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `distancia_km` decimal(8,2) DEFAULT NULL,
  `tempo_estimado_min` int(11) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rotas` (`id`, `nome`, `distancia_km`, `tempo_estimado_min`, `data_criacao`) VALUES
(1, 'Rota Sul-Norte', 3511.25, 3511, '2025-10-27 15:36:21');

CREATE TABLE rota_estacoes (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `id_rota` int(11) NOT NULL,
  `id_estacao` int(11) NOT NULL,
  `ordem` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rota_estacoes` (`id`, `id_rota`, `id_estacao`, `ordem`) VALUES
(1, 1, 1, 0),
(2, 1, 3, 1),
(3, 1, 4, 2),
(4, 1, 5, 3),
(5, 1, 6, 4),
(6, 1, 7, 5),
(7, 1, 8, 6),
(8, 1, 9, 7),
(9, 1, 10, 8),
(10, 1, 11, 9),
(11, 1, 2, 10);

ALTER TABLE `rota_estacoes`
  ADD KEY `id_rota` (`id_rota`),
  ADD KEY `id_estacao` (`id_estacao`);

ALTER TABLE `rota_estacoes`
  ADD CONSTRAINT `rota_estacoes_ibfk_1` FOREIGN KEY (`id_rota`) REFERENCES `rotas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rota_estacoes_ibfk_2` FOREIGN KEY (`id_estacao`) REFERENCES `estacoes` (`id`) ON DELETE CASCADE;
COMMIT;

CREATE TABLE chamados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    prioridade ENUM('baixa', 'media', 'alta') NOT NULL,
    status ENUM('aberto', 'em_andamento', 'fechado') DEFAULT 'aberto',
    user_id INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuario(pk_user) ON DELETE CASCADE
);

CREATE TABLE sensordata (
    pk_sensor_data INT AUTO_INCREMENT PRIMARY KEY,
    sensor_value DECIMAL(10,2) NOT NULL,
    sensor_type VARCHAR(50) NOT NULL,
    sensor_topic VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CREATE TABLE sensor (
--     pk_sensor_data INT AUTO_INCREMENT PRIMARY KEY,
--     sensor_value DECIMAL(10,2) NOT NULL,
--     sensor_type VARCHAR(50) NOT NULL,
--     sensor_topic VARCHAR(100),
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );