<?php

namespace app\config;

use PDO;

class Database
{

    private $host;
    private $db;
    private $user;
    private $pass;
    private $port;

    private function __construct()
    {
        $this->host = $_ENV["DB_HOST"];
        $this->db = $_ENV['DB_DATABASE'];
        $this->user = $_ENV['DB_USER'];
        $this->pass = $_ENV['DB_PASSWORD'];
        $this->port = $_ENV['DB_PORT'];
    }

    private static $conn;

    public static function GetInstance()
    {
        if (self::$conn === null) {
            $instance = new self();

            self::$conn = new PDO("mysql:host={$instance->host};port={$instance->port};dbname={$instance->db};charset=utf8mb4", $instance->user, $instance->pass);

            self::CreateTable();
        }
        return self::$conn;
    }

    public static function CreateTable()
    {

        // Eu configurei o sistema para criar as tabelas do banco de dados automaticamente
        // na primeira vez que ele é iniciado. Isso é útil porque é um sistema para minha
        // escola, e eles gostaram muito. Se outras escolas quiserem usar o sistema, pode
        // ser que não tenha ninguém lá que saiba criar as tabelas no banco de dados. Então,
        // quando a pessoa hospedar o sistema, ele já vai funcionar com todas as tabelas criadas,
        // facilitando a vida de todo mundo e garantindo que tudo funcione direitinho sem
        // precisar de conhecimento técnico.

        $professores = "CREATE TABLE IF NOT EXISTS professores(
                    id          int AUTO_INCREMENT primary key,
                    nome        varchar(255),
                    usuario     varchar(255),
                    senha       varchar(255),
                    numero      varchar(30),
                    disciplinas TEXT(255)
                );";

        $disciplinas = "CREATE TABLE IF NOT EXISTS disciplinas(
                    id          int AUTO_INCREMENT primary key,
                    nome        varchar(255)
            );";

        $turmas = "CREATE TABLE IF NOT EXISTS turmas(
                    id          int AUTO_INCREMENT primary key,
                    nome        varchar(255),
                    turno       varchar(255),
                    serie       varchar(255),
                    curso       varchar(255)
            );";

        $alunos = "CREATE TABLE IF NOT EXISTS alunos(
                    ra          int primary key,
                    nome        varchar(255),
                    data_nasc   varchar(255),
                    turma       varchar(255),
                    turno       varchar(255)
                );";

        $descritores = "CREATE TABLE IF NOT EXISTS descritores(
                    id          int AUTO_INCREMENT primary key,
                    descritor   varchar(255),
                    habilidade  text,
                    materia     varchar(255)
                );";

        $gabarito_provas_alunos = "CREATE TABLE IF NOT EXISTS gabarito_alunos(
                id INT AUTO_INCREMENT PRIMARY KEY,
                aluno VARCHAR(255),
                ra VARCHAR(255),
                turma VARCHAR(255),
                turno VARCHAR(255),
                id_prova INT,
                serie INT,
                nome_professor VARCHAR(255),
                descritores VARCHAR(255),
                disciplina VARCHAR(255),
                nome_prova VARCHAR(255),
                pontos_prova FLOAT,
                QNT_perguntas INT,
                data_aluno DATE,
                acertos INT,
                porcentagem INT,
                pontos_aluno INT, 
                pontos_aluno_quebrado FLOAT,
                perguntas_respostas VARCHAR(255),
                perguntas_certas VARCHAR(255),
                perguntas_erradas VARCHAR(255),
                descritores_certos VARCHAR(255),
                descritores_errados VARCHAR(255),
                recuperacao VARCHAR(255),
                status VARCHAR(255),
                metodo VARCHAR(255)
            );";

        $gabarito_provas_alunos_prova = "CREATE TABLE IF NOT EXISTS gabarito_alunos_primeira_prova(
                id INT AUTO_INCREMENT PRIMARY KEY,
                aluno VARCHAR(255),
                ra VARCHAR(255),
                turma VARCHAR(255),
                turno VARCHAR(255),
                id_prova INT,
                serie INT,
                nome_professor VARCHAR(255),
                descritores VARCHAR(255),
                disciplina VARCHAR(255),
                nome_prova VARCHAR(255),
                pontos_prova FLOAT,
                QNT_perguntas INT,
                data_aluno DATE,
                acertos INT,
                porcentagem INT,
                pontos_aluno INT, 
                pontos_aluno_quebrado FLOAT,
                perguntas_respostas VARCHAR(255),
                perguntas_certas VARCHAR(255),
                perguntas_erradas VARCHAR(255),
                descritores_certos VARCHAR(255),
                descritores_errados VARCHAR(255),
                metodo          varchar(255)
            );";

        $gabarito_provas_professores = "CREATE TABLE IF NOT EXISTS gabarito_professores(
                id              int AUTO_INCREMENT primary key,
                nome_professor  varchar(255),
                nome_prova      varchar(255),
                turmas          varchar(255),
                descritores     varchar(255),
                disciplina     varchar(255),
                valor           int,
                QNT_perguntas   int,
                data_prova      date,
                gabarito        varchar(255),
                liberado        varchar(255),
                liberar_prova   varchar(255),
                metodo          varchar(255),
                area_conhecimento varchar(150) DEFAULT NULL,
                orientacoes text
        );"; 

        $logsADM = "CREATE TABLE IF NOT EXISTS logs_adm(
            id          int AUTO_INCREMENT primary key,
            autor       varchar(255),
            data        DATETIME,
            descricao   TEXT
        );";

        $logsPROFESSOR = "CREATE TABLE IF NOT EXISTS logs_professor(
            id          int AUTO_INCREMENT primary key,
            autor       varchar(255),
            data        DATETIME,
            descricao   TEXT
        );";

        $Periodo = "CREATE TABLE IF NOT EXISTS periodo(
            id          int AUTO_INCREMENT primary key,
            nome varchar(255),
            data_inicial DATE,
            data_final   DATE,
            data_criacao DATETIME
        );";

        $PFA = "CREATE TABLE IF NOT EXISTS usuarios_pfa(
            id          int AUTO_INCREMENT primary key,
            nome varchar(255),
            usuario varchar(255),
            senha varchar(255),
            turno varchar(255),
            disciplina varchar(255)
        );";

        $simulados = "CREATE TABLE IF NOT EXISTS `simulados` (
            `id` int NOT NULL AUTO_INCREMENT,
            `turma_id` int DEFAULT NULL,
            `nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
            `area_conhecimento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
            `data` datetime NOT NULL,
            `orientacoes` text COLLATE utf8mb4_general_ci,
            PRIMARY KEY (`id`),
            KEY `turma_id` (`turma_id`),
            CONSTRAINT `turma_id` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`)
        );";

        $simuladosProva = "CREATE TABLE IF NOT EXISTS `simulados_prova` (
            `id` int NOT NULL AUTO_INCREMENT,
            `simulado_id` int DEFAULT NULL,
            `gabarito_professor_id` int DEFAULT NULL,
            `ordem` int DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `FK__simulados` (`simulado_id`),
            KEY `FK__gabarito_professores` (`gabarito_professor_id`),
            CONSTRAINT `FK__gabarito_professores` FOREIGN KEY (`gabarito_professor_id`) REFERENCES `gabarito_professores` (`id`),
            CONSTRAINT `FK__simulados` FOREIGN KEY (`simulado_id`) REFERENCES `simulados` (`id`)
        );";

        self::GetInstance()->query($logsADM);
        self::GetInstance()->query($logsPROFESSOR);
        self::GetInstance()->query($professores);
        self::GetInstance()->query($disciplinas);
        self::GetInstance()->query($turmas);
        self::GetInstance()->query($alunos);
        self::GetInstance()->query($descritores);
        self::GetInstance()->query($gabarito_provas_professores);
        self::GetInstance()->query($gabarito_provas_alunos);
        self::GetInstance()->query($gabarito_provas_alunos_prova);
        self::GetInstance()->query($Periodo);
        self::GetInstance()->query($PFA);
        self::GetInstance()->query($simulados);
        self::GetInstance()->query($simuladosProva);
    }
}
