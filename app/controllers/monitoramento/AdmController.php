<?php

namespace app\controllers\monitoramento;

use app\controllers\monitoramento\MainController;
use app\models\monitoramento\ADModel;
use app\models\monitoramento\AlunoModel;
use app\models\monitoramento\ProfessorModel;

class AdmController
{

    public static function login_adm_verifica()
    {

        if ($_POST["campo_adm"] == $_ENV["SENHA_ADM"]) {
            $_SESSION["ADM"] = true;
            header("location:adm_home");
        } else {
            $_SESSION["PopUp_PRF_NaoENC"] = true;
            header("location: login_adm");
            exit;
        }
    }

    public static function backups()
    {
        $diretorio = 'app/config/backups/';
        $arquivos = scandir($diretorio);
        $dados = [];

        foreach ($arquivos as $arquivo) {
            if ($arquivo != "index.php" && preg_match('/(\d{4}-\d{2}-\d{2})__(\d{2})-(\d{2})/', $arquivo, $matches)) {
                $data = isset($matches[1]) ? $matches[1] : 'Data não disponível';
                $hora = isset($matches[2]) ? $matches[2] : 'Hora não disponível';
                $minuto = isset($matches[3]) ? $matches[3] : 'Minuto não disponível';

                $data_formatada = date('d/m/Y', strtotime($data));

                $caminho_arquivo = $diretorio . $arquivo;

                $timestamp = strtotime("$data $hora:$minuto:00");

                $tamanho_arquivo_bytes = filesize($caminho_arquivo);

                $tamanho_formatado = '';
                $unidades = array('B', 'KB', 'MB', 'GB', 'TB');
                for ($i = 0; $tamanho_arquivo_bytes >= 1024 && $i < 4; $i++) {
                    $tamanho_arquivo_bytes /= 1024;
                }
                $tamanho_formatado = round($tamanho_arquivo_bytes, 2) . ' ' . $unidades[$i];

                $dados[] = [
                    'data' => $data_formatada,
                    'hora' => $hora,
                    'minuto' => $minuto,
                    'tamanho' => $tamanho_formatado,
                    'arquivo' => $arquivo,
                    'timestamp' => $timestamp,
                ];
            }
        }

        usort($dados, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        return $dados;
    }

    public static function adm_home()
    {
        if (MainController::Verificar_sessao("ADM")) {

            self::Get_forms();

            $dados = [
                "alunos" => [
                    "provas_feitas" => ADModel::GetProvas(),
                    "alunos" => AlunoModel::GetAlunos(),
                ],
                "turmas" => [
                    "turmas" => ADModel::GetTurmas(),
                ],
                "turnos" => explode(",", $_ENV["TURNOS"]),
                "cursos" => explode(",", $_ENV["CURSOS"]),
                "NTurmas" => explode(",", $_ENV["NUMERO_TURMAS"]),
                "Nseries" => explode(",", $_ENV["NUMEROS_SERIES"]),
                "periodos" => ADModel::GetPeriodos(),
                "disciplinas" => ADModel::GetDisciplinas(),
                "logsADM" => ADModel::GetLogsADM(),
                "logsPROF" => ADModel::GetLogsProfessor(),
                "PFAs" => ADModel::GetPFA(),
                "backups" => self::backups(),
                "professores" => ADModel::GetProfessores(),
            ];

            MainController::Templates("public/views/adm/home.php", "ADM", $dados);
        } else {
            header("location: home");
        }
    }

    public static function Get_forms()
    {

        if (isset($_POST["Enviar-periodo"])) {
            $dataAtual = new \DateTime();
            $dataFormatada = $dataAtual->format('Y-m-d H:i:s');

            $dados = [
                "nome" => $_POST["NomePeriodo"],
                "dataInicial" => $_POST["dataInicial"],
                "dataFinal" => $_POST["dataFinal"],
                "data" => $dataFormatada,
            ];
            if (ADModel::adicionarPeriodo($dados)) {
                self::inserirLogsADM("Foi adicionado o período {$dados["nome"]}.");
                $_SESSION["PopUp_add_periodo"] = true;
                header("location: adm_home");
                exit();
            }

        }

        if (isset($_POST["excluir-periodo"])) {
            $periodo = explode(";", $_POST["excluir-periodo"]);
            if (ADModel::ExcluirPeriodo($periodo[0])) {

                self::inserirLogsADM("O período {$periodo[1]} foi excluído.");
                $_SESSION["PopUp_excluir_periodo"] = true;
                header("location: adm_home");
                exit();
            }
        }

        if (isset($_POST["excluir-pfa"])) {
            $PFA = explode(";", $_POST["excluir-pfa"]);
            if (ADModel::ExcluirPFA($PFA[0])) {

                self::inserirLogsADM("O PFA {$PFA[1]} foi excluído.");
                $_SESSION["PopUp_Excluir_PFA"] = true;
                header("location: adm_home");
                exit();
            }
        }

        if (isset($_POST["enviar-prova-editada"])) {

            $contador = 1;
            $perguntas = [];

            $prova = ProfessorModel::GetProvabyID($_POST["id_prova"]);


            while ($contador <= $prova["QNT_perguntas"]) {
                    $perguntas[] = $_POST["gabarito_questao_" . $contador];
                    $contador++;
            }

            $dadosProva = [
                "gabarito" => implode(";", $perguntas),
                "id_prova" => $_POST["id_prova"],
                "ra" => $_POST["ra"],
                "nome" => $_POST["nome_aluno_prova"],
                "id" => $_POST["id_aluno_prova"],
                "turma" => $_POST["turmas_prova"]
            ];

            MainController::pre($dadosProva);

            self::alterarProvaAluno($dadosProva);
        }

        if (isset($_POST["enviar-turma-add"])) {

            $dados_turma = [
                "serie" => $_POST["serie_turma"],
                "turno" => $_POST["turno_adicionar"],
                "curso" => $_POST["curso_turma"],
                "numero" => $_POST["numero_turma"],
            ];

            if ($_POST["nomeTurma"] == null) {
                $dados_turma["nome"] = self::formarNomeTurma($dados_turma);
            } else {
                $dados_turma["nome"] = $_POST["nomeTurma"];
            }

            if (ADModel::AdicionarTurma($dados_turma)) {
                self::inserirLogsADM("Foi adicionado a turma {$dados_turma["nome"]}.");
                $_SESSION["PopUp_inserir_turma"] = true;
                header("location: adm_home");
                exit();
            }

        }

        if (isset($_POST["excluir-turma"])) {
            $turma = explode(";", $_POST["excluir-turma"]);
            if (ADModel::ExcluirTurma($turma[0])) {

                self::inserirLogsADM("A turma {$turma[1]} foi excuída.");
                $_SESSION["PopUp_Excluir_turma"] = true;
                header("location: adm_home");
                exit();
            }
        }

        if (isset($_POST["salvarPFA"])) {
            
            $dados = [
                "nome" => $_POST["nome"],
                "usuario" => $_POST["user"],
                "senha" => $_POST["senha"],
                "disciplina" => $_POST["disciplinaPFA"],
                "turno" => $_POST["TurnoPFA"]
            ];

            if (ADModel::inserirPFA($dados)) {

                self::inserirLogsADM("O usuario de PFA {$dados["nome"]} foi adicionando.");
                $_SESSION["PopUp_Inserir_PFA"] = true;
                header("location: adm_home");
                exit();
            }
        }

        if (isset($_POST["excluir-prova-aluno"])) {
            $prova = explode(";", $_POST["excluir-prova-aluno"]);
            if (ADModel::ExcluirProva($prova[1], $prova[0])) {

                self::inserirLogsADM("A Prova do aluno(a) {$prova[2]} da materia {$prova[3]} Foi Excluída.");
                $_SESSION["PopUp_excluir_prova"] = true;
                header("location: adm_home");
                exit();
            }
        }

        if (isset($_POST["excluir_professor"])) {
            $professor = explode(";", $_POST["excluir_professor"]);
            if (ADModel::ExcluirProfessor($professor[0])) {

                self::inserirLogsADM("O professor {$professor[1]} foi excluído.");

                $_SESSION["PopUp_excluir_professor"] = true;
                header("location: adm_home");
                exit();
            }
        }

        if (isset($_POST["excluir-aluno"])) {
            $aluno = explode(";", $_POST["excluir-aluno"]);
            if (ADModel::ExcluirAluno($aluno[0])) {

                self::inserirLogsADM("O aluno(a) {$aluno[1]} foi excluído.");

                $_SESSION["PopUp_Excluir_aluno"] = true;
                header("location: adm_home");
                exit();
            }
        }

        if (isset($_POST["Enviar-edit-professor"])) {

            if ($_POST["disciplina_professor_editar"] == null) {
                $_SESSION["PopUp_not_Materia"] = true;
                header("location: adm_home");
                exit();
            }

            $dados_editar_professor = [
                "id" => $_POST["id"],
                "nome" => $_POST["nome_professor"],
                "usuario" => explode(" ", $_POST["nome_professor"])[0],
                "senha" => $_POST["senha_acesso"],
                "numero" => $_POST["usuario_acesso"],
                "disciplinas" => implode(";", $_POST["disciplina_professor_editar"]),
            ];

            if (ADModel::EditarProfessor($dados_editar_professor)) {

                self::inserirLogsADM("O professor {$dados_editar_professor["nome"]} foi Editado.");

                $_SESSION["PopUp_editar_professor"] = true;
                header("location: adm_home");
                exit();
            }
        }

        if (isset($_POST["Enviar-professor"])) {

            if ($_POST["disciplina_professor"] == null) {
                $_SESSION["PopUp_not_Materia"] = true;
                header("location: adm_home");
                exit();
            }

            $dados_inserir_professor = [
                "nome" => $_POST["nome_professor"],
                "usuario" => explode(" ", $_POST["nome_professor"])[0],
                "senha" => $_POST["senha_acesso"],
                "numero" => $_POST["usuario_acesso"],
                "disciplinas" => implode(";", $_POST["disciplina_professor"]),
            ];

            if (ADModel::AdicionarProfessor($dados_inserir_professor)) {

                self::inserirLogsADM("O professor {$dados_inserir_professor["nome"]} foi Adicionado.");
                $_SESSION["PopUp_add_professor_true"] = true;
                header("location: adm_home");
                exit();
            }
        }

        if (isset($_POST["Enviar-materia"])) {
            if (ADModel::AdicionarDisciplina($_POST["nomeMateria"])) {

                self::inserirLogsADM("A matéria {$_POST["nomeMateria"]} foi Adicionada.");
                $_SESSION["PopUp_add_materia_true"] = true;
                header("location: adm_home");
                exit();
            }
        }

        if (isset($_POST["excluir-materia"])) {
            $materia = explode(";", $_POST["excluir-materia"]);
            if (ADModel::ExcluirDisciplina($materia[0])) {

                self::inserirLogsADM("A matéria {$materia[1]} foi excluída.");

                $_SESSION["PopUp_excluir_materia_true"] = true;
                header("location: adm_home");
                exit();
            }
        }
    }

    public static function alterarProvaAluno($dados)
    {
        // MainController::pre($dados);
        $prova = ProfessorModel::GetProvabyID($dados["id_prova"]);

        $gabarito_professor = explode(";", $prova["gabarito"]);
        $gabarito_aluno = explode(";", $dados["gabarito"]);
        $perguntas_certas = [];
        $perguntas_erradas = [];
        $perguntas_respostas = [];
        $descritores_corretos = [];
        $descritores_errados = [];
        $acertos_aluno = 0;
        $valor_cada_pergunta = $prova["valor"] / $prova["QNT_perguntas"];
        $descritores_questoes = $prova["descritores"] != null ? explode(";", $prova["descritores"]) : null;


        $contador = 0;
        MainController::pre($descritores_questoes);
        while ($contador < $prova["QNT_perguntas"]) {
            if ($gabarito_aluno[$contador] == $gabarito_professor[$contador]) {
                $descritores_corretos[] = $descritores_questoes != null ? $descritores_questoes[$contador] : null;
                $acertos_aluno++;
                $perguntas_certas[] = $gabarito_aluno[$contador];
                $perguntas_respostas[] = $gabarito_aluno[$contador];
            } else {
                $descritores_errados[] = $descritores_questoes != null ? $descritores_questoes[$contador] : null;
                $perguntas_respostas[] = $gabarito_aluno[$contador];
                $perguntas_erradas[] = $gabarito_aluno[$contador];
            }


            $contador++;
        }

        if ($prova["id"] == null) {
            $descritores_corretos = null;
            $descritores_errados = null;
        } else {
            $descritores_corretos = implode(";", $descritores_corretos);
            $descritores_errados = implode(";", $descritores_errados);
        }

        $pontos_aluno = $valor_cada_pergunta * $acertos_aluno;
        if (is_float($pontos_aluno)) {
            $pontos_aluno = number_format($pontos_aluno, 1);
        }

        $dados_atualizacao = [
            "ra" => $dados["ra"],
            "ID" => $dados["id"],
            "ID_prova" => $dados["id_prova"],
            "acertos" => $acertos_aluno,
            "descritores" => $prova["descritores"],
            "turma" => $dados["turma"],
            "porcentagem" => (number_format(round($pontos_aluno),0) / $prova["valor"]) * 100,
            "pontos_aluno" => number_format(round($pontos_aluno),0),
            "pontos_aluno_quebrado" => number_format($pontos_aluno,1),
            "perguntas_respostas" => $dados["gabarito"],
            "perguntas_certas" => implode(";", $perguntas_certas),
            "perguntas_erradas" => implode(";", $perguntas_erradas),
            "descritores_certos" => $descritores_questoes != null ? $descritores_corretos : null,
            "descritores_errados" => $descritores_questoes != null ? $descritores_errados : null,
            "pontos_prova" => $prova["valor"],
        ];

        MainController::pre($dados_atualizacao);


        if (ProfessorModel::atualizar_gabarito_aluno($dados_atualizacao) && ProfessorModel::atualizar_gabarito_aluno_primeira($dados_atualizacao)) {
            self::inserirLogsADM("A Prova do aluno(a) {$dados["nome"]} da materia {$prova["disciplina"]} Foi Editada.");
            $_SESSION["PopUp_ditar_prova"] = true;
            header("location: adm_home");
            exit();
        }

    }

    public static function inserirLogsADM($string)
    {
        $dados_logs = [
            "data" => date('Y-m-d H:i:s'),
            "autor" => "ADM",
            "descricao" => $string,
        ];

        $query = ADModel::adicionarLogsADM($dados_logs);

        return $query;
    }

    public static function editar_dados_aluno()
    {
        if ($_SESSION["ADM"]) {

            $dados = [
                "ra" => $_POST["ra"],
                "nome" => $_POST["nome"],
                "turno" => $_POST["turno"],
                "data_nasc" => $_POST["data"],
                "turma" => $_POST["turma"],
            ];


            $Edit = ADModel::EditarAluno($dados);

            if ($Edit) {
                self::inserirLogsADM("O aluno(a) {$dados["nome"]} foi editado.");
                $_SESSION["PopUp_editar_aluno_true"] = true;
                header("location: adm_home");
                exit();
            }

        } else {
            header("location: ADM");
        }
    }

    public static function adicionar_aluno()
    {
        $dados = [
            "ra" => $_POST["ra"],
            "nome" => $_POST["nome"],
            "turno" => $_POST["turno_adicionar"],
            "data_nasc" => $_POST["data_nasc"] ?? null,
            "turma" => $_POST["turma_adicionar"],
        ];

        $query = ADModel::AdicionarAluno($dados);

        if ($query) {
            self::inserirLogsADM("O aluno(a) {$dados["nome"]} foi adicionado.");
            $_SESSION["PopUp_add_aluno"] = true;
            header("location: adm_home");
            exit();
        }

    }

    public static function formarNomeTurma($dados)
    {
        $serie = $dados["serie"];
        $turno = $dados["turno"];
        $curso = $dados["curso"];
        $numero = $dados["numero"];

        if ($curso == "INFORMÁTICA") {
            $curso = "IPI";
        } else if ($curso == "ADMINISTRAÇÃO") {
            $curso = "ADM";
        } else {
            $curso = "HUM";
        }

        if ($turno == "INTERMEDIÁRIO") {
            $nome_turma = "{$serie}ºIM0{$numero}-EMI-{$curso}";
        } else if ($turno == "VESPERTINO") {
            $nome_turma = "{$serie}ºV0{$numero}-EM-{$curso}";
        } else {
            $nome_turma = "{$serie}ºN0{$numero}-EM-{$curso}";
        }

        return $nome_turma;
    }

    public static function GetTurmas()
    {
        $turmas = ADModel::GetTurmas();
        return $turmas;
    }

}
