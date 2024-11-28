<?php extract($data); ?>

<main class="main-home-professor">

    <div class="group-btns" style="width: 100%;">
        <a href="lancar_gabarito?prova_id=<?= $prova['id'] ?>" class="btn-padrao">Voltar</a>
    </div>

    <center>
        <h1 class="titulo-NSL">NSL - SISTEMA DE MONITORAMENTO</h1>
    </center>

    <div class="container-lancar-prova">

        <div class="dados-aluno">
            <h3>Aluno(a): </h3>
            <p><?= $aluno['ra'] ?> - <?= $aluno['nome'] ?></p>

            <?php if($ja_respondido): ?>
                <div class="mensagem-alerta">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Atenção: as respostas deste aluno(a) foram incluídas nesta avaliação!</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if($simulado): ?>
            <ul class="dados-prova" style="margin: 0;">
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
                <?php if($prova['descritores']): ?>
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
                <h4>Quantidade de questões:</h4>
                <p><?= $prova['QNT_perguntas'] ?></p>
            </li>
        </ul>
    </div>


    <div class="container-lancar-prova">

        <div class="area-camera">

            <div style="position: relative;">
            <video id="video-input" class="video" autoplay playsinline></video>
                <div id="video-mask">
                    <div class="area-mask">
                        <div class="corner top-left"></div>
                        <div class="corner top-right"></div>
                        <div class="corner bottom-left"></div>
                        <div class="corner bottom-right"></div>
                    </div>
                </div>
            </div>

            <canvas id="screenshot" style="width: 100% !important; height: 100%; display: none;"></canvas>

            <div class="spinner-loading">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30">
                    <path d="M9.972 2.508a.5.5 0 0 0-.16-.556l-.178-.129a5 5 0 0 0-2.076-.783C6.215.862 4.504 1.229 2.84 3.133H1.786a.5.5 0 0 0-.354.147L.146 4.567a.5.5 0 0 0 0 .706l2.571 2.579a.5.5 0 0 0 .708 0l1.286-1.29a.5.5 0 0 0 .146-.353V5.57l8.387 8.873A.5.5 0 0 0 14 14.5l1.5-1.5a.5.5 0 0 0 .017-.689l-9.129-8.63c.747-.456 1.772-.839 3.112-.839a.5.5 0 0 0 .472-.334"/>
                </svg>
            </div>
        </div>

        <div id="cam-ctrl" class="group-btns controles">

            <button type="button" id="capturar-imagem" class="btn btn-padrao">
                Capturar
            </button>
            <button type="button" id="pause-video" class="btn btn-padrao">
                Pause
            </button>
            <button type="button" id="play-video" class="btn btn-padrao d-none">
                Play
            </button>
            <button type="button" id="trocar-camera" class="btn btn-padrao">
                Trocar
            </button>
            <button type="button" id="back-to-capture" class="btn btn-padrao close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>


            <div class="spinner-loading">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30">
                    <path d="M9.972 2.508a.5.5 0 0 0-.16-.556l-.178-.129a5 5 0 0 0-2.076-.783C6.215.862 4.504 1.229 2.84 3.133H1.786a.5.5 0 0 0-.354.147L.146 4.567a.5.5 0 0 0 0 .706l2.571 2.579a.5.5 0 0 0 .708 0l1.286-1.29a.5.5 0 0 0 .146-.353V5.57l8.387 8.873A.5.5 0 0 0 14 14.5l1.5-1.5a.5.5 0 0 0 .017-.689l-9.129-8.63c.747-.456 1.772-.839 3.112-.839a.5.5 0 0 0 .472-.334"/>
                </svg>
            </div>

        </div>

        <div class="area-respostas">

            <h3 class="respostas-marcadas">Respostas</h3>

            <form action="salvar_gabarito" method="post">
                <table id="tabela-respostas-gabarito">
                    <tbody id="lista-respostas">

                        <?php if(!empty($provas_do_simulado)): ?>

                            <?php for($i=0;$i<count($provas_do_simulado);$i++): ?>
                                <tr class="tr-header">
                                    <th colspan="6"><?= $provas_do_simulado[$i]['nome_prova']; ?></th>
                                </tr>
                                <tr class="tr-header">
                                    <th>Questão</th>
                                    <th colspan="5">Alternativas</th>
                                </tr>

                                <?php for($j=1; $j <= $provas_do_simulado[$i]['QNT_perguntas']; $j++): ?>
                                    <tr>
                                        <td>
                                            <?= $j ?>
                                        </td>
                                        <?php foreach(range('A', 'E') as $letra): ?>
                                            <td>
                                                <label for="<?= "prova-{$i}-pergunta-{$j}-{$letra}" ?>">
                                                    <?= $letra ?>
                                                </label>
                                                <input type="radio" 
                                                    name="prova[<?= $i ?>][perguntas_respostas][<?= $j ?>]" 
                                                    id="<?= "prova-{$i}-pergunta-{$j}-{$letra}" ?>"
                                                    value="<?= $letra ?>"
                                                >
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endfor ?>
                            <?php endfor; ?>
                            
                        <?php else: ?>

                            <tr class="tr-header">
                                <th colspan="6"><?= $prova['nome_prova'] ?></th>
                            </tr>
                            <tr class="tr-header">
                                <th>Questão</th>
                                <th colspan="5">Alternativas</th>
                            </tr>

                            <?php

                                $respostas = [];
                                if(!empty($ja_respondido)) {
                                    $pares = explode(";", $ja_respondido['perguntas_respostas']);

                                    foreach ($pares as $par) {
                                        list($chave, $valor) = explode(",", $par);
                                        $respostas[$chave] = $valor;
                                    }
                                }
                            ?>

                            <?php for($i=1; $i <= $prova['QNT_perguntas']; $i++): ?>
                                <tr>
                                    <td>
                                        <?= $i ?>
                                    </td>
                                    <?php foreach(range('A', 'E') as $letra): ?>
                                        <td>
                                            <label for="<?= "prova-0-pergunta-{$i}-{$letra}" ?>">
                                                <?= $letra ?>
                                            </label>
                                            <input type="radio" 
                                                name="prova[0][perguntas_respostas][<?= $i ?>]" 
                                                id="<?= "prova-0-pergunta-{$i}-{$letra}" ?>" 
                                                value="<?= $letra ?>"
                                                <?= (isset($respostas[$i]) && $respostas[$i] == $letra) ? 'checked' : '' ?>
                                            >
                                        </td>
                                    <?php endforeach; ?>

                                </tr>
                            <?php endfor ?>
                        <?php endif; ?>

                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">
                                <input type="hidden" name="prova_id" value="<?= $prova['id'] ?>">
                                <input type="hidden" name="simulado_id" value="<?= $he_simulado['simulado_id'] ?? '' ?>">
                                <input type="hidden" name="ra_aluno" value="<?= $aluno['ra'] ?>">
                                <button type="submit" class="btn enviar">Salvar respostas</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </form>

        </div>

    </div>
</main>

<div id="modal-debug">
    <div class="modal-content">
        <span class="close-btn">Fechar</span>
        <div id="images-debug"></div>
    </div>
</div>
<button id="openModalBtn">Abrir Modal</button>

<?php
    $disciplinas = $simulado
        ? array_values(array_map(
            fn($disciplina) => ucwords(mb_strtolower($disciplina['disciplina'])),
            $simulado['gabarito_professor']
        ))
        : [$prova['disciplina']];

    $disciplinasJson = json_encode($disciplinas);

    $gabarito = implode(';', array_map('base64_decode', explode(';', $prova['gabarito'])));
?>

<script>

    // Obtém o modal e o botão
    const modal = document.getElementById('modal-debug');
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.querySelector('.close-btn');

    // Função para abrir o modal
    openModalBtn.addEventListener('click', function () {
        modal.style.display = 'flex'; // Mostra o modal com flexbox
    });

    // Função para fechar o modal quando clicar no botão de fechar
    closeModalBtn.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    // Fechar o modal ao clicar fora dele
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // especificação de como está disponibilizado as respostas
    const configAnswers = {
        total_questao: `<?= $prova['QNT_perguntas'] ?>`, //quantidade total de questões
        total_alternativas: 5,                           // qtd de alternativas por questões
        qtdColumn: 1,                                    // qtd de colunas
        qtd_materias: <?= count($provas_do_simulado) > 0 ? count($provas_do_simulado) : 1 ?>,   // quantidade de colunas de matérias que tem no gabarito
        qtdX: 6,                                         // qtd de alternativas + identificação do n° questão
        qtdY: `<?= $prova['QNT_perguntas'] ?>`,                                        // qtd de questões por colunas
    }

    const alternativas = `<?= $gabarito ?>`.split(';');
    const respostasCorretas = alternativas.map(alternativa => {
        const partes = alternativa.split(',');
        return partes[1] ? partes[1].trim() : null;
    });
    
    const correctAnswers = respostasCorretas.filter(item => item !== null);
    const disciplinas = <?= $disciplinasJson ?>;


</script>
<!--<script src="./public/assents/js/professor/lib/opencv.js"></script>-->
<script src="https://docs.opencv.org/4.x/opencv.js"></script>
<script src="./public/assents/js/professor/main3.js"></script>
