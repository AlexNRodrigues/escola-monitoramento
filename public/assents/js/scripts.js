 

function Mostrar_campo_email() {
    document.querySelector("#campo-ra").style.display = "none";
    document.querySelector("#campo-email").style.display = "block";
    const container = document.querySelector(".login-aluno");
    container.style.height = "480px"
}

function Mostrar_campo_ra() {
    document.querySelector("#campo-ra").style.display = "block";
    document.querySelector("#campo-email").style.display = "none";
    const container = document.querySelector(".login-aluno");
    container.style.height = "400px"
}

function Fechar_PopUp(popup) {
    var popupElement = document.getElementById(popup);
    popupElement.style.display = 'none';
  }
  
  

function Mostrar_PopUp(popup){
    document.getElementById(popup).style.display = 'block';
}
 
// function Mostrar_container_gestor(container){
//     document.getElementById("container-gestor-01").style.display = "none";
//     document.getElementById("container-gestor-02").style.display = "none";
//     document.getElementById("container-gestor-03").style.display = "none";
//     document.getElementById("container-gestor-04").style.display = "none";
//     document.getElementById("container-gestor-05").style.display = "none";

//     document.getElementById(container).style.display = "block";

// }

function mostrarConteudo(id) {
    var conteudos = document.querySelectorAll('.conteudo-item');
    conteudos.forEach(function(conteudo) {
        conteudo.classList.remove('active');
    });
    document.getElementById(id).classList.add('active');
}
  document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('.searchInput');

    searchInputs.forEach(searchInput => {
        searchInput.addEventListener('input', function() {
            const inputText = this.value.trim();
            const index = this.dataset.index;
            const descritoresContainer = document.querySelector(`.descritoresContainer[data-index="${index}"]`);
            if (inputText.length === 0) {
                descritoresContainer.innerHTML = '';
                return;
            }
            fetch('app/config/GetDescritores.php')
                .then(response => response.json())
                .then(data => {
                    const filteredDescritores = data.filter(descritor => {
                        return descritor.descritor.toLowerCase().includes(inputText.toLowerCase());
                    });
                    renderDescritores(filteredDescritores, descritoresContainer);
                })
                .catch(error => console.error('Erro ao obter descritores:', error));
        });
    });

    function renderDescritores(descritores, container) {
        container.innerHTML = '';

        descritores.forEach(descritor => {
            const div = document.createElement('div');
            div.textContent = descritor.descritor;
            div.classList.add('descritor');
            div.addEventListener('click', function() {
                container.previousElementSibling.value = descritor.descritor;
                container.innerHTML = ''; // Oculta a lista de descritores após a seleção
            });
            container.appendChild(div);
        });
    }

    // Adiciona um event listener para clicar em qualquer parte do documento
    document.addEventListener('click', function(event) {
        const clickedElement = event.target;

        // Verifica se o clique ocorreu fora do campo de busca e da lista de descritores
        if (!clickedElement.classList.contains('searchInput') && !clickedElement.classList.contains('descritor')) {
            const allDescritoresContainers = document.querySelectorAll('.descritoresContainer');
            allDescritoresContainers.forEach(container => {
                container.innerHTML = ''; // Oculta todas as listas de descritores
            });
        }
    });
}); 
 
const menu = document.getElementById("area_menu_lateral")
const menuBtn = document.getElementById("icone-menu-lateral")
const menu_conteudo = document.getElementById("menu-lateral-icone-conteudo")
const icone_fechar_menu = document.querySelector(".icone-menu-lateral-fechar")
//menu esquerdo
const menuBtnleft = document.getElementById("icone-menu-esquerdo-lateral")
const menu_conteudo_left = document.getElementById("menu-lateral-esquerdo-icone-conteudo")
const icone_fechar_menu_left = document.querySelector(".icone-menu-lateral-esquerdo-fechar")
 

icone_fechar_menu.addEventListener('click', function() {
    menu.style.display = "none"
    menu.style.backgroundColor = "rgba(0, 0, 0, 0)"
    document.body.classList.remove('no-scroll');
    menu_conteudo.style.right = '-320px'
})

menuBtn.addEventListener('click', function() {
    document.body.classList.add('no-scroll');
    menu.style.display = "block"
    menu.style.backgroundColor = "rgba(0, 0, 0, 0.507)"
    menu_conteudo.style.right = '0px'
})

icone_fechar_menu_left.addEventListener('click', function() {
    menu.style.display = "none"
    menu.style.backgroundColor = "rgba(0, 0, 0, 0)"
    menu_conteudo_left.style.left = '-320px'
    document.body.classList.remove('no-scroll');
})

menuBtnleft.addEventListener('click', function() {
    menu.style.display = "block"
    menu.style.backgroundColor = "rgba(0, 0, 0, 0.507)"
    document.body.classList.add('no-scroll');
    menu_conteudo_left.style.left = '0px'
})

document.addEventListener("DOMContentLoaded", function() {
    const circles = document.querySelectorAll('.animated-circle');
    circles.forEach(circle => {
        const offset = circle.getAttribute('data-offset');
        circle.style.setProperty('--offset', offset);
        circle.style.animation = 'none';
        void circle.offsetWidth; 
        circle.style.animation = null; 
    });
});

function resetForm() {
    const form = document.getElementById('filterForm');
    form.reset();
 
    document.getElementById('turma').selectedIndex = 0;
    document.getElementById('turno').selectedIndex = 0;
    document.getElementById('disciplina').selectedIndex = 0;
    document.getElementById('serie').selectedIndex = 0;
    document.getElementById('professor').selectedIndex = 0;
    document.getElementById('periodo').selectedIndex = 0;
    document.getElementById('metodo').selectedIndex = 0;
}

function resetFormProva() {
    const form = document.getElementById('filterFormProva'); 
    document.getElementById('disciplina').selectedIndex = 0; 
    document.getElementById('professor').selectedIndex = 0;
}

function resetFormDesc() {
    const form = document.getElementById('filterFormDesc');
    form.reset(); 
    document.getElementById('turma').selectedIndex = 0;
    document.getElementById('turno').selectedIndex = 0;
    document.getElementById('disciplina').selectedIndex = 0;
    document.getElementById('serie').selectedIndex = 0; 
    document.getElementById('descritor').value = '';
}

// document.addEventListener('DOMContentLoaded', (event) => { 
//     const formularios = document.querySelectorAll('form');

//     formularios.forEach(formulario => {
//         formulario.addEventListener('submit', function(event) {
//             event.preventDefault();  
//         });
//     });
// });
 
function exportToExcel(nome, tableId) {
    var table = document.getElementById(tableId); 

    var sheet = XLSX.utils.table_to_sheet(table);

    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, sheet, 'Dados');

    XLSX.writeFile(wb, nome + '.xlsx');
}

function mostarTabela(tabela) {
    var descritores = document.getElementById("table-descritores-primeira");
    var descritores_rec = document.getElementById("table-descritores-rec");
    var botoes_descritores = document.getElementById("botoes-alternar-prova");
    var respostas = document.getElementById("table-respostas");
    var notas = document.getElementById("table-notas");

    var tableNotas = document.getElementById("export-notas");
    var tableDesc = document.getElementById("export-descritores");
    var tableResp = document.getElementById("export-respostas");

    if (tabela == "RESPOSTAS") {
        if(tableNotas) tableNotas.classList.add("hidden");
        if(tableDesc) tableDesc.classList.add("hidden");
        if(tableResp) tableResp.classList.remove("hidden");
        if (notas) notas.classList.add("hidden");
        if (respostas) respostas.classList.remove("hidden");
        if (descritores) descritores.classList.add("hidden");
        if (descritores_rec) descritores_rec.classList.add("hidden");
        if (botoes_descritores) botoes_descritores.classList.add("hidden");
    } else if (tabela == "DESCRITORES") {
        if(tableNotas) tableNotas.classList.add("hidden");
        if(tableDesc) tableDesc.classList.remove("hidden");
        if(tableResp) tableResp.classList.add("hidden");
        if (descritores) descritores.classList.remove("hidden");
        if (notas) notas.classList.add("hidden");
        if (respostas) respostas.classList.add("hidden");
        if (descritores_rec) descritores_rec.classList.add("hidden");
        if (botoes_descritores) botoes_descritores.classList.remove("hidden");
    } else if (tabela == "NOTAS") {
        if(tableNotas) tableNotas.classList.remove("hidden");
        if(tableDesc) tableDesc.classList.add("hidden");
        if(tableResp) tableResp.classList.add("hidden");
        if (notas) notas.classList.remove("hidden");
        if (descritores) descritores.classList.add("hidden");
        if (respostas) respostas.classList.add("hidden");
        if (descritores_rec) descritores_rec.classList.add("hidden");
        if (botoes_descritores) botoes_descritores.classList.add("hidden");
    } else {
        if (descritores_rec) descritores_rec.classList.remove("hidden");
        if (notas) notas.classList.add("hidden");
        if (descritores) descritores.classList.add("hidden");
    }
}

function MostrarCarregamento() {
    var form = document.querySelector('form');
    var radiosValidos = true;

    form.querySelectorAll('input[type=radio]').forEach(function(radio) {
        var name = radio.getAttribute('name');
        var radioGroup = form.querySelectorAll('input[type=radio][name="' + name + '"]');
        var checked = Array.from(radioGroup).some(radio => radio.checked);

        if (!checked) {
            radiosValidos = false;
        }
    });

    if (radiosValidos) {
        var carregar = document.getElementById("div_carregamento");
        var button_gab = document.getElementById("button_enviar_gabarito");

        carregar.classList.remove("hidden");
        button_gab.classList.add("hidden");
    } else {
        return false;
    }
}


function filtrarAlunos() {
    var inputRA, inputNome, filterRA, filterNome, selectTurma, filterTurma, table, tr, tdRA, tdNome, tdTurma, i;
    inputRA = document.getElementById("filtroRAAlunos");
    inputNome = document.getElementById("filtroNomeAlunos");
    selectTurma = document.getElementById("selecionar-turmas-aluno");
    filterRA = inputRA.value.toUpperCase();
    filterNome = inputNome.value.toUpperCase();
    filterTurma = selectTurma.value.toUpperCase();
    table = document.getElementById("tabelaAlunos");
    tr = table.getElementsByTagName("tr");

    for (i = 0; i < tr.length; i++) {
        tdRA = tr[i].getElementsByTagName("td")[0];
        tdNome = tr[i].getElementsByTagName("td")[1];
        tdTurma = tr[i].getElementsByTagName("td")[2];

        if (tdRA && tdNome && tdTurma) {
            var raValue = tdRA.textContent || tdRA.innerText;
            var nomeValue = tdNome.textContent || tdNome.innerText;
            var turmaValue = tdTurma.textContent || tdTurma.innerText;

            if (
                (raValue.toUpperCase().indexOf(filterRA) > -1 || filterRA === '') &&
                (nomeValue.toUpperCase().indexOf(filterNome) > -1 || filterNome === '') &&
                (turmaValue.toUpperCase().indexOf(filterTurma) > -1 || filterTurma === 'SELECIONAR')
            ) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}


function filtrarTabela(tabelaId, filtroRAId, filtroNomeId) {
    var filtroRA = document.getElementById(filtroRAId).value.toUpperCase();
    var filtroNome = document.getElementById(filtroNomeId).value.toUpperCase();

    $.ajax({
        url: 'app/config/GetProvas.php',
        method: 'GET',
        success: function(data) {
            var dados = JSON.parse(data);
            var tabela = document.getElementById(tabelaId);
            var tbody = tabela.getElementsByTagName('tbody')[0];
            tbody.innerHTML = '';

            dados.forEach(function(aluno) {
                if (aluno.ra.toUpperCase().indexOf(filtroRA) > -1 && aluno.aluno.toUpperCase().indexOf(filtroNome) > -1) {
                    var tr = document.createElement('tr');
            
                    var tdRA = document.createElement('td');
                    tdRA.textContent = aluno.ra;
                    tr.appendChild(tdRA);
            
                    var tdNome = document.createElement('td');
                    tdNome.textContent = aluno.aluno;
                    tr.appendChild(tdNome);
            
                    var tdTurma = document.createElement('td');
                    tdTurma.textContent = aluno.turma;
                    tr.appendChild(tdTurma);
            
                    var tdData = document.createElement('td');
                    tdData.textContent = aluno.data_aluno;
                    tr.appendChild(tdData);
            
                    var tdDisciplina = document.createElement('td');
                    tdDisciplina.textContent = aluno.disciplina;
                    tr.appendChild(tdDisciplina);

                                
                    var tdDisciplina = document.createElement('td');
                    tdDisciplina.textContent = aluno.nome_prova;
                    tr.appendChild(tdDisciplina);
            
                    var tdPontos = document.createElement('td');
                    tdPontos.textContent = aluno.pontos_aluno;
                    tr.appendChild(tdPontos);
            
                    // Botão Editar
                    var tdEditar = document.createElement('td');
                    var btnEditar = document.createElement('button');
                    btnEditar.className = 'btn-editar';
                    btnEditar.textContent = 'EDITAR';
            
                    // Definindo os parâmetros diretamente no onclick
                    btnEditar.onclick = function() {
                        editarProvaAluno(
                            aluno.ra,
                            aluno.perguntas_respostas,
                            aluno.aluno,
                            aluno.id_prova,
                            aluno.id,
                            aluno.disciplina,
                            aluno.nome_prova,
                            aluno.data_aluno,
                            aluno.turma
                        );
                    };
            
                    tdEditar.appendChild(btnEditar);
                    tr.appendChild(tdEditar);
            
                    // Botão Excluir
                    var tdExcluir = document.createElement('td');
                    var formExcluir = document.createElement('form');
                    formExcluir.method = 'post';
                    var btnExcluir = document.createElement('button');
                    btnExcluir.type = 'submit';
                    btnExcluir.className = 'btn-excluir';
                    btnExcluir.textContent = 'EXCLUIR';
                    btnExcluir.value = aluno.ra + ';' + aluno.id_prova + ';' + aluno.aluno + ';' + aluno.disciplina;
                    btnExcluir.name = 'excluir-prova-aluno';
                    formExcluir.appendChild(btnExcluir);
                    tdExcluir.appendChild(formExcluir);
                    tr.appendChild(tdExcluir);
            
                    tbody.appendChild(tr);
                }
            });
        }
    });
}




function filtrarTabelaMaterias() {
    var tabela, tr, td, i, nome, filtroNome;
    tabela = document.getElementById("tabelaMaterias");
    tr = tabela.getElementsByTagName("tr");
    filtroNome = document.getElementById("filtroNomeMaterias").value.toUpperCase();

    for (i = 1; i < tr.length; i++) {
        nome = tr[i].getElementsByTagName("td")[1];
        if (nome) {
            if (nome.innerHTML.toUpperCase().indexOf(filtroNome) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

function filtrarTabelaProfessores() {
    var tabela, tr, td, i, nome, filtroNome;
    tabela = document.getElementById("tabelaProfessores");
    tr = tabela.getElementsByTagName("tr");
    filtroNome = document.getElementById("filtroNomeProfessores").value.toUpperCase();

    for (i = 1; i < tr.length; i++) {
        nome = tr[i].getElementsByTagName("td")[0];
        if (nome) {
            if (nome.innerHTML.toUpperCase().indexOf(filtroNome) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}


function editarAluno(ra, nome, turma, turno, dataNasc) {
    document.getElementById('tabelaAlunos').style.display = 'none';
    document.getElementById('filtro-container-alunos').style.display = 'none';
    document.getElementById('formEditar').classList.add('active');
    document.getElementById('editarRA').value = ra;
    document.querySelector('#alunos h1').style.display = 'none';
    document.getElementById('editarNome').value = nome;
    document.getElementById('data').value = dataNasc;

    // Seleciona a turma correspondente
    var turmas = document.getElementsByName('turma');
    for (var i = 0; i < turmas.length; i++) {
        if (turmas[i].value === turma) {
            turmas[i].checked = true;
            break;
        }
    }

    // Seleciona o turno correspondente
    var turnos = document.getElementsByName('turno');
    for (var i = 0; i < turnos.length; i++) {
        if (turnos[i].value === turno) {
            turnos[i].checked = true;
            break;
        }
    }
}

function ColocarDadosProf(id, nome, usuario, senha, disciplinas) {
    // Ocultar a tabela e mostrar o formulário de edição
    document.getElementById('tabelaProfessores').style.display = 'none';
    document.getElementById('filtro-professor').style.display = 'none';
    document.getElementById('titulo-professor').style.display = 'none';
    document.getElementById('brs').style.display = 'none';
    document.getElementById('form-editar-professor').classList.remove('hidden');

    // Preencher os campos do formulário com os dados do professor
    document.getElementById('nome_professor_editar_add').value = nome;
    document.getElementById('usuario_acesso_editar').value = usuario;
    document.getElementById('id-professor-editar').value = id;
    document.getElementById('senha_acesso_editar').value = senha;

    // Dividir as disciplinas e marcar as checkboxes correspondentes
    let disciplinasArray = disciplinas.split(';');
    let checkboxes = document.querySelectorAll('input[name="disciplina_professor_editar_[]"]');

    // Desmarcar todas as checkboxes
    checkboxes.forEach((checkbox) => {
        checkbox.checked = false;
    });

    // Marcar as disciplinas do professor
    disciplinasArray.forEach((disciplina) => {
        let checkbox = document.getElementById(`disciplina_professor_editar_${disciplina}`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}
 

function cancelarEdicao() {
    document.getElementById('filtro-container-alunos').style.display = 'block';
    document.getElementById('formEditar').classList.remove('active');
    document.querySelector('#alunos h1').style.display = 'block';
    document.getElementById('tabelaAlunos').style.display = 'table';

    document.getElementById('tabelaProfessores').style.display = 'block';
    document.getElementById('filtro-professor').style.display = 'block';
    document.getElementById('brs').style.display = 'block';
    document.getElementById('titulo-professor').style.display = 'block';
    document.getElementById('form-editar-professor').classList.add('hidden');

    document.getElementById('tabelaProvas').style.display = 'block';
    document.getElementById('titulo-provas-feitas').style.display = 'block';
    document.getElementById('filtro-container-provas').style.display = 'block';
    document.getElementById('editar-prova-aluno').style.display = 'none';
}

function AlterarModoAddTurma(container,status){

    if(status == "mostrar"){
        document.getElementById(container).classList.remove("hidden");
        input = document.getElementById("nomeTurma").setAttribute("required","true");
    }else{
        document.getElementById(container).classList.add("hidden");
        input = document.getElementById("nomeTurma").removeAttribute("required");
    }
}

function editarProvaAluno(ra, gabarito, nome,IdProva,id,disciplina,data,turma) {
    document.getElementById('tabelaProvas').style.display = 'none';
    document.getElementById('titulo-provas-feitas').style.display = 'none';
    document.getElementById('filtro-container-provas').style.display = 'none';
    document.getElementById('editar-prova-aluno').style.display = 'block';

    document.getElementById('nome-aluno-editar').innerText = nome;
    document.getElementById('disciplina_prova').innerText = disciplina;
    document.getElementById('data_aluno').innerText = data;

    document.getElementById('ra_prova').value = ra;
    document.getElementById('nome_aluno_prova').value = nome;
    document.getElementById('id_prova').value = IdProva;
    document.getElementById('id_aluno_prova').value = id; 

    var turmas = document.getElementsByName('turmas_prova');
    for (var i = 0; i < turmas.length; i++) {
        if (turmas[i].value === turma) {
            turmas[i].checked = true;
            break;
        }
    }


    let respostas = gabarito.split(';');
    let tabela = '<table class="aluno_inserir_gabarito">';

    respostas.forEach(function(resposta) {
        let [questao, alternativa] = resposta.split(',');

        tabela += `
            <tr>
                <td><span>${questao}</span></td>
                <td><div><input type="radio" name="gabarito_questao_${questao}" value="${questao},A" ${alternativa === 'A' ? 'checked' : ''}><span>A</span></div></td>
                <td><div><input type="radio" name="gabarito_questao_${questao}" value="${questao},B" ${alternativa === 'B' ? 'checked' : ''}><span>B</span></div></td>
                <td><div><input type="radio" name="gabarito_questao_${questao}" value="${questao},C" ${alternativa === 'C' ? 'checked' : ''}><span>C</span></div></td>
                <td><div><input type="radio" name="gabarito_questao_${questao}" value="${questao},D" ${alternativa === 'D' ? 'checked' : ''}><span>D</span></div></td>
                <td><div><input type="radio" name="gabarito_questao_${questao}" value="${questao},E" ${alternativa === 'E' ? 'checked' : ''}><span>E</span></div></td>
            </tr>
        `;
    });

    tabela += "</table>";

    
    document.getElementById('tabela-alternativar-editar').innerHTML = tabela;
}
 
document.querySelectorAll('.professor-selectable-numbers .number-box').forEach(box => {
    box.addEventListener('click', function() {
        const parentDiv = this.parentElement;
        const hiddenInput = parentDiv.nextElementSibling;

        parentDiv.querySelectorAll('.number-box').forEach(box => box.classList.remove('selected'));
        this.classList.add('selected');
        hiddenInput.value = this.getAttribute('data-value');
    });
});

document.getElementById('metodo_prova')?.addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('valor-container').style.display = 'block';
    }
});

document.getElementById('metodo_att')?.addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('valor-container').style.display = 'none';
    }
});

if (document.getElementById('metodo_att') && document.getElementById('metodo_att').checked) {
    document.getElementById('valor-container').style.display = 'none';
}

