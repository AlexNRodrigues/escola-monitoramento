// Listagem dos simulados
function alicarFiltros() {
    const disciplina = document.getElementById("disciplina").value.toLowerCase().trim();
    const professor = document.getElementById("professor").value.toLowerCase().trim();
    const turma = document.getElementById("turma").value.toLowerCase().trim();
    const rows = document.querySelectorAll(".tabela-provas tbody tr");

    rows.forEach(row => {

        const cellDisciplina = row.cells[3].textContent.toLowerCase().trim();
        const cellProfessor = row.cells[4].textContent.toLowerCase().trim();
        const cellTurma = row.cells[5].textContent.toLowerCase().trim();

        const matchesDisciplina = !disciplina || cellDisciplina === disciplina;
        const matchesProfessor = !professor || cellProfessor === professor;
        const matchesTurma = !turma || cellTurma === turma;

        // Exibir a linha se atender a todos os filtros, caso contrário, ocultar
        row.style.display = (matchesDisciplina && matchesProfessor && matchesTurma) ? "" : "none";
    });
}


// Ordem das provas simuladas
const selectedItems = [];
const listaOrdemSelecao = document.getElementById("lista-ordem-selecao");
const ordemSelecaoInput = document.getElementById("ordem-selecao-input");

// Função para atualizar a exibição da ordem de seleção
function updateOrderDisplay() {
    if(listaOrdemSelecao !== null) {
        listaOrdemSelecao.innerHTML = "";
        document.getElementById("ordem-selecao").style.display = selectedItems.length > 0 ? 'block' : 'none';

        // Para cada item selecionado, adiciona um item à lista
        selectedItems.forEach((item, index) => {
            const listItem = document.createElement("li");
            listItem.textContent = `Seleção ${index + 1}: Prova ID ${item.id}, Nome: ${item.nome}`;
            listaOrdemSelecao.appendChild(listItem);
        });

        // Atualiza o valor do input oculto com a ordem dos IDs selecionados
        ordemSelecaoInput.value = selectedItems.map(item => item.id).join(",");
    }
}

// Obter todos os checkboxes na tabela
const checkboxes = document.querySelectorAll('input[type="checkbox"][name="id_prova[]"]');

// Adicionar evento de clique para cada checkbox
checkboxes.forEach(checkbox => {
    checkbox.addEventListener("change", function () {
        const provaId = this.value;
        const provaNome = this.closest("tr").querySelector('[data-label="Nome:"]').textContent.trim();

        if (this.checked) {
            selectedItems.push({ id: provaId, nome: provaNome }); // Adiciona ao array com o ID e o Nome
        } else {
            const index = selectedItems.findIndex(item => item.id === provaId);
            if (index > -1) {
                selectedItems.splice(index, 1); // Remove do array
            }
        }

        updateOrderDisplay();
    });
});

// Evento de clique para o botão "Excluir"
document.querySelectorAll('button.excluir-simulado').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();

        const confirmarExclusao = confirm("Tem certeza de que deseja excluir este simulado?");

        if (confirmarExclusao) {
            // Envia a requisição POST para 'excluir_simulado' usando Fetch API
            fetch('excluir_simulado', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    mensagem: "Exclusão solicitada para o simulado",
                    simulado: btn.dataset.simulado,
                })
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message || "Simulado excluído com sucesso!");
                    window.location.reload();
                })
                .catch(error => console.error('Erro:', error));
        }
    });
});

// #################################### //

// Página de criação de um simulado
document.getElementById("disciplina")?.addEventListener("change", alicarFiltros);
document.getElementById("professor")?.addEventListener("change", alicarFiltros);
document.getElementById("turma")?.addEventListener("change", alicarFiltros);

const formEnviarProvasSelecionadas = document.querySelector("form.listagem-provas");
const inputs = document.querySelectorAll("input[type='checkbox']");
const valoresSelecionados = [];

inputs.forEach(input => {
    input.addEventListener('change', () => {
        const selecionados = Array.from(inputs).filter(input => input.checked);
        if (input.checked && selecionados.length > 3) {
            input.checked = false;
            alert('O limite de seleção é de três avaliações.');
        } else if (input.checked) {
            valoresSelecionados.push(input.value);
        } else {
            const index = valoresSelecionados.indexOf(input.value);
            if (index > -1) {
                valoresSelecionados.splice(index, 1);
            }
        }
    });
});

window.addEventListener('load', function () {
        const selecionados = Array.from(inputs).filter(input => input.checked);

        selecionados.forEach((input) => {
            valoresSelecionados.push(input.value);
            selectedItems.push({
                id: input.dataset.id,
                nome: input.closest("tr").querySelector('[data-label="Nome:"]').textContent.trim()
            });
        })

        updateOrderDisplay()
});



formEnviarProvasSelecionadas?.addEventListener('submit', function(event) {
    event.preventDefault();
    
    if(valoresSelecionados.length > 0) {
        this.submit();
    } else {
        alert('Selecione, no mínimo, uma avaliação para elaborar o gabarito.');
    }
});
