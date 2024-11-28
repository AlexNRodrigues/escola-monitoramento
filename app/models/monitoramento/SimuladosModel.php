<?php

namespace app\models\monitoramento;

use app\config\Database;
use PDO;

class SimuladosModel
{
    public static function getSimulados($filtro)
    {
        $sql = "SELECT 
                    s.id,
                    s.nome,
                    s.area_conhecimento,
                    s.data,
                    s.orientacoes,
                    t.nome AS turma, 
                    sp.gabarito_professor_id,
                    gp.nome_prova,
                    gp.disciplina,
                    gp.QNT_perguntas,
                    gp.valor
                FROM simulados s
                LEFT JOIN simulados_prova sp ON sp.simulado_id = s.id
                LEFT JOIN gabarito_professores gp ON gp.id = sp.gabarito_professor_id
                LEFT JOIN turmas t ON t.id = s.turma_id
                WHERE 1=1
        ";

        $params = [];
        if (!empty($filtro['nome'])) {
            $sql .= " AND s.nome LIKE :NOME";
            $params[':NOME'] = '%' . $filtro['nome'] . '%';
        }
        if (!empty($filtro['disciplina'])) {
            $sql .= " AND gp.disciplina LIKE :disciplina";
            $params[':disciplina'] = '%' . $filtro['disciplina'] . '%';
        }
        if (!empty($filtro['turma'])) {
            $sql .= " AND t.nome LIKE :turma";
            $params[':turma'] = '%' . $filtro['turma'] . '%';
        }
        if (!empty($filtro['prova_simulado'])) {
            $sql .= " AND s.id = (SELECT simulado_id FROM simulados_prova WHERE gabarito_professor_id = {$filtro['prova_simulado']})";
        }

        $query = Database::GetInstance()->prepare($sql);

        foreach ($params as $key => $value) {
            $query->bindValue($key, $value);
        }
    
       $query->execute();
       $results = $query->fetchAll(PDO::FETCH_ASSOC);

        $simulados = [];
        foreach ($results as $row) {
            $simuladoId = $row['id'];

            $prova = [
                $row['gabarito_professor_id'] => [
                    'id'            => $row['gabarito_professor_id'],
                    'nome_prova'    => $row['nome_prova'],
                    'disciplina'    => $row['disciplina'],
                    'QNT_perguntas' => $row['QNT_perguntas'],
                    'valor'         => $row['valor'],
                ]
            ];

           if (!isset($simulados[$simuladoId])) {
               $simulados[$simuladoId] = [
                   'id' => $row['id'],
                   'nome' => $row['nome'],
                   'turma' => $row['turma'],
                   'area_conhecimento' => $row['area_conhecimento'],
                   'gabarito_professor' => $prova,
               ];
           } else {
               $simulados[$simuladoId]['gabarito_professor'] += $prova;
           }
        }

        return $simulados;
    }

    public static function getSimulado($id)
    {

        $sql = "SELECT 
                    s.id,
                    s.turma_id,
                    s.nome,
                    s.area_conhecimento,
                    s.orientacoes
                FROM simulados s
                WHERE s.id = :ID
        ";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":ID", $id);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public static function getProvasSimulado($simulado_id = null)
    {
        $sql = "SELECT * 
                FROM gabarito_professores gp 
                LEFT JOIN simulados_prova sp ON sp.gabarito_professor_id = gp.id
                WHERE 1=1
        ";

        $params = [];
        if($simulado_id) {
            $sql .= " AND sp.simulado_id = :ID";
            $params[':ID'] = $simulado_id;
        }

        $sql .= ' ORDER BY sp.ordem ASC';

        $query = Database::GetInstance()->prepare($sql);

        foreach ($params as $key => $value) {
            $query->bindValue($key, $value);
        }

        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getProvas()
    {
        $sql = "SELECT t1.*
                FROM gabarito_professores t1
                LEFT JOIN simulados_prova t2 ON t1.id = t2.gabarito_professor_id;
        ";

        $query = Database::GetInstance()->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getProvasDisponiveis()
    {
        $sql = "SELECT t1.*
                FROM gabarito_professores t1
                LEFT JOIN simulados_prova t2 ON t1.id = t2.gabarito_professor_id
                WHERE t2.gabarito_professor_id IS NULL;
        ";

        $query = Database::GetInstance()->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addSimulado($dados)
    {
        $sql = "INSERT INTO simulados 
        ( turma_id, nome, area_conhecimento, data, orientacoes ) 
        VALUE (:TURMA_ID, :NOME, :AREA_CONHECIMENTO, :DATA, :ORIENTACOES)";

        $query = Database::GetInstance()->prepare($sql);
        $params = [
            ':TURMA_ID' => $dados["turma_id"],
            ':NOME' => $dados["nome"],
            ':AREA_CONHECIMENTO' => $dados["area_conhecimento"],
            ':DATA' => $dados["data"],
            ':ORIENTACOES' => $dados["orientacoes"]
        ];

        foreach ($params as $key => $value) {
            $query->bindValue($key, $value);
        }
        $query->execute();
        $simuladoId = Database::GetInstance()->lastInsertId();
        
        $ordem_selecao = explode(',', $dados['ordem_selecao']);
        
        foreach ($ordem_selecao as $ordem => $gabaritoId) {
            self::addSimuladoProva($simuladoId, $gabaritoId, $ordem);
        }

        return $simuladoId;
    }

    public static function addSimuladoProva($simuladoId, $gabaritoId, $ordem = 0)
    {
        $sql = "INSERT INTO simulados_prova (simulado_id, gabarito_professor_id, ordem) VALUES (:SIMULADO_ID, :GABARITO_ID, :ORDEM)";
        
        $query = Database::GetInstance()->prepare($sql);
        $query->bindParam(":SIMULADO_ID", $simuladoId);
        $query->bindParam(":GABARITO_ID", $gabaritoId);
        $query->bindParam(":ORDEM", $ordem);
        
        $query->execute();
    }

    public static function getSimuladoProva($id)
    {
        $sql = "SELECT * FROM simulados_prova 
                LEFT JOIN simulados s ON s.id = simulados_prova.simulado_id
                WHERE gabarito_professor_id = :GABARITO
        ";

        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(':GABARITO', $id);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateSimulado($dados, $id)
    {
        $db = Database::GetInstance();
        $sql = "UPDATE simulados 
                SET turma_id = :TURMA_ID, 
                    nome = :NOME, 
                    area_conhecimento = :AREA_CONHECIMENTO,
                    orientacoes = :ORIENTACOES 
                WHERE id = :ID"
        ;

        $query = $db->prepare($sql);
        $params = [
            ':TURMA_ID' => $dados["turma_id"],
            ':NOME' => $dados["nome"],
            ':AREA_CONHECIMENTO' => $dados["area_conhecimento"],
            ':ORIENTACOES' => $dados["orientacoes"],
            ':ID' => $dados["id"]
        ];

        foreach ($params as $key => $value) {
            $query->bindValue($key, $value);
        }
        $query->execute();

        $simuladoId = $dados["id"];

        // Excluir as prova associada ao simulado
        $stmt = $db->prepare("DELETE FROM simulados_prova WHERE simulado_id = :SIMULADO");
        $stmt->bindParam(':SIMULADO', $simuladoId, PDO::PARAM_INT);

        if($stmt->execute()) {
            $ordem_selecao = explode(',', $dados['ordem_selecao']);

            foreach ($ordem_selecao as $ordem => $gabaritoId) {
                self::addSimuladoProva($simuladoId, $gabaritoId, $ordem);
            }
        }

        return $simuladoId;
    }

    public static function excluirSimulado($id)
    {
        $db = Database::GetInstance();

        try {
            
            // Iniciar uma transação
            $db->beginTransaction();

            $query = $db->prepare("SELECT id FROM simulados WHERE id = :ID");
            $query->bindValue(":ID", $id);
            $query->execute();
    
            $simulado = $query->fetch(PDO::FETCH_ASSOC)["id"];
       
            if ($simulado) {

                // Excluir as prova associada ao simulado
                $stmt = $db->prepare("DELETE FROM simulados_prova WHERE simulado_id = :SIMULADO");
                $stmt->bindParam(':SIMULADO', $simulado, PDO::PARAM_INT);
                $stmt->execute();

                // Excluir o simulado da tabela de simulados
                $stmt = $db->prepare("DELETE FROM simulados WHERE id = :ID");
                $stmt->bindParam(':ID', $simulado, PDO::PARAM_INT);
                $stmt->execute();

                // Confirmar a transação
                $db->commit();

                return true;
            } 
            
            return false;
            
        } catch (\Throwable $th) {
            // Em caso de erro, desfaz a transação
            $db->rollBack();
            return false;
        }
    }
}