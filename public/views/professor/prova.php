<?php extract($data); ?>

<main class="main-home-professor paia">
    <center>
        <h1 class="titulo-NSL">
            <i class="fas fa-chalkboard-teacher"></i> NSL - SISTEMA DE MONITORAMENTO
        </h1>
        <h2><i class="fas fa-file-alt"></i> <?=$data["nome_prova"]?></h2>
    </center>

    <form method="post" action="">
        <center>
            <div class="alternar-liberar-gabarito">
                <span>
                    <i class="fas fa-eye"></i> Aluno pode ver o resultado (gabarito)?
                </span><br><br>
                <?php if ($data["liberado"] == "SIM") {?>
                    <button type="submit" name="status" value="sim" class="button-prova-liberado" style="background-color: #16c1aa;">
                        <i class="fas fa-check"></i> SIM
                    </button>
                    <button type="submit" name="status" value="não" class="button-prova-liberado">
                        <i class="fas fa-times"></i> NÃO
                    </button>
                <?php } else {?>
                    <button type="submit" name="status" value="sim" class="button-prova-liberado">
                        <i class="fas fa-check"></i> SIM
                    </button>
                    <button type="submit" name="status" value="não" class="button-prova-liberado" style="background-color: #16c1aa;">
                        <i class="fas fa-times"></i> NÃO
                    </button>
                <?php }?>
            </div><br><br>

            <div class="alternar-liberar-gabarito">
                <span>
                    <i class="fas fa-user-check"></i> O aluno que faltou tem permissão para realizar a prova?
                </span><br><br>
                <?php if ($data["liberar_prova"] == true) {?>
                    <button type="submit" name="status-liberado" value="sim" class="button-prova-liberado" style="background-color: #16c1aa;">
                        <i class="fas fa-check"></i> SIM
                    </button>
                    <button type="submit" name="status-liberado" value="não" class="button-prova-liberado">
                        <i class="fas fa-times"></i> NÃO
                    </button>
                <?php } else {?>
                    <button type="submit" name="status-liberado" value="sim" class="button-prova-liberado">
                        <i class="fas fa-check"></i> SIM
                    </button>
                    <button type="submit" name="status-liberado" value="não" class="button-prova-liberado" style="background-color: #16c1aa;">
                        <i class="fas fa-times"></i> NÃO
                    </button>
                <?php }?>
            </div><br><br>

            <input type="hidden" name="id-prova" value="<?=$_SESSION["id_prova_professor"]?>">
            <b><span>*As Alterações acima serão aplicadas para todas as turmas*</span></b>
        </center>
    </form>

    <div class="detalhes-prova">
        <table>
            <thead>
                <tr>
                    <th colspan="2"><i class="fas fa-info-circle"></i> DETALHES PROVA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>VALOR</td>
                    <td><?=$data["prova"]["valor"]?> Pontos</td>
                </tr>
                <tr>
                    <td>PERGUNTAS</td>
                    <td><?=$data["prova"]["QNT_perguntas"]?> Questões</td>
                </tr>
                <tr>
                    <th colspan="2"><i class="fas fa-users"></i> TURMAS</th>
                </tr>
                <?php
                $turmas = explode(",", $data["prova"]["turmas"]);
                $turmaCount = count($turmas);
                for ($i = 0; $i < $turmaCount; $i += 2) {?>
                    <tr>
                        <td><?=$turmas[$i]?></td>
                        <td><?=isset($turmas[$i + 1]) ? $turmas[$i + 1] : ''?></td>
                    </tr>
                <?php }?>
            </tbody>
        </table>
    </div>

    <div class="area-professor-botoes-prova">
        <?php if ($data["provas_rec"] != null) {?>
            <center>
                <div class="area_provas_rec_professor">
                    <form action="prova_recuperacao" method="post">
                        <?php for ($i = 0; $i < $data["provas_rec"]["quantidade"]; $i++) {?>
                            <button name="prova" value="<?=$data["provas_rec"]["provas"][$i]["id"]?>">
                                <i class="fas fa-redo"></i> <?=$i + 1?>ª RECUPERAÇÃO
                            </button><br>
                        <?php }?>
                    </form>
                </div>
            </center>
        <?php }?>
        
        <button class="excluir-prova" onclick="Mostrar_PopUp('PopUp_excluir_prova')">
            <i class="fas fa-trash-alt"></i> EXCLUIR PROVA
        </button>
    </div>

    <div style="display: none;" id="PopUp_excluir_prova" class="PopUp-sobreposicao">
        <div class="conteudo-popup">
            <h2><i class="fas fa-exclamation-triangle"></i> CUIDADO!</h2>
            <p>Excluir a prova resultará na perda de todos os dados registrados, incluindo os dos alunos que fizeram essa prova!</p>
            <b><p>Deseja excluir?</p></b>
            <div class="inserir-usuario-excluir-prova">
                <form method="post" action="">
                    <div>
                        <label for="user">Insira seu Usuario:</label>
                        <input required id="user" name="user" type="text">
                    </div><br><br>
                    <button type="submit" name="enviar-user" value="e" class="botao-form-enviar">
                        <i class="fas fa-check-circle"></i> Excluir Prova
                    </button>
                </form>
            </div>
            <button onclick="Fechar_PopUp('PopUp_excluir_prova')" class="Fechar-Popup-icon">X</button>
        </div>
    </div>

    <?php if(!$he_simulado): ?>
        <div class="area-download-gabarito">
            <hr>

            <h2>Gabarito</h2>

            <form action="download_gabarito" class="download-gabarito" method="post">
                <div class="form-group">
                    <label for="orientacoes">Orientações</label>
                    <div id="quill-container"></div>
                    <textarea name="orientacoes" id="orientacoes" cols="30" rows="10" required maxlength="600"></textarea>
                </div>

                <button type="submit" class="botao-form-enviar"  id="download-gabarito">
                    Download
                </button>
            </form>
        </div>
    <?php endif; ?>

    <br><br><br><br><br><br>
</main>

<script src="./public/assents/js/quill_config.js"></script>
