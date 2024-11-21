<main class="main-home">
    <center>
        <h1 class="titulo-NSL">NSL - SISTEMA DE MONITORAMENTO</h1>
    </center>

    <div   class="form-filtros-gestor">
    <form id="filterForm" action="gestor_home" method="post">
                <button type="submit" name="geral" class="button-vsGeral" value="geral">GERAL</button>
 
        <select name="turma" id="turma">
            <option value="SELECIONAR">TURMA</option>
            <?php foreach ($data["turmas"] as $turma) {
                $selected = ($data["filtros"]["turma"] ?? '') == $turma["nome"] ? 'selected' : '';
            ?>
                <option value="<?= $turma["nome"] ?>" <?= $selected ?>><?= $turma["nome"] ?></option>
            <?php } ?>
        </select>
 
        <select name="turno" id="turno">
            <option value="SELECIONAR">TURNO</option>
            <?php foreach ($data["turnos"] as $turno) {
                $selected = ($data["filtros"]["turno"] ?? '') == $turno ? 'selected' : '';
            ?>
                <option value="<?= $turno ?>" <?= $selected ?>><?= $turno ?></option>
            <?php } ?>
        </select>
 
        <select name="disciplina" id="disciplina">
            <option value="SELECIONAR">DISCIPLINA</option>
            <?php foreach ($data["disciplinas"] as $disciplina) {
                $selected = ($data["filtros"]["disciplina"] ?? '') == $disciplina["nome"] ? 'selected' : '';
            ?>
                <option value="<?= $disciplina["nome"] ?>" <?= $selected ?>><?= $disciplina["nome"] ?></option>
            <?php } ?>
        </select>
 
        <select name="professor" id="professor">
            <option value="SELECIONAR">PROFESSOR</option>
            <?php foreach ($data["professores"] as $professor) {
                $selected = ($data["filtros"]["professor"] ?? '') == $professor["nome"] ? 'selected' : '';
            ?>
                <option value="<?= $professor["nome"] ?>" <?= $selected ?>><?= $professor["nome"] ?></option>
            <?php } ?>
        </select>
 
        <select name="serie" id="serie">
            <option value="SELECIONAR">SÉRIE</option>
            <?php
                $selected_1 = ($data["filtros"]["serie"] ?? '') == '1' ? 'selected' : '';
                $selected_2 = ($data["filtros"]["serie"] ?? '') == '2' ? 'selected' : '';
                $selected_3 = ($data["filtros"]["serie"] ?? '') == '3' ? 'selected' : '';
            ?>
            <option value="1" <?= $selected_1 ?>>1º Série</option>
            <option value="2" <?= $selected_2 ?>>2º Série</option>
            <option value="3" <?= $selected_3 ?>>3º Série</option>
        </select>

        <select name="periodo" id="periodo">
            <option value="SELECIONAR">PERÍODO</option>
            <?php foreach ($data["periodos"] as $periodo) {
                $selected = ($data["filtros"]["periodo"] ?? '') == $periodo["nome"] ? 'selected' : '';
            ?>
                <option value="<?= $periodo["nome"] ?>" <?= $selected ?>><?= $periodo["nome"] ?></option>
            <?php } ?>
        </select>

        <select name="metodo" id="metodo">
        <?php
                $selectedMetodoProva = ($data["filtros"]["metodo"] ?? '') == 'prova' ? 'selected' : '';
                $selectedMetodoAtividade = ($data["filtros"]["metodo"] ?? '') == 'atividade' ? 'selected' : '';
                $selectedMetodoAma = ($data["filtros"]["metodo"] ?? '') == 'ama' ? 'selected' : '';
            ?>
            <option  value="SELECIONAR">METODO</option>
            <option <?= $selectedMetodoProva ?> value="prova">PROVA</option>
            <option <?= $selectedMetodoAtividade ?> value="atividade">ATIVIDADE</option>
            <option <?= $selectedMetodoAma ?> value="ama">AMA</option>
        </select>

        <button class="fechar" onclick="resetForm()">Limpar</button>
        <input type="submit" name="filtro" value="Filtrar">
    </form>
</div>


    <br> 
<?php if($data != NULL){?>
    <?php if (!$data["geral"]) { ?>
        <?php if (!$data["status"]) { ?>

        <h2>FILTROS</h2>
            <div class="filtros_exibir">
                <?php foreach ($data["filtros"] as $filtro => $value) {
                    if ($value != NULL && $filtro != "datas" ) { ?>
                        <div>
                            <h4> <?= $filtro ?> </h4>
                            <?php if($filtro == "serie"){ ?>
                                <span> <?= $value ?>º Série </span>
                             <?php }else{ ?>
                            <span> <?= $value ?>  </span>
                            <?php }?>
                        </div>
                <?php }
                } ?>
            </div>

            <h1>NÃO FORAM ENCONTRADOS RESULTADOS PARA ESSA CONSULTA!</h1>
            <div><br><br><br><br></div>
        <?php } else { ?>

                <h2>FILTROS</h2> 
            <div class="filtros_exibir">
                <?php foreach ($data["filtros"] as $filtro => $value) {
                    if ($value != NULL && $filtro != "datas" ) { ?>
                        <div>
                            <h4> <?= $filtro ?> </h4>
                            <?php if($filtro == "serie"){ ?>
                                <span> <?= $value ?>º Série </span>
                             <?php }else{ ?>
                            <span> <?= $value ?>  </span>
                            <?php }?>
                        </div>
                <?php }
                } ?>
            </div>

            <div class="gestor_desempenho_escola">
                <div class="rosca">
                    <?= $data["graficos_filtro"]["porcentagem"] ?>
                    <h3>DESEMPENHO</h3>
                </div>
                <hr>
                <div>
                    <?= $data["graficos_filtro"]["proeficiencia"] ?>
                    <h3>PROFICIÊNCIA</h3>
                </div>
            </div>
            <br><br>
            <?= $data["alunosFiltro60"] ?>
            <br><br>

            <h1>DESEMPENHO TURMAS</h1>
            <div class="gestor_area_turmas_geral">
                <?php foreach ($data["resultadosTurmas"] as $turma => $value) { ?>
                    <div>
                        <?= $value ?>
                        <span><?= $turma ?></span>
                    </div>
                <?php } ?>
            </div>

            <div><br><br><br><br></div>

        <?php   } ?>
    <?php } else { ?>

        <h1>DESEMPENHO ESCOLAR GERAL</h1>
        <div class="gestor_desempenho_escola">
            <div class="rosca">
                <?= $data["roscaGeral"] ?>
                <h3>DESEMPENHO GERAL</h3>
            </div>
            <hr>
            <div>
                <?= $data["colunaGeral"] ?>
                <h3>PROFICIÊNCIA</h3>
            </div>
        </div>

        <h1>DESEMPENHO TURNO</h1>
        <div class="gestor_area_turnos_geral">
            <?php foreach ($data["dados_turnos"] as $turno => $value) { 
                if($value != NULL){?>
                <center>
                    <h3><?= $turno ?></h3>
                </center>
                <div class="turno">
                    <div class="turno_rosca">
                        <?= $value[0] ?>
                        <center>
                            <h4>DESEMPENHO GERAL</h4>
                        </center>
                    </div>
                    <div class="turno_coluna">
                        <?= $value[1] ?>
                        <center>
                            <h4>PROFICIÊNCIA</h4>
                        </center>
                    </div>
                </div>
            <?php }else{?>
                <center>
                    <h2>SEM DADOS DO TURNO <?= $turno ?></h2>
                </center>
                <?php }
        } ?>
        </div>

        <h1>DESEMPENHO TURMAS</h1>
        <div class="gestor_area_turmas_geral">
            <?php foreach ($data["dadosturmas"] as $turma => $value) { ?>
                <div>
                    <?= $value ?>
                    <span><?= $turma ?></span>
                </div>
            <?php } ?>
        </div>
        <div><br><br><br><br></div>
        <div><br><br><br><br></div>

    <?php } ?>
    <?php }else{ ?>
        <div class="height">
        <center>
            <h1>SEM DADOS !</h1>
            <h2>Nenhum Aluno inseriu alguma prova !</h2>
        </center>
        </div>
        <?php } ?>

        <?php if(isset($data["quantidade_dados"])): ?>
            <div class="area-dados-graficos">
                <span>
                    <?= $data["quantidade_dados"] . "PROVA(S) CONTABILIZADAS" ?>
                </span>
            </div>
        <?php endif; ?>
<br><br><br>
<br>
</main>