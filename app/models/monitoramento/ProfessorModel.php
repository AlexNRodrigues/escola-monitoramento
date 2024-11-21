<?php

namespace app\models\monitoramento;

use app\config\Database;
use PDO;

class ProfessorModel
{

    public static function verificarLogin($user)
    {
        $sql = "SELECT * FROM professores WHERE numero = :dado";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindvalue(":dado", $user);
        $query->execute();

        if ($query->rowCount() > 0) {
            return $query->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    public static function GetTurmas()
    {
        $sql = "SELECT * FROM turmas";
        $query = Database::GetInstance()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function Inserir_gabarito($dados)
    {
        $sql = "INSERT INTO gabarito_professores
        (nome_professor,nome_prova,turmas,descritores,valor,QNT_perguntas,data_prova,gabarito,disciplina,metodo)
        VALUES (:NPRF,:NPRV,:TR,:DES,:VLR,:QTPR,:DTPRV,:GABARITO,:MT, :MTD)";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindvalue(":NPRF", $dados["nome_prof"]);
        $query->bindvalue(":NPRV", $dados["nome_prova"]);
        $query->bindvalue(":TR", $dados["turmas"]);
        $query->bindvalue(":DES", $dados["descritores"]);
        $query->bindvalue(":VLR", $dados["valor"]);
        $query->bindvalue(":QTPR", $dados["perguntas"]);
        $query->bindvalue(":DTPRV", $dados["data"]);
        $query->bindvalue(":GABARITO", $dados["gabarito"]);
        $query->bindvalue(":MT", $dados["materia"]);
        $query->bindvalue(":MTD", $dados["metodo"]);
        $query->execute();

        return $query;
    }

    public static function alterar_liberado($id, $state)
    {
        $sql = "UPDATE gabarito_professores SET liberado = :STATE WHERE id = :ID";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":STATE", $state);
        $query->bindValue(":ID", $id);
        $query->execute();

        return $query;
    }

    public static function alterar_liberadoRec($id, $state)
    {
        $sql = "UPDATE gabarito_professores_recuperacao SET liberado = :STATE WHERE id = :ID";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":STATE", $state);
        $query->bindValue(":ID", $id);
        $query->execute();

        return $query;
    }

    public static function alterar_liberado_ver($id, $state)
    {
        $sql = "UPDATE gabarito_professores SET liberar_prova = :STATE WHERE id = :ID";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":STATE", $state);
        $query->bindValue(":ID", $id);
        $query->execute();

        return $query;
    }

    public static function alterar_liberado_verRec($id, $state)
    {
        $sql = "UPDATE gabarito_professores_recuperacao SET liberar_prova = :STATE WHERE id = :ID";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":STATE", $state);
        $query->bindValue(":ID", $id);
        $query->execute();

        return $query;
    }

    public static function GetProvabyID($id)
    {
        $sql = "SELECT * FROM gabarito_professores WHERE id = :id";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":id", $id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // public static function GetProvaRecbyIDprova($id)
    // {
    //     $sql = "SELECT * FROM gabarito_professores_recuperacao WHERE id_prova = :id";
    //     $query = Database::GetInstance()->prepare($sql);
    //     $query->bindValue(":id", $id);
    //     $query->execute();
    //     return $query->fetchAll(PDO::FETCH_ASSOC);
    // }

    public static function GetProvaRecbyID($id)
    {
        $sql = "SELECT * FROM gabarito_professores_recuperacao WHERE id = :id";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":id", $id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public static function GetProvaRec()
    {
        $sql = "SELECT * FROM gabarito_professores_recuperacao";
        $query = Database::GetInstance()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function GetProvaPrimeira()
    {
        $sql = "SELECT * FROM gabarito_alunos_primeira_prova";
        $query = Database::GetInstance()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function GetProvaRecAlunos()
    {
        $sql = "SELECT * FROM gabarito_alunos_recuperacao";
        $query = Database::GetInstance()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function GetProvasFeitasbyID($id)
    {
        $sql = "SELECT * FROM gabarito_alunos WHERE id_prova = :id ORDER BY turma ASC";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":id", $id);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function GetProvasRecFeitasbyID($id)
    {
        $sql = "SELECT * FROM gabarito_alunos_recuperacao WHERE id_prova = :id ORDER BY turma ASC";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":id", $id);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function atualizar_gabarito_aluno($dados)
    {
        $sql = "UPDATE gabarito_alunos SET acertos = :AC, porcentagem = :PORC,turma = :TURMA, pontos_aluno = :PN, pontos_aluno_quebrado = :PNTQ, perguntas_certas = :PC,perguntas_respostas = :PER_RESP,  perguntas_erradas = :PE, descritores = :DESCR,descritores_certos = :DC, descritores_errados = :DE, pontos_prova = :PV,metodo = :METODO WHERE id = :ID_ALUNO AND id_prova = :ID_PROVA";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":AC", $dados["acertos"]);
        $query->bindValue(":PORC", $dados["porcentagem"]);
        $query->bindValue(":TURMA", $dados["turma"]);
        $query->bindValue(":PN", $dados["pontos_aluno"]);
        $query->bindValue(":PNTQ", $dados["pontos_aluno_quebrado"]);
        $query->bindValue(":PC", $dados["perguntas_certas"]);
        $query->bindValue(":PER_RESP", $dados["perguntas_respostas"]);
        $query->bindValue(":PE", $dados["perguntas_erradas"]);
        $query->bindValue(":DESCR", $dados["descritores"]);
        $query->bindValue(":DC", $dados["descritores_certos"]);
        $query->bindValue(":DE", $dados["descritores_errados"]);
        $query->bindValue(":PV", $dados["pontos_prova"]);
        $query->bindValue(":METODO", $dados["metodo"]);
        $query->bindValue(":ID_ALUNO", $dados["ID"]);
        $query->bindValue(":ID_PROVA", $dados["ID_prova"]);
        $query->execute();

        return $query;  
    }

    public static function atualizar_gabarito_aluno_primeira($dados)
    {
        $sql = "UPDATE gabarito_alunos_primeira_prova SET acertos = :AC,turma = :TURMA, porcentagem = :PORC, pontos_aluno = :PN,pontos_aluno_quebrado = :PNTQ, perguntas_certas = :PC, perguntas_erradas = :PE, descritores = :DESCR, descritores_certos = :DC, descritores_errados = :DE, pontos_prova = :PV,metodo = :METODO WHERE ra = :RA AND id_prova = :ID_PROVA";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":AC", $dados["acertos"]);
        $query->bindValue(":PORC", $dados["porcentagem"]);
        $query->bindValue(":PN", $dados["pontos_aluno"]);
        $query->bindValue(":PNTQ", $dados["pontos_aluno_quebrado"]);
        $query->bindValue(":PC", $dados["perguntas_certas"]);
        $query->bindValue(":TURMA", $dados["turma"]);
        $query->bindValue(":PE", $dados["perguntas_erradas"]);
        $query->bindValue(":DESCR", $dados["descritores"]);
        $query->bindValue(":DC", $dados["descritores_certos"]);
        $query->bindValue(":DE", $dados["descritores_errados"]);
        $query->bindValue(":PV", $dados["pontos_prova"]);
        $query->bindValue(":RA", $dados["ra"]);
        $query->bindValue(":METODO", $dados["metodo"]);
        $query->bindValue(":ID_PROVA", $dados["ID_prova"]);
        $query->execute();

        return $query;
    }

    public static function atualizar_gabarito_aluno_rec($dados)
    {
        $sql = "UPDATE gabarito_alunos_recuperacao SET acertos = :AC, porcentagem = :PORC, pontos_aluno = :PN,  perguntas_certas = :PC, perguntas_erradas = :PE, descritores_certos = :DC, descritores_errados = :DE, pontos_prova = :PV WHERE ra = :RA AND id_prova = :ID_PROVA";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":AC", $dados["acertos"]);
        $query->bindValue(":PORC", $dados["porcentagem"]);
        $query->bindValue(":PN", $dados["pontos_aluno"]);
        $query->bindValue(":PC", $dados["perguntas_certas"]);
        $query->bindValue(":PE", $dados["perguntas_erradas"]);
        $query->bindValue(":DC", $dados["descritores_certos"]);
        $query->bindValue(":DE", $dados["descritores_errados"]);
        $query->bindValue(":PV", $dados["pontos_prova"]);
        $query->bindValue(":RA", $dados["ra"]);
        $query->bindValue(":ID_PROVA", $dados["ID_prova"]);
        $query->execute();

        return $query;
    }

    public static function atualizar_gabarito_professor($dados)
    {
        $sql = "UPDATE gabarito_professores SET descritores = :DESC, valor = :VALOR, gabarito = :GAB, metodo = :MET WHERE id = :ID";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":DESC", $dados["descritores"]);
        $query->bindValue(":VALOR", $dados["valor"]);
        $query->bindValue(":GAB", $dados["gabarito"]);
        $query->bindValue(":MET", $dados["metodo"]);
        $query->bindValue(":ID", $dados["ID_prova"]);
        $query->execute();

        return $query;
    }

    public static function atualizar_gabarito_professorRec($dados)
    {
        $sql = "UPDATE gabarito_professores_recuperacao SET descritores = :DESC, valor = :VALOR, gabarito = :GAB WHERE id = :ID";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":DESC", $dados["descritores"]);
        $query->bindValue(":VALOR", $dados["valor"]);
        $query->bindValue(":GAB", $dados["gabarito"]);
        $query->bindValue(":ID", $dados["ID_prova"]);
        $query->execute();

        return $query;
    }

    public static function inserir_gabarito_recuperacao($dados)
    {
        $sql = "INSERT INTO gabarito_professores_recuperacao
        (id_prova, alunos, nome_professor, nome_prova, descritores, disciplina, valor, QNT_perguntas, data_prova_rec, gabarito)
        VALUES (:IDPROVA, :ALUNOS, :NOMEPROF, :NOMEPROVA, :DESCRITORES, :DISCIPLINA, :VALOR, :QTPERGUNTAS, :DATAPROVAREC, :GABARITO)";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":IDPROVA", $dados["id_prova"]);
        $query->bindValue(":ALUNOS", $dados["alunos"]);
        $query->bindValue(":NOMEPROF", $dados["nome_prof"]);
        $query->bindValue(":NOMEPROVA", $dados["nome_prova"]);
        $query->bindValue(":DESCRITORES", $dados["descritores"]);
        $query->bindValue(":DISCIPLINA", $dados["materia"]);
        $query->bindValue(":VALOR", $dados["valor"]);
        $query->bindValue(":QTPERGUNTAS", $dados["perguntas"]);
        $query->bindValue(":DATAPROVAREC", $dados["data"]);
        $query->bindValue(":GABARITO", $dados["gabarito"]);
        $query->execute();

        return $query;
    }

    public static function ExcluirProvaAluno($id)
    {
        $sql = "DELETE FROM gabarito_alunos WHERE id_prova = :id";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":id", $id);
        $query->execute();

        return $query;
    }

    public static function ExcluirProvaProf($id)
    {
        $sql = "DELETE FROM gabarito_professores WHERE id = :id";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":id", $id);
        $query->execute();

        return $query;
    }

    public static function ExcluirProvaRecAluno($id)
    {
        $sql = "DELETE FROM gabarito_alunos_recuperacao WHERE id_prova = :id";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":id", $id);
        $query->execute();

        return $query;
    }

    public static function ExcluirProvaRecProf($id)
    {
        $sql = "DELETE FROM gabarito_professores_recuperacao WHERE id = :id";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":id", $id);
        $query->execute();

        return $query;
    }

    public static function getAlunosByTurma($turma) {

        $sql = "SELECT ra, nome, turma FROM alunos WHERE turma LIKE :turma ORDER BY nome ASC";

        $turmaParam = '%' . $turma . '%';

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":turma", $turmaParam);
        $query->execute();
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAlunosByProva($prova) {
        $sql = 'SELECT ra, aluno FROM gabarito_alunos WHERE id_prova = :prova';

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":prova", $prova);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getProvaByRa($prova, $ra)
    {
        $sql = "SELECT * FROM gabarito_alunos WHERE id_prova = :id AND ra = :ra";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":id", $prova);
        $query->bindValue(":ra", $ra);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function inserir_gabarito_aluno($dados)
    {
        $sql = "INSERT INTO gabarito_alunos
            (aluno, ra, turma, id_prova, nome_professor, descritores, disciplina, nome_prova, pontos_prova, QNT_perguntas, data_aluno, acertos, pontos_aluno, pontos_aluno_quebrado, perguntas_certas, descritores_certos, descritores_errados, perguntas_erradas, perguntas_respostas, porcentagem, turno, serie, status)
            VALUES (:ALUNO, :RA, :TURMA, :ID_PROVA, :NOME_PROFESSOR, :DESCRITORES, :DISCIPLINA, :NOME_PROVA, :PONTOS_PROVA, :QNT_PERGUNTAS, :DATA_ALUNO, :ACERTOS, :PONTOS_ALUNO, :PNTQ, :PERGUNTAS_CERTAS, :DESCRITORES_CERTOS, :DESCRITORES_ERRADOS, :PERG_ERRADAS, :PERG_RESP, :PORC, :TURNO, :SER, :STATUS)";

        $query = Database::GetInstance()->prepare($sql);

        $params = [
            ":ALUNO" => $dados["aluno"],
            ":RA" => $dados["ra"],
            ":TURMA" => $dados["turma"],
            ":TURNO" => $dados["turno"],
            ":ID_PROVA" => $dados["id_prova"],
            ":SER" => $dados["serie"],
            ":NOME_PROFESSOR" => $dados["nome_professor"],
            ":DESCRITORES" => $dados["descritores"],
            ":DISCIPLINA" => $dados["disciplina"],
            ":NOME_PROVA" => $dados["nome_prova"],
            ":PONTOS_PROVA" => $dados["pontos_prova"],
            ":QNT_PERGUNTAS" => $dados["QNT_perguntas"],
            ":DATA_ALUNO" => $dados["data_aluno"],
            ":ACERTOS" => $dados["acertos"],
            ":PORC" => $dados["porcentagem"],
            ":PONTOS_ALUNO" => $dados["pontos_aluno"],
            ":PNTQ" => $dados["pontos_aluno_quebrado"],
            ":PERG_RESP" => $dados["perguntas_respostas"],
            ":PERGUNTAS_CERTAS" => $dados["perguntas_certas"],
            ":PERG_ERRADAS" => $dados["perguntas_erradas"],
            ":DESCRITORES_CERTOS" => $dados["descritores_certos"],
            ":DESCRITORES_ERRADOS" => $dados["descritores_errados"],
            ":STATUS" => $dados["status"],
        ];

        foreach ($params as $key => $value) {
            $query->bindValue($key, $value);
        }

        return $query->execute();
    }
}
