<?php

namespace app\controllers\monitoramento;

use app\controllers\monitoramento\MainController;
use app\models\monitoramento\ADModel;
use app\models\monitoramento\AlunoModel;
use app\models\monitoramento\ProfessorModel;
use app\models\monitoramento\SimuladosModel;
use DateTime;
use Dompdf\Dompdf;

class ProfessorController
{

    public static function home_professor()
    {

        $info = ProfessorModel::verificarLogin($_POST["user-prof"]);
        if ($info != false) {
            if ($info["senha"] == $_POST["senha-prof"]) {
                $_SESSION["PROFESSOR"] = true;
                $_SESSION["nome_professor"] = $info["nome"];
                $_SESSION["nome_usuario"] = $info["usuario"];
                $_SESSION["numero"] = $info["numero"];
                $_SESSION["disciplinas"] = $info["disciplinas"];
                $_SESSION["id_professor"] = $info["id"];
                header("location: professor_home");
            } else {
                $_SESSION["PopUp_PRF_Senha"] = true;
                header("location: login_professor");
                exit;
            }
        } else {
            $_SESSION["PopUp_PRF_NaoENC"] = true;
            header("location: login_professor");
            exit;
        }
    }
    public static function professor_home()
    {
        if ($_SESSION["PROFESSOR"]) {

            MainController::Templates("public/views/professor/home.php", "PROFESSOR", [
                'nome' => $_SESSION["nome_professor"],
                'nome_usuario' => $_SESSION["nome_usuario"],
                'numero' => $_SESSION["numero"],
                'disciplinas' => $_SESSION["disciplinas"],
            ]);

        } else {
            header("location: adm");
        }
    }

    public static function inserir_gabarito()
    {

        if ($_SESSION["PROFESSOR"]) {
            $turmas = ProfessorModel::GetTurmas();
            $_SESSION["PAG_VOLTAR"] = "professor_home";

            MainController::Templates("public/views/professor/inserir_gabarito.php", "PROFESSOR", $turmas);
        } else {
            header("location: adm");
        }
    }

    public static function professor_security(){
        if ($_SESSION["PROFESSOR"]) {
            $_SESSION["PAG_VOLTAR"] = "professor_home";

            MainController::Templates("public/views/professor/security.php", "PROFESSOR");
        } else {
            header("location: adm");
        }
    }

    public static function criar_gabarito()
    {

        if ($_SESSION["PROFESSOR"]) {

            if ($_POST["gabarito-turmas"] == null) {
                $_SESSION["popup_not_turmas"] = true;
                header("location: inserir_gabarito");
                exit;
            }

            if ($_POST["qtn-perguntas"] == null) {
                $_SESSION["PopUp_not_QntP"] = true;
                header("location: inserir_gabarito");
                exit;
            }

            if ($_POST["valor-prova"] == null && $_POST["metodo"] == "prova") {
                $_SESSION["PopUp_not_valor"] = true;
                header("location: inserir_gabarito");
                exit;
            }

            $_SESSION["PAG_VOLTAR"] = "inserir_gabarito";

            $turmas = $_POST["gabarito-turmas"];
            $perguntas = $_POST["qtn-perguntas"];
            $valor = $_POST["valor-prova"];
            $nome = $_POST["nome-prova"];
            $materia = $_POST["Materias-professor-gabarito"];
            $descritores = $_POST["descritores"];

            if ($_SESSION["PROFESSOR"]) {
                $turmas = $_POST["gabarito-turmas"];
                $perguntas = $_POST["qtn-perguntas"];
                $valor = $_POST["valor-prova"];

                $dados = [
                    "turmas" => $turmas,
                    "perguntas" => $perguntas,
                    "valor" => $valor,
                    "metodo" => $_POST["metodo"],
                    "nome_prova" => $nome,
                    "materia" => $materia,
                    "alternativas" => explode(",",$_ENV["ALTERNATIVAS"]),
                    "descritores" => $descritores,
                ];

                MainController::Templates("public/views/professor/criar_gabarito.php", "PROFESSOR", $dados);
            } else {
                header("location: home");
            }
        } else {
            header("location: adm");
        }
    }

    public static function criar_gabarito_respostas()
    {
        if ($_SESSION["PROFESSOR"]) {
            $gabarito_prova = [];
            $descritores_prova = [];
            $dataAtual = new DateTime();
            $dataFormatada = $dataAtual->format('Y-m-d');

            $contador = 1;

            while ($contador <= $_POST["numero_perguntas"]) {
                $descritores_prova[$contador] = $contador . "," . $_POST["DESCRITOR_" . $contador];
                $gabarito_prova[$contador] = base64_encode($_POST[$contador]);

                $contador++;
            }

            $descritores = implode(";", $descritores_prova);

            if ($_POST["descritor"] == "não") {
                $descritores = null;
            }

            $gabarito = implode(";", $gabarito_prova);

            $dados = [
                "turmas" => $_POST["turmas_gabarito"],
                "perguntas" => $_POST["numero_perguntas"],
                "valor" => $_POST["valor_prova"],
                "metodo" => $_POST["metodo"],
                "data" => $dataFormatada,
                "nome_prova" => $_POST["nome_prova"],
                "descritores" => $descritores,
                "gabarito" => $gabarito,
                "nome_prof" => $_SESSION["nome_professor"],
                "materia" => $_POST["materia_prova"],
            ];
            if (ProfessorModel::inserir_gabarito($dados)) {
                self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} cadastrou a prova {$dados["nome_prova"]} ");
                $_SESSION["PopUp_inserir_gabarito_professor"] = true;
                header("location: inserir_gabarito");
                exit;
            }
        } else {
            header("location: home");
        }
    }

    public static function ver_provas()
    {
        if (!$_SESSION["PROFESSOR"]) {
            header("location: adm");
        }

        $provas_professores = AlunoModel::GetProvas();
        $provas_alunos = AlunoModel::GetProvasFinalizadas();
        $provas = [];
        $_SESSION["PAG_VOLTAR"] = "professor_home";

        if ($provas_professores != null) {

            foreach ($provas_professores as $professor) {
                if ($professor["nome_professor"] == $_SESSION["nome_professor"]) {
                    $provas[] = $professor;
                }
            }

            $provas_organizadas = [];

            foreach (ADModel::GetPeriodos() as $periodo) {
                $nome_periodo = $periodo['nome'];
                $data_inicial = $periodo['data_inicial'];
                $data_final = $periodo['data_final'];

                $provas_organizadas[$nome_periodo] = [];

                foreach ($provas as $index => $prova) {
                    $data_prova = $prova['data_prova'];

                    if ($data_prova >= $data_inicial && $data_prova <= $data_final) {
                        $provas_organizadas[$nome_periodo][$index] = $prova;
                        $provas_organizadas[$nome_periodo][$index]['total_alunos'] = count(AlunoModel::GetProvasbyID($prova['id']));
                    }
                }
            }

            $dados = [
                "provas" => $provas_organizadas,
            ];
        }
//            if ($provas_professores != null) {
//                foreach ($provas_professores as $professor) {
//                    if ($professor["nome_professor"] == $_SESSION["nome_professor"]) {
//                        $provas[] = $professor;
//                    }
//                }
//
//                foreach (ADModel::GetPeriodos() as $periodo) {
//                    $nome_periodo = $periodo['nome'];
//                    $data_inicial = $periodo['data_inicial'];
//                    $data_final = $periodo['data_final'];
//
//                    $provas_organizadas[$nome_periodo] = [];
//
//                    foreach ($provas as $prova) {
//                        $data_prova = $prova['data_prova'];
//
//                        if ($data_prova >= $data_inicial && $data_prova <= $data_final) {
//                            $provas_organizadas[$nome_periodo][] = $prova;
//                        }
//                    }
//                }
//
//                $dados = [
//                    "provas" => $provas_organizadas,
//                "provas_alunos" => $provas_alunos,
//            ];
//
//            foreach ($dados['provas'] as $periodo => $provass) {
//                usort($provass, function ($a, $b) {
//                    return strtotime($b['data_prova']) - strtotime($a['data_prova']);
//                });
//                $dados['provas'][$periodo] = $provass;
//            }
//
//        }

        if ($provas == null || $provas_professores == null) {
            $dados = null;
        }

        MainController::Templates("public/views/professor/provas.php", "PROFESSOR", $dados);
    }
    public static function prova()
    {
        if ($_SESSION["PROFESSOR"]) {
            $provas_professores = AlunoModel::GetProvas();
            $provas_alunos = AlunoModel::GetProvasFinalizadas();
            $_SESSION["PAG_VOLTAR"] = "ver_provas";

            if (isset($_POST["id-prova"])) {
                $id_prova = $_POST["id-prova"];
                $_SESSION["id_prova_professor"] = $_POST["id-prova"];
            } else {
                $id_prova = $_SESSION["id_prova_professor"];
            }

            $provas = [];
            $provas_turma = [];
            $liberado = false;

            foreach ($provas_professores as $prova) {
                if ($prova["id"] == $id_prova) {
                    $provaa = $prova;
                    $turmas = $prova["turmas"];
                    $nome_prova = $prova["nome_prova"];
                    $liberado = $prova["liberado"] == "SIM" ? true : false;
                    if ($prova["liberar_prova"] == null) {
                        $liberar_prova = true;
                    } else {
                        $liberar_prova = false;
                    }
                }
            }

            if ($provas_alunos != null) {
                foreach ($provas_alunos as $prova) {
                    if ($prova["id_prova"] == $id_prova) {
                        $provas[] = $prova;
                    }
                }
            }

            $turma = explode(",", $turmas);
            $turma = $turma[0];

            if (isset($_POST["status"])) {
                if ($_POST["status"] == "sim") {
                    ProfessorModel::alterar_liberado($id_prova, "SIM");
                    self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} liberou os acesso as respostas da prova {$nome_prova}. ");
                    header("Location: prova");
                } else {
                    self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} bloqueou os acesso as respostas da prova {$nome_prova}. ");
                    ProfessorModel::alterar_liberado($id_prova, null);
                    header("Location: prova");
                }
            }

            if (isset($_POST["status-liberado"])) {
                if ($_POST["status-liberado"] == "sim") {
                    ProfessorModel::alterar_liberado_ver($id_prova, null);
                    self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} permitiu os alunos fazerem a prova {$nome_prova} ");
                    header("Location: prova");
                } else {
                    ProfessorModel::alterar_liberado_ver($id_prova, "NÃO");
                    self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} bloqueou os alunos de fazerem a prova {$nome_prova} ");
                    header("Location: prova");
                }
            }

            if (isset($_POST["turma"])) {
                $turma = $_POST["turma"];

            }

            foreach ($provas as $prova) {
                if ($prova["turma"] == $turma) {
                    $provas_turma[] = $prova;
                }
            }

            if (isset($_POST["enviar-user"])) {
                if ($_POST["user"] == $_SESSION["numero"]) {
                    ProfessorModel::ExcluirProvaAluno($id_prova);
                    ProfessorModel::ExcluirProvaProf($id_prova);
                    self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} excluiu a prova {$nome_prova}. ");
                    header("Location: ver_provas");
                    $_SESSION["PopUp_Excluir_prova"] = true;
                    exit();
                } else {
                    echo "<script> window.alert('Nome de usuario Incorreto!') </script>";
                }
            }

            $dados = [
                "provas" => $provas,
                "turmas" => explode(",", $turmas),
                "turma" => $turma,
                "provas_turma" => $provas_turma,
                "liberado" => $liberado,
                "liberar_prova" => $liberar_prova,
                "prova" => $provaa,
                "nome_prova" => $nome_prova,
            ];

            // $provas_rec = ProfessorModel::GetProvaRecbyIDprova($id_prova);
            // if (count($provas_rec) > 0) {
            //     $dados["provas_rec"] = [
            //         "quantidade" => count($provas_rec),
            //         "provas" => $provas_rec,
            //     ];
            // } else {
            //     $dados["provas_rec"] = null;
            // }
            $dados["provas_rec"] = null;

            // echo "<pre>";
            // print_r($dados);
            // echo "</pre>";

            MainController::Templates("public/views/professor/prova.php", "PROFESSOR", $dados);
        } else {
            header("location: adm");
        }
    }

    public static function prova_recuperacao()
    {
        if ($_SESSION["PROFESSOR"]) {

            if (isset($_POST["prova"])) {
                $id = $_POST["prova"];
                $_SESSION["id_prova_professorRec"] = $_POST["prova"];
            } else {
                $id = $_SESSION["id_prova_professorRec"];
            }
            $prova = ProfessorModel::GetProvaRecbyID($id);
            $provas_rec = ProfessorModel::GetProvaRecAlunos();
            $provas_rec_professor = ProfessorModel::GetProvaRec();
            $id_prova_origin = $prova["id_prova"];
            $nome_prova = $prova["nome_prova"];

            $alunos_prova = [];
            if (strpos($prova["alunos"], ";")) {
                $alunos_prova = explode(";", $prova["alunos"]);
            } else {
                $alunos_prova[] = $prova["alunos"];
            }

            $alunos = [];
            foreach (AlunoModel::GetAlunos() as $aluno) {
                foreach ($alunos_prova as $aln_prova) {
                    if ($aln_prova == $aluno["ra"]) {
                        $alunos[$aln_prova] = [
                            "nome" => $aluno["nome"],
                            "turma" => $aluno["turma"],
                            "status" => "NÃO FEZ",
                            "Pontos" => " ",
                        ];
                    }
                }
            }

            // echo "<pre>";
            // print_r($alunos);
            // echo "</pre>";

            foreach ($provas_rec_professor as $prova) {
                if ($prova["id"] == $id) {
                    $liberado = $prova["liberado"] == "SIM" ? true : false;
                }
            }

            if (isset($_POST["status"])) {
                if ($_POST["status"] == "sim") {
                    ProfessorModel::alterar_liberadoRec($id, "SIM");
                    self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} liberou os acesso as respostas da prova de recuperação {$nome_prova}. ");
                    header("Location: prova_recuperacao");
                } else {
                    ProfessorModel::alterar_liberadoRec($id, null);
                    self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} bloqueou os acesso as respostas da prova de recuperação {$nome_prova}. ");
                    header("Location: prova_recuperacao");
                }
            }
            $provas_rec_professor = ProfessorModel::GetProvaRec();

            foreach ($provas_rec_professor as $prova) {
                if ($prova["id"] == $id) {
                    if ($prova["liberar_prova"] == null) {
                        $liberar_prova = true;
                    } else {
                        $liberar_prova = false;
                    }
                }
            }

            if (isset($_POST["status-liberado"])) {
                if ($_POST["status-liberado"] == "sim") {
                    ProfessorModel::alterar_liberado_verRec($id, null);
                    self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} liberou a da prova de recuperação {$nome_prova}. ");
                    header("Location: prova_recuperacao");
                } else {
                    ProfessorModel::alterar_liberado_verRec($id, "NÃO");
                    self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} bloqueou a da prova de recuperação {$nome_prova}. ");
                    header("Location: prova_recuperacao");
                }
            }

            if (isset($_POST["enviar-user"])) {
                if ($_POST["user"] == $_SESSION["numero"]) {
                    ProfessorModel::ExcluirProvaRecAluno($id_prova_origin);
                    ProfessorModel::ExcluirProvaRecProf($id_prova_origin);
                    self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} excluiu a da prova de recuperação {$nome_prova}. ");
                    header("Location: ver_provas");
                    $_SESSION["PopUp_Excluir_prova"] = true;
                    exit();
                } else {
                    echo "<script> window.alert('Nome de usuario Incorreto!') </script>";
                }
            }

            foreach ($provas_rec as $prova) {
                foreach ($alunos as $ra => $aluno) {

                    if ($ra == $prova["ra"] && $prova["id_prova_rec"] == $id) {
                        $alunos[$ra]["status"] = "FEZ";
                        $alunos[$ra]["Pontos"] = $prova["pontos_aluno"];
                    }
                }
            }

            uasort($alunos, function ($a, $b) {
                return strcmp($a['turma'], $b['turma']);
            });

            // echo "<pre>";
            // print_r($alunos);
            // echo "</pre>";'

            $dados = [
                "alunos" => $alunos,
                "liberado" => $liberado,
                "id" => $id,
                "liberar_prova" => $liberar_prova,
                "id_prova" => $id_prova_origin,
            ];

            MainController::Templates("public/views/professor/dados_prova_rec.php", "PROFESSOR", $dados);

        } else {
            header("location: adm");
        }
    }

    public static function editar_gabarito_recuperacao()
    {
        if ($_SESSION["PROFESSOR"]) {
            $_SESSION["PAG_VOLTAR"] = "ver_provas";

            $id = $_POST["id-prova"];
            $id_prova = $_POST["id_prova_origin"];
            $_SESSION["ID_PROVA_EDITAR"] = $id;

            $prova_professor = ProfessorModel::GetProvaRecbyID($id);
            $valor = $prova_professor["valor"];
            $perguntas = $prova_professor["QNT_perguntas"];

            $gabarito = explode(";", $prova_professor["gabarito"]);

            if ($prova_professor["descritores"] == null) {
                $descritores = null;
            } else {
                $descritores = explode(";", $prova_professor["descritores"]);
            }

            $dados = [
                "gabarito" => $gabarito,
                "descritores" => $descritores,
                "valor" => $valor,
                "perguntas" => $perguntas,
                "nome" => $prova_professor["nome_prova"],
                "id_prova" => $id_prova,
            ];

            // echo "<pre>";
            // print_r($dados);
            // echo "</pre>";

            MainController::Templates("public/views/professor/editar_provaRec.php", "PROFESSOR", $dados);
        } else {
            header("location: adm");
        }
    }

    public static function atualizar_gabarito_rec()
    {
        if ($_SESSION["PROFESSOR"]) {
            $id_prova = $_POST['id_prova_origin'];
            $provaRec = ProfessorModel::GetProvaRecbyID($id_prova);
            $nome_prova = $provaRec["nome_prova"];
            $numero_perguntas = $_POST['numero_perguntas'];
            $descritor_flag = $_POST['descritor'];

            $gabarito_prova = [];
            $descritores_prova = [];

            for ($contador = 1; $contador <= $numero_perguntas; $contador++) {
                $descritores_prova[$contador] = $contador . "," . $_POST["DESCRITOR_" . $contador];
                $gabarito_prova[$contador] = $_POST[$contador];
            }

            $descritores = implode(";", $descritores_prova);
            if ($descritor_flag == "não") {
                $descritores = null;
            }

            $gabarito = implode(";", $gabarito_prova);

            $novo_gabarito_professor = [
                "descritores" => $descritores,
                "valor" => $_POST['valor_prova'],
                "gabarito" => $gabarito,
                "ID_prova" => $id_prova,
            ];

            ProfessorModel::atualizar_gabarito_professorRec($novo_gabarito_professor);
            $provas_alunos = ProfessorModel::GetProvasRecFeitasbyID($id_prova);

            foreach ($provas_alunos as $prova_aluno) {
                $gabarito_professor = explode(";", $novo_gabarito_professor["gabarito"]);
                $gabarito_aluno = explode(";", $prova_aluno["perguntas_respostas"]);
                $descritores_questoes = explode(";", $prova_aluno["descritores"]);

                $acertos_aluno = 0;
                $perguntas_certas = [];
                $perguntas_erradas = [];
                $descritores_corretos = [];
                $descritores_errados = [];

                foreach ($gabarito_professor as $index => $resposta_correta) {

                    if ($gabarito_aluno[$index] == $resposta_correta || strpos($resposta_correta, "null") !== false) {
                        $acertos_aluno++;
                        $perguntas_certas[] = $gabarito_aluno[$index];
                        $descritores_corretos[] = $descritores_questoes[$index];
                    } else {
                        $perguntas_erradas[] = $gabarito_aluno[$index];
                        $descritores_errados[] = $descritores_questoes[$index];
                    }
                }

                $valor_cada_pergunta = $novo_gabarito_professor["valor"] / count($gabarito_professor);
                $pontos_aluno = $valor_cada_pergunta * $acertos_aluno;

                $dados_atualizacao = [
                    "ra" => $prova_aluno["ra"],
                    "ID" => $prova_aluno["id"],
                    "ID_prova" => $id_prova,
                    "acertos" => $acertos_aluno,
                    "porcentagem" => ($acertos_aluno / count($gabarito_professor)) * 100,
                    "pontos_aluno" => $pontos_aluno,
                    "perguntas_certas" => implode(";", $perguntas_certas),
                    "perguntas_erradas" => implode(";", $perguntas_erradas),
                    "descritores_certos" => implode(";", $descritores_corretos),
                    "descritores_errados" => implode(";", $descritores_errados),
                    "pontos_prova" => $_POST['valor_prova'],
                ];

                ProfessorModel::atualizar_gabarito_aluno_rec($dados_atualizacao);
            }

            self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} editou a prova de recuperação {$nome_prova}. ");
            $_SESSION["PopUp_inserir_prova"] = true;
            header("location: professor_home");
            exit();

        } else {
            header("location: adm");
        }
    }

    public static function relatorio_professor()
    {
        if ($_SESSION["PROFESSOR"]) {
            $provas_professores = AlunoModel::GetProvas();
            $provas_alunos = AlunoModel::GetProvasFinalizadas();
            $_SESSION["PAG_VOLTAR"] = "professor_home";
            $provas = [];

            if ($provas_professores != null) {
                foreach ($provas_professores as $professor) {
                    if ($professor["nome_professor"] == $_SESSION["nome_professor"]) {
                        $provas[] = $professor;
                    }
                }

                foreach (ADModel::GetPeriodos() as $periodo) {
                    $nome_periodo = $periodo['nome'];
                    $data_inicial = $periodo['data_inicial'];
                    $data_final = $periodo['data_final'];
    
                    $provas_organizadas[$nome_periodo] = [];
    
                    foreach ($provas as $prova) {
                        $data_prova = $prova['data_prova'];
    
                        if ($data_prova >= $data_inicial && $data_prova <= $data_final) {
                            $provas_organizadas[$nome_periodo][] = $prova;
                        }
                    }
                } 
                $dados = [
                    "provas" => $provas_organizadas,
                    "provas_alunos" => $provas_alunos,
                ];

                // dd($dados["provas"]);

                foreach ($dados['provas'] as $periodo => $provass) {
                    usort($provass, function ($a, $b) {
                        return strtotime($b['data_prova']) - strtotime($a['data_prova']);
                    });
                    $dados['provas'][$periodo] = $provass;
                }

            }

            if ($provas == null || $provas_professores == null) {
                $dados = null;
            }

            MainController::Templates("public/views/professor/provas_relatorios.php", "PROFESSOR", $dados);
        } else {
            header("location: adm");
        }
    }

    public static function relatorio_prova()
    {
        if ($_SESSION["PROFESSOR"]) {

            if(!isset($_POST["id-prova"])){
                header("location: relatorio_professor");
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
                    $turmasString = $professor["turmas"];
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

            MainController::Templates("public/views/professor/relatorio_prova.php", "PROFESSOR", $dados);
        } else {
            header("location: adm");
        }
    }

    public static function calcular_descritores_por_aluno($alunos_por_turma)
    {
        $descritores_por_aluno = ["descritores" => [], "ALUNOS" => []];
        $alunos_para_processar = [];

        if (isset($_POST["filtrar"])) {
            $turma = $_POST["turma-filtros"];
            if ($turma != "geral" && isset($alunos_por_turma[$turma])) {
                $alunos_para_processar = $alunos_por_turma[$turma];
            } else {
                foreach ($alunos_por_turma as $turma_alunos) {
                    $alunos_para_processar = array_merge($alunos_para_processar, $turma_alunos);
                }
            }
        } else {
            foreach ($alunos_por_turma as $turma_alunos) {
                $alunos_para_processar = array_merge($alunos_para_processar, $turma_alunos);
            }
        }

        foreach ($alunos_para_processar as $aluno) {
            $descritores_aluno = [];
            $descritores_raw = explode(';', $aluno['descritores']);

            foreach ($descritores_raw as $index => $descritor_raw) {
                $partes_descritor = explode(',', $descritor_raw);
                $descritor = $partes_descritor[1];
                $descritores_aluno[$index + 1] = "ERROU";
                $descritores_por_aluno["descritores"][$index + 1] = $descritor;
            }

            $descritores_certos = explode(';', $aluno['descritores_certos']);
            foreach ($descritores_certos as $descritor_certos) {
                $partes_certos = explode(',', $descritor_certos);
                if (count($partes_certos) >= 2) {
                    $index_certo = array_search($descritor_certos, $descritores_raw) + 1;
                    if ($index_certo !== false) {
                        $descritores_aluno[$index_certo] = "ACERTOU";
                    }
                }
            }
            $descritores_por_aluno["ALUNOS"][$aluno["aluno"]] = $descritores_aluno;
        }

        ksort($descritores_por_aluno["descritores"]);

        if (empty($descritores_por_aluno["ALUNOS"])) {
            $descritores_por_aluno = null;
        }

        return $descritores_por_aluno;
    }

    public static function calcularPorcentagem($total, $parte)
    {
        if ($total == 0) {
            return 0;
        }
        return ($parte / $total) * 100;
    }

    public static function editar_prova()
    {

        if ($_SESSION["PROFESSOR"]) {
            $_SESSION["PAG_VOLTAR"] = "ver_provas";

            $id = $_POST["id-prova"];
            $_SESSION["ID_PROVA_EDITAR"] = $id;

            $prova_professor = ProfessorModel::GetProvabyID($id);
            $valor = $prova_professor["valor"];
            $perguntas = $prova_professor["QNT_perguntas"];

            $gabarito = explode(";", $prova_professor["gabarito"]);

            $descritores = $prova_professor["descritores"] != null
                ? explode(";", $prova_professor["descritores"])
                : [];

            $he_simulado = SimuladosModel::getSimuladoProva($prova_professor['id']);

            MainController::Templates("public/views/professor/editar_prova.php", "PROFESSOR", [
                "gabarito" => $gabarito,
                "descritores" => $descritores,
                "valor" => $valor,               
                "alternativas" => explode(",",$_ENV["ALTERNATIVAS"]),
                "perguntas" => $perguntas,
                "nome" => $prova_professor["nome_prova"],
                "metodo" => $prova_professor["metodo"],
                'he_simulado' => $he_simulado,
            ]);
        } else {
            header("location: adm");
        }
    }

    public static function atualizar_gabarito()
    {
        if ($_SESSION["PROFESSOR"]) {
            $id_prova = $_SESSION["ID_PROVA_EDITAR"];
            $provaEdit = ProfessorModel::GetProvabyID($id_prova);
            $nome_prova = $provaEdit["nome_prova"];
            $numero_perguntas = $_POST['numero_perguntas'];
            $descritor_flag = $_POST['descritor'];

            $gabarito_prova = [];
            $descritores_prova = [];

            for ($contador = 1; $contador <= $numero_perguntas; $contador++) {
                $descritores_prova[$contador] = $contador . "," . $_POST["DESCRITOR_" . $contador];
                $gabarito_prova[$contador] = base64_encode($_POST[$contador]);
            }

            $descritores = implode(";", $descritores_prova);
            if ($descritor_flag == "não") {
                $descritores = null;
            }

            $gabarito = implode(";", $gabarito_prova);

            $novo_gabarito_professor = [
                "descritores" => $descritores,
                "valor" => $_POST['valor_prova'],
                "gabarito" => $gabarito,
                "ID_prova" => $id_prova,
                "metodo" => $_POST['metodo'],
            ];

            ProfessorModel::atualizar_gabarito_professor($novo_gabarito_professor);

            $provas_alunos = ProfessorModel::GetProvasFeitasbyID($id_prova);

            foreach ($provas_alunos as $prova_aluno) {
                $gabarito_professor = [];
                $gabarito_crip = explode(";", $novo_gabarito_professor["gabarito"]);
                foreach($gabarito_crip as $resposta){
                    $gabarito_professor[] = base64_decode($resposta);
                }
                $gabarito_aluno = explode(";", $prova_aluno["perguntas_respostas"]);
                $descritores_questoes = $descritores_prova;

                $acertos_aluno = 0;
                $perguntas_certas = [];
                $perguntas_erradas = [];
                $descritores_corretos = [];
                $descritores_errados = [];

                foreach ($gabarito_professor as $index => $resposta_correta) {

                    if ($gabarito_aluno[$index] == $resposta_correta || strpos($resposta_correta, "null") !== false) {
                        $acertos_aluno++;
                        $perguntas_certas[] = $gabarito_aluno[$index];
                        $descritores_corretos[] = $descritores_questoes[$index + 1];
                    } else {
                        $perguntas_erradas[] = $gabarito_aluno[$index];
                        $descritores_errados[] = $descritores_questoes[$index + 1];
                    }
                }

                $valor_cada_pergunta = $novo_gabarito_professor["valor"] / count($gabarito_professor);
                $pontos_aluno = $valor_cada_pergunta * $acertos_aluno;

                $porcentagemm = ($acertos_aluno / $prova_aluno["QNT_perguntas"]) * 100;

                if($_POST['metodo'] == "ama"){
                    if ($porcentagemm >= 0 && $porcentagemm <= 25) {
                        $pontos_aluno = 4;
                    } elseif ($porcentagemm > 26 && $porcentagemm <= 50) {
                        $pontos_aluno = 6;
                    } elseif ($porcentagemm > 51 && $porcentagemm <= 75) {
                        $pontos_aluno = 8;
                    } elseif ($porcentagemm > 76 && $porcentagemm <= 100) {
                        $pontos_aluno = 10;
                    } 
                }else{
                    $pontos_aluno = $valor_cada_pergunta * $acertos_aluno;
                }
    
                if($prova_aluno["valor"] == 0){
                    $porcentagemm = ($acertos_aluno / $prova_aluno["QNT_perguntas"]) * 100;
                }else{
                    $porcentagemm = (number_format(round($pontos_aluno),0) / $prova_aluno["prova_gabarito"]["valor"]) * 100;
                }
    
                if (is_float($pontos_aluno)) {
                    $pontos_aluno = number_format($pontos_aluno, 1);
                }

                $dados_atualizacao = [
                    "ra" => $prova_aluno["ra"],
                    "ID" => $prova_aluno["id"],
                    "turma" => $prova_aluno["turma"],
                    "ID_prova" => $id_prova,
                    "acertos" => $acertos_aluno,
                    "porcentagem" => $porcentagemm,
                    "pontos_aluno" => number_format(round($pontos_aluno),0),
                    "pontos_aluno_quebrado" => number_format($pontos_aluno,1),
                    "descritores" => $descritores,
                    "perguntas_certas" => implode(";", $perguntas_certas),
                    "perguntas_respostas" => $prova_aluno["perguntas_respostas"],
                    "perguntas_erradas" => implode(";", $perguntas_erradas),
                    "descritores_certos" => implode(";", $descritores_corretos),
                    "descritores_errados" => implode(";", $descritores_errados),
                    "pontos_prova" => $_POST['valor_prova'],
                    "metodo" => $_POST['metodo'],
                ];

                ProfessorModel::atualizar_gabarito_aluno($dados_atualizacao);
                ProfessorModel::atualizar_gabarito_aluno_primeira($dados_atualizacao);
                
            }

            self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} editou a prova {$nome_prova}. ");
            header("location: professor_home");
            $_SESSION["PopUp_prova_editada"] = true;
            exit();

        } else {
            header("location: adm");
        }
    }

    public static function add_recuperacao()
    {
        $id = $_SESSION["id_prova_professor"];

        $provas_professores = AlunoModel::GetProvas();
        $_SESSION["dados_prova_rec"]["id"] = $_POST["id-prova"];
        $alunos = AlunoModel::GetAlunos();

        foreach ($provas_professores as $prova) {
            if ($prova["id"] == $id) {
                $turmas = explode(",", $prova["turmas"]);
            }
        }
        $turmas_alunos = [];

        foreach ($alunos as $aluno) {

            foreach ($turmas as $turma) {
                if ($aluno["turma"] == $turma) {
                    $turmas_alunos[$turma][$aluno["ra"]] = $aluno["nome"];
                }
            }
        }

        $turmas_truncated = [];

        foreach ($turmas_alunos as $turma => $alunos) {
            foreach ($alunos as $ra => $nome) {
                if (mb_strlen($nome) > 20) {
                    $nome = mb_substr($nome, 0, 17) . "...";
                }
                $turmas_truncated[$turma][$ra] = $nome;
            }
        }

        // echo "<pre>";
        // print_r($turmas_truncated);
        // echo "<pre>";

        $dados = [
            "alunos" => $turmas_truncated,
        ];

        MainController::Templates("public/views/professor/add_recuperacao.php", "PROFESSOR", $dados);

    }

    public static function inserir_gabarito_rec()
    {
        $alunos = implode(";", $_POST["alunos"]);
        $perguntas = $_POST["qtn-perguntas"];
        $descritores = $_POST["descritores"];
        $id_prova = $_SESSION["dados_prova_rec"]["id"];
        $_SESSION["dados_prova_rec"] = [
            "id" => $id_prova,
            "alunos" => $alunos,
            "perguntas" => $perguntas,
            "descritores" => $descritores,
        ];
        $_SESSION["GABARITO_REC"] = true;

        header("location: criar_gabarito_rec");
        // echo $alunos;
        // echo "<pre>";
        // print_r($_SESSION);
        // echo "<pre>";
    }

    public static function criar_gabarito_rec()
    {
        MainController::Templates("public/views/professor/criar_gabarito_rec.php", "PROFESSOR", null);
    }

    public static function criar_gabarito_rec_resp()
    {

        foreach (AlunoModel::GetProvas() as $prova) {
            if ($prova["id"] == $_SESSION["dados_prova_rec"]["id"]) {
                $nome_prova = $prova["nome_prova"];
                $materia = $prova["disciplina"];
                $valor = $prova["valor"];
            }
        }
        $dataHoraAtual = new DateTime();
        $dataFormatada = $dataHoraAtual->format('Y-m-d H:i:s');

        $descritores = '';
        $gabarito = '';

        $total_perguntas = $_SESSION["dados_prova_rec"]["perguntas"];

        for ($i = 1; $i <= $total_perguntas; $i++) {
            $descritor = ($_SESSION["dados_prova_rec"]["descritores"] == "sim") ? $_POST["DESCRITOR_$i"] : "";
            $descritores .= "$i,{$descritor};";
            $resposta = $_POST["$i"];
            list($numero_pergunta, $letra_resposta) = explode(",", $resposta);
            $gabarito .= "$numero_pergunta,$letra_resposta;";
        }

        $descritores = rtrim($descritores, ";");
        if ($_SESSION["dados_prova_rec"]["descritores"] != "sim") {
            $descritores = null;
        }
        $gabarito = rtrim($gabarito, ";");

        // echo $gabarito;
        // echo "<br>";
        // echo "<br>";
        // echo $descritores;

        $dados = [
            "id_prova" => $_SESSION["dados_prova_rec"]["id"],
            "alunos" => $_SESSION["dados_prova_rec"]["alunos"],
            "perguntas" => $_SESSION["dados_prova_rec"]["perguntas"],
            "valor" => $valor,
            "data" => $dataFormatada,
            "nome_prova" => $nome_prova,
            "descritores" => $descritores,
            "gabarito" => $gabarito,
            "nome_prof" => $_SESSION["nome_professor"],
            "materia" => $materia,
        ];

        $consulta = ProfessorModel::inserir_gabarito_recuperacao($dados);

        if ($consulta) {
            self::inserirLogsProfessor("O professor(a) {$_SESSION["nome_professor"]} adicionou de recuperação para a prova {$nome_prova}. ");
            $_SESSION["PopUp_inserir_gabarito_professor"] = true;
            header("location: ver_provas");
            exit();
        }

        // echo "<pre>";
        // print_r($dados);
        // echo "</pre>";

    }

    public static function inserirLogsProfessor($string)
    {
        $dados_logs = [
            "data" => date('Y-m-d H:i:s'),
            "autor" => "PROFESSOR",
            "descricao" => $string,
        ];

        $query = ADModel::adicionarLogsProfessor($dados_logs);

        return $query;
    }

    public static function download_gabarito()
    {   
        if (!$_SESSION["PROFESSOR"]) {
            header("location: adm");
        }
        
        $idProva = $_SESSION["ID_PROVA_EDITAR"];

        if (!$idProva) {
            header('Location: ' . $_SESSION['PAG_VOLTAR']);
            exit;
        }

        $prova = ProfessorModel::GetProvabyID($idProva);
        $orientacoes = $_POST['orientacoes'] ?? '';

        ob_start();

        include "public/views/professor/download_gabarito.php";

        $html_content = ob_get_contents();
        ob_end_clean();

        $test = true;
        $test = false;

        if ($test) {
            echo $html_content;
        } else {

            try {
                $options = [
                    'enable_remote' => true,
                    'defaultFont' => 'Arial',
                    'enable_php' => true,
                ];

                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html_content);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                $dompdf->stream("gabarito.pdf", [
                    "Attachment" => false
                ]);

            } catch (\Exception $exception) {
                dd($exception->getMessage());
            }
        }

        exit;
    }

    public static function lancar_gabarito() {

        if (!$_SESSION["PROFESSOR"]) {
            header("location: adm");
        }
        
        if (!isset($_GET['query_array']['prova_id'])) {
            header("location: {$_SESSION['PAG_VOLTAR']}");
        }

        $id_prova = $_GET['query_array']['prova_id'];
        $prova = ProfessorModel::GetProvabyID($id_prova);

        if ($_SESSION['nome_professor'] != $prova['nome_professor']) {
            $_SESSION["PopUp_Prova_outro_professor"] = true;
            header("location: {$_SESSION['PAG_VOLTAR']}");
            exit;
        }
        
        $turma = $prova['turmas'];
        $alunos = ProfessorModel::getAlunosByTurma($turma);

        $provas_finalizadas = ProfessorModel::getAlunosByProva($id_prova);
        $respostas_lancadas = !empty($provas_finalizadas) ? array_column($provas_finalizadas, 'aluno', 'ra') : [];

        $simulado = SimuladosModel::getSimulados(['prova_simulado' => $id_prova]);

        MainController::Templates("public/views/professor/lancar_gabarito.php", "PROFESSOR", [
            'prova'  => $prova,
            'alunos' => $alunos,
            'respotas_lancadas' => $respostas_lancadas,
            'simulado' => reset($simulado),
        ]);
    }

    public static function capturar_gabarito()
    {
        if (!$_SESSION["PROFESSOR"]) {
            header("location: adm");
        }

        if (!isset($_GET['query_array']['prova_id'], $_GET['query_array']['ra_aluno'])) {
            header("location: {$_SESSION['PAG_VOLTAR']}");
        }

        $id_prova = $_GET['query_array']['prova_id'];
        $prova = ProfessorModel::GetProvabyID($id_prova);
        $he_simulado = SimuladosModel::getSimuladoProva($id_prova);

        $provas_do_simulado = $he_simulado
            ? SimuladosModel::getProvasSimulado($he_simulado['id'])
            : [];

        $ra_aluno = $_GET['query_array']['ra_aluno'];
        $aluno = AlunoModel::GetAlunoByRa($ra_aluno);
        $ja_respondido = ProfessorModel::getProvaByRa($id_prova, $ra_aluno);

        $simulado = SimuladosModel::getSimulados(['prova_simulado' => $id_prova]);

        MainController::Templates("public/views/professor/capturar_gabarito.php", "PROFESSOR", [
            'prova'  => $prova,
            'aluno' => $aluno,
            'ja_respondido' => $ja_respondido[0] ?? [],
            'he_simulado' => $he_simulado ?? [],
            'provas_do_simulado' => $provas_do_simulado,
            'simulado' => reset($simulado),
        ]);
    }

    public static function salvar_gabarito() {
        if (!$_SESSION["PROFESSOR"]) {
            header("location: adm");
        }

        $prova_redirect = '';

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = $_POST;

            $prova_redirect = $dados['prova_id'];
            $aluno = AlunoModel::GetAlunoByRa($dados['ra_aluno']);

            $resultado = false;

            if(!empty($dados['simulado_id'])) {
                $provas = SimuladosModel::getProvasSimulado($dados['simulado_id']);

                foreach ($provas as $idx => $prova) {
                    $respostas = $dados['prova'][$idx]['perguntas_respostas'] ?? [];
                    $resultado = self::salvarRespostasAluno($aluno, $prova, $respostas);
                }
            } else {
                $prova = ProfessorModel::GetProvabyID($dados['prova_id']);
                $respostas = $dados['prova'][0]['perguntas_respostas'] ?? [];
                $resultado = self::salvarRespostasAluno($aluno, $prova, $respostas);
            }

            if (!$resultado) {
                $_SESSION["PopUp_Gabarito_Prova_Erro"] = true;
            } else {
                $_SESSION["PopUp_Gabarito_Prova"] = true;
            }
        }

        header("location: lancar_gabarito?prova_id={$prova_redirect}");
        exit;
    }

    private static function salvarRespostasAluno($aluno, $prova, $respostas)
    {
        $id_prova = $prova['gabarito_professor_id'] ?? $prova['id'];
        $ja_respondido = ProfessorModel::getProvaByRa($id_prova, $aluno['ra'])[0] ?? [];      
        $gabarito = explode(';', $prova['gabarito']);
        $descritores = $prova['descritores'] ? explode(';', $prova['descritores']) : [];
        $respostas_recebida = array_map(function($key, $value) {
            return "{$key},{$value}";
        }, array_keys($respostas), $respostas);

        $respostas_corretas = array_intersect($gabarito, $respostas_recebida);
        $respostas_erradas = array_diff($respostas_recebida, $gabarito);

        $descritores_corretos = array_intersect_key($descritores, $respostas_corretas);
        $descritores_errados = array_diff_key($descritores, $respostas_corretas);

        $total_questoes = count($gabarito);
        $total_acertos = count($respostas_corretas);
        $porcetagem_acerto = ($total_acertos / $total_questoes) * 100;
        $ponto_questao = $prova['valor'] / $total_questoes;

        $resultado = false;
        if($ja_respondido) {
            $update = [
                'acertos' => $total_acertos,
                'porcentagem' => $porcetagem_acerto,
                'turma' => $aluno['turma'],
                'pontos_aluno' => round($ponto_questao * $total_acertos),
                'pontos_aluno_quebrado' => ($ponto_questao * $total_acertos),
                'perguntas_certas' => implode(';', $respostas_corretas),
                'perguntas_respostas' => implode(';', $respostas),
                'perguntas_erradas' => implode(';', $respostas_erradas),
                'descritores' => $prova['descritores'],
                'descritores_certos' => implode(';', $descritores_errados),
                'descritores_errados' => implode(';', $descritores_errados),
                'pontos_prova' => $prova['valor'],
                'ID' => $ja_respondido['id'],
                'ID_prova' => $id_prova,
            ];
            $resultado = ProfessorModel::atualizar_gabarito_aluno($update);
        } else {
            $insert = [
                'aluno' => $aluno['nome'],
                'ra' => $aluno['ra'],
                'turma' => $aluno['turma'],
                'turno' => $aluno['turno'],
                'id_prova' => $id_prova,
                'serie' => str_split($prova['turmas'],1)[0],
                'nome_professor' => $prova['nome_professor'],
                'descritores' => $prova['descritores'],
                'disciplina' => $prova['disciplina'],
                'nome_prova' => $prova['nome_prova'],
                'pontos_prova' => $prova['valor'],
                'QNT_perguntas' => $prova['QNT_perguntas'],
                'data_aluno' => $prova['data_prova'],
                'acertos' => $total_acertos,
                'porcentagem' => $porcetagem_acerto,
                'pontos_aluno' => round($ponto_questao * $total_acertos),
                'pontos_aluno_quebrado' => ($ponto_questao * $total_acertos),
                'perguntas_respostas' => implode(';', $respostas),
                'perguntas_certas' => implode(';', $respostas_corretas),
                'perguntas_erradas' => implode(';', $respostas_erradas),
                'descritores_certos' => implode(';', $descritores_errados),
                'descritores_errados' => implode(';', $descritores_errados),
                'recuperacao' => '',
                'status' => 'Fez a 1º Prova',
            ];
            $resultado = ProfessorModel::inserir_gabarito_aluno($insert);
        }

        return $resultado;
    }

}
