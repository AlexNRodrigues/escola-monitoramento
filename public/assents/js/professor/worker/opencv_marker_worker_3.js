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

function sendAnswers(answers) {
    self.postMessage({command: "marked-answers", answers });
}

// Processamento da Imagem
function processImage(img) {
    // 1º Buscar contornos
    let contours = findContours(img);

    // 2º Buscar o maior contorno
    let contoursData = findLargestContour(contours);

    // 3º Pegar o maior contorno
    let biggestContour = contoursData.slice(0, 1).map(data => data.index);
    console.log(biggestContour);

    let respostas = [];

    // 4º se encontrou o maior contorno
    if(biggestContour.length > 0) {
        let largestContour = contours.get(biggestContour[0]);
     
        // 5º capturar os vértices do contorno
        let vertices = getContourVertices(largestContour);
        // console.log(vertices);

        // Desenha os quatro cantos
        // for (let j = 0; j < 4; j++) {
        //     let x = vertices[j].x;
        //     let y = vertices[j].y;
        //     cv.circle(img, new cv.Point(x, y), 5, new cv.Scalar(0, 0, 255, 255), -1);
        // }

        if(vertices.length > 0) {
            const imgResized = transformPerspective(vertices, img);

            const contours = findContours(imgResized);

            let contoursData = findLargestContour(contours);
                                    
            let largestContoursIndices = contoursData.slice(0, configAnswers.qtd_materias).map(data => data.index);
            console.log("Índices dos maiores contornos:", largestContoursIndices);

            // Percorre os maiores contornos encontrados que são as colunas com respostas
            for (let i = 0; i < largestContoursIndices.length; i++) {
                let largestContour = contours.get(largestContoursIndices[i]);

                respostas[i] = findAnswers(largestContour, imgResized, configAnswers);
                

                // cv.drawContours(imgResized, contours, largestContoursIndices[i], new cv.Scalar(255, 0, 0, 255), 2, cv.LINE_8);
            }

            // sendProcessedImage(imgResized);
        }
    }

    sendAnswers(respostas);

    // sendProcessedImage(img);


    // Limpar memória
    img.delete();
    return [];
}

function findContours(img) {

    // Convertendo para escala de cinza
    const grayMat = new cv.Mat();
    cv.cvtColor(img, grayMat, cv.COLOR_RGBA2GRAY);

    // Aplicando o GaussianBlur para reduzir o ruído.
    const blurredMat = new cv.Mat();
    cv.GaussianBlur(grayMat, blurredMat, new cv.Size(5, 5), 0);
    
    // Aplicando detecção de bordas para encontrar as bordas exatas do retângulo
    const edges = new cv.Mat();
    cv.Canny(blurredMat, edges, 75, 200);
    
    // Encontrando os contornos
    let contours = new cv.MatVector();
    let hierarchy = new cv.Mat();
    cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

    grayMat.delete();
    blurredMat.delete();
    edges.delete();
    hierarchy.delete();

    return contours;
}

function findLargestContour(contours) {
    // Array para armazenar os índices e as áreas dos contornos
    let contoursData = [];

    for (let i = 0; i < contours.size(); i++) {
        let contour = contours.get(i);

        let area = Math.round(cv.contourArea(contour));
        let rect = cv.boundingRect(contour);
        let xPosition = rect.x;
        
        // Adicionar o índice, área e posição x do contorno ao array
        contoursData.push({ index: i, area: area, x: xPosition });
    }

    return contoursData.sort((a, b) => {
        // Calcule uma prioridade: área / posição x para aproximar pelo tamanho e proximidade de 0 em x
        const priorityA = a.area / (a.x + 1);  // adiciona +1 para evitar divisão por zero
        const priorityB = b.area / (b.x + 1);

        // Ordenar de acordo com a prioridade calculada
        return priorityB - priorityA;
    });
}

function sortVertices(vertices) {
    // Calcula as somas e diferenças de coordenadas dos pontos
    let sum = vertices.map(p => p.x + p.y);
    let diff = vertices.map(p => p.y - p.x);

    // Encontra os índices dos pontos extremos
    let topLeft = sum.indexOf(Math.min(...sum));
    let bottomRight = sum.indexOf(Math.max(...sum));
    let topRight = diff.indexOf(Math.min(...diff));
    let bottomLeft = diff.indexOf(Math.max(...diff));

    // Retorna os pontos na ordem: [topoEsquerda, topoDireita, inferiorDireita, inferiorEsquerda]
    return [
        vertices[topLeft],
        vertices[topRight],
        vertices[bottomRight],
        vertices[bottomLeft]
    ];
}

function getContourVertices(largestContour) {
    // Aproxima o contorno ao polígono
    const approx = new cv.Mat();
    const perimeter = cv.arcLength(largestContour, true);
    cv.approxPolyDP(largestContour, approx, 0.02 * perimeter, true);

    // Extrai os pontos do polígono como uma lista de vértices
    let points = [];
    for (let i = 0; i < approx.rows; i++) {
        let point = approx.data32S.slice(i * 2, i * 2 + 2);
        points.push({ x: point[0], y: point[1] });
    }

    approx.delete();

    return sortVertices(points);
}

function transformPerspective(vertices, img) {
    let [topLeft, topRight, bottomRight, bottomLeft] = vertices;

    // Calcular largura e altura do retângulo para a nova perspectiva
    let width = Math.max(
        Math.hypot(bottomRight.x - bottomLeft.x, bottomRight.y - bottomLeft.y),
        Math.hypot(topRight.x - topLeft.x, topRight.y - topLeft.y)
    );
    let height = Math.max(
        Math.hypot(topRight.x - bottomRight.x, topRight.y - bottomRight.y),
        Math.hypot(topLeft.x - bottomLeft.x, topLeft.y - bottomLeft.y)
    );

    // Definir pontos de destino
    let dstPoints = cv.matFromArray(4, 1, cv.CV_32FC2, [
        0, 0,
        width, 0,
        width, height,
        0, height
    ]);

    // Definir pontos de origem com base nos vértices encontrados
    let srcPoints = cv.matFromArray(4, 1, cv.CV_32FC2, [
        topLeft.x + 2, topLeft.y - 2,
        topRight.x + 2, topRight.y + 2,
        bottomRight.x + 2, bottomRight.y + 2,
        bottomLeft.x + 2, bottomLeft.y - 2
    ]);

    // Calcular a matriz de transformação
    let M = cv.getPerspectiveTransform(srcPoints, dstPoints);
    let transformed = new cv.Mat();
    cv.warpPerspective(img, transformed, M, new cv.Size(width, height), cv.INTER_AREA, cv.BORDER_CONSTANT, new cv.Scalar());

    // Limpar memória
    srcPoints.delete();
    dstPoints.delete();
    M.delete();

    return transformed;
}

function transformPerspectiveColumn(points, img) {
    // Ordenando localização dos marcadores
    let sortedBySum = points.slice().sort((a, b) => (a.x + a.y) - (b.x + b.y));
    let sortedByDiff = points.slice().sort((a, b) => (a.x - a.y) - (b.x - b.y));

    let topLeft = sortedBySum[0];                           // Menor soma x + y
    let bottomLeft = sortedByDiff[0];                       // Maior soma x + y
    let bottomRight = sortedBySum[sortedBySum.length - 1];  // Menor diferença x - y
    let topRight = sortedByDiff[sortedByDiff.length - 1];   // Maior diferença x - y

    // Calculando a distância entre marcadores
    let widthBetweenDots = topRight.x - topLeft.x;
    let heightBetweenDots = bottomLeft.y - topLeft.y;

    // console.log(`width: ${widthBetweenDots}, height: ${heightBetweenDots}`);
    // console.log(sortedBySum[0], sortedByDiff[0], sortedBySum[sortedBySum.length - 1], sortedByDiff[sortedByDiff.length - 1]);

    // Desenhas as linhas entre os marcadres encontrados
    // cv.line(img, topLeft, topRight, new cv.Scalar(255,0,0,255), 2);         // top
    // cv.line(img, topRight, bottomRight, new cv.Scalar(255,255,0,255), 2);   // right
    // cv.line(img, bottomRight, bottomLeft, new cv.Scalar(0,0,255,255), 2);   // bottom
    // cv.line(img, bottomLeft, topLeft, new cv.Scalar(0,255,0,255), 2);       // left
    // createCanvasDebug(img);

    // Definir os quatro pontos da área de interesse (em ordem)
    let srcPoints = cv.matFromArray(4, 1, cv.CV_32FC2, [
        topLeft.x, topLeft.y,         // Top-left
        topRight.x, topRight.y,       // Top-right
        bottomRight.x, bottomRight.y, // Bottom-right
        bottomLeft.x, bottomLeft.y    // Bottom-left
    ]);

    // Definir os pontos de destino, criando uma área retangular
    let dstPoints = cv.matFromArray(4, 1, cv.CV_32FC2, [
        0, 0,                                  // Top-left
        widthBetweenDots, 0,                   // Top-right
        widthBetweenDots, heightBetweenDots,   // Bottom-right
        0, heightBetweenDots                   // Bottom-left
    ]);

    // Calcula a matriz de transformação de perspectiva
    let M = cv.getPerspectiveTransform(srcPoints, dstPoints);

    // Aplica a transformação de perspectiva na imagem com os filtros
    let resized = new cv.Mat();
    cv.warpPerspective(img, resized, M, new cv.Size(widthBetweenDots, heightBetweenDots), cv.INTER_AREA, cv.BORDER_CONSTANT, new cv.Scalar());

    // Liberar memória
    M.delete();
    // resized.delete();

    return resized;
}


function findAnswers(largestContour, imgResized, configAnswers) {                
    let points = [];

    // recuperar o vértice e cada ponto encontrado no maior contorno
    for (let j = 0; j < largestContour.rows; ++j) {
        let x = largestContour.intPtr(j, 0)[0];
        let y = largestContour.intPtr(j, 0)[1];
        points.push({x: x, y: y});
    }
    
    let col = transformPerspectiveColumn(points, imgResized); // Imagem de Teste
    let alternativeColumn = transformPerspectiveColumn(points, imgResized);

    cv.cvtColor(alternativeColumn, alternativeColumn, cv.COLOR_BGR2GRAY);
    cv.threshold(alternativeColumn, alternativeColumn, 0, 255, cv.THRESH_BINARY_INV | cv.THRESH_OTSU);

    // Delimitando área da coluna que representa as respostas
    let rect = cv.boundingRect(alternativeColumn);

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


            // é uma coluna vazia
            if (isEmptyColumn) {
                // desenha uma borda na localização onde for uma coluna de separação de respostas
                cv.rectangle(col, startPoint, endPoint, new cv.Scalar(255, 0, 0, 255), 2);
            } else {
                if (isQuestionNumber) {
                    // desenha uma borda onde for identificado com número de questão
                    cv.rectangle(col, startPoint, endPoint, new cv.Scalar(0, 0, 255, 255), 2);
                } else { // é uma coluna com a resposta da questão
                    // desenha uma borda onde for alternativas de uma questão
                    cv.rectangle(col, startPoint, endPoint, new cv.Scalar(0, 255, 0, 255), 2);
                }
            }

            if (!isEmptyColumn && !isQuestionNumber) {
                // Defina a área de interesse (ROI) do quadrado
                let roi = alternativeColumn.roi(new cv.Rect(squareX, squareY, squareSizeX, squareSizeY));
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

                // debug.push({question :{ alternativa: alternative, nivel_preto :blackPixelRatioPercent}});
                console.log(`${question} :{ alternativa: ${alternative}, nivel_preto: ${blackPixelRatioPercent}`);

                // Criando a lista de respostas marcadas no gabarito
                if(blackPixelRatioPercent < 65) {
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

    sendProcessedImage(col)

    return answers;
}