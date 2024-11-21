// ### Config Câmera e Vídeo ###

// ctrl video
const videoSrc = document.getElementById("video-input");
const btnCapturarImagem = document.getElementById("capturar-imagem");
const btnPauseCamera = document.getElementById("pause-video");
const btnPlayCamera = document.getElementById("play-video");
const btnTrocarCamera = document.getElementById("trocar-camera");
const btnBackToCapture = document.getElementById("back-to-capture");

// config vídeo
const constraints = {
    video: {
        height: {
            min: 720,
            ideal: 1080,
            max: 1440,
        },
        width: {
            min: 1280,
            ideal: 1920,
            max: 2560,
        },
    },
};

// uutilizar câmera frontal
let useFrontCamera = false;

// stream do vídeo
let videoStream;

// iniciar video stream
btnPlayCamera.addEventListener("click", function () {
    videoSrc.play();
    btnPlayCamera.classList.add("d-none");
    btnPauseCamera.classList.remove("d-none");
});

// pausar video stream
btnPauseCamera.addEventListener("click", function() {
    videoSrc.pause();
    btnPauseCamera.classList.add("d-none");
    btnPlayCamera.classList.remove("d-none");
});

// trocar câmera
btnTrocarCamera.addEventListener("click", function() {
    useFrontCamera = !useFrontCamera;
    initializeCamera();
});

// take screenshot
btnCapturarImagem.addEventListener("click", function () {
    const canvas = document.getElementById("screenshot");
    // const ctx = canvas.getContext("2d");

    canvas.width = videoSrc.videoWidth;
    canvas.height = videoSrc.videoHeight;
    canvas.getContext("2d").drawImage(videoSrc, 0, 0);
    canvas.style.display = 'inline';

    stopVideoStream();
    videoSrc.style.display = 'none';

    processarGabarito()
});

btnBackToCapture.addEventListener("click", function () {
    document.getElementById("screenshot").style.display = 'none';
    videoSrc.style.display = 'inline';
    initializeCamera();
});

// parar video stream
function stopVideoStream() {
    if (videoStream) {
        videoStream.getTracks().forEach((track) => {
            track.stop();
        });
    }
}

// inicializar câmera
async function initializeCamera() {
    stopVideoStream();
    constraints.video.facingMode = useFrontCamera ? "user" : "environment";

    try {
        videoStream = await navigator.mediaDevices.getUserMedia(constraints);
        videoSrc.srcObject = videoStream;
    } catch (err) {
        alert("Could not access the camera");
    }
}

initializeCamera();


// ### Especificações da estrutura do gabarito
console.log(configAnswers)

// ### Config OpenCV Worker

// Cria o worker
const worker = new Worker('./public/assents/js/professor/worker/opencv_worker.js');

// Recebe a msg de volta do worker
worker.onmessage = function(event) {
    console.log(`recebi algo`);
    console.log(event);

    const command = event.data.command;

    // Seleção do comando que será executado de acordo com a resposta obtida
    switch (command) {
        case 'processed-image':
            const processedData = event.data.processedData;
            const processedMatInfo = event.data.processedMatInfo;

            // Atualiza o canvas com a imagem processada
            // showImage(processedData, processedMatInfo);
            showDebugImage(processedData, processedMatInfo);

            break;
        case 'marked-answers':
            const respostas = event.data.answers;
            console.log(respostas)

            if(respostas.length > 0) processarRespostas(respostas)

            break;
        case 'ready-to-work':
            const spinners = document.querySelectorAll('div.spinner-loading');
            spinners.forEach(spinner => spinner.style.display = 'none');

            break;
        default:
            console.log(`comando: ${command} não reconhecido`);
            break;
    }
};

worker.onerror = function(error) {
    console.error('Erro no Web Worker:', error.message);
};

function processarGabarito() {
    // Ler a imagem e criar um objeto `Mat`.
    const srcMat = cv.imread("screenshot");

    // Converte o Mat para um Uint8Array (array de bytes)
    const imageData = new Uint8Array(srcMat.data);

    // Prepara os metadados necessários para recriar o Mat no Worker
    const matInfo = {
        rows: srcMat.rows,
        cols: srcMat.cols,
        type: srcMat.type(),
        channels: srcMat.channels(),
    };

    // Envia os dados e as informações do Mat para o Worker
    worker.postMessage({command: "process-image", imageData, matInfo, configAnswers}, [imageData.buffer]);

    // Libera a memória do objeto Mat
    srcMat.delete();

    // document.querySelector('.canvas-spinner img').style.display = 'flex';
    // document.getElementById('steps').innerHTML = '';
}

function processarRespostas(respostas) {

    const options = {
        '': '',
        0:'A',
        1:'B',
        2:'C',
        3:'D',
        4:'E',
    };

    const listaRespostas = document.getElementById('lista-respostas');
    if (!listaRespostas) {
        console.error('Elemento #lista-respostas não encontrado.');
        return;
    }

    // Limpa as respostas anteriores
    listaRespostas.innerHTML = '';
    let todasAsPerguntas = '';

    respostas.forEach((resposta, index) => {
        // Verifica se o índice existe em correctAnswers
        if (index >= correctAnswers.length) {
            console.warn(`Resposta correta não encontrada para a questão ${index + 1}`);
            return;
        }

        const questaoNumero = index + 1;
        const alternativaSelecionada = options[resposta] || '';
        const statusResposta = correctAnswers[index] === alternativaSelecionada ? 'Correto' : 'Incorreto';

        // Cria a linha da tabela com innerHTML
        const tr = document.createElement("tr");

        let inputsHtml = '';
        Object.keys(options).forEach((key) => {
            if (key !== '') { // Ignora a opção de 'Nenhuma opção selecionada'
                const value = options[key];
                const isChecked = value === alternativaSelecionada ? 'checked' : '';
                const inputName = statusResposta === 'Correto' ? `perguntas_certas[${questaoNumero}]` : `perguntas_erradas[${questaoNumero}]`;

                inputsHtml += `
                    <td>
                        <label for="${questaoNumero},${value}">${value}</label>
                        <input type="radio" name="${inputName}" id="${questaoNumero},${value}" value="${value}" ${isChecked}>
                    </td>
                `;
            }
        });

        // Adiciona toda a estrutura de inputs para a questão
        tr.innerHTML = `
            <td>${questaoNumero}</td>
            ${inputsHtml}
        `;

        // Adiciona a linha à tabela
        listaRespostas.appendChild(tr);

        todasAsPerguntas += todasAsPerguntas.length > 0 ? `;${questaoNumero + ',' + alternativaSelecionada}` : `${questaoNumero + ',' + alternativaSelecionada}`;
    });

    document.querySelector("input#perguntas_respostas").value = todasAsPerguntas;
    document.querySelector("div.area-respostas").style.display = 'block';
}

// let respostas = [0, 1, 2, 3, 4, 0, 1, 2, 3, 4];
// processarRespostas(respostas);

function showDebugImage(processedData, processedMatInfo) {
    const canva = document.createElement("canvas");

    const processedMat = new cv.Mat(processedMatInfo.rows, processedMatInfo.cols, processedMatInfo.type);
    processedMat.data.set(new Uint8Array(processedData));

    // Desenha a imagem processada de volta no canvas
    cv.imshow(canva, processedMat);
    processedMat.delete();

    const modal = document.getElementById("images-debug");
    modal.appendChild(canva);
}

