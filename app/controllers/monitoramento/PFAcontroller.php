<?php

namespace app\controllers\monitoramento;

use app\controllers\monitoramento\MainController;
use app\models\monitoramento\ADModel;
use app\models\monitoramento\AlunoModel;
use app\models\monitoramento\ProfessorModel;
use app\models\monitoramento\GestorModel;
use DateTime;

class PFAcontroller
{
    public static function validar_login_pfa(){
        $user = $_POST["user"];
        $senha = $_POST["senha"];

        $info = ADModel::verificarLoginPFA($user);
        if ($info != false) {
            if ($info["senha"] == $senha) {
                $_SESSION["PFA"] = true;
                $_SESSION["nome_PFA"] = $info["nome"];
                $_SESSION["disciplina_PFA"] = $info["disciplina"];
                $_SESSION["turno_PFA"] = $info["turno"];
                header("location: pfa_home");
            } else {
                $_SESSION["PopUp_PFA_NaoENC"] = true;
                header("location: login_pfa");
                exit;
            }
        } else {
            $_SESSION["PopUp_PRF_NaoENC"] = true;
            header("location: login_professor");
            exit;
        }

    }

    public static function pfa_home(){

        if($_SESSION["PFA"]){
            $filtros = GestorController::obterFiltros();

            $filtros["turno"] = $_SESSION["turno_PFA"];
            $filtros["disciplina"] = $_SESSION["disciplina_PFA"];

            if(isset($_POST["geral"])){
                $filtros["turma"] = NULL;
                $filtros["professor"] = NULL;
                $filtros["periodo"] = NULL;
            }
 
            $resultados = GestorModel::GetResultadosFiltrados($filtros);

            $statusResultados = $resultados == NULL ? False : True;

            $turmas = [];
 
            $alunos_por_turma = [];
            foreach ($resultados as $prova) {
                $turma_prova = $prova["turma"];
                if (!isset($alunos_por_turma[$turma_prova])) {
                    $alunos_por_turma[$turma_prova] = array();
                }
                $alunos_por_turma[$turma_prova][] = $prova;
            }

            $statusFiltro = False;
            foreach($filtros as $filtro => $valor){
                if($filtro != "turno" && $filtro != "disciplina"){
                    if($valor != NULL){
                        $statusFiltro = True;
                    }
                }
            }

            // MainController::pre($descritores);

            $dados = [
                "turmas" => self::pegarTurmas(),
                "periodos" => ADModel::GetPeriodos(),
                "filtros"   => $filtros,
                "professores" => self::pegarProf(),
                "statusFiltro"  => $statusFiltro,
                "statusResultados"  => $statusResultados,
                "graficosTurmas"    => GestorController::DadosGeralTurmas($resultados),
                "proeficiencia" => MainController::gerarGraficoColunas(GestorController::GetProeficiencia($resultados)),
                "grafico60" => MainController::gerarGraficoRosca(GestorController::calcularPorcentagemAcima60($resultados)),
                "descritores" => self::ProcessarDescritores($alunos_por_turma),
            ];
    
            MainController::Templates("public/views/pfa/home.php","PFA",$dados);

        }else{
            header("location: adm");
        }
    }

    public static function ProcessarDescritores($dados){
            $media_descritores_geral = [];
            $acertos_por_descritor = [];

        foreach ($dados as $turma => $alunos) {
            $acertos_por_descritor[$turma] = [];

            foreach ($alunos as $aluno) {
                $descritores_prova = explode(';', $aluno['descritores']);
                $descritores_certos = explode(';', $aluno['descritores_certos']);

                foreach ($descritores_prova as $descritor) {
                    if (!isset($acertos_por_descritor[$turma][$descritor])) {
                        $acertos_por_descritor[$turma][$descritor] = 0;
                    }
                }

                foreach ($descritores_certos as $descritor) {
                    if (!empty($descritor)) {
                        $acertos_por_descritor[$turma][$descritor]++;
                    }
                }
            }
        }

        $percentual_por_descritor = [];

        foreach ($acertos_por_descritor as $turma => $acertos) {
            $total_alunos = count($dados[$turma]);

            foreach ($acertos as $descritor => $quantidade_acertos) {
                $percentual = ($quantidade_acertos / $total_alunos) * 100;
                $percentual_por_descritor[$turma][$descritor] = $percentual;
            }
        }

        $percentual_descritores_turmas = [];

        foreach ($percentual_por_descritor as $turma => $descritores) {
            $descritores_modificados = [];
            $percentuais_sem_questao = [];

            foreach ($descritores as $descritor => $percentual) {
                if (!empty($descritor)) {
                    $nome_descritor = explode(',', $descritor)[1];

                    if (!isset($percentuais_sem_questao[$nome_descritor])) {
                        $percentuais_sem_questao[$nome_descritor] = [$percentual];
                    } else {
                        $percentuais_sem_questao[$nome_descritor][] = $percentual;
                    }
                }
            }

            foreach ($percentuais_sem_questao as $nome_descritor => $percentuais) {
                $media_percentual = array_sum($percentuais) / count($percentuais);
                $descritores_modificados[$nome_descritor] = $media_percentual;
            }

            $percentual_descritores_turmas[$turma] = $descritores_modificados;
        }

        foreach ($percentual_descritores_turmas as $turma) {
            foreach ($turma as $descritor => $percentual) {
                if (!isset($media_descritores_geral[$descritor])) {
                    $media_descritores_geral[$descritor] = 0;
                }
                $media_descritores_geral[$descritor] += $percentual / count($percentual_descritores_turmas);
            }
        }
        arsort($media_descritores_geral);

        foreach ($media_descritores_geral as $descritor => $percentual) {
            $media_descritores_geral[$descritor] = MainController::gerarGraficoRosca(number_format($percentual, 1));
        }

        return $media_descritores_geral;
    }

    public static function pegarTurmas(){
        $filtros = GestorController::obterFiltros();

        $filtros["turno"] = $_SESSION["turno_PFA"];
        $filtros["disciplina"] = $_SESSION["disciplina_PFA"];
        $filtros["turma"] = NULL;
        $filtros["professor"] = NULL;
        $filtros["periodo"] = NULL;

        $resultados = GestorModel::GetResultadosFiltrados($filtros);

        $turmas = [];

        foreach ($resultados as $resultado) {
            if (isset($resultado['turma']) && !in_array($resultado['turma'], $turmas)) {
                $turmas[] = $resultado['turma'];
            }
        }

        sort($turmas);

        return $turmas;
    }

    public static function pegarProf(){
        $filtros = GestorController::obterFiltros();

        $filtros["turno"] = $_SESSION["turno_PFA"];
        $filtros["disciplina"] = $_SESSION["disciplina_PFA"];
        $filtros["turma"] = NULL;
        $filtros["professor"] = NULL;
        $filtros["periodo"] = NULL;

        $resultados = GestorModel::GetResultadosFiltrados($filtros);

        $professores = [];

        foreach($resultados as $prova){
            if(!isset($professores[$prova["nome_professor"]])){
                $professores[$prova["nome_professor"]] = $prova["nome_professor"];
            }
        }
        sort($professores);

        return $professores;
    }

    public static function pfa_provas(){
        if ($_SESSION["PFA"]) {

            $provas_alunos = AlunoModel::GetProvasFinalizadas();

            $filtros = GestorController::obterFiltros();

            $filtros["turno"] = $_SESSION["turno_PFA"];
            $filtros["disciplina"] = $_SESSION["disciplina_PFA"];

            if(isset($_POST["geral"])){
                $filtros["professor"] = NULL;
                $filtros["periodo"] = NULL;
            }
 
            if ($filtros["professor"] == null && $filtros["periodo"] == null) {
                $geral = true;
            } else {
                $geral = false;
            }

            $resultados = GestorModel::GetResultadosFiltrados($filtros);

            $professores = self::pegarProf();

            $provas_professores = GestorModel::GetProvasFiltrados($filtros);

            $provas = [];

            $provas_IDs = [];
            foreach($resultados as $result){
                if (isset($result['id_prova']) && !in_array($result['id_prova'], $provas_IDs)) {
                    $provas_IDs[] = $result['id_prova'];
                }
            }

            foreach($provas_professores as $provaProf){
                foreach($professores as $prof){
                    if($prof == $provaProf["nome_professor"] && in_array($provaProf["id"],$provas_IDs)){
                        $provas[] = $provaProf;
                    }
                }
            }

            if ($provas_professores != null) {
  
                usort($provas, function ($a, $b) {
                    return strtotime($b['data_prova']) - strtotime($a['data_prova']);
                });

                $status = true;

            } else {
                $status = false;
            }

            // echo "<pre>";
            // print_r($provas);
            // echo "</pre>";

            $dados = [
                "provas" => $provas,
                "filtros" => $filtros,
                "periodos" => ADModel::GetPeriodos(),
                "disciplinas" => ADModel::GetDisciplinas(),
                "professores" => $professores,
                "provas_alunos" => $provas_alunos,
                "status" => $status,
                "geral" => $geral,
            ];

            MainController::Templates("public/views/pfa/provas.php", "PFA", $dados);

        } else {
            header("location: adm");
        }
    }

    public static function pfa_prova(){
        if($_SESSION["PFA"]){    

            if(!isset($_POST["id-prova"])){
                header("location: pfa_provas");
            }

            $id_prova = $_POST["id-prova"];

            $_SESSION["PAG_VOLTAR"] = "relatorio_professor";
            $provas = AlunoModel::GetProvasFinalizadas();
            // $provasRec = ProfessorModel::GetProvaRecAlunos();
            $provasPrimeira = ProfessorModel::GetProvaPrimeira();
            $provas_professores = AlunoModel::GetProvas();
            $dados_turmas = [];
            $filtro_turmas = false;
            $status_desc = false;

            foreach ($provas_professores as $professor) {
                if ($professor["id"] == $id_prova) {
                    $turmas = explode(",", $professor["turmas"]);
                    $nome_prova = $professor["nome_prova"];
                    if ($professor["descritores"] != null) {
                        $descritores = explode(";", $professor["descritores"]);
                        $status_desc = true;
                    } else {
                        $status_desc = false;
                    }
                }
            }

            $alunos_por_turma = array();
            foreach ($provas as $prova) {
                if ($prova["id_prova"] == $id_prova) {
                    $turma_prova = $prova["turma"];
                    if (!isset($alunos_por_turma[$turma_prova])) {
                        $alunos_por_turma[$turma_prova] = array();
                    }
                    $alunos_por_turma[$turma_prova][] = $prova;
                }
            }

            // $alunos_por_turma_rec = array();
            // foreach ($provasRec as $prova) {
            //     if ($prova["id_prova"] == $id_prova) {
            //         $turma_prova = $prova["turma"];
            //         if (!isset($alunos_por_turma_rec[$turma_prova])) {
            //             $alunos_por_turma_rec[$turma_prova] = array();
            //         }
            //         $alunos_por_turma_rec[$turma_prova][] = $prova;
            //     }
            // }

            $alunos_por_turma_primeira = array();
            foreach ($provasPrimeira as $prova) {
                if ($prova["id_prova"] == $id_prova) {
                    $turma_prova = $prova["turma"];
                    if (!isset($alunos_por_turma_primeira[$turma_prova])) {
                        $alunos_por_turma_primeira[$turma_prova] = array();
                    }
                    $alunos_por_turma_primeira[$turma_prova][] = $prova;
                }
            }

            $total_pontos_geral = 0;
            $total_alunos_geral = 0;
            $total_acertos_geral = 0;
            $total_acima_60 = 0;
            $total_alunos = 0;

            foreach ($alunos_por_turma as $turma) {
                $pontos = 0;
                $alunos = 0;
                $acertos = 0;
                $alunos_acima_60 = 0;
                foreach ($turma as $aluno) {
                    $pontos += $aluno["pontos_aluno"];
                    $acertos += $aluno["acertos"];
                    $alunos++;
                    $turma_nome = $aluno["turma"];
                    $pontos_prova = $aluno["pontos_prova"];
                    $acertosTotal = $aluno["QNT_perguntas"];
                    $porcentagem_aluno = ($aluno["acertos"] / $aluno["QNT_perguntas"]) * 100;
                    if ($aluno["descritores"] == null) {
                        $descriotores_sn = false;
                    } else {
                        $descriotores_sn = true;
                    }
                    if ($porcentagem_aluno >= 60) {
                        $alunos_acima_60++;
                        $total_acima_60++;
                    }
                }

                $porcentagem_acima_60 = number_format(($alunos_acima_60 / $alunos) * 100, 1);

                if($aluno["metodo"] == "prova"){ 
                    $porcentagemm = number_format((($pontos / $alunos) / $pontos_prova) * 100, 0);
                    $graficor = MainController::gerarGraficoRosca(number_format((($pontos / $alunos) / $pontos_prova) * 100, 1));
                }else{
                    $graficor = MainController::gerarGraficoRosca(number_format((($acertos / $alunos) / $acertosTotal) * 100, 1));
                    $porcentagemm = ($aluno["acertos"] / $aluno["QNT_perguntas"]) * 100;
                }

                $dados_turmas[$turma_nome] = [
                    "total_pontos_turma" => $pontos,
                    "alunos" => $alunos,
                    "pontos_prova" => $pontos_prova,
                    "porcentagem" => $porcentagemm,
                    "porcentagem_acima_60" => $porcentagem_acima_60,
                    "turma_nome" => $turma_nome,
                    "grafico" => $graficor,
                ];

                $total_pontos_geral += $pontos;
                $total_acertos_geral += $acertos;
                $total_alunos_geral += $alunos;
                $total_alunos += $alunos;
            }

            $media_geral_porcentagem = number_format((($total_acertos_geral / $total_alunos_geral) / $acertosTotal) * 100, 2);
            // $media_geral_porcentagem = number_format((($total_pontos_geral / $total_alunos_geral) / $pontos_prova) * 100, 2);

            $porcentagem_geral_acima_60 = number_format(($total_acima_60 / $total_alunos) * 100, 1);

            $media_descritores_geral = [];

            if ($status_desc == true) {
                $acertos_por_descritor = [];

                foreach ($alunos_por_turma as $turma => $alunos) {
                    $acertos_por_descritor[$turma] = [];

                    foreach ($alunos as $aluno) {
                        $descritores_prova = explode(';', $aluno['descritores']);
                        $descritores_certos = explode(';', $aluno['descritores_certos']);

                        foreach ($descritores_prova as $descritor) {
                            if (!isset($acertos_por_descritor[$turma][$descritor])) {
                                $acertos_por_descritor[$turma][$descritor] = 0;
                            }
                        }

                        foreach ($descritores_certos as $descritor) {
                            if (!empty($descritor)) {
                                $acertos_por_descritor[$turma][$descritor]++;
                            }
                        }
                    }
                }

                $percentual_por_descritor = [];

                foreach ($acertos_por_descritor as $turma => $acertos) {
                    $total_alunos = count($alunos_por_turma[$turma]);

                    foreach ($acertos as $descritor => $quantidade_acertos) {
                        $percentual = ($quantidade_acertos / $total_alunos) * 100;
                        $percentual_por_descritor[$turma][$descritor] = $percentual;
                    }
                }

                $percentual_descritores_turmas = [];

                foreach ($percentual_por_descritor as $turma => $descritores) {
                    $descritores_modificados = [];
                    $percentuais_sem_questao = [];

                    foreach ($descritores as $descritor => $percentual) {
                        if (!empty($descritor)) {
                            $nome_descritor = explode(',', $descritor)[1];

                            if (!isset($percentuais_sem_questao[$nome_descritor])) {
                                $percentuais_sem_questao[$nome_descritor] = [$percentual];
                            } else {
                                $percentuais_sem_questao[$nome_descritor][] = $percentual;
                            }
                        }
                    }

                    foreach ($percentuais_sem_questao as $nome_descritor => $percentuais) {
                        $media_percentual = array_sum($percentuais) / count($percentuais);
                        $descritores_modificados[$nome_descritor] = $media_percentual;
                    }

                    $percentual_descritores_turmas[$turma] = $descritores_modificados;
                }

                foreach ($percentual_descritores_turmas as $turma) {
                    foreach ($turma as $descritor => $percentual) {
                        if (!isset($media_descritores_geral[$descritor])) {
                            $media_descritores_geral[$descritor] = 0;
                        }
                        $media_descritores_geral[$descritor] += $percentual / count($percentual_descritores_turmas);
                    }
                }

                foreach ($media_descritores_geral as $descritor => $percentual) {
                    $media_descritores_geral[$descritor] = MainController::gerarGraficoRosca(number_format($percentual, 1));
                }
            }

            // print_r($acertos_por_descritor);
            // print_r($percentual_por_descritor);
            // print_r($percentual_descritores_turmas);
            // print_r($media_descritores_geral);

            // MainController::pre($acertos_por_descritor);
            // MainController::pre($percentual_por_descritor);
            // MainController::pre($percentual_descritores_turmas);
            // MainController::pre($media_descritores_geral);

            $contador_alunos = 0;

            $medida_geral = [
                "Abaixo do Básico" => 0,
                "Básico" => 0,
                "Médio" => 0,
                "Avançado" => 0,
            ];

            $porcentagem_alunos_turma = [];
            $contador_turma = 0;

            foreach ($alunos_por_turma as $turma) {
                $medida_turma = [
                    "Abaixo do Básico" => 0,
                    "Básico" => 0,
                    "Médio" => 0,
                    "Avançado" => 0,
                ];

                $contador_alunos_turma = 0;
                foreach ($turma as $aluno) {
                    $percentual = ($aluno["acertos"] / $aluno["QNT_perguntas"]) * 100;
                    if ($percentual <= 25) {
                        $medida_turma["Abaixo do Básico"] += 1;
                    } else if ($percentual <= 50) {
                        $medida_turma["Básico"] += 1;
                    } else if ($percentual <= 75) {
                        $medida_turma["Médio"] += 1;
                    } else {
                        $medida_turma["Avançado"] += 1;
                    }
                    $contador_alunos_turma++;
                    $nome = $aluno["turma"];
                }

                $porcentagem_turma = [];
                foreach ($medida_turma as $categoria => $quantidade) {
                    $porcentagem_turma[$categoria] = number_format(($quantidade / $contador_alunos_turma) * 100, 0);
                }

                $porcentagem_alunos_turma[$nome] = $porcentagem_turma;

                foreach ($medida_turma as $categoria => $quantidade) {
                    $medida_geral[$categoria] += $quantidade;
                }

                $contador_alunos += $contador_alunos_turma;
            }

            $porcentagem_alunos = [];
            foreach ($medida_geral as $categoria => $quantidade) {
                $porcentagem_alunos[$categoria] = number_format(($quantidade / $contador_alunos) * 100, 0);
            }
            $dados_turma = null;

            if (isset($_POST["filtrar"])) {
                $turma = $_POST["turma-filtros"];
                if ($turma != "geral") {
                    $filtro_turmas = true;

                    $grafico_descritores_turma = [];
                    if ($status_desc) {
                        foreach ($percentual_descritores_turmas[$turma] as $descritor => $percentual) {
                            $grafico_descritores_turma[$descritor] = MainController::gerarGraficoRosca(number_format($percentual, 1));
                        }
                    }

                    $dados_turma = [
                        "nome" => $turma,
                        "grafico_coluna" => MainController::gerarGraficoColunas($porcentagem_alunos_turma[$turma]),
                        "descritores" => $grafico_descritores_turma,
                        "percentual_turma" => MainController::gerarGraficoRosca($dados_turmas[$turma]["porcentagem"]),
                        "percentual_turma_60" => MainController::gerarGraficoRosca($dados_turmas[$turma]["porcentagem_acima_60"]),
                    ];
                } else {
                    $filtro_turmas = false;
                }

            }

            $turmass = array_column($dados_turmas, 'turma_nome');

            array_multisort($turmass, SORT_ASC, $dados_turmas);

            $provas_tudo = [];

            foreach ($provas as $prova) {
                if ($prova["id_prova"] == $id_prova) {

                    $prova["NotaP"] = "INDEFINIDO";
                    foreach ($provasPrimeira as $pm) {
                        if ($pm["id_prova"] == $prova["id_prova"]) {
                            if ($pm["ra"] == $prova["ra"]) {
                                $prova["NotaP"] = $pm["pontos_aluno"];
                            }
                        }
                    }

                    // if ($prova["recuperacao"] != null) {
                    //     foreach ($provasRec as $pr) {
                    //         if ($pr["id_prova"] == $prova["id_prova"]) {
                    //             if ($pr["ra"] == $prova["ra"]) {
                    //                 $prova["notaRec"] = $pr["pontos_aluno"];
                    //             }
                    //         }
                    //     }
                    // } else {
                    //     $prova["notaRec"] = "INDEFINIDO";
                    // }
                    $prova["notaRec"] = "INDEFINIDO";

                    $provas_tudo[] = $prova;
                }
            }

 
            $respostas_por_aluno = [];

            if (isset($_POST["filtrar"])) {
                $turma = $_POST["turma-filtros"];
                if ($turma != "geral") {
                    $provas_filtro = array_filter($provas_tudo, function ($prova) use ($turma) {
                        return $prova["turma"] == $turma;
                    });
                    $provas_tudo = $provas_filtro;
                }
            }

            foreach ($provas_tudo as $prova) {
                if ($prova["id_prova"] == $id_prova) {
                    $aluno = $prova["aluno"];
                    if (!isset($respostas_por_aluno[$aluno])) {
                        $respostas_por_aluno[$aluno] = [];
                    }

                    $perguntas_respostas = explode(';', $prova["perguntas_respostas"]);
                    foreach ($perguntas_respostas as $pergunta_resposta) {
                        list($questao, $resposta) = explode(',', $pergunta_resposta);
                        $respostas_por_aluno[$aluno][$questao] = in_array($pergunta_resposta, explode(';', $prova["perguntas_certas"])) ? "ACERTOU" : "ERROU";
                    }
                }
            }

            ksort($respostas_por_aluno);

            $descritores_por_aluno_primeira = $status_desc ? ProfessorController::calcular_descritores_por_aluno($alunos_por_turma_primeira) : null;

            // MainController::pre($descritores_por_aluno_primeira);

            // $descritores_por_aluno_rec = $status_desc ? ProfessorController::calcular_descritores_por_aluno($alunos_por_turma_rec) : null;
            $descritores_por_aluno_rec =  null;

            $alunosTurma = AlunoModel::GetAlunos();

            if (isset($_POST["filtrar"])) {
                $turma = $_POST["turma-filtros"];
                $alunos_prova = [];
                $alunosFazerProva = [];

                foreach($alunosTurma as $aluno){
                    if($turma == $aluno["turma"]){
                        $alunosFazerProva[] = $aluno;
                    }
                }
                
                $AlunosQueFaltou = [];
                foreach($alunosFazerProva as $aluno){
                    $statusAluno = false;  
                    foreach($provas_tudo as $provs){
                        if($aluno["ra"] == $provs["ra"]){
                            $statusAluno = true;
                            break;
                        }
                    }
                    if(!$statusAluno){
                        $AlunosQueFaltou[] = [
                            "aluno" => $aluno["nome"],
                            "ra" => $aluno["ra"],
                            "turma" => $aluno["turma"],
                            "porcentagem" => 0,
                            "NotaP" => 1,
                            "status" => "FALTOU",
                            "QNT_perguntas" => 0,
                            "acertos" => 0
                        ];
                    }       
                }
            } else {
                $alunos_prova = [];
                $alunosFazerProva = [];

                foreach($alunosTurma as $aluno){
                    if(in_array($aluno["turma"], $turmas)){
                        $alunosFazerProva[] = $aluno;
                    }
                }
                
                $AlunosQueFaltou = [];
                foreach($alunosFazerProva as $aluno){
                    $statusAluno = false;  
                    foreach($provas_tudo as $provs){
                        if($aluno["ra"] == $provs["ra"]){
                            $statusAluno = true;
                            break;
                        }
                    }
                    if(!$statusAluno){
                        $AlunosQueFaltou[] = [
                            "aluno" => $aluno["nome"],
                            "ra" => $aluno["ra"],
                            "turma" => $aluno["turma"],
                            "porcentagem" => 0,
                            "NotaP" => 1,
                            "status" => "FALTOU",
                            "QNT_perguntas" => 0,
                            "acertos" => 0
                        ];
                    }       
                }
            }

            $provas_tudo = array_merge($AlunosQueFaltou,$provas_tudo);

            usort($provas_tudo, function ($a, $b) {
                $result = strcmp($a['turma'], $b['turma']);
                if ($result === 0) {
                    return strcmp($a['aluno'], $b['aluno']);
                }
                return $result;
            });
            
            $dados = [
                "dados_turma" => $dados_turmas,
                "nome_prova" => $nome_prova,
                "media_geral_porcentagem" => MainController::gerarGraficoRosca($media_geral_porcentagem),
                "porcentagem_geral_acima_60" => MainController::gerarGraficoRosca($porcentagem_geral_acima_60),
                "descritores" => $descriotores_sn,
                "percentual_descritores" => $media_descritores_geral,
                "grafico_colunas" => MainController::gerarGraficoColunas($porcentagem_alunos),
                "dados_turma_grafico" => $dados_turma,
                "respostas_alunos" => $respostas_por_aluno,
                "filtro" => $filtro_turmas,
                "provas_turma" => $provas_tudo,
                "descritores_alunos" => $descritores_por_aluno_primeira,
                "descritores_alunos_rec" => $descritores_por_aluno_rec,
            ];

            MainController::Templates("public/views/pfa/prova.php", "PFA", $dados);
        } else {
            header("location: adm");
        }
    }
}