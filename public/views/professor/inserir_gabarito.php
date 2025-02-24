<?php 
$icones = [
    "LÍNGUA PORTUGUESA" => '<i class="fas fa-book fa-2x" style="color: #ff6347;"></i>',
    "LÍNGUA INGLESA" => '<i class="fas fa-language fa-2x" style="color: #4682b4;"></i>',
    "EDUCAÇÃO FÍSICA" => '<i class="fas fa-running fa-2x" style="color: #ff4500;"></i>',
    "ARTE" => '<i class="fas fa-paint-brush fa-2x" style="color: #ff69b4;"></i>',
    "FÍSICA" => '<i class="fas fa-atom fa-2x" style="color: #00bfff;"></i>',
    "QUÍMICA" => '<i class="fas fa-flask fa-2x" style="color: #32cd32;"></i>',
    "MATEMÁTICA" => '<i class="fas fa-square-root-alt fa-2x" style="color: #ffd700;"></i>',
    "FILOSOFIA" => '<i class="fas fa-brain fa-2x" style="color: #8b0000;"></i>',
    "SOCIOLOGIA" => '<i class="fas fa-users fa-2x" style="color: #daa520;"></i>',
    "HIGIENE, SAÚDE E SEGURANÇA" => '<i class="fas fa-medkit fa-2x" style="color: #ff4500;"></i>',
    "SISTEMAS OPERACIONAIS" => '<i class="fas fa-desktop fa-2x" style="color: #4682b4;"></i>',
    "ALGORITMO E LÓGICA DE PROGRAMAÇÃO" => '<i class="fas fa-code fa-2x" style="color: #2e8b57;"></i>',
    "BIOLOGIA" => '<i class="fas fa-leaf fa-2x" style="color: green;"></i>',
    "GEOGRAFIA" => '<i class="fas fa-globe fa-2x" style="color: #4682b4;"></i>',
    "HISTÓRIA" => '<i class="fas fa-hourglass-half fa-2x" style="color: #cd853f;"></i>',
    "CULTURA DIGITAL" => '<i class="fas fa-network-wired fa-2x" style="color: #ff4500;"></i>',
    "INTRODUÇÃO A REDE DE COMPUTADORES E PROTOCOLOS" => '<i class="fas fa-project-diagram fa-2x" style="color: #4682b4;"></i>',
    "LINGUAGEM DE PROGRAMAÇÃO APLICADA A WEB" => '<i class="fas fa-laptop-code fa-2x" style="color: #2e8b57;"></i>',
    "IOT - INTERNET OF THINGS" => '<i class="fas fa-sitemap fa-2x" style="color: #4682b4;"></i>',
    "BANCO DE DADOS" => '<i class="fas fa-database fa-2x" style="color: #ff6347;"></i>',
    "APLICATIVOS WEB" => '<i class="fas fa-mobile-alt fa-2x" style="color: #4682b4;"></i>',
    "A ESPÉCIE HUMANA E A RELAÇÃO COM OS RECURSOS NATURAIS" => '<i class="fas fa-tree fa-2x" style="color: green;"></i>',
    "PERCURSO FILOSÓFICO SOBRE A ESPÉCIE HUMANA" => '<i class="fas fa-brain fa-2x" style="color: #8b0000;"></i>',
    "TRAJETÓRIAS HUMANAS NA HISTÓRIA" => '<i class="fas fa-hourglass-half fa-2x" style="color: #cd853f;"></i>',
    "ANÁLISE E PROJETO DE SISTEMAS" => '<i class="fas fa-project-diagram fa-2x" style="color: #4682b4;"></i>',
    "ARQUITETURA, SEGURANÇA E PROJETOS DE REDE" => '<i class="fas fa-network-wired fa-2x" style="color: #2e8b57;"></i>',
    "PROGRAMAÇÃO PARA WEB DESIGN" => '<i class="fas fa-laptop-code fa-2x" style="color: #ff6347;"></i>',
    "LINGUAGEM DE PROGRAMAÇÃO ORIENTADA A OBJETO" => '<i class="fas fa-code fa-2x" style="color: #4682b4;"></i>',
    "DESENVOLVIMENTO DE SISTEMAS" => '<i class="fas fa-laptop-code fa-2x" style="color: #2e8b57;"></i>',
    "DESENVOLVIMENTO DE GAMES" => '<i class="fas fa-gamepad fa-2x" style="color: #ff4500;"></i>',
    "COMUNICAÇÃO E BRANDING" => '<i class="fas fa-bullhorn fa-2x" style="color: #ffd700;"></i>',
    "ADMINISTRAÇÃO FINANCEIRA" => '<i class="fas fa-chart-line fa-2x" style="color: #32cd32;"></i>',
    "DIREITO EMPRESARIAL E TRIBUTÁRIO" => '<i class="fas fa-gavel fa-2x" style="color: #8b0000;"></i>',
    "GESTÃO DE PRODUÇÃO E QUALIDADE" => '<i class="fas fa-cogs fa-2x" style="color: #4682b4;"></i>',
    "PLANEJAMENTOS E INVESTIMENTOS" => '<i class="fas fa-chart-pie fa-2x" style="color: #ffd700;"></i>',
    "GESTÃO PÚBLICA" => '<i class="fas fa-landmark fa-2x" style="color: #4682b4;"></i>',
    "E-COMMERCE" => '<i class="fas fa-shopping-cart fa-2x" style="color: #32cd32;"></i>',
    "ROTINAS ADMINISTRATIVAS" => '<i class="fas fa-tasks fa-2x" style="color: #ff4500;"></i>',
    "ECONOMIA" => '<i class="fas fa-money-bill-wave fa-2x" style="color: #ffd700;"></i>',
    "LOGÍSTICA" => '<i class="fas fa-truck fa-2x" style="color: #4682b4;"></i>',
    "COOPERATIVISMO" => '<i class="fas fa-handshake fa-2x" style="color: #32cd32;"></i>',
    "MARKETING ORGANIZACIONAL" => '<i class="fas fa-bullseye fa-2x" style="color: #ff4500;"></i>',
    "INDIVÍDUO, NATUREZA E SOCIEDADE" => '<i class="fas fa-leaf fa-2x" style="color: green;"></i>',
    "PERSPECTIVA GEOGRÁFICA: DESENVOLVIMENTO E ESPAÇO" => '<i class="fas fa-globe fa-2x" style="color: #4682b4;"></i>',
];


?>
<main class="main-home-professor">
    <div class="professor-form-section">
        <center>
            <h1 class="titulo-NSL">NSL - SISTEMA DE MONITORAMENTO</h1>

            <h2 class="form-section-header">INSERIR GABARITO</h2>
        </center>

        <form action="criar_gabarito" method="post">
            <div class="professor-form-section-wrapper">
                <div class="form-group-inputs-text">
                    <label for="nome-prova" class="professor-form-group-label">Insira o Nome da Prova/atividade:</label><br>
                    <input type="text" maxlength="25" required id="nome-prova" name="nome-prova"
                        class="professor-input-text-field">
                </div>
                <br>
                <div class="form-group-inputs-text">
                    <label for="area_conhecimento" class="professor-form-group-label">Área de Conhecimento da Prova</label><br>
                    <input type="text" maxlength="100" id="area_conhecimento" name="area_conhecimento"
                           class="professor-input-text-field">
                </div>
            </div>

            <div class="professor-form-section-wrapper">
                <center>
                    <h3 class="form-section-header">METODO AVALIATIVO:</h3>
                    <div class="professor-form-group">
                        <div>
                            <input type="radio" required id="metodo_prova" name="metodo" value="prova"
                                class="custom-radio-button">
                            <label style="width: 220px;" for="metodo_prova">PROVA AVALIATIVA</label>
                        </div>
                        <div>
                            <input type="radio" required name="metodo" id="metodo_att" value="atividade"
                                class="custom-radio-button">
                            <label style="width: 220px;" for="metodo_att">ATIVIDADE DE REVISÃO</label>
                        </div>
                        <div>
                            <input type="radio" required name="metodo" id="metodo_ama" value="ama"
                                class="custom-radio-button">
                            <label style="width: 220px;" for="metodo_ama">AMA</label>
                        </div>
                    </div>
                </center>
            </div>

            <div class="professor-form-section-wrapper">
                <center>
                    <h3 class="form-section-header">DESCRITORES:</h3>
                    <div class="professor-form-group">
                        <div>
                            <input type="radio" required id="descritores_sim" name="descritores" value="sim"
                                class="custom-radio-button">
                            <label for="descritores_sim">SIM</label>
                        </div>
                        <div>
                            <input type="radio" required name="descritores" id="descritores_nao" value="não"
                                class="custom-radio-button">
                            <label for="descritores_nao">NÃO</label>
                        </div>
                    </div>
                </center>
            </div>

            <div class="professor-turmas-section">
                <center>
                    <h3 class="form-section-header">TURMAS INTERMEDIÁRIO</h3>
                    <div class="professor-turmas-container">
                        <?php foreach ($data as $turma) {if ($turma["turno"] == "INTERMEDIÁRIO") {?>
                        <div>
                            <input name="gabarito-turmas[]" id="<?=$turma["nome"]?>" type="checkbox"
                                value="<?=$turma["nome"]?>" class="custom-checkbox-button">
                            <label for="<?=$turma["nome"]?>"><?=$turma["nome"]?></label>
                        </div>
                        <?php }}?>
                    </div>

                    <h3 class="form-section-header">TURMAS VESPERTINO</h3>
                    <div class="professor-turmas-container">
                        <?php foreach ($data as $turma) {if ($turma["turno"] == "VESPERTINO") {?>
                        <div>
                            <input name="gabarito-turmas[]" id="<?=$turma["nome"]?>" type="checkbox"
                                value="<?=$turma["nome"]?>" class="custom-checkbox-button">
                            <label for="<?=$turma["nome"]?>"><?=$turma["nome"]?></label>
                        </div>
                        <?php }}?>
                    </div>
                </center>
            </div>

            <div class="professor-disciplinas-section">
                <h3 class="form-section-header">SELECIONE A DISCIPLINA CORRESPONDENTE:</h3> <br>
                <div class="professor-disciplinas-container">
                    <?php
                        if (strpos($_SESSION["disciplinas"], ";")) {?>
                    <?php $materias = explode(";", $_SESSION["disciplinas"]);
                        foreach ($materias as $materia) {?>
                    <div class="input-radio-disciplina-gabarito">
                       
                        <?php if (isset($icones[mb_strtoupper($materia, 'UTF-8')])) {
                            echo $icones[mb_strtoupper($materia, 'UTF-8')];
                        } ?>
                        <input type="radio" required name="Materias-professor-gabarito" id="<?=$materia?>"
                            value="<?=$materia?>" class="custom-disciplinas-radio">
                        <label for="<?=$materia?>"><?=$materia?></label>
                    </div>
                    <?php }?>
                    <?php } else {?>
                    <div class="input-radio-disciplina-gabarito">
                    <?php if (isset($icones[mb_strtoupper($_SESSION["disciplinas"], 'UTF-8')])) {
                            echo $icones[mb_strtoupper($_SESSION["disciplinas"], 'UTF-8')];
                        } ?>
                        <input type="radio" required name="Materias-professor-gabarito" id="<?=$_SESSION["disciplinas"]?>"
                            value="<?=$_SESSION["disciplinas"]?>" class="custom-disciplinas-radio">
                        <label for="<?=$_SESSION["disciplinas"]?>"><?=$_SESSION["disciplinas"]?></label>
                    </div>
                    <?php }?>
                </div>
            </div>

            <div class="professor-form-section-wrapper">
                <center>
                    <h3 class="form-section-header">QUANTIDADE DE QUESTÕES:</h3>
                    <div class="professor-form-group">
                        <div class="professor-selectable-numbers" id="quantidade-perguntas">
                            <?php for ($i = 1; $i <= 40; $i++): ?>
                                <div class="number-box" data-value="<?= $i ?>"><?= $i ?></div>
                            <?php endfor; ?>
                        </div>
                        <input name="qtn-perguntas" required type="hidden" class="professor-input-number-field">
                    </div>
                </center>
                <center>
                    <div id="valor-container">
                    <h3 class="form-section-header">VALOR:</h3>
                    <div class="professor-form-group">
                        <div class="professor-selectable-numbers" id="valor">
                            <?php for($i = 1; $i <= 20; $i++): ?>
                                <div class="number-box" data-value="<?= $i ?>"><?= $i ?></div>
                            <?php endfor; ?>
                        </div>
                        <input name="valor-prova" required type="hidden" class="professor-input-number-field">
                    </div>
                    </div>
                </center>
            </div>

            <br><br>
            <center>
                <input type="submit" value="Criar Gabarito" class="botao-form-enviar">
            </center>
        </form>
        <div>
            <br><br><br>
            <br><br><br>
            <br><br><br>
        </div>
    </div>
</main>
