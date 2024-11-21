<?php

namespace app\controllers\monitoramento;

use app\config\Queryy;
use app\controllers\monitoramento\MainController;
use app\models\monitoramento\ADModel;
use app\models\monitoramento\AlunoModel;
use app\models\monitoramento\ProfessorModel;
use DateTime;

class AlunoController
{

    public static function login_aluno_entrar()
    {
        $ra = $_POST["ra"];
        $ra = preg_replace('/\D/', '', $ra);

        $query = AlunoModel::verificarLogin($ra);

        if ($query !== false) {
            $_SESSION["ra"] = $ra;
            $_SESSION["nome_aluno"] = $query["nome"];
            $_SESSION["turno"] = $query["turno"];
            $_SESSION["turma"] = $query["turma"];
            $_SESSION["data_nasc"] = $query["data_nasc"];
            $_SESSION["ALUNO"] = true;
            header("location: aluno_home");
            exit();
        } else {
            $_SESSION["PopUp_RA_NaoENC"] = true;
            header("location: home");
            exit();
        }
    }

    public static function aluno_home()
    {
        if ($_SESSION["ALUNO"]) {

            $dados = AlunoModel::GetProvas();
            $provas_feitas = AlunoModel::GetProvasFinalizadas();
            // $provas_rec = ProfessorModel::GetProvaRec();
            // $provas_rec_feitas = ProfessorModel::GetProvaRecAlunos();
            $provas_aluno = [];
            $provas_aluno_feitas = [];

            if ($dados != null) {
                foreach ($dados as $prova) {
                    if (strpos($prova["turmas"], ",")) {
                        $turmas = explode(",", $prova["turmas"]);
                        foreach ($turmas as $turma) {
                            if ($turma == $_SESSION["turma"]) {
                                $provas_aluno[] = $prova;
                            }
                        }
                    } else {
                        if ($prova["turmas"] == $_SESSION["turma"]) {
                            $provas_aluno[] = $prova;
                        }
                    }
                }
            } else {
                $provas_aluno = null;
            }

            $provas_aluno_feitas = [];

            if ($provas_feitas != null) {
                foreach ($provas_feitas as $prova) {
                    if ($prova["ra"] == $_SESSION["ra"]) {
                        $provas_aluno_feitas[] = $prova;
                    }
                }
            }

            // echo "<pre>";
            // print_r($provas_aluno);
            // echo "</pre>";

            // dump($provas_aluno_feitas);

            // dd(ADModel::GetPeriodos());

            $provas_organizadas = [];

            foreach (ADModel::GetPeriodos() as $periodo) {
                $nome_periodo = $periodo['nome'];
                $data_inicial = $periodo['data_inicial'];
                $data_final = $periodo['data_final'];

                $provas_organizadas[$nome_periodo] = [];

                foreach ($provas_aluno_feitas as $prova) {
                    $data_prova = $prova['data_aluno'];

                    if ($data_prova >= $data_inicial && $data_prova <= $data_final) {
                        $provas_organizadas[$nome_periodo][] = $prova;
                    }
                }
            }

            // dd($provas_organizadas);

            MainController::Templates("public/views/aluno/home.php", "ALUNO", [
                "periodos" => ADModel::GetPeriodos(),
                "provas" => $provas_aluno,
                "provas_organizadas" => $provas_organizadas,
                "alternativas" => explode(",",$_ENV["ALTERNATIVAS"]),
                "provas_feitas" => $provas_aluno_feitas
                
            ]);
        } else {
            header("location: home");
        }
    }

    public static function gabarito_aluno_rec()
    {
        if ($_SESSION["ALUNO"]) {
            $id = $_POST["id-prova"];
            $dados = ProfessorModel::GetProvaRec();

            foreach ($dados as $prova) {
                if ($prova["id"] == $id) {
                    $_SESSION["prova_gabarito"] = $prova;
                    MainController::Templates("public/views/aluno/gabarito_rec.php", "ALUNO", $prova);
                }
            }
        } else {
            header("location: home");
        }

    }

    /*
    public static function cadastrar_gabarito_aluno_rec()
    {
        if ($_SESSION["ALUNO"]) {
            $gabarito_professor = explode(";", $_SESSION["prova_gabarito"]["gabarito"]);
            $gabarito_aluno = [];
            $perguntas_certas = [];
            $perguntas_erradas = [];
            $perguntas_respostas = [];
            $descritores_corretos = [];
            $descritores_errados = [];
            $acertos_aluno = 0;
            $dataAtual = new DateTime();
            $dataFormatada = $dataAtual->format('Y-m-d');
            $valor_cada_pergunta = $_SESSION["prova_gabarito"]["valor"] / $_SESSION["prova_gabarito"]["QNT_perguntas"];
            $descritores_questoes = explode(";", $_SESSION["prova_gabarito"]["descritores"]);

            $contador = 1;
            while ($contador <= $_SESSION["prova_gabarito"]["QNT_perguntas"]) {
                $gabarito_aluno[] = $_POST["gabarito_questao_" . $contador];
                $contador++;
            }

            $contador = 0;

            echo "<pre>";
            print_r($gabarito_professor);
            echo "</pre>";

            while ($contador < $_SESSION["prova_gabarito"]["QNT_perguntas"]) {
                // echo "<br>";
                // echo $_SESSION["prova_gabarito"]["QNT_perguntas"];
                // echo "<br>";
                // echo $gabarito_aluno[$contador];
                // echo "<br>";
                // echo $gabarito_professor[$contador];
                // echo "<br>";

                if ($gabarito_aluno[$contador] == $gabarito_professor[$contador]) {
                    $descritores_corretos[] = $descritores_questoes[$contador];
                    $acertos_aluno++;
                    $perguntas_certas[] = $gabarito_aluno[$contador];
                    $perguntas_respostas[] = $gabarito_aluno[$contador];
                } else {
                    $descritores_errados[] = $descritores_questoes[$contador];
                    $perguntas_respostas[] = $gabarito_aluno[$contador];
                    $perguntas_erradas[] = $gabarito_aluno[$contador];
                }

                $contador++;
            }

            if ($_SESSION["prova_gabarito"]["id"] == null) {
                $descritores_corretos = null;
                $descritores_errados = null;
            } else {
                $descritores_corretos = implode(";", $descritores_corretos);
                $descritores_errados = implode(";", $descritores_errados);
            }

            if ($_SESSION["prova_gabarito"]["descritores"] == null) {
                $descritores_corretos = null;
                $descritores_errados = null;
            }

            $pontos_aluno = $valor_cada_pergunta * $acertos_aluno;
            if (is_float($pontos_aluno)) {
                $pontos_aluno = number_format($pontos_aluno, 1);
            }

            $dados = [
                "aluno" => $_SESSION["nome_aluno"],
                "ra" => $_SESSION["ra"],
                "turma" => $_SESSION["turma"],
                "id_prova_rec" => $_SESSION["prova_gabarito"]["id"],
                "id_prova" => $_SESSION["prova_gabarito"]["id_prova"],
                "nome_professor" => $_SESSION["prova_gabarito"]["nome_professor"],
                "descritores" => $_SESSION["prova_gabarito"]["descritores"],
                "disciplina" => $_SESSION["prova_gabarito"]["disciplina"],
                "nome_prova" => $_SESSION["prova_gabarito"]["nome_prova"],
                "pontos_prova" => $_SESSION["prova_gabarito"]["valor"],
                "QNT_perguntas" => $_SESSION["prova_gabarito"]["QNT_perguntas"],
                "turno" => $_SESSION["turno"],
                "porcentagem" => number_format(($acertos_aluno / $_SESSION["prova_gabarito"]["QNT_perguntas"]) * 100, 1),
                "serie" => substr($_SESSION["turma"], 0, 1),
                "data_aluno" => $dataFormatada,
                "acertos" => $acertos_aluno,
                "pontos_aluno" => $pontos_aluno,
                "perguntas_certas" => implode(";", $perguntas_certas),
                "perguntas_respostas" => implode(";", $perguntas_respostas),
                "perguntas_erradas" => implode(";", $perguntas_erradas),
                "descritores_certos" => $descritores_corretos,
                "descritores_errados" => $descritores_errados,
            ];

            echo "<pre>";
            print_r($dados);
            echo "</pre>";

            self::processar_rec($dados);

            // if(AlunoModel::Inserir_dados_prova($dados)){
            //     $_SESSION["PopUp_inserir_prova"] = True;
            //     header("location:aluno_home");
            // }
        } else {
            header("location: home");
        }
    }

    public static function processar_rec($prova_rec)
    {
        $provas = AlunoModel::GetProvasFinalizadas();

        $prova_feita = null;
        foreach ($provas as $prova) {
            if ($prova["id_prova"] == $prova_rec["id_prova"] && $prova["ra"] == $prova_rec["ra"]) {
                $prova_feita = $prova;
            }
        }

        echo $prova_rec["id_prova"];
        if ($prova_feita == null) {
            $status = "Fez só a recuperação";
            AlunoModel::Inserir_dados_prova_rec($prova_rec);
            AlunoModel::Inserir_dados_prova($prova_rec, $status);
            AlunoModel::SetRecAluno($prova_rec["id_prova"], $prova_rec["ra"]);
            $_SESSION["PopUp_inserir_gabarito_professor"] = true;
            header("location: aluno_home");
            exit();

        } else if ($prova_feita != null) {

            if ($prova_feita["pontos_aluno"] == $prova_rec["pontos_aluno"]) {

                $status = "Recuperação: mesma nota da 1º prova";
                AlunoModel::Inserir_dados_prova_rec($prova_rec);
                AlunoModel::UpdateStatusAluno($prova_rec["ra"], $prova_rec["id_prova"], $status);
                AlunoModel::SetRecAluno($prova_feita["id_prova"], $prova["ra"]);
                $_SESSION["PopUp_inserir_gabarito_professor"] = true;
                header("location: aluno_home");
                exit();

            } else if ($prova_feita["pontos_aluno"] > $prova_rec["pontos_aluno"]) {

                $status = "Recuperação: nota menor que a 1º prova";
                AlunoModel::Inserir_dados_prova_rec($prova_rec);
                AlunoModel::SetRecAluno($prova["id_prova"], $prova["ra"]);
                AlunoModel::UpdateStatusAluno($prova_rec["ra"], $prova_rec["id_prova"], $status);

                $_SESSION["PopUp_inserir_gabarito_professor"] = true;
                header("location: aluno_home");
                exit();

            } else if ($prova_feita["pontos_aluno"] < $prova_rec["pontos_aluno"]) {
                $status = "Recuperação: nota maior que a 1º prova";
                AlunoModel::Inserir_dados_prova_rec($prova_rec);

                $Update = [
                    "QNT_perguntas" => $prova_rec["QNT_perguntas"],
                    "porcentagem" => $prova_rec["porcentagem"],
                    "acertos" => $prova_rec["acertos"],
                    "pontos_aluno" => $prova_rec["pontos_aluno"],
                    "perguntas_respostas" => $prova_rec["perguntas_respostas"],
                    "perguntas_certas" => $prova_rec["perguntas_certas"],
                    "perguntas_erradas" => $prova_rec["perguntas_erradas"],
                    "descritores_certos" => $prova_rec["descritores_certos"],
                    "descritores_errados" => $prova_rec["descritores_errados"],
                    "status" => $status,
                    "ra" => $prova_rec["ra"],
                    "recuperacao" => "FEZ RECUPERAÇÂO",
                    "id_prova" => $prova_rec["id_prova"],
                ];

                AlunoModel::UpdateGabaritoAluno($Update);

                $_SESSION["PopUp_inserir_gabarito_professor"] = true;
                header("location: aluno_home");
                exit();

            }
        }
    }
*/
    
    public static function gabarito_aluno()
    {
        if ($_SESSION["ALUNO"]) {

            $dados = AlunoModel::GetProvas();

            if (isset($_POST["id-prova"])) {
                $id = $_POST["id-prova"];
                $_SESSION["id_prova_professor"] = $_POST["id-prova"];
            } else {
                $id = $_SESSION["id_prova_professor"];
            }

            foreach ($dados as $prova) {
                if ($prova["id"] == $id) {
                    $_SESSION["prova_gabarito"] = $prova;
                    $dados = [
                        "prova" => $prova,
                        "alternativas" => explode(",",$_ENV["ALTERNATIVAS"])
                    ];
                    MainController::Templates("public/views/aluno/gabarito.php", "ALUNO", $dados);
                }
            }

            if (isset($_POST["enviar_gabarito_aluno"])) {
                sleep(3);
                self::cadastrar_gabarito_aluno();
            }

        } else {
            header("location: home");
        }

    }

    public static function cadastrar_gabarito_aluno()
    {
        if ($_SESSION["ALUNO"]) {

            
            // $provas_alunos_verificar = AlunoModel::GetProvasFinalizadas();

            $provas_alunos_verificar = Queryy::select("gabarito_alunos","ra = {$_SESSION["ra"]}");

            if($provas_alunos_verificar != null){

                foreach ($provas_alunos_verificar as $prov) {
                    if ($prov["id_prova"] == $_SESSION["prova_gabarito"]["id"]) {
                        $_SESSION["PopUp_Prova_Feita"] = true;
                        // MainController::pre($_SESSION["prova_gabarito"]);
                        header("location:aluno_home");
                        exit();
                    }
                }
            }

            $gabarito_professor = [];
            $gabarito_crip = explode(";", $_SESSION["prova_gabarito"]["gabarito"]);
            foreach($gabarito_crip as $resposta){
                $gabarito_professor[] = base64_decode($resposta);
            }
            $gabarito_aluno = [];
            $perguntas_certas = [];
            $perguntas_erradas = [];
            $perguntas_respostas = [];
            $descritores_corretos = [];
            $descritores_errados = [];
            $acertos_aluno = 0;
            $dataAtual = new DateTime();
            $dataFormatada = $dataAtual->format('Y-m-d');
            $valor_cada_pergunta = $_SESSION["prova_gabarito"]["valor"] / $_SESSION["prova_gabarito"]["QNT_perguntas"];
            $descritores_questoes = explode(";", $_SESSION["prova_gabarito"]["descritores"]);
            
            $contador = 1;
            while ($contador <= $_SESSION["prova_gabarito"]["QNT_perguntas"]) {
                $gabarito_aluno[] = $_POST["gabarito_questao_" . $contador];
                $contador++;
            }
            
            $contador = 0;

            while ($contador < $_SESSION["prova_gabarito"]["QNT_perguntas"]) {
                if ($gabarito_aluno[$contador] == $gabarito_professor[$contador]) {
                    $descritores_corretos[] = $descritores_questoes[$contador];
                    $acertos_aluno++;
                    $perguntas_certas[] = $gabarito_aluno[$contador];
                    $perguntas_respostas[] = $gabarito_aluno[$contador];
                } else {
                    $descritores_errados[] = $descritores_questoes[$contador];
                    $perguntas_respostas[] = $gabarito_aluno[$contador];
                    $perguntas_erradas[] = $gabarito_aluno[$contador];
                }

                $contador++;
            }

            if ($_SESSION["prova_gabarito"]["id"] == null) {
                $descritores_corretos = null;
                $descritores_errados = null;
            } else {
                $descritores_corretos = implode(";", $descritores_corretos);
                $descritores_errados = implode(";", $descritores_errados);
            }
            
            $porcentagemm = ($acertos_aluno / $_SESSION["prova_gabarito"]["QNT_perguntas"]) * 100;

            if($_SESSION["prova_gabarito"]["metodo"] == "ama"){
                if ($porcentagemm >= 0 && $porcentagemm <= 25.0) {
                    $pontos_aluno = 4;
                } elseif ($porcentagemm > 25.0 && $porcentagemm <= 50.0) {
                    $pontos_aluno = 6;
                } elseif ($porcentagemm > 50.0 && $porcentagemm <= 75.0) {
                    $pontos_aluno = 8;
                } elseif ($porcentagemm > 75.0 && $porcentagemm <= 100.0) {
                    $pontos_aluno = 10;
                } 
            }else{
                $pontos_aluno = $valor_cada_pergunta * $acertos_aluno;
            }

            if($_SESSION["prova_gabarito"]["valor"] == 0){
                $porcentagemm = ($acertos_aluno / $_SESSION["prova_gabarito"]["QNT_perguntas"]) * 100;
            }else{
                $porcentagemm = (number_format(round($pontos_aluno),0) / $_SESSION["prova_gabarito"]["valor"]) * 100;
            }

            if (is_float($pontos_aluno)) {
                $pontos_aluno = number_format($pontos_aluno, 1);
            }
            
            $dados = [
                "aluno" => $_SESSION["nome_aluno"],
                "ra" => $_SESSION["ra"],
                "turma" => $_SESSION["turma"],
                "id_prova" => $_SESSION["prova_gabarito"]["id"],
                "nome_professor" => $_SESSION["prova_gabarito"]["nome_professor"],
                "descritores" => $_SESSION["prova_gabarito"]["descritores"],
                "disciplina" => $_SESSION["prova_gabarito"]["disciplina"],
                "nome_prova" => $_SESSION["prova_gabarito"]["nome_prova"],
                "pontos_prova" => $_SESSION["prova_gabarito"]["valor"],
                "metodo" => $_SESSION["prova_gabarito"]["metodo"],
                "QNT_perguntas" => $_SESSION["prova_gabarito"]["QNT_perguntas"],
                "turno" => $_SESSION["turno"],
                "porcentagem" => $porcentagemm,
                "serie" => substr($_SESSION["turma"], 0, 1),
                "data_aluno" => $dataFormatada,
                "acertos" => $acertos_aluno,
                "pontos_aluno" => number_format(round($pontos_aluno),0),
                "pontos_aluno_quebrado" => number_format($pontos_aluno,1),
                "perguntas_certas" => implode(";", $perguntas_certas),
                "perguntas_respostas" => implode(";", $perguntas_respostas),
                "perguntas_erradas" => implode(";", $perguntas_erradas),
                "descritores_certos" => $descritores_corretos,
                "descritores_errados" => $descritores_errados,
                "status" => "Fez a 1º Prova",
            ];


            if (AlunoModel::Inserir_dados_prova_1_prova($dados)) {
                AlunoModel::Inserir_dados_prova($dados, "Fez a 1º Prova");
                $_SESSION["PopUp_inserir_gabarito_professor"] = true;
                header("location:aluno_home");
                exit();
            }
        } else {
            header("location: home");
        }
    }

}
