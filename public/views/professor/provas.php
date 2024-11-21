<?php #extract($data); ?>

<main class="main-home-professor">
    <center>
        <h1 class="titulo-NSL">NSL - SISTEMA DE MONITORAMENTO</h1>
        <h2>PROVAS - <?= $_SESSION["nome_professor"] ?></h2>
    </center>
    <br>
    <?php $status = false; ?>

    <?php if(isset($data)): ?>

        <?php foreach ($data["provas"] as $periodo => $provas): ?>

            <details class="periodo-provas" >
                <summary> <?=$periodo?> </summary>

                <?php foreach($provas as $prova): ?>

                    <?php
                        $background_color = match ($prova["metodo"]) {
                            "prova" => '#33C27A',
                            "ama"   => '#DBDD10',
                            default => '#217D7D',
                        };

                        $icon = match ($prova["metodo"]) {
                            "prova" => '<i class="fas fa-file-alt fa-4x" style="color: #33C27A;"></i>',
                            "ama"   => '<i class="fas fa-clipboard-list fa-4x" style="color: #DBDD10;"></i>',
                            default => '<i class="fas fa-tasks fa-4x" style="color: #217D7D;"></i>',
                        };
                    ?>

                    <div class="prova-pendente" style="width:85%" >
                        <div class="linha-vertical-campo-prova" style="background-color: <?= $background_color ?>;"></div>
                        <div class="conteudo-prova">
                            <?= $icon ?>
                            <div class="prova-detalhes">
                                <center>
                                    <span class="prova-nome-disciplina">
                                        <?= $prova["nome_prova"] ?>
                                    </span> <br>
                                                <span class="prova-nome-disciplina">
                                        <?= $prova["data_prova"] ?>
                                    </span> <br>
                                    <span class="prova-nome-professor">
                                        <?= $prova['total_alunos'] ?> aluno(s) Fizeram a prova/atividade.
                                    </span>
                                </center>
                            </div>

                            <div class="div_editar_ver_prova" >
                                <form method="post" action="editar_prova">
                                    <button type="submit" value="<?= $prova['id'] ?>" name="id-prova" class="botao-form-editar"><i class="fas fa-pencil-alt"></i></button>
                                </form>
                                <form method="post" action="prova">
                                    <button type="submit" value="<?= $prova['id'] ?>" name="id-prova" class="botao-form-enviar-prova">Ver</button>
                                </form>
                                <a href="lancar_gabarito?prova_id=<?= $prova['id'] ?>" class="botao-form-enviar-prova botao-form-lancar-gabarito">
                                    Lançar gabarito
                                </a>
                            </div>
                        </div>
                    </div>

                    <br>

                <?php endforeach; ?>
            </details>
        <?php endforeach; ?>

    <?php else: ?>
        <div class="height">
            <center>
                <h1>SEM DADOS !</h1>
                <h2>Você não inseriu nenhuma prova !</h2>
            </center>
        </div>
    <?php endif; ?>



</main>