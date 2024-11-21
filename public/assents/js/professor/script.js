const nomeBuscado = document.querySelector("#nome-aluno");
const table = document.querySelectorAll("table.tabela-alunos tbody tr");

nomeBuscado?.addEventListener('input', function(event) {
    const nomeInserido = normalizeString(this.value).toLowerCase();

    table.forEach(linha => {
        const nome = normalizeString(linha.dataset.nome).toLowerCase();
        linha.style.display = nome.includes(nomeInserido) ? 'block' : 'none';
    });
});

function normalizeString(str) {
    return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}



