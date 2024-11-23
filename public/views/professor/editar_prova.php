<?php extract($data); ?>

<main class="main-home-professor">
    <h1 class="titulo-NSL">NSL - SISTEMA DE MONITORAMENTO</h1>
    <h1>EDITAR GABARITO</h1>
    <h2><?= $nome ?></h2>

    <div class="">
        <form action="atualizar_gabarito" method="post">

            <div class="valor_pontos_editar">
                <h2>Valor</h2>
                <input type="text" name="valor_prova" value="<?= $valor ?>">
            </div>

            <br><br>

            <div class="professor-form-section-wrapper">
                <h3 class="form-section-header">METODO AVALIATIVO:</h3>
                <div class="professor-form-group">
                    <div>
                        <input type="radio" <?= $metodo == "prova" ? "checked" : "" ?> required id="metodo_prova" name="metodo" value="prova"
                            class="custom-radio-button">
                        <label style="width: 220px;" for="metodo_prova">PROVA AVALIATIVA</label>
                    </div>
                    <div>
                        <input type="radio"  <?= $metodo == "atividade" ? "checked" : "" ?>  required name="metodo" id="metodo_att" value="atividade"
                            class="custom-radio-button">
                        <label style="width: 220px;" for="metodo_att">ATIVIDADE DE REVISÃO</label>
                    </div>
                    <div>
                        <input type="radio"  <?= $metodo == "ama" ? "checked" : "" ?>  required name="metodo" id="metodo_ama" value="ama"
                            class="custom-radio-button">
                        <label style="width: 220px;" for="metodo_ama">AMA</label>
                    </div>
                </div>
            </div>
 
            <?php if (!empty($descritores)): ?>
                <div>
                    <h3>
                        Nos descritores, Não esqueça de colocar a "_" e o <br> prefixo da materia, Exemplo : "D027_M"
                    </h3>
                    <h4>Não coloque o descritor sem o prefixo </h4>
               </div>
            <?php endif; ?>

            <input type="hidden" name="numero_perguntas" value="<?= $perguntas ?>">
            <input type="hidden" name="descritor" value="<?= !empty($descritores) ? 'sim' : 'não' ?>">

            <table class="tabela-alternativas-escolher">

                <?php $gabarito = array_map('base64_decode', $gabarito); ?>

                <?php for ($i=1; $i <= $perguntas; $i++): ?>
                    <?php $resposta = explode(",", $gabarito[$i - 1])[1]; ?>

                    <tr>
                        <td>
                            <span><?=$i ?></span>
                        </td>

                        <?php if(!empty($descritores)): ?>
                            <td>
                                <?php
                                    $descritor = explode(",", $descritores[$i - 1])[1];
                                    echo $descritor;
                                ?>
                                <div style="display: block;" class="campos-selecionar-descritores">
                                    <input type="text"
                                           class="searchInput"
                                           data-index="<?= $i ?>"
                                           name="DESCRITOR_<?= $i ?>"
                                           value="<?= $descritor ?>"
                                           placeholder="DESCRITOR"
                                    />
                                    <div style="display: block;" class="descritoresContainer" data-index="<?= $i ?>"></div>
                                </div>
                            </td>
                        <?php endif; ?>

                        <?php foreach($alternativas as $alternativa): ?>
                            <td>
                                <div class="Ds" >
                                    <input type="radio"
                                           name="<?="{$i}"?>"
                                           required
                                           id="<?="{$i},{$alternativa}"?>"
                                           value="<?="{$i},{$alternativa}"?>"
                                            <?= $resposta === $alternativa ? 'checked' : '' ?>
                                    >
                                    <label for="<?="{$i},{$alternativa}"?>"><?= $alternativa?></label>
                                </div>
                            </td>
                        <?php endforeach; ?>

                        <td style="padding: 13px;border-top: solid 1px white; border-bottom:  solid 1px white;"></td>

                        <td>
                            <div class="label-anular">
                                <input type="radio"
                                       id="anular_<?= $i ?>"
                                       name="<?= $i ?>"
                                       required
                                       value="<?= "{$i},null" ?>"
                                        <?= $resposta == 'null' ? 'checked' : '' ?>
                                />
                                <label for="anular_<?= $i ?>">Anular Questão</label>
                            </div>
                        </td>

                    </tr>
                <?php endfor; ?>

            </table>
            <br>

            <h3>
                Ao selecionar a opção “anular questão” , a questão<br>
                será anulada e todos os alunos que escolheram <br>
                qualquer alternativa para essa questão receberão pontuação.
            </h3>

            <br><br><br>
            <input type="submit" value="Atualizar gabarito" class="botao-form-enviar">
        </form>

    </div>

</main>


