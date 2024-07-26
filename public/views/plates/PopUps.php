<?php
$popups = [
    "PopUp_RA_NaoENC" => [
        "status_class" => "uncheck",
        "title" => "RA NÃO ENCONTRADO",
        "message" => "Infelizmente não encontramos o <br> RA em nosso banco de dados.",
    ],

    "PopUp_not_Materia" => [
        "status_class" => "uncheck",
        "title" => "NENHUMA DISCIPLINA!",
        "message" => "Selecione pelo menos uma <br> para este professor.",
    ],

    "PopUp_add_aluno" => [
        "status_class" => "check",
        "title" => "SUCESSO",
        "message" => "Aluno inserido com sucesso!",
    ],

    "PopUp_Excluir_turma" => [
        "status_class" => "check",
        "title" => "SUCESSO",
        "message" => "Turma excluída com sucesso!",
    ],

    "PopUp_Excluir_aluno" => [
        "status_class" => "check",
        "title" => "SUCESSO",
        "message" => "Aluno excluído com sucesso!",
    ],

    "PopUp_editar_aluno_true" => [
        "status_class" => "check",
        "title" => "SUCESSO!",
        "message" => "Os dados do aluno <br> foram editados com sucesso!",
    ],
    "PopUp_PRF_NaoENC" => [
        "status_class" => "uncheck",
        "title" => "USUÁRIO NÃO ENCONTRADO",
        "message" => "Infelizmente não encontramos o <br> Usuário em nossos registros.",
    ],
    "PopUp_Prova_Feita" => [
        "status_class" => "uncheck",
        "title" => "PROVA JÁ FEITA!",
        "message" => "Parece que você já realizou <br> esta prova.",
    ],
    "PopUp_PRF_Senha" => [
        "status_class" => "uncheck",
        "title" => "SENHA INCORRETA!",
        "message" => "Você digitou a senha incorreta para este usuário <br> Tente Novamente.",
    ],
    "popup_not_turmas" => [
        "status_class" => "uncheck",
        "title" => "TURMAS NÃO SELECIONADAS!",
        "message" => "Por favor, insira pelo menos uma <br> turma para proseguir.",
    ],
    "PopUp_add_professor_true" => [
        "status_class" => "check",
        "title" => "SUCESSO",
        "message" => "Professor inserido com <br> sucesso!",
    ],

    "PopUp_excluir_professor" => [
        "status_class" => "check",
        "title" => "SUCESSO",
        "message" => "Professor excluido com <br> sucesso!",
    ],

    "PopUp_editar_professor" => [
        "status_class" => "check",
        "title" => "SUCESSO",
        "message" => "Professor editado com <br> sucesso!",
    ],

    "PopUp_add_materia_true" => [
        "status_class" => "check",
        "title" => "SUCESSO",
        "message" => "Matéria inserida com <br> sucesso!",
    ],
    "PopUp_excluir_materia_true" => [
        "status_class" => "check",
        "title" => "SUCESSO",
        "message" => "Matéria excluída com <br> sucesso!",
    ],
    "PopUp_inserir_turma" => [
        "status_class" => "check",
        "title" => "SUCESSO",
        "message" => "Turma inserida com <br> sucesso!",
    ],
    "PopUp_Excluir_prova" => [
        "status_class" => "check",
        "title" => "SUCESSO",
        "message" => "Prova excluída com <br> sucesso!",
    ],
    "PopUp_inserir_gabarito_professor" => [
        "status_class" => "check",
        "title" => "SUCESSO",
        "message" => "Gabarito inserido com <br> sucesso!",
    ],
];

foreach ($popups as $popup_id => $popup_data) {
    if ($_SESSION[$popup_id] == true) {
        echo "<div id='$popup_id' class='PopUp-sobreposicao'>";
        echo "<div class='conteudo-popup'>";
        echo "<h2>{$popup_data['title']}</h2>";

        if ($popup_data['status_class'] == 'check') {
            echo "<div class='check'>";
            echo "<div class='linha-checked-1'></div>";
            echo "<div class='linha-checked-2'></div>";
            echo "</div>";
        } else {
            echo "<div class='uncheck'>";
            echo "<div class='linha-unchecked-1'></div>";
            echo "<div class='linha-unchecked-2'></div>";
            echo "</div>";
        }

        echo "<p>{$popup_data['message']}</p>";
        echo "<button onclick=\"Fechar_PopUp('$popup_id')\" class='Fechar-Popup'>FECHAR</button>";
        echo "</div>";
        echo "</div>";

        $_SESSION[$popup_id] = false;
    }
}
