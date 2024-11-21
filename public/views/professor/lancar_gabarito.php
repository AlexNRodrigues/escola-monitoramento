<?php extract($data); ?>

<main class="main-home-professor">

    <div class="group-btns" style="width: 100%;">
        <a href="ver_provas" class="btn-padrao">Voltar</a>
    </div>

    <center>
        <h1 class="titulo-NSL">NSL - SISTEMA DE MONITORAMENTO</h1>
    </center>

    <div class="container-lancar-prova">

        <?php if($simulado): ?>
            <ul class="dados-prova">
                <li>
                    <h4>Simulado:</h4>
                    <p><?= $simulado['nome'] ?></p>
                </li>
                <li>
                    <h4>Disciplinas:</h4>
                    <p>
                        <?php foreach ($simulado['gabarito_professor'] as $gabarito): ?>
                            - <?= ucwords(mb_strtolower($gabarito['disciplina'])) ?>
                        <?php endforeach; ?>
                    </p>
                </li>
                <li>
                    <h4>Área de Conhecimento:</h4>
                    <p><?= $simulado['area_conhecimento'] ?></p>
                </li>
                <li></li>
            </ul>
        <?php endif; ?>

        <ul class="dados-prova">
            <li>
                <h4>Prova:</h4>
                <p><?= $prova['nome_prova'] ?></p>
            </li>
            <li>
                <h4>Turma:</h4> 
                <p><?= $prova['turmas'] ?></p>
            </li>
            <li>
                <h4>Disciplina:</h4>
                <p><?= ucwords(mb_strtolower($prova['disciplina'])) ?></p>
            </li>
            <li>
                <h4>Descritores:</h4>
                <?php if(!empty($prova['descritores'])): ?>
                    <?php
                        $descritores = array_map(
                            fn ($d) => explode(',', $d)[1],
                            explode(';', $prova['descritores'])
                        );
                    ?>
                    <ul class="lista-descritores">
                        <?php foreach ($descritores as $descritor): ?>
                            <li>- <?= $descritor ?></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif; ?>
            </li>
            <li>
                <h4>Valor:</h4>
                <p><?= $prova['valor'] ?></p>
            </li>
            <li>
                <h4>Quantidade de questõe:</h4>
                <p><?= $prova['QNT_perguntas'] ?></p>
            </li>
        </ul>

    </div>
    
    <div class="container-lancar-prova">
    
        <h2 class="nome-aluno">Aluno(a)</h2>
        
        <div class="box-lista-alunos">
            <div class="form-group">
                <label for="nome-aluno">Nome</label>
                <input type="text" name="nome-aluno" id="nome-aluno">
            </div>

            <table class="tabela-alunos">
                <thead>
                    <tr>
                        <th>RA</th>
                        <th>Nome</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="lista-provas">
                    <?php foreach($alunos As $aluno): ?>
                        <tr data-nome="<?= mb_strtolower($aluno['nome']) ?>">
                            <td data-label="RA: ">
                                <?= $aluno['ra'] ?>
                            </td>
                            <td data-label="Nome: ">
                                <?= ucwords(mb_strtolower($aluno['nome'])) ?>
                            </td>
                            <td>
                                <?php if(($respotas_lancadas[$aluno['ra']] ?? null) === $aluno['nome']): ?>
                                    <span class="badge">OK</span>
                                <?php endif; ?>
                                <a href="capturar_gabarito?prova_id=<?= $prova['id'] ?>&ra_aluno=<?= $aluno['ra'] ?>" class="botao-form-enviar-prova botao-form-lancar-gabarito" style="width: fit-content;">
                                    Lançar Respostas
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="./public/assents/js/professor/script.js"></script>