<?php extract($data); ?>

<main class="main-home-professor">
    <center>
        <h1 class="titulo-NSL">NSL - SISTEMA DE MONITORAMENTO</h1>
    </center>

    <div class="container-provas">

        <form id="form-buscar-prova" style="flex-direction: column">
            <h2 class="nome-form-buscar-prova">Buscar Provas</h2>

            <div>
                <div class="form-group">
                    <label for="nome">Nome do Simulado</label>
                    <input type="text" id="nome" name="nome" value="<?= $filtro['nome'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label for="disciplina">Disciplina</label>
                    <select id="disciplina" name="disciplina">
                        <option value="">Selecione uma opção</option>
                        <?php foreach($disciplinas as $disciplina): ?>
                            <option
                                value="<?= $disciplina ?>"
                                <?= !empty($filtro['disciplina']) && $filtro['disciplina'] == $disciplina ? 'selected' : ''; ?>
                            >
                                <?= $disciplina ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="turma">Turma</label>
                    <select id="turma" name="turma">
                        <option value="">Selecione uma opção</option>
                        <?php foreach($turmas as $turma): ?>
                            <option
                                value="<?= $turma['nome'] ?>"
                                <?= !empty($filtro['turma']) && $filtro['turma'] == $turma['nome'] ? 'selected' : ''; ?>
                            >
                                <?= $turma['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group btns search">
                <button type="submit" class="btn buscar">Buscar</button>
                <a href="./simulados" class="btn limpar">Limpar filtro</a>
            </div>

        </form>

        <div class="listagem-provas">

            <div class="header-listagem-provas">
                <h3>Simulados</h3>
                <a href="criar_simulado" class="add-prova">Criar Simulado</a>
            </div>

            <table class="tabela-provas">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Turma</th>
                        <th>Disciplinas</th>
                        <th>Área de Conhecimento</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="lista-provas">
                    <?php foreach($simulados as $simulado): ?>
                        <tr>
                            <td data-label="ID:">
                                <?= $simulado['id'] ?>
                            </td>
                            <td data-label="Nome:">
                                <?= $simulado['nome'] ?>
                            </td>
                            <td data-label="Turma:">
                                <?= $simulado['turma'] ?>
                            </td>
                            <td data-label="Disciplinas:">
                                <ul style="
                                        margin: 0;
                                        padding: 0;
                                        list-style: none;
                                        display: flex;
                                        column-gap: 9px;
                                        flex-wrap: wrap;
                                ">
                                    <?php foreach ($simulado['gabarito_professor'] as $gabarito): ?>
                                        <li>
                                            - <?= ucwords(mb_strtolower($gabarito['disciplina'])) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td data-label="Área de Conhecimento:">
                                <?= $simulado['area_conhecimento'] ?>
                            </td>
                            <td data-label="Ver">

                                <div class="group-btns">
                                    <a href="editar_simulado?simulado=<?= $simulado['id'] ?>" class="btn-padrao">Ver</a>
                                    <a href="download_multi_gabarito?simulado=<?= $simulado['id'] ?>" target="_blank" class="btn-padrao">Gabarito</a>
                                    <button type="button" class="btn-padrao excluir excluir-simulado" data-simulado="<?= $simulado['id'] ?>">Excluir</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</main>

<script src="./public/assents/js/gestor/script.js"></script>