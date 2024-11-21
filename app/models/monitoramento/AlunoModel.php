<?php

namespace app\models\monitoramento;

use app\config\Database;
use PDO;

class AlunoModel
{

    public static function verificarLogin($user)
    {
        $sql = "SELECT * FROM alunos WHERE ra = :dado";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindvalue(":dado", $user);
        $query->execute();

        if ($query->rowCount() > 0) {
            return $query->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public static function GetProvas()
    {
        $sql = "SELECT * FROM gabarito_professores ORDER BY data_prova ASC";
        $query = Database::GetInstance()->prepare($sql);
        $query->execute();
        if ($query->rowCount() > 0) {
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public static function GetAlunos()
    {
        $sql = "SELECT * FROM alunos ORDER BY nome ASC ";
        $query = Database::GetInstance()->prepare($sql);
        $query->execute();
        if ($query->rowCount() > 0) {
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public static function GetAlunoByRa($ra)
    {
        $sql = "SELECT * FROM alunos WHERE ra = :ra";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindvalue(":ra", $ra);
        $query->execute();
        if ($query->rowCount() > 0) {
            return $query->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public static function GetProvasFinalizadas()
    {
        $sql = "SELECT * FROM gabarito_alunos ORDER BY data_aluno DESC ";
        $query = Database::GetInstance()->prepare($sql);
        $query->execute();
        if ($query->rowCount() > 0) {
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public static function Inserir_dados_prova($dados, $string)
    {
        $sql = "INSERT INTO gabarito_alunos
        (aluno, ra, turma, id_prova, nome_professor, descritores, disciplina, nome_prova, pontos_prova, QNT_perguntas, data_aluno, acertos, pontos_aluno,pontos_aluno_quebrado, perguntas_certas, descritores_certos, descritores_errados,perguntas_erradas,perguntas_respostas,porcentagem,turno,serie,status,metodo)
        VALUES (:ALUNO, :RA, :TURMA, :ID_PROVA, :NOME_PROFESSOR, :DESCRITORES, :DISCIPLINA, :NOME_PROVA, :PONTOS_PROVA, :QNT_PERGUNTAS, :DATA_ALUNO, :ACERTOS, :PONTOS_ALUNO,:PNTQ, :PERGUNTAS_CERTAS, :DESCRITORES_CERTOS, :DESCRITORES_ERRADOS,:PERG_ERRADAS,:PERG_RESP,:PORC,:TURNO,:SER,:ALN,:MTD)";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":ALUNO", $dados["aluno"]);
        $query->bindValue(":RA", $dados["ra"]);
        $query->bindValue(":TURMA", $dados["turma"]);
        $query->bindValue(":ID_PROVA", $dados["id_prova"]);
        $query->bindValue(":NOME_PROFESSOR", $dados["nome_professor"]);
        $query->bindValue(":DESCRITORES", $dados["descritores"]);
        $query->bindValue(":DISCIPLINA", $dados["disciplina"]);
        $query->bindValue(":NOME_PROVA", $dados["nome_prova"]);
        $query->bindValue(":PONTOS_PROVA", $dados["pontos_prova"]);
        $query->bindValue(":QNT_PERGUNTAS", $dados["QNT_perguntas"]);
        $query->bindValue(":DATA_ALUNO", $dados["data_aluno"]);
        $query->bindValue(":ACERTOS", $dados["acertos"]);
        $query->bindValue(":PONTOS_ALUNO", $dados["pontos_aluno"]);
        $query->bindValue(":PNTQ", $dados["pontos_aluno_quebrado"]);
        $query->bindValue(":PERGUNTAS_CERTAS", $dados["perguntas_certas"]);
        $query->bindValue(":DESCRITORES_CERTOS", $dados["descritores_certos"]);
        $query->bindValue(":DESCRITORES_ERRADOS", $dados["descritores_errados"]);
        $query->bindValue(":PERG_ERRADAS", $dados["perguntas_erradas"]);
        $query->bindValue(":PERG_RESP", $dados["perguntas_respostas"]);
        $query->bindValue(":PORC", $dados["porcentagem"]);
        $query->bindValue(":TURNO", $dados["turno"]);
        $query->bindValue(":SER", $dados["serie"]);
        $query->bindValue(":ALN", $string);
        $query->bindValue(":MTD", $dados["metodo"]);

        $query->execute();

        return $query;
    }

    public static function Inserir_dados_prova_1_prova($dados)
    {
        $sql = "INSERT INTO gabarito_alunos_primeira_prova
        (aluno, ra, turma, id_prova, nome_professor, descritores, disciplina, nome_prova, pontos_prova, QNT_perguntas, data_aluno, acertos, pontos_aluno,pontos_aluno_quebrado, perguntas_certas, descritores_certos, descritores_errados,perguntas_erradas,perguntas_respostas,porcentagem,turno,serie,metodo)
        VALUES (:ALUNO, :RA, :TURMA, :ID_PROVA, :NOME_PROFESSOR, :DESCRITORES, :DISCIPLINA, :NOME_PROVA, :PONTOS_PROVA, :QNT_PERGUNTAS, :DATA_ALUNO, :ACERTOS, :PONTOS_ALUNO,:PNTQ, :PERGUNTAS_CERTAS, :DESCRITORES_CERTOS, :DESCRITORES_ERRADOS,:PERG_ERRADAS,:PERG_RESP,:PORC,:TURNO,:SER,:MTD)";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":ALUNO", $dados["aluno"]);
        $query->bindValue(":RA", $dados["ra"]);
        $query->bindValue(":TURMA", $dados["turma"]);
        $query->bindValue(":ID_PROVA", $dados["id_prova"]);
        $query->bindValue(":NOME_PROFESSOR", $dados["nome_professor"]);
        $query->bindValue(":DESCRITORES", $dados["descritores"]);
        $query->bindValue(":DISCIPLINA", $dados["disciplina"]);
        $query->bindValue(":NOME_PROVA", $dados["nome_prova"]);
        $query->bindValue(":PONTOS_PROVA", $dados["pontos_prova"]);
        $query->bindValue(":QNT_PERGUNTAS", $dados["QNT_perguntas"]);
        $query->bindValue(":DATA_ALUNO", $dados["data_aluno"]);
        $query->bindValue(":ACERTOS", $dados["acertos"]);
        $query->bindValue(":PONTOS_ALUNO", $dados["pontos_aluno"]);
        $query->bindValue(":PNTQ", $dados["pontos_aluno_quebrado"]);
        $query->bindValue(":PERGUNTAS_CERTAS", $dados["perguntas_certas"]);
        $query->bindValue(":DESCRITORES_CERTOS", $dados["descritores_certos"]);
        $query->bindValue(":DESCRITORES_ERRADOS", $dados["descritores_errados"]);
        $query->bindValue(":PERG_ERRADAS", $dados["perguntas_erradas"]);
        $query->bindValue(":PERG_RESP", $dados["perguntas_respostas"]);
        $query->bindValue(":PORC", $dados["porcentagem"]);
        $query->bindValue(":TURNO", $dados["turno"]);
        $query->bindValue(":SER", $dados["serie"]);
        $query->bindValue(":MTD", $dados["metodo"]);
        $query->execute();

        return $query;
    }

   
    public static function GetProvasbyID($id)
    {
        $sql = "SELECT * FROM gabarito_alunos WHERE id_prova = :id";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":id", $id);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function UpdateStatusAluno($ra, $id_prova, $string)
    {
        $sql = "UPDATE gabarito_alunos SET status = :STRING WHERE ra = :RA AND id_prova = :ID_PROVA";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":STRING", $string);
        $query->bindValue(":RA", $ra);
        $query->bindValue(":ID_PROVA", $id_prova);
        $query->execute();
        return $query;
    }

    public static function SetRecAluno($id_prova, $ra)
    {
        $sql = "UPDATE gabarito_alunos SET recuperacao = 'FEZ RECUPERAÇÂO' WHERE id_prova = :ID_PROVA AND ra = :RA";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":RA", $ra);
        $query->bindValue(":ID_PROVA", $id_prova);
        $query->execute();
        return $query;
    }

    public static function UpdateGabaritoAluno($dados)
    {
        $sql = "UPDATE gabarito_alunos SET
            QNT_perguntas = :QNT_PERGUNTAS,
            porcentagem = :PORCENTAGEM,
            acertos = :ACERTOS,
            pontos_aluno = :PONTOS_ALUNO,
            perguntas_respostas = :PERG_RESP,
            perguntas_certas = :PERGUNTAS_CERTAS,
            perguntas_erradas = :PERG_ERRADAS,
            descritores_certos = :DESCRITORES_CERTOS,
            descritores_errados = :DESCRITORES_ERRADOS,
            recuperacao = :RCPR,
            status = :STATUS
            WHERE ra = :RA AND id_prova = :ID_PROVA";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":QNT_PERGUNTAS", $dados["QNT_perguntas"]);
        $query->bindValue(":PORCENTAGEM", $dados["porcentagem"]);
        $query->bindValue(":ACERTOS", $dados["acertos"]);
        $query->bindValue(":PONTOS_ALUNO", $dados["pontos_aluno"]);
        $query->bindValue(":PERG_RESP", $dados["perguntas_respostas"]);
        $query->bindValue(":PERGUNTAS_CERTAS", $dados["perguntas_certas"]);
        $query->bindValue(":PERG_ERRADAS", $dados["perguntas_erradas"]);
        $query->bindValue(":DESCRITORES_CERTOS", $dados["descritores_certos"]);
        $query->bindValue(":DESCRITORES_ERRADOS", $dados["descritores_errados"]);
        $query->bindValue(":STATUS", $dados["status"]);
        $query->bindValue(":RCPR", $dados["recuperacao"]);
        $query->bindValue(":RA", $dados["ra"]);
        $query->bindValue(":ID_PROVA", $dados["id_prova"]);

        $query->execute();

        return $query;
    }

}
