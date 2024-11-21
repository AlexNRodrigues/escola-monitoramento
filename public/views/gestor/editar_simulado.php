<?php extract($data); ?>

<main class="main-home-professor">
    <center>
        <h1 class="titulo-NSL">NSL - SISTEMA DE MONITORAMENTO</h1>
    </center>

    <div class="container-provas">

        <div class="group-btns">
            <a href="simulados" class="btn-padrao">Voltar</a>
        </div>

        <form method="POST" class="listagem-provas">

            <div class="form-group">
                <label for="nome">Nome do Simulado</label>
                <input type="text" id="nome" name="nome" required value="<?= $simulado['nome'] ?>">
            </div>

            <div class="form-group">
                <label for="t">Turma</label>
                <select id="t" name="turma">
                    <option value="">Selecione uma opção</option>
                    <?php foreach($turmas as $turma): ?>
                        <option value="<?= $turma['id'] ?>" <?= $simulado['turma_id'] == $turma['id'] ? 'selected' : '' ?>>
                            <?= $turma['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="area_conhecimento">Área de Conhecimento</label>
                <input type="text" id="area_conhecimento" name="area_conhecimento" required value="<?= $simulado['area_conhecimento'] ?>">
            </div>

            <div class="form-group">
                <label for="orientacoes">Orientações</label>
                <div id="quill-container"></div>
                <textarea name="orientacoes" id="orientacoes" cols="30" rows="10" ><?= $simulado['orientacoes'] ?></textarea>
            </div>

            <div id="ordem-selecao">
                <h4>Ordem de Seleção</h4>
                <ul id="lista-ordem-selecao"></ul>
            </div>

            <div class="form-group">
                <label for="">Prova</label>
                <table class="tabela-provas">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Data</th>
                            <th>Nome</th>
                            <th>
                                <select id="disciplina">
                                    <option value="">Disciplina</option>
                                    <?php foreach($disciplinas as $disciplina): ?>
                                        <option value="<?= $disciplina ?>">
                                            <?= $disciplina ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </th>
                            <th>
                                <select id="professor">
                                    <option value="">Professor</option>
                                    <?php foreach($professores as $professor): ?>
                                        <option value="<?= $professor['nome'] ?>">
                                            <?= $professor['nome'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </th>
                            <th>
                                <select id="turma">
                                    <option value="">Turma</option>
                                    <?php foreach($turmas as $turma): ?>
                                        <option value="<?= $turma['nome'] ?>">
                                            <?= $turma['nome'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </th>
                            <th>Valor</th>
                            <th>Qtd Questões</th>
                        </tr>
                    </thead>
                    <tbody id="lista-provas">
                        <?php foreach($provas as $prova): ?>
                            <tr>
                                <td data-label="Selecionar:">
                                    <input type="checkbox"
                                           name="id_prova[]"
                                           value="<?= $prova['id'] ?>"
                                           data-id="<?= $prova['id'] ?>"
                                           <?= in_array($prova['id'], $ids_prova) ? 'checked' : '' ?>
                                    >
                                </td>
                                <td data-label="Data:">
                                    <?= date('d/m/Y', strtotime($prova['data_prova'])); ?>
                                </td>
                                <td data-label="Nome:">
                                    <?= $prova['nome_prova'] ?>
                                </td>
                                <td data-label="Disciplina:">
                                    <?= $prova['disciplina'] ?>
                                </td>
                                <td data-label="Professor:">
                                    <?= $prova['nome_professor'] ?>
                                </td>
                                <td data-label="Turma:">
                                    <?= $prova['turmas'] ?>
                                </td>
                                <td data-label="Valor:" >
                                    <?= $prova['valor'] ?>
                                </td>
                                <td data-label="Qtd Questões:">
                                    <?= $prova['QNT_perguntas'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8">
                                <input type="hidden" name="ordem_selecao" id="ordem-selecao-input">
                                <button type="submit" id="enviar-selecionados" class="btn enviar">Enviar Selecionados</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </form>
    </div>

</main>

<script src="./public/assents/js/gestor/script.js"></script>
<script src="./public/assents/js/quill_config.js"></script>
<script>
    quill.root.innerHTML = `<?= $simulado['orientacoes'] ?>`;
</script>