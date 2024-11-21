<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gabarito.pdf</title>

    <style>
        @import url(https://themes.googleusercontent.com/fonts/css?kit=fpjTOVmNbO4Lz34iLyptLUXza5VhXqVC6o75Eld_V98);

        @page {
            margin-top: 0px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            max-height: 100vh;
            height: 100%;
        }

        /* Quadrados pretos nos cantos */
        .corner {
            position: absolute;
            width: 18px;
            height: 18px;
            background-color: black;
        }

        .top-left {
            top: 0;
            left: 0;
        }

        .top-right {
            top: 0;
            right: 0;
        }

        .bottom-left {
            bottom: 0;
            left: 0;
        }

        .bottom-right {
            bottom: 0;
            right: 0;
        }

        h1,
        h2,
        h3 {
            text-align: center;
            margin: 0;
        }

        p {
            margin: 0;
            line-height: 1.5;
        }

        .content {
            text-align: justify;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 2px 6px;
            font-size: 12pt;
            text-align: left;
        }

        .table-info {
            color: #000000;
            font-weight: 400;
            text-decoration: none;
            vertical-align: baseline;
            font-size: 13pt;
        }

        .column-with-alternatives {
            /* width: auto; Ajustado para auto, já que fit-content não é suportado */
            padding-top: 12pt;
            display: inline-block;
            /* text-align: ; */
        }

        .column-alternatives {
            width: 100%; /* Definir a largura como 100% */
            border: 2px solid #000000;
            text-align: center;
        }

        .alternative {
            width: 16px;
            height: 16px;
            border: 1px solid #000000;
            border-radius: 50%;
            margin: 4px 3px;
            display: inline-block;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            line-height: 16px;
        }

        .num_questao {
            width: 16px;
            height: 16px;
            margin: 4px 3px;
            display: inline-block;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            line-height: 16px;
        }

        .line-alternatives {
            text-align: center; /* Alterado para centralizar os itens */
            width: auto; /* Ajustado para auto, pois min-content não é suportado */
            padding: 0px 6px;
        }

        .bg-column-alternatives {
            background-color: gainsboro;
        }
    </style>

</head>

<body style="height: 100vh">

    <div class="container">
        <div class="header" style="text-align: center;">
           
            <div style="width: 75%; height: 126px; margin: 0 auto;">
                <img src="data:image/jpeg;base64,<?php echo base64_encode(file_get_contents('public/assents/img/header-pdf.png')); ?>" alt="Sample Image" style="height: 100%;">
            </div>

            <h1 style="font-size: 16pt;"><?= $prova["nome_prova"]; ?></h1>
            <h2 style="font-size: 13pt;"><?= $prova["turmas"]; ?></h2>
        </div>

        <div class="content">

            <table class="table table-info">
                <tbody>
                    <tr>
                        <td colspan="1" rowspan="1">
                            Estudante:
                        </td>
                    </tr>
                    <tr>
                        <td colspan="1" rowspan="1">
                            Turma:
                        </td>
                    </tr>
                    <tr>
                        <td colspan="1" rowspan="1">
                            Área de Conhecimento:
                            <span style="font-weight: 700;"><?= $prova['area_conhecimento'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="1" rowspan="1">
                            Data:
                        </td>
                    </tr>
                </tbody>
            </table>

            <div>
                <p style="font-weight: 700; font-size: 12pt;">Prezado(a) estudante,</p>
                <p style="font-weight: 400; font-size: 11pt; font-style: italic;">Leia atentamente as instruções a seguir:</p>
                <div style="font-weight: 400; font-size: 11pt;"><?= $orientacoes ?></div>
            </div>

            <div style="
                margin-top: 18px;
                position: relative;
            ">

<!--                <div class="corner top-left"></div>-->
<!--                <div class="corner top-right"></div>-->

                <div style="  box-sizing: border-box; display: flex; width: 100%; height: 99%; align-items: center; justify-content: center; border: 3px solid #000000; ">
                    <div style="flex-grow: 1; width: 100%; height: 100%;">

                        <table class="table-grid" style="display: inline-table; border: 2px solid #000000; padding: 4px 0 0 0; margin: 10px;">
                            <!-- <thead>
                            <tr>
                                <th colspan="5"><?= $prova["disciplina"]; ?></th>
                            </tr>
                        </thead> -->
                            <tbody>
                            <?php for ($i=1; $i <= $prova["QNT_perguntas"]; $i++): ?>
                                <tr class="">
                                    <td class="num_questao"><?= $i; ?></td>
                                    <td class="alternative">A</td>
                                    <td class="alternative">B</td>
                                    <td class="alternative">C</td>
                                    <td class="alternative">D</td>
                                    <td class="alternative">E</td>
                                </tr>
                            <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

<!--            <div class="corner bottom-left"></div>-->
<!--            <div class="corner bottom-right"></div>-->
        

        </div>

    </div>
</body>

</html>