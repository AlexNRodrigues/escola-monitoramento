<main class="main-home-aluno">
    <center>
        <h1><?=$data["nome_prova"]?></h1>

    </center>
    <span><b>DISCIPLINA:</b> <?=$data["disciplina"]?></span><br>
    <span><b>PROFESSOR: </b><?=$data["nome_professor"]?></span><br>
    <span><b>VALOR: </b> <?=$data["valor"]?></span><br>

    <div class="aluno_inserir_gabarito">
        <form action="cadastrar_gabarito_aluno" method="post">
            <table>
                <?php
                $contador = 1;
                while ($contador <= $data["QNT_perguntas"]) {?>
                <tr>
                    <td>
                        <span><?php echo $contador ?></span>
                    </td>

                    <td>
                        <div><input type="radio" name="gabarito_questao_<?php echo "{$contador}" ?>" required
                                value="<?php echo "{$contador},A" ?>"><span>A</span></div>
                    </td>
                    <td>
                        <div><input type="radio" name="gabarito_questao_<?php echo "{$contador}" ?>" required
                                value="<?php echo "{$contador},B" ?>"><span>B</span></div>
                    </td>
                    <td>
                        <div><input type="radio" name="gabarito_questao_<?php echo "{$contador}" ?>" required
                                value="<?php echo "{$contador},C" ?>"><span>C</span></div>
                    </td>
                    <td>
                        <div><input type="radio" name="gabarito_questao_<?php echo "{$contador}" ?>" required
                                value="<?php echo "{$contador},D" ?>"><span>D</span></div>
                    </td>
                    <td>
                        <div><input type="radio" name="gabarito_questao_<?php echo "{$contador}" ?>" required
                                value="<?php echo "{$contador},E" ?>"><span>E</span></div>
                    </td>


                </tr>
                <?php
                    $contador++;}
                ?>
            </table>
            <br><br><br>
            <div>
                <center>
                    <h3>ANALISE TODAS AS RESPOSTAS ANTES <br> DE CLICAR NO BOTÃO ABAIXO!</h3>
                </center>
                <br>
            </div>
            <center>
                <input type="submit" value="Enviar gabarito" class="botao-form-enviar">
            </center>

        </form>


    </div>
    <div>
        <br><br><br><br><br><br><br>
        <br><br><br><br><br><br><br>
    </div>
</main>