<?php

namespace app\controllers\monitoramento;

use app\controllers\monitoramento\MainController;
use app\models\monitoramento\ADModel;
use app\models\monitoramento\AlunoModel;
use app\models\monitoramento\GestorModel;
use app\models\monitoramento\ProfessorModel;
use app\models\monitoramento\SimuladosModel;
use Dompdf\Dompdf;

class GestorController
{

    public static function login_gestor_verifica()
    {

        if ($_POST["user-gestor"] == $_ENV["SENHA_GESTOR"]) {
            $_SESSION["GESTOR"] = true;
            header("location:gestor_home");
        } else {
            $_SESSION["PopUp_PRF_NaoENC"] = true;
            header("location: login_gestor");
            exit();
        }
    }
    public static function gestor_home()
    {
        if ($_SESSION["GESTOR"]) {

            $btnGeral = isset($_POST["geral"]) || !isset($_POST["filtro"]);
            $dados = null;
            $dados = self::processarFiltros($btnGeral);

            // echo "<pre>";
            // print_r($btnGeral);
            // echo "</pre>";

            MainController::Templates("public/views/gestor/graficos.php", "GESTOR", $dados);
            // MainController::Templates("public/views/gestor/descritores.php", "GESTOR", $dados);

        } else {
            header("location: adm");
        }
    }

    public static function gestor_descritores()
    {
        if ($_SESSION["GESTOR"]) {

            $turnos = ["INTERMEDIÁRIO", "VESPERTINO"];

            $filtros = self::obterFiltros();
            $resultados = GestorModel::GetResultadosFiltrados($filtros);
            $descritores = self::DadosDescritores(self::Descritores($resultados));

            uasort($descritores, function ($a, $b) {
                return $a['porcentagem'] <=> $b['porcentagem'];
            });

            $disciplinas = ["Língua Portuguesa", "Matemática", "Física", "Biologia", "Química"];

            $dados = [
                "filtros" => $filtros,
                "turmas" => ADModel::GetTurmas(),
                "professores" => ADModel::GetProfessores(),
                "turnos" => $turnos,
                "descritores" => $descritores,
                "disciplinas" => $disciplinas,
            ];

            if (isset($_POST["DESCRITOR"])) {
                $dados["descritor"] = $_POST["DESCRITOR"];
            } else {
                $dados["descritor"] = false;
            }

            $btnGeral = isset($_POST["geral"]) || !isset($_POST["filtro"]);

            if ($btnGeral) {
                $dados["geral"] = true;
            } else {
                $dados["geral"] = false;
            }

            MainController::Templates("public/views/gestor/descritores.php", "GESTOR", $dados);
        } else {
            header("location:ADM");
        }
    }

    public static function Descritores($provas_alunos)
    {
        $provas_professores = AlunoModel::GetProvas();
        // $provas_alunos = AlunoModel::GetProvasFinalizadas();

        $descritores_alunos_todos = [];

        foreach ($provas_alunos as $prova) {
            if ($prova["descritores"] != null) {
                $descritor_aluno = [];
                $descritores_P = explode(";", $prova["descritores"]);
                $descritores_certos = $prova["descritores_certos"] ? explode(";", $prova["descritores_certos"]) : [];
                $descritores_errados = $prova["descritores_errados"] ? explode(";", $prova["descritores_errados"]) : [];

                $contador_descritores = [];
                $acertos_descritores = [];

                foreach ($descritores_P as $descritor) {
                    $desc = explode(",", $descritor);
                    if (count($desc) == 2) {
                        $desc = $desc[1];

                        if (!isset($contador_descritores[$desc])) {
                            $contador_descritores[$desc] = 0;
                            $acertos_descritores[$desc] = 0;
                        }
                        $contador_descritores[$desc]++;
                    }
                }

                foreach ($descritores_certos as $descritor) {
                    $desc = explode(",", $descritor);
                    if (count($desc) == 2) {
                        $desc = $desc[1];
                        if (isset($acertos_descritores[$desc])) {
                            $acertos_descritores[$desc]++;
                        }
                    }
                }

                foreach ($contador_descritores as $desc => $total) {
                    if ($total > 0) {
                        $acertos = $acertos_descritores[$desc];
                        $porcentagem = ($acertos / $total) * 100;
                        $descritor_aluno[$desc] = $porcentagem;
                    }
                }

                $descritores_alunos_todos[] = $descritor_aluno;
            }
        }

        // echo "<pre>";
        // print_r($descritores_alunos_todos);
        // // print_r($provas_alunos);
        // echo "</pre>";
        // echo "<br>";

        return $descritores_alunos_todos;
    }

    public static function DadosDescritores($DescAlunos)
    {
        $descritores_total = [];

        foreach ($DescAlunos as $descritores) {
            foreach ($descritores as $descritor => $value) {
                if (!isset($descritores_total[$descritor])) {
                    $descritores_total[$descritor] = [
                        "soma_porcentagem" => 0,
                        "quantidade" => 0,
                        "proeficiencia" => [
                            "Abaixo do Básico" => 0,
                            "Básico" => 0,
                            "Médio" => 0,
                            "Avançado" => 0,
                        ],
                    ];
                }
            }
        }

        // Calcule a soma das porcentagens e a contagem de proficiências
        foreach ($DescAlunos as $descritores) {
            foreach ($descritores as $descritor => $value) {
                $descritores_total[$descritor]["soma_porcentagem"] += $value;
                $descritores_total[$descritor]["quantidade"]++;

                if ($value < 25) {
                    $descritores_total[$descritor]["proeficiencia"]["Abaixo do Básico"]++;
                } elseif ($value >= 25 && $value < 50) {
                    $descritores_total[$descritor]["proeficiencia"]["Básico"]++;
                } elseif ($value >= 50 && $value < 75) {
                    $descritores_total[$descritor]["proeficiencia"]["Médio"]++;
                } else {
                    $descritores_total[$descritor]["proeficiencia"]["Avançado"]++;
                }
            }
        }

        foreach ($descritores_total as $descritor => $data) {
            $quantidade = $data["quantidade"];
            $descritores_total[$descritor]["porcentagem"] = number_format($data["soma_porcentagem"] / $quantidade, 1);

            foreach ($data["proeficiencia"] as $faixa => $contagem) {
                $descritores_total[$descritor]["proeficiencia"][$faixa] = number_format(($contagem / $quantidade) * 100, 0);
            }

            unset($descritores_total[$descritor]["soma_porcentagem"]);
            unset($descritores_total[$descritor]["quantidade"]);
        }

        foreach ($descritores_total as $descritor => $value) {
            $descritores_total[$descritor]["proeficiencia"] = MainController::gerarGraficoHorizontal($value["proeficiencia"], $descritor);
            $descritores_total[$descritor]["porcentagem_grafico"] = MainController::gerarGraficoRosca($value["porcentagem"]);
            $descritores_total[$descritor]["porcentagem"] = $value["porcentagem"];
        }

        // echo "<pre>";
        // print_r($descritores_total);
        // echo "</pre>";

        return $descritores_total;
    }

    public static function obterFiltros()
    {
        $turma = ($_POST['turma'] ?? null) === "SELECIONAR" ? null : ($_POST['turma'] ?? null);
        $turno = ($_POST['turno'] ?? null) === "SELECIONAR" ? null : ($_POST['turno'] ?? null);
        $disciplina = ($_POST['disciplina'] ?? null) === "SELECIONAR" ? null : ($_POST['disciplina'] ?? null);
        $professor = ($_POST['professor'] ?? null) === "SELECIONAR" ? null : ($_POST['professor'] ?? null);
        $serie = ($_POST['serie'] ?? null) === "SELECIONAR" ? null : ($_POST['serie'] ?? null);
        $periodo = ($_POST['periodo'] ?? null) === "SELECIONAR" ? null : ($_POST['periodo'] ?? null);
        $metodo = ($_POST['metodo'] ?? null) === "SELECIONAR" ? null : ($_POST['metodo'] ?? null);


        if($periodo != NULL){

            $periodos = ADModel::GetPeriodos();
            foreach($periodos as $p){
                if($p["nome"] == $periodo){
                    $datas = $p;
                }
            }
        }else{
            $datas = NULL;
        }

        return [
            "turma" => $turma,
            "turno" => $turno,
            "disciplina" => $disciplina,
            "professor" => $professor,
            "serie" => $serie,
            "periodo" => $periodo,
            "metodo" => $metodo,
            "datas" => $datas
        ];
    }

    private static function gerarDadosTurnos()
    {
        $dados_turno_geral = [];
        $turnos = ["INTERMEDIÁRIO", "VESPERTINO"];

        foreach ($turnos as $turno) {
            $DadosTurnos = GestorModel::GetFiltro("turno", $turno);
            if (count($DadosTurnos) > 0) {
                $dados_turno_geral[$turno] = [
                    MainController::gerarGraficoRosca(self::procentagemGeral($DadosTurnos)),
                    MainController::gerarGraficoColunas(self::GetProeficiencia($DadosTurnos)),
                ];
            } else {
                $dados_turno_geral[$turno] = null;
            }
        }

        return $dados_turno_geral;
    }

    private static function processarFiltros($btnGeral)
    {
        $todas_provas = AlunoModel::GetProvasFinalizadas();

        if ($todas_provas != null) {

            $turnos = ["INTERMEDIÁRIO", "VESPERTINO"];

            $filtros = self::obterFiltros();

            $dados_turno_geral = self::gerarDadosTurnos();

            $dados = [
                "turnos" => $turnos,
                "disciplinas" => ADModel::GetDisciplinas(),
                "professores" => ADModel::GetProfessores(),
                "turmas" => ADModel::GetTurmas(),
                "status" => false,
                "periodos" => ADModel::GetPeriodos(),
                "filtros" => $filtros,
                "roscaGeral" => MainController::gerarGraficoRosca(self::procentagemGeral($todas_provas)),
                "colunaGeral" => MainController::gerarGraficoColunas(self::GetProeficiencia($todas_provas)),
                "dados_turnos" => $dados_turno_geral,
                "geral" => $btnGeral,
            ];

            if ($btnGeral) {
                $dados["dadosturmas"] = self::DadosGeralTurmas($todas_provas);
                $dados["dadosturnos"] = self::DadosGeralTurno($todas_provas);
            }


            $resultados = GestorModel::GetResultadosFiltrados($filtros);

            $dados["quantidade_dados"] = count($resultados);

            // dd($resultados);
            // self::DadosDescritores(self::Descritores($resultados));

            if (!$btnGeral) {
                $dados["resultadosTurmas"] = self::DadosGeralTurmas($resultados);
                $dados["alunosFiltro60"] = MainController::gerarGrafico60(self::calcularPorcentagemAcima60($resultados));
            } else {
                $dados["resultadosTurmas"] = null;
            }

            // echo "<pre>";
            // print_r($resultadosTurmas);
            // echo "</pre>";

            if (count($resultados) > 0) {
                $dados["graficos_filtro"] = self::GetGraficosFiltros($resultados);
            } else {
                $dados["graficos_filtro"] = null;
            }

            $dados["status"] = count($resultados) > 0;

            return $dados;
        } else {
            return false;
        }
    }

    public static function calcularPorcentagemAcima60($provas)
    {
        $total_alunos = count($provas);
        $alunos_acima_60 = 0;

        foreach ($provas as $prova) {
            $porcentagem_aluno = ($prova["acertos"] / $prova["QNT_perguntas"]) * 100;
            if ($porcentagem_aluno >= 60) {
                $alunos_acima_60++;
            }
        }

        if ($total_alunos === 0) {
            return 0;
        }

        $porcentagem_acima_60 = ($alunos_acima_60 / $total_alunos) * 100;
        return number_format($porcentagem_acima_60, 0);
    }

    public static function GetGraficosFiltros($provas)
    {
        $proeficiencia = self::GetProeficiencia($provas);
        $porcentagem = self::procentagemGeral($provas);

        $dados = [
            "proeficiencia" => MainController::gerarGraficoColunas($proeficiencia),
            "porcentagem" => MainController::gerarGraficoRosca($porcentagem),
        ];
        return $dados;
    }

    public static function procentagemGeral($provas)
    {

        // echo "<pre>";
        // print_r($provas);
        // echo "</pre>";

        $numero_linhas = count($provas);
        $porcentagem = 0;

        foreach ($provas as $prova) {
            $porcentagem += $prova["porcentagem"];
        }

        // echo $numero_linhas;

        return number_format($porcentagem / $numero_linhas, 2);
    }

    public static function GetProeficiencia($provas)
    {
        $proficiência = array(
            "Abaixo do Básico" => 0,
            "Básico" => 0,
            "Médio" => 0,
            "Avançado" => 0,
        );

        foreach ($provas as $prova) {
            $porcentagem = $prova["porcentagem"];

            if ($porcentagem >= 0 && $porcentagem < 25) {
                $proficiência["Abaixo do Básico"]++;
            } elseif ($porcentagem >= 25 && $porcentagem < 50) {
                $proficiência["Básico"]++;
            } elseif ($porcentagem >= 50 && $porcentagem < 75) {
                $proficiência["Médio"]++;
            } elseif ($porcentagem >= 75 && $porcentagem <= 100) {
                $proficiência["Avançado"]++;
            }
        }

        $totalAlunos = count($provas);

        $porcentagensProficiência = array();

        foreach ($proficiência as $nivel => $quantidade) {
            if ($quantidade == 0) {
                $porcentagem = 0;
            } else {
                $porcentagem = number_format((($quantidade / $totalAlunos) * 100), 1);
            }
            $porcentagensProficiência[$nivel] = $porcentagem;
        }

        return $porcentagensProficiência;
    }

    public static function DadosGeralTurno($provas)
    {
        $dadosPorTurno = array();

        foreach ($provas as $prova) {
            $turno = $prova["turno"];
            $porcentagem = $prova["porcentagem"];

            if (!isset($dadosPorTurno[$turno])) {

                $dadosPorTurno[$turno] = array(
                    "quantidade" => 1,
                    "soma_porcentagem" => $porcentagem,
                );
            } else {
                $dadosPorTurno[$turno]["quantidade"]++;
                $dadosPorTurno[$turno]["soma_porcentagem"] += $porcentagem;
            }
        }

        $mediasPorTurno = array();

        foreach ($dadosPorTurno as $turno => $dados) {
            $quantidade = $dados["quantidade"];
            $soma_porcentagem = $dados["soma_porcentagem"];
            $media = $soma_porcentagem / $quantidade;

            $mediasPorTurno[$turno] = MainController::gerarGraficoRosca(number_format($media, 2));
        }

        return $mediasPorTurno;
    }

    public static function DadosGeralTurmas($provas)
    {
        $dadosPorTurma = array();

        foreach ($provas as $prova) {
            $turma = $prova["turma"];
            $porcentagem = $prova["porcentagem"];

            if (!isset($dadosPorTurma[$turma])) {

                $dadosPorTurma[$turma] = array(
                    "quantidade" => 1,
                    "soma_porcentagem" => $porcentagem,
                );
            } else {

                $dadosPorTurma[$turma]["quantidade"]++;
                $dadosPorTurma[$turma]["soma_porcentagem"] += $porcentagem;
            }
        }

        $mediasPorTurma = array();

        foreach ($dadosPorTurma as $turma => $dados) {
            $quantidade = $dados["quantidade"];
            $soma_porcentagem = $dados["soma_porcentagem"];
            $media = $soma_porcentagem / $quantidade;

            $mediasPorTurma[$turma] = MainController::gerarGraficoRosca(number_format($media, 1));
        }

        $turmasIntermediario = [];
        $turmasVespertino = [];

        foreach ($mediasPorTurma as $turma => $valor) {
            $turno = substr($turma, 3, 1);
            if ($turno === 'I') {
                $turmasIntermediario[$turma] = $valor;
            } else {
                $turmasVespertino[$turma] = $valor;
            }
        }

        ksort($turmasIntermediario);

        ksort($turmasVespertino);

        $arrayOrdenado = $turmasIntermediario + $turmasVespertino;

        return $arrayOrdenado;
    }

    public static function gestor_provas()
    {
        if ($_SESSION["GESTOR"]) {
            $provas_alunos = AlunoModel::GetProvasFinalizadas();
            $provas = [];

            $disciplina = ($_POST['disciplina'] ?? null) === "SELECIONAR" ? null : ($_POST['disciplina'] ?? null);
            $professor = ($_POST['professor'] ?? null) === "SELECIONAR" ? null : ($_POST['professor'] ?? null);

            if ($professor == null && $disciplina == null) {
                $geral = true;
            } else {
                $geral = false;
            }

            $filtros = [
                "disciplina" => $disciplina,
                "professor" => $professor,
            ];

            $provas_professores = GestorModel::GetProvasFiltrados($filtros);

            if ($provas_professores != null) {
                foreach ($provas_professores as $professor) {
                    $provas[] = $professor;
                }

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
                "disciplinas" => ADModel::GetDisciplinas(),
                "professores" => ADModel::GetProfessores(),
                "provas_alunos" => $provas_alunos,
                "status" => $status,
                "geral" => $geral,
            ];

            MainController::Templates("public/views/gestor/provas.php", "GESTOR", $dados);

        } else {
            header("location: adm");
        }
    }

    public static function gestor_prova()
    {
        if ($_SESSION["GESTOR"]) {
            if(!isset($_POST["id-prova"])){
                header("location: gestor_provas");
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
            $descritores_por_aluno_rec = null;

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

            MainController::Templates("public/views/gestor/prova.php", "GESTOR", $dados);
        } else {
            header("location: adm");
        }
    }

    public static function simulados()
    {
        if (!$_SESSION["GESTOR"]) header("location: adm");

        $filtro = [];
        if(!empty($_GET['query_array']) && is_array($_GET['query_array'])) {
            foreach ($_GET['query_array'] as $chave => $valor) {
                $filtro[$chave] = urldecode(trim($valor));
            }
        }

        $simulados = SimuladosModel::getSimulados($filtro);

        $disciplinas = GestorModel::GetNomeDisciplinas();
        $turmas = ProfessorModel::GetTurmas();
        $professores =  ADModel::GetProfessores();

        MainController::Templates("public/views/gestor/simulados.php", "GESTOR", [
            'disciplinas'  => $disciplinas,
            'turmas'       => $turmas,
            'professores'  => $professores,
            'simulados'    => $simulados,
            'filtro'       => $filtro,
        ]);
    }

    public static function criar_simulado()
    {
        if (!$_SESSION["GESTOR"]) header("location: adm");

        $provasDisponiveis = SimuladosModel::getProvasDisponiveis();

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $data = $_POST;

            $ano = date('Y');
            $mes = date('m');
            
            $insert = [
                'turma_id' => $data['turma'],
                'nome' => $data['nome'],
                'area_conhecimento' => $data['area_conhecimento'],
                'data' => date('Y-m-d', strtotime("$ano-$mes-01")),
                'orientacoes' => $data['orientacoes'],
                'gabarito_ids' => $data['id_prova'],
                'ordem_selecao' => $data['ordem_selecao'],
            ];

            $simulado = SimuladosModel::addSimulado($insert);

            if($simulado) {
                $_SESSION["PopUp_Simulado_Sucesso_Adicionado"] = true;
            } else {
               $_SESSION["PopUp_Simulado_Erro_Adicionado"] = false;
            }

            header("location: simulados");
        }

        MainController::Templates("public/views/gestor/criar_simulado.php", "GESTOR", [
            'provas'       => $provasDisponiveis,
            'disciplinas'  => GestorModel::GetNomeDisciplinas(),
            'turmas'       => ProfessorModel::GetTurmas(),
            'professores'  => ADModel::GetProfessores(),
        ]);
    }

    public static function editar_simulado()
    {
        if (!$_SESSION["GESTOR"] && !isset($_GET['query_array']['simulado'])) header("location: adm");

        $simulado_id = $_GET['query_array']['simulado'];

        $simulado = SimuladosModel::getSimulado($simulado_id);
        $provasSimulado = SimuladosModel::getProvasSimulado($simulado_id);
        $ids_prova = array_map(fn ($prova) => $prova['gabarito_professor_id'], $provasSimulado);

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $dados = $_POST;

            $update = [
                'id' => $simulado['id'],
                'turma_id' => $dados['turma'],
                'nome' => $dados['nome'],
                'area_conhecimento' => $dados['area_conhecimento'],
                'orientacoes' => $dados['orientacoes'],
                'gabarito_ids' => $dados['id_prova'],
                'ordem_selecao' => $dados['ordem_selecao'],
            ];

            $simulado = SimuladosModel::updateSimulado($update, $simulado_id);

            if($simulado) {
                $_SESSION["PopUp_Simulado_Sucesso_Atualizado"] = true;
            } else {
                $_SESSION["PopUp_Simulado_Erro_Atualizado"] = false;
            }

            header("location: simulados");
            exit;
        }

        MainController::Templates("public/views/gestor/editar_simulado.php", "GESTOR", [
            'disciplines'  => GestorModel::GetNomeDisciplinas(),
            'turmas'       => ProfessorModel::GetTurmas(),
            'professores'  => ADModel::GetProfessores(),
            'provas'       => SimuladosModel::getProvas(),
            'simulado'     => $simulado,
            'ids_prova'    => $ids_prova,
        ]);
    }

    public static function excluir_simulado()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = file_get_contents("php://input");
            $simulado_id = json_decode($input, true)['simulado'];

            $resultado = false;

            if($simulado_id) {
                $resultado = SimuladosModel::excluirSimulado($simulado_id);
            }

            echo json_encode(['resultado' => $resultado, 'simulado_id' => $simulado_id]);
        }
    }

    public static function download_multi_gabarito() 
    {
        if (!$_SESSION["GESTOR"]) header("location: adm");

        if ($_SERVER['REQUEST_METHOD'] !== 'get'  && empty($_GET['query_array']['simulado'])) {
            header("location: {$_SESSION['PAG_VOLTAR']}");
        }

        $simulado_id = $_GET['query_array']['simulado'];
        $simulado = SimuladosModel::getSimulado($simulado_id);
        $provas = SimuladosModel::getProvasSimulado($simulado_id);

        $turmas = array_unique([...array_column($provas, 'turmas')]);

        ob_start();
        include "public/views/gestor/download_gabarito.php";

        $html_content = ob_get_contents();
        ob_end_clean();

        $test = true;
        $test = false;

        if ($test) {
            echo $html_content;
        } else {
            $options = [
                'enable_remote' => true,
                'defaultFont' => 'Arial',
                'enable_php' => true,
            ];

            // instantiate and use the dompdf class
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html_content);
    
            // (Optional) Setup the paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
    
            // Render the HTML as PDF
            $dompdf->render();
    
            // Output the generated PDF to Browser
            $dompdf->stream("gabarito.pdf", [
                "Attachment" => false
            ]);
        }

        exit;
    }
}
