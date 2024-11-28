const quill = new Quill('#quill-container', {
    theme: 'snow', // Tema básico do Quill
    placeholder: 'Escreva seu texto aqui...',
    modules: {
        toolbar: [
            [{ header: [1, 2, false] }], // Títulos
            ['bold', 'italic', 'underline'], // Formatação de texto
            [{ list: 'ordered' }, { list: 'bullet' }], // Listas
        ]
    }
});

// Texto padrão de orientações do gabarito
const defaultText = "Todas as questões são objetivas. Para cada questão, são apresentadas 4 opções de resposta, de \"A\" a \"E\". Apenas uma alternativa responde à questão.\nApós resolver à prova escrita, transfira suas respostas para o gabarito com atenção";

// Configurar o conteúdo padrão no editor
if(!document.getElementById('orientacoes').value){
    quill.setText(defaultText);
    document.getElementById('orientacoes').value = defaultText;
}

quill.root.addEventListener('keyup', function() {
    document.getElementById('orientacoes').value = quill.root.innerHTML;
});