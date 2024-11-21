// importScripts('https://docs.opencv.org/4.x/opencv.js');
importScripts('../lib/opencv.js');

let cvReady = false;
let configAnswers;

// Certifique-se de que o OpenCV.js está carregado antes de processar qualquer imagem
cv.onRuntimeInitialized = function() {
    cvReady = true;
    console.log("OpenCV.js está pronto!");

    self.postMessage({command: "ready-to-work", cvReady });
};

self.onmessage = function (event) {
    // Verifica se o OpenCV.js já foi carregado
    if (!cvReady) {
        console.error("Erro: OpenCV.js ainda não está pronto.");
        return;
    }
    const command = event.data.command;
    const imageData = event.data.imageData;
    const matInfo = event.data.matInfo;
    configAnswers = event.data?.configAnswers;

    if (command === "process-image") {
        // Recria o Mat no Worker a partir do Uint8Array
        const mat = new cv.Mat(matInfo.rows, matInfo.cols, matInfo.type);
        mat.data.set(new Uint8Array(imageData));

        // Função que irá processar a imagem
        processImage(mat);
    }
};

function processImage(mat) {
    // Convertendo para escala de cinza
    const grayMat = new cv.Mat();
    cv.cvtColor(mat, grayMat, cv.COLOR_RGBA2GRAY);

    // Aplicando o GaussianBlur para reduzir o ruído.
    const blurredMat = new cv.Mat();
    cv.GaussianBlur(grayMat, blurredMat, new cv.Size(5, 5), 0);
    // sendProcessedImage(blurredMat)

    // Aplicando detecção de bordas para encontrar as bordas exatas do retângulo
    const edges = new cv.Mat();
    cv.Canny(blurredMat, edges, 75, 200);

    // sendProcessedImage(edges);

    // Encontrando os contornos
    let contours = new cv.MatVector();
    let hierarchy = new cv.Mat();
    cv.findContours(edges, contours, hierarchy, cv.RETR_CCOMP, cv.CHAIN_APPROX_SIMPLE);

    // Variáveis para armazenar o maior contorno que tenha quatro lados
    let largestContour = null;
    let largestContourIndex = 0;
    let maxArea = 0;

    // console.log(contours.size())

    for (let i = 0; i < contours.size(); i++) {
        let contour = contours.get(i);

        // desenhar um contorno nas bordas encontradas
        // cv.drawContours( mat, contours, i, new cv.Scalar(255, 0, 0, 255), 1, cv.LINE_8, hierarchy, 100);

        // Aproximando o contorno a um polígono com menos vértices
        let perimeter = cv.arcLength(contour, true);
        let approx = new cv.Mat();
        cv.approxPolyDP(contour, approx, 0.02 * perimeter, true);

        // Se o contorno tem 4 lados
        if (approx.rows === 4) {
            let area = cv.contourArea(contour);
            if (area > maxArea) {
                maxArea = area;
                largestContour = approx;
                largestContourIndex = i;
            }
        }

        // approx.delete();
    }

    // Se encontrar um contorno maior com 4 lados, desenhe-o na imagem
    if (largestContour) {
        // cv.drawContours( mat, contours, largestContourIndex, new cv.Scalar(0, 255, 0, 255), 2, cv.LINE_8, hierarchy, 100);

        // Obter o retângulo de mínimo ajuste
        let rect = cv.minAreaRect(largestContour);

        let points = [];

        // recuperar o vértice e cada ponto encontrado no maior contorno
        for (let j = 0; j < largestContour.rows; ++j) {
            let x = largestContour.intPtr(j, 0)[0];
            let y = largestContour.intPtr(j, 0)[1];

            points.push({x: x, y: y});
        }

        // Todos os pontos de vértices encontrados no contorno
        // console.log(points);

        // Encontra os pontos específicos
        let sortedBySum = points.slice().sort((a, b) => (a.x + a.y) - (b.x + b.y));
        let sortedByDiff = points.slice().sort((a, b) => (a.x - a.y) - (b.x - b.y));

        // vertíces ordenados
        // console.log(sortedBySum, sortedByDiff);

        let topLeft = sortedBySum[0];                                // Menor soma x + y
        let bottomLeft = sortedByDiff[0];                            // Maior soma x + y
        let bottomRight = sortedBySum[sortedBySum.length - 1];       // Menor diferença x - y
        let topRight = sortedByDiff[sortedByDiff.length - 1];        // Maior diferença x - y

        // Desenha os círculos em cada ponto correspondente
        cv.circle(mat, new cv.Point(topLeft.x, topLeft.y), 5, new cv.Scalar(255, 0, 0, 255), -1);  // Canto superior esquerdo (vermelho)
        cv.circle(mat, new cv.Point(topRight.x, topRight.y), 5, new cv.Scalar(255, 255, 0, 255), -1); // Canto superior direito (amarelo)
        cv.circle(mat, new cv.Point(bottomRight.x, bottomRight.y), 5, new cv.Scalar(0, 0, 255, 255), -1);  // Canto inferior direito (azul)
        cv.circle(mat, new cv.Point(bottomLeft.x, bottomLeft.y), 5, new cv.Scalar(0, 255, 0, 255), -1);  // Canto inferior esquerdo (verde)

        // Imagem com os vertíces definidos
        sendProcessedImage(mat);

        // Definir os pontos de origem para a transformação de perspectiva
        let srcPts = cv.matFromArray(4, 1, cv.CV_32FC2, [
            topLeft.x, topLeft.y,         // Top-left
            topRight.x, topRight.y,       // Top-right
            bottomRight.x, bottomRight.y, // Bottom-right
            bottomLeft.x, bottomLeft.y    // Bottom-left
        ]);

        // Ajustar o destino baseado na nova dimensão da imagem (largura e altura)
        let width = rect.size.width;
        let height = rect.size.height;

        // Definir os pontos de destino para a transformação de perspectiva
        let dstPts = cv.matFromArray(4, 1, cv.CV_32FC2, [
            0, 0,                    // Top-left
            width - 1, 0,            // Top-right
            width - 1, height - 1,   // Bottom-right
            0, height - 1            // Bottom-left
        ]);

        // Calcula a matriz de transformação de perspectiva
        let M = cv.getPerspectiveTransform(srcPts, dstPts);

        // Aplica a transformação de perspectiva na imagem com os filtros
        let resized = new cv.Mat();
        cv.warpPerspective(blurredMat, resized, M, new cv.Size(width, height), cv.INTER_AREA, cv.BORDER_CONSTANT, new cv.Scalar());
        sendProcessedImage(resized);

        // Aplica a transformação de perspectiva na imagem original
        let paper = new cv.Mat();
        cv.warpPerspective(mat, paper, M, new cv.Size(width, height));
        sendProcessedImage(paper);

        // Aplicar a limiarização de Otsu com inversão binária
        // Tem que estar em escala de cinza para aplicar o filtro de threshold
        let thresh = new cv.Mat();
        // necessário para realizar a verificação deconsiderando as burdas da imagem para não ter influencia
        cv.threshold(resized, thresh, 0, 255, cv.THRESH_BINARY_INV | cv.THRESH_OTSU);

        sendProcessedImage(thresh);

        // Delimitando área da coluna que representa as respostas
        rect = cv.boundingRect(thresh);

        // Calcula o número de quadrados que cabem na largura e altura do retângulo
        // Com base na quantidade que alternativas e questões informadas
        // número total de colunas X = (1 + n° de alternativas) + (n° colunas informadas - 1)
        let numSquaresX = ((1 + configAnswers.total_alternativas) * configAnswers.qtdColumn) + configAnswers.qtdColumn -1;
        let numSquaresY = configAnswers.qtdY;

        // medidas de X e Y de cada quadrado
        let squareSizeX = rect.width / numSquaresX;
        let squareSizeY = rect.height / numSquaresY;

        // número de colunas vazias na grade
        const numEmptyColumn = configAnswers.qtdColumn -1;

        // variáveis que armazena a coluna, questão e alternativa a cada iteração
        let column = 0;
        let question = 0;
        let alternative = 0;
        let answers = [];

        // Itera sobre a quantidade de linhas (Y)
        for (let y = 0; y < numSquaresY; y++) {
            // itera sobre a quantidade de alternativas por linha
            for (let x = 0; x < numSquaresX; x++) {

                // Verifica se a coluna atual é uma coluna que separa as alternativas
                const isEmptyColumn = (x + 1) % (configAnswers.qtdX + 1) === 0;
                // Verifica se a coluna atual é a coluna que numera a questão
                const isQuestionNumber = x % (configAnswers.qtdX + 1) === 0;

                // Calcula as coordenadas do canto superior esquerdo de cada quadrado
                let squareY = rect.y + y * squareSizeY;
                let squareX = rect.x + x * squareSizeX;

                // Define o ponto inicial e final para desenhar o quadrado (utilizado somente se for exibir as imagem com o que foi selecionado)
                let startPoint = new cv.Point(squareX, squareY);
                let endPoint = new cv.Point(squareX + squareSizeX, squareY + squareSizeY);

                // console.log(`isEmptyColumn: ${isEmptyColumn} | isQuestionNumber: ${isQuestionNumber} | column: ${column} | question: ${question} | alternative: ${alternative}`);

                // é uma coluna vazia
                if (isEmptyColumn) {
                    // desenha uma borda na localização onde for uma coluna de separação de respostas
                    cv.rectangle(paper, startPoint, endPoint, new cv.Scalar(255, 0, 0, 255), 2);
                } else {
                    if (isQuestionNumber) {
                        // desenha uma borda onde for identificado com número de questão
                        cv.rectangle(paper, startPoint, endPoint, new cv.Scalar(0, 0, 255, 255), 2);
                    } else { // é uma coluna com a resposta da questão
                        // desenha uma borda onde for alternativas de uma questão
                        cv.rectangle(paper, startPoint, endPoint, new cv.Scalar(0, 255, 0, 255), 2);
                    }
                }

                if (!isEmptyColumn && !isQuestionNumber) {
                    // Defina a área de interesse (ROI) do quadrado
                    let roi = thresh.roi(new cv.Rect(squareX, squareY, squareSizeX, squareSizeY));
                    let roiSize = roi.size();

                    // criar a máscara binária do mesmo tamanho da imagem
                    // para verificar se a alternativa está ou não preenchida
                    let mask = new cv.Mat.zeros(roiSize.height, roiSize.width, cv.CV_8UC1); // Uma máscara de zeros (preto)

                    // Definir o limite para a cor "preto"
                    let lowerBlack = new cv.Mat(roi.rows, roi.cols, cv.CV_8UC1, [0,0,0,0]);
                    let upperBlack = new cv.Mat(roi.rows, roi.cols, cv.CV_8UC1, [150,150,150,150]);

                    // Encontrar os pixels que são considerados pretos
                    let blackPixels = new cv.Mat();
                    cv.inRange(roi, lowerBlack, upperBlack, blackPixels); // 0 a 150 é considerado "preto"

                    // Contar o número de pixels pretos
                    let numBlackPixels = cv.countNonZero(blackPixels);
                    let blackPixelRatio = numBlackPixels / (roi.rows * roi.cols);

                    // se a porcentagem for maior que >40% significa que provavelmente foi preenchido
                    let blackPixelRatioPercent = (numBlackPixels / (roi.rows * roi.cols)) * 100;

                    if(blackPixelRatioPercent < 70) {
                        // Criando a lista de respostas marcadas no gabarito
                        answers[question] = alternative;
                    }else {
                        if (answers[question] === undefined) {
                            answers[question] = '';
                        }
                    }

                    // Libera a memória
                    lowerBlack.delete();
                    upperBlack.delete();
                    blackPixels.delete();
                }


                // verifica se é uma coluna vazia (coluna que separa as respostas)
                // e se a coluna atual é menor que o total disponível
                // se for menor aumenta a coluna se for maior volta do início
                if (isEmptyColumn && column < configAnswers.qtdColumn - 1) {
                    column++;
                } else if (column === configAnswers.qtdColumn - 1 && alternative === configAnswers.total_alternativas - 1) {
                    column = 0;
                }

                // pega a questão que está sendo verificada no momento
                question = column * configAnswers.qtdY + y;

                // faz a contagem do número da alternativa atual
                if(!isQuestionNumber && !isEmptyColumn && alternative < configAnswers.total_alternativas - 1) {
                    alternative++;
                } else {
                    alternative = 0;
                }
            }
        }

        sendProcessedImage(paper);
        sendMarkedAnswers(answers);

        // Libera a memória
        srcPts.delete();
        dstPts.delete();
        M.delete();
        resized.delete();
        thresh.delete();
        paper.delete();
    }


    // sendProcessedImage(mat);

    mat.delete();
    grayMat.delete();
    blurredMat.delete();
    edges.delete();
}

// Envia a imagem processada de volta para o thread principal
function sendProcessedImage(mat) {
    const processedData = new Uint8Array(mat.data);
    const processedMatInfo = {
        rows: mat.rows,
        cols: mat.cols,
        type: mat.type(),
        channels: mat.channels()
    };

    self.postMessage({command: "processed-image", processedData, processedMatInfo }, [processedData.buffer]);
}


function sendMarkedAnswers(answers) {
    self.postMessage({command: "marked-answers", answers });
}