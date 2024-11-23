-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           8.0.34 - MySQL Community Server - GPL
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.5.0.6677
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para monitoramento
CREATE DATABASE IF NOT EXISTS `monitoramento` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `monitoramento`;

-- Copiando estrutura para tabela monitoramento.alunos
CREATE TABLE IF NOT EXISTS `alunos` (
  `ra` int NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data_nasc` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turma` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turno` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.descritores
CREATE TABLE IF NOT EXISTS `descritores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descritor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `habilidade` text COLLATE utf8mb4_general_ci,
  `materia` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.disciplinas
CREATE TABLE IF NOT EXISTS `disciplinas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.gabarito_alunos
CREATE TABLE IF NOT EXISTS `gabarito_alunos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `aluno` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ra` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turma` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turno` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_prova` int DEFAULT NULL,
  `serie` int DEFAULT NULL,
  `nome_professor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descritores` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `disciplina` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_prova` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pontos_prova` float DEFAULT NULL,
  `QNT_perguntas` int DEFAULT NULL,
  `data_aluno` date DEFAULT NULL,
  `acertos` int DEFAULT NULL,
  `porcentagem` int DEFAULT NULL,
  `pontos_aluno` int DEFAULT NULL,
  `pontos_aluno_quebrado` float DEFAULT NULL,
  `perguntas_respostas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `perguntas_certas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `perguntas_erradas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descritores_certos` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descritores_errados` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `recuperacao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metodo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.gabarito_alunos_primeira_prova
CREATE TABLE IF NOT EXISTS `gabarito_alunos_primeira_prova` (
  `id` int NOT NULL AUTO_INCREMENT,
  `aluno` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ra` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turma` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turno` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_prova` int DEFAULT NULL,
  `serie` int DEFAULT NULL,
  `nome_professor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descritores` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `disciplina` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_prova` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pontos_prova` float DEFAULT NULL,
  `QNT_perguntas` int DEFAULT NULL,
  `data_aluno` date DEFAULT NULL,
  `acertos` int DEFAULT NULL,
  `porcentagem` int DEFAULT NULL,
  `pontos_aluno` int DEFAULT NULL,
  `pontos_aluno_quebrado` float DEFAULT NULL,
  `perguntas_respostas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `perguntas_certas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `perguntas_erradas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descritores_certos` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descritores_errados` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metodo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.gabarito_alunos_recuperacao
CREATE TABLE IF NOT EXISTS `gabarito_alunos_recuperacao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `aluno` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ra` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turma` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turno` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_prova` int DEFAULT NULL,
  `id_prova_rec` int DEFAULT NULL,
  `serie` int DEFAULT NULL,
  `nome_professor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descritores` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `disciplina` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_prova` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pontos_prova` float DEFAULT NULL,
  `QNT_perguntas` int DEFAULT NULL,
  `data_aluno` date DEFAULT NULL,
  `acertos` int DEFAULT NULL,
  `porcentagem` int DEFAULT NULL,
  `pontos_aluno` int DEFAULT NULL,
  `pontos_aluno_quebrado` float DEFAULT NULL,
  `perguntas_respostas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `perguntas_certas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `perguntas_erradas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descritores_certos` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descritores_errados` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.gabarito_professores
CREATE TABLE IF NOT EXISTS `gabarito_professores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_professor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_prova` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turmas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descritores` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `disciplina` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `valor` int DEFAULT NULL,
  `QNT_perguntas` int DEFAULT NULL,
  `data_prova` date DEFAULT NULL,
  `gabarito` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `liberado` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `liberar_prova` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metodo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `area_conhecimento` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `orientacoes` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.gabarito_professores_recuperacao
CREATE TABLE IF NOT EXISTS `gabarito_professores_recuperacao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_prova` int DEFAULT NULL,
  `alunos` text COLLATE utf8mb4_general_ci,
  `nome_professor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_prova` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descritores` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `disciplina` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `valor` int DEFAULT NULL,
  `QNT_perguntas` int DEFAULT NULL,
  `data_prova_rec` datetime DEFAULT NULL,
  `gabarito` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `liberado` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `liberar_prova` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.logs_adm
CREATE TABLE IF NOT EXISTS `logs_adm` (
  `id` int NOT NULL AUTO_INCREMENT,
  `autor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.logs_professor
CREATE TABLE IF NOT EXISTS `logs_professor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `autor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.periodo
CREATE TABLE IF NOT EXISTS `periodo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data_inicial` date DEFAULT NULL,
  `data_final` date DEFAULT NULL,
  `data_criacao` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.professores
CREATE TABLE IF NOT EXISTS `professores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `usuario` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `disciplinas` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.simulados
CREATE TABLE IF NOT EXISTS `simulados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `turma_id` int DEFAULT NULL,
  `nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `area_conhecimento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data` datetime NOT NULL,
  `orientacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `turma_id` (`turma_id`),
  CONSTRAINT `turma_id` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.simulados_prova
CREATE TABLE IF NOT EXISTS `simulados_prova` (
  `id` int NOT NULL AUTO_INCREMENT,
  `simulado_id` int DEFAULT NULL,
  `gabarito_professor_id` int DEFAULT NULL,
  `ordem` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__simulados` (`simulado_id`),
  KEY `FK__gabarito_professores` (`gabarito_professor_id`),
  CONSTRAINT `FK__gabarito_professores` FOREIGN KEY (`gabarito_professor_id`) REFERENCES `gabarito_professores` (`id`),
  CONSTRAINT `FK__simulados` FOREIGN KEY (`simulado_id`) REFERENCES `simulados` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.turmas
CREATE TABLE IF NOT EXISTS `turmas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turno` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `serie` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `curso` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela monitoramento.usuarios_pfa
CREATE TABLE IF NOT EXISTS `usuarios_pfa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `usuario` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turno` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `disciplina` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportação de dados foi desmarcado.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
