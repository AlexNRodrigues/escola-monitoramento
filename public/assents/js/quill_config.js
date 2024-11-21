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

quill.root.addEventListener('keyup', function() {
    document.getElementById('orientacoes').value = quill.root.innerHTML;
});