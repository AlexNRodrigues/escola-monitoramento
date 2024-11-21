<?php 

namespace app\models\monitoramento;

use app\config\Database;
use PDO;

class GestorModel{
      
    public static function GetResultadosFiltrados($filtros){
        $query = "SELECT * FROM gabarito_alunos WHERE 1=1";
        
        if($filtros['turma']){
            $query .= " AND turma = :turma";
        }
        if($filtros['turno']){
            $query .= " AND turno = :turno";
        }
        if($filtros['disciplina']){
            $query .= " AND disciplina = :disciplina";
        }
        if($filtros['professor']){
            $query .= " AND nome_professor = :professor";
        }
        if($filtros['serie']){
            $query .= " AND serie = :serie";
        }
        if($filtros['metodo']){
            $query .= " AND metodo = :metodo";
        }

        if($filtros['datas']){
            // $query .= " AND data_aluno >= :data_inicial AND data_aluno <= :data_final";
            $query .= " AND data_aluno BETWEEN :data_inicial AND :data_final";
        }


        $stmt = Database::GetInstance()->prepare($query);
    
        if($filtros['turma']){
            $stmt->bindValue(':turma', $filtros['turma']);
        }
        if($filtros['turno']){
            $stmt->bindValue(':turno', $filtros['turno']);
        }
        if($filtros['disciplina']){
            $stmt->bindValue(':disciplina', $filtros['disciplina']);
        }
        if($filtros['professor']){
            $stmt->bindValue(':professor', $filtros['professor']);
        }
        if($filtros['serie']){
            $stmt->bindValue(':serie', $filtros['serie']);
        }
        if($filtros['metodo']){
            $stmt->bindValue(':metodo', $filtros['metodo']);
        }
        if($filtros['datas']){
            $stmt->bindValue(':data_inicial', $filtros['datas']["data_inicial"]);
            $stmt->bindValue(':data_final', $filtros['datas']["data_final"]);
        }

    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function GetProvasFiltrados($filtros){
        $query = "SELECT * FROM gabarito_professores WHERE 1=1";
        
        if($filtros['disciplina']){
            $query .= " AND disciplina = :disciplina";
        }
        if($filtros['professor']){
            $query .= " AND nome_professor = :professor";
        }
        $stmt = Database::GetInstance()->prepare($query);

        if($filtros['disciplina']){
            $stmt->bindValue(':disciplina', $filtros['disciplina']);
        }
        if($filtros['professor']){
            $stmt->bindValue(':professor', $filtros['professor']);
        }
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function GetFiltro($campo,$valor){
        $sql = "SELECT * FROM gabarito_alunos WHERE $campo = :valor ";
        $query = Database::GetInstance()->prepare($sql);
        $query->bindValue(":valor", $valor);
        $query->execute();
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function GetNomeDisciplinas(){
        $query = "SELECT DISTINCT disciplina FROM gabarito_professores ORDER BY disciplina ASC";
        
        $stmt = Database::GetInstance()->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public static function GetProvas($condicao = null) {
        $query = "SELECT * FROM gabarito_professores";

        $params = [];

        if ($condicao) {
            $query .= " WHERE 1 = 1";

            if (!empty($condicao['disciplina'])) {
                $query .= " AND disciplina = :disciplina";
                $params[':disciplina'] = $condicao['disciplina'];
            }
            if (!empty($condicao['turma'])) {
                $query .= " AND turmas = :turma";
                $params[':turma'] = $condicao['turma'];
            }
            if (!empty($condicao['professor'])) {
                $query .= " AND nome_professor = :professor";
                $params[':professor'] = $condicao['professor'];
            }
            if (!empty($condicao['data_inicio']) && !empty($condicao['data_fim'])) {
                $query .= " AND data_prova BETWEEN :data_inicio AND :data_fim";
                $params[':data_inicio'] = $condicao['data_inicio'];
                $params[':data_fim'] = $condicao['data_fim'];
            }
        }

        $query .= " ORDER BY data_prova DESC";

        $stmt = Database::GetInstance()->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        // $stmt->debugDumpParams();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}