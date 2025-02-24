// importScripts('https://docs.opencv.org/4.x/opencv.js');
importScripts('../lib/opencv.js');

const IMAGE_WIDTH = 1080.5221326885164;
const IMAGE_HEIGHT = 458.0996463454849;

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
        // Recria a Matriz da imagem no Worker a partir do Uint8Array
        const mat = new cv.Mat(matInfo.rows, matInfo.cols, matInfo.type);
        mat.data.set(new Uint8Array(imageData));

        console.log('aqui');
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


function processImage(src) {    
    const gray = convertToGrayscale(src);
    const blurred = applyGaussianBlur(gray);
    const thresh = applyThreshold(blurred);
    const contours = findContours(thresh);

    const points = extractContourPoints(contours);
    if (points.length >= 4) {
        const { topLeft, topRight, bottomRight, bottomLeft } = sortPoints(points);
        drawPointsOnImage(src, topLeft, topRight, bottomRight, bottomLeft);

        const resized = applyPerspectiveTransform(src, topLeft, topRight, bottomRight, bottomLeft);
        processResizedImage(resized);
    }

    cleanup([gray, blurred, thresh]);
}

function convertToGrayscale(src) {
    const gray = new cv.Mat();
    cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY, 0);
    return gray;
}

function applyGaussianBlur(gray) {
    const blurred = new cv.Mat();
    cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 0);
    return blurred;
}

function applyThreshold(blurred) {
    const thresh = new cv.Mat();
    cv.threshold(blurred, thresh, 0, 255, cv.THRESH_BINARY_INV | cv.THRESH_OTSU);
    return thresh;
}

function findContours(thresh) {
    const contours = new cv.MatVector();
    const hierarchy = new cv.Mat();
    cv.findContours(thresh, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);
    hierarchy.delete();
    return contours;
}

function extractContourPoints(contours) {
    let points = [];
    for (let i = 0; i < contours.size(); i++) {
        const cnt = contours.get(i);
        const approx = approximateContour(cnt);
        const area = cv.contourArea(approx);

        if (area > 100 && area < 3000 && approx.rows <= 6) {
            const p = extractPointsFromContour(approx);
            const moments = cv.moments(cnt, false);
            const cx = moments.m10 / moments.m00;
            const cy = moments.m01 / moments.m00;
            points.push({ x: cx, y: cy, p: p });
        }
        approx.delete();
    }
    return points;
}

function approximateContour(cnt) {
    const approx = new cv.Mat();
    cv.approxPolyDP(cnt, approx, 0.02 * cv.arcLength(cnt, true), true);
    return approx;
}

function extractPointsFromContour(approx) {
    let p = [];
    for (let i = 0; i < approx.rows; i++) {
        const x = approx.intPtr(i, 0)[0];
        const y = approx.intPtr(i, 0)[1];
        p.push({ x, y });
    }
    p.sort((a, b) => (Math.round(a.x) === Math.round(b.x) ? a.y - b.y : a.x - b.x));
    return p;
}

function sortPoints(points) {
    const sortedBySum = points.slice().sort((a, b) => a.x + a.y - (b.x + b.y));
    const sortedByDiff = points.slice().sort((a, b) => a.x - a.y - (b.x - b.y));

    return {
        topLeft: sortedBySum[0],
        bottomLeft: sortedByDiff[0],
        bottomRight: sortedBySum[sortedBySum.length - 1],
        topRight: sortedByDiff[sortedByDiff.length - 1],
    };
}

function drawPointsOnImage(src, topLeft, topRight, bottomRight, bottomLeft) {
    const colors = [
        { point: topLeft, color: new cv.Scalar(255, 0, 0, 255) }, // red
        { point: topRight, color: new cv.Scalar(255, 255, 0, 255) }, // yellow
        { point: bottomRight, color: new cv.Scalar(0, 0, 255, 255) }, // blue
        { point: bottomLeft, color: new cv.Scalar(0, 255, 0, 255) }, // green
    ];

    colors.forEach(({ point, color }) => {
        cv.circle(src, new cv.Point(point.x, point.y), 5, color, -1);
    });
    sendProcessedImage(src);
}

function applyPerspectiveTransform(src, topLeft, topRight, bottomRight, bottomLeft) {
    const srcPts = cv.matFromArray(4, 1, cv.CV_32FC2, [
        topLeft.x, topLeft.y,
        topRight.x, topRight.y,
        bottomRight.x, bottomRight.y,
        bottomLeft.x, bottomLeft.y,
    ]);

    const dstPts = cv.matFromArray(4, 1, cv.CV_32FC2, [
        0, 0,
        IMAGE_WIDTH, 0,
        IMAGE_WIDTH, IMAGE_HEIGHT,
        0, IMAGE_HEIGHT,
    ]);

    const M = cv.getPerspectiveTransform(srcPts, dstPts);
    const resized = new cv.Mat();
    cv.warpPerspective(src, resized, M, new cv.Size(IMAGE_WIDTH, IMAGE_HEIGHT), cv.INTER_AREA, cv.BORDER_CONSTANT, new cv.Scalar());
    return resized;
}

function processResizedImage(resized) {
    const locs = getLocations();
    for (const loc in locs) {
        if (locs.hasOwnProperty(loc)) {
            drawLocationText(resized, loc, locs[loc]);
            processOptions(resized, loc, locs[loc]);
        }
    }
    sendProcessedImage(resized);
}

function getLocations() {
    return {
        q_1: [
            {
                x: 58,
                y: 49,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 178,
                y: 49,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 138,
                y: 49,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 98,
                y: 49,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 219,
                y: 49,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_11: [
            {
                x: 327,
                y: 49,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 367,
                y: 49,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 407,
                y: 49,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 448,
                y: 49,
                width: 23,
                height: 24,
                i: "d",
            },
            {
                x: 487,
                y: 49,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_21: [
            {
                x: 597,
                y: 49,
                width: 23,
                height: 24,
                i: "a",
            },
            {
                x: 637,
                y: 49,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 677,
                y: 49,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 717,
                y: 49,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 757,
                y: 49,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_31: [
            {
                x: 866,
                y: 49,
                width: 24,
                height: 23,
                i: "a",
            },
            {
                x: 907,
                y: 49,
                width: 23,
                height: 23,
                i: "b",
            },
            {
                x: 947,
                y: 49,
                width: 23,
                height: 23,
                i: "c",
            },
            {
                x: 987,
                y: 49,
                width: 23,
                height: 23,
                i: "d",
            },
            {
                x: 1026,
                y: 49,
                width: 24,
                height: 23,
                i: "e",
            },
        ],
        q_2: [
            {
                x: 58,
                y: 87,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 98,
                y: 87,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 138,
                y: 87,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 178,
                y: 87,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 218,
                y: 87,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_12: [
            {
                x: 327,
                y: 87,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 367,
                y: 87,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 407,
                y: 87,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 448,
                y: 87,
                width: 23,
                height: 25,
                i: "d",
            },
            {
                x: 487,
                y: 87,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_22: [
            {
                x: 597,
                y: 88,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 637,
                y: 88,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 677,
                y: 88,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 717,
                y: 88,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 757,
                y: 88,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_32: [
            {
                x: 867,
                y: 88,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 907,
                y: 87,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 947,
                y: 87,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 986,
                y: 87,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 1026,
                y: 87,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_3: [
            {
                x: 58,
                y: 125,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 98,
                y: 125,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 138,
                y: 125,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 178,
                y: 125,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 219,
                y: 125,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_13: [
            {
                x: 328,
                y: 126,
                width: 23,
                height: 24,
                i: "a",
            },
            {
                x: 368,
                y: 126,
                width: 23,
                height: 23,
                i: "b",
            },
            {
                x: 407,
                y: 126,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 447,
                y: 126,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 488,
                y: 126,
                width: 23,
                height: 23,
                i: "e",
            },
        ],
        q_23: [
            {
                x: 597,
                y: 126,
                width: 23,
                height: 24,
                i: "a",
            },
            {
                x: 637,
                y: 126,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 677,
                y: 126,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 717,
                y: 126,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 757,
                y: 126,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_33: [
            {
                x: 867,
                y: 126,
                width: 22,
                height: 24,
                i: "a",
            },
            {
                x: 907,
                y: 125,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 947,
                y: 125,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 987,
                y: 125,
                width: 23,
                height: 24,
                i: "d",
            },
            {
                x: 1027,
                y: 125,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_4: [
            {
                x: 59,
                y: 164,
                width: 23,
                height: 23,
                i: "a",
            },
            {
                x: 98,
                y: 164,
                width: 24,
                height: 23,
                i: "b",
            },
            {
                x: 138,
                y: 164,
                width: 24,
                height: 23,
                i: "c",
            },
            {
                x: 178,
                y: 164,
                width: 24,
                height: 23,
                i: "d",
            },
            {
                x: 219,
                y: 164,
                width: 23,
                height: 23,
                i: "e",
            },
        ],
        q_14: [
            {
                x: 328,
                y: 164,
                width: 23,
                height: 24,
                i: "a",
            },
            {
                x: 367,
                y: 164,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 407,
                y: 164,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 448,
                y: 164,
                width: 23,
                height: 24,
                i: "d",
            },
            {
                x: 488,
                y: 164,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_24: [
            {
                x: 597,
                y: 164,
                width: 23,
                height: 24,
                i: "a",
            },
            {
                x: 637,
                y: 164,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 677,
                y: 164,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 717,
                y: 164,
                width: 23,
                height: 24,
                i: "d",
            },
            {
                x: 757,
                y: 164,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_34: [
            {
                x: 866,
                y: 164,
                width: 23,
                height: 23,
                i: "a",
            },
            {
                x: 907,
                y: 164,
                width: 23,
                height: 23,
                i: "b",
            },
            {
                x: 947,
                y: 164,
                width: 23,
                height: 23,
                i: "c",
            },
            {
                x: 987,
                y: 164,
                width: 23,
                height: 23,
                i: "d",
            },
            {
                x: 1027,
                y: 164,
                width: 23,
                height: 23,
                i: "e",
            },
        ],
        q_5: [
            {
                x: 58,
                y: 201,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 98,
                y: 201,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 138,
                y: 201,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 178,
                y: 201,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 219,
                y: 201,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_15: [
            {
                x: 327,
                y: 201,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 367,
                y: 201,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 407,
                y: 201,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 447,
                y: 201,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 487,
                y: 201,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_25: [
            {
                x: 597,
                y: 201,
                width: 23,
                height: 24,
                i: "a",
            },
            {
                x: 637,
                y: 201,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 677,
                y: 201,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 717,
                y: 201,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 757,
                y: 201,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_35: [
            {
                x: 866,
                y: 201,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 906,
                y: 201,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 947,
                y: 201,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 986,
                y: 201,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 1027,
                y: 201,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_6: [
            {
                x: 58,
                y: 239,
                width: 24,
                height: 23,
                i: "a",
            },
            {
                x: 98,
                y: 239,
                width: 24,
                height: 23,
                i: "b",
            },
            {
                x: 138,
                y: 239,
                width: 24,
                height: 23,
                i: "c",
            },
            {
                x: 178,
                y: 239,
                width: 24,
                height: 23,
                i: "d",
            },
            {
                x: 218,
                y: 239,
                width: 24,
                height: 23,
                i: "e",
            },
        ],
        q_16: [
            {
                x: 327,
                y: 239,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 367,
                y: 239,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 407,
                y: 239,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 447,
                y: 239,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 487,
                y: 239,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_26: [
            {
                x: 597,
                y: 239,
                width: 24,
                height: 23,
                i: "a",
            },
            {
                x: 637,
                y: 239,
                width: 23,
                height: 23,
                i: "b",
            },
            {
                x: 677,
                y: 239,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 717,
                y: 239,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 757,
                y: 240,
                width: 24,
                height: 23,
                i: "e",
            },
        ],
        q_36: [
            {
                x: 866,
                y: 239,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 906,
                y: 239,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 946,
                y: 239,
                width: 24,
                height: 23,
                i: "c",
            },
            {
                x: 986,
                y: 239,
                width: 24,
                height: 23,
                i: "d",
            },
            {
                x: 1026,
                y: 239,
                width: 24,
                height: 23,
                i: "e",
            },
        ],
        q_7: [
            {
                x: 58,
                y: 276,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 98,
                y: 276,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 138,
                y: 276,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 178,
                y: 276,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 219,
                y: 276,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_17: [
            {
                x: 328,
                y: 277,
                width: 23,
                height: 23,
                i: "a",
            },
            {
                x: 367,
                y: 277,
                width: 24,
                height: 23,
                i: "b",
            },
            {
                x: 407,
                y: 277,
                width: 24,
                height: 23,
                i: "c",
            },
            {
                x: 447,
                y: 277,
                width: 24,
                height: 23,
                i: "d",
            },
            {
                x: 487,
                y: 277,
                width: 24,
                height: 25,
                i: "e",
            },
        ],
        q_27: [
            {
                x: 597,
                y: 277,
                width: 23,
                height: 23,
                i: "a",
            },
            {
                x: 637,
                y: 276,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 677,
                y: 276,
                width: 23,
                height: 25,
                i: "c",
            },
            {
                x: 717,
                y: 276,
                width: 24,
                height: 25,
                i: "d",
            },
            {
                x: 757,
                y: 276,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_37: [
            {
                x: 867,
                y: 277,
                width: 23,
                height: 23,
                i: "a",
            },
            {
                x: 907,
                y: 276,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 947,
                y: 276,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 987,
                y: 276,
                width: 23,
                height: 24,
                i: "d",
            },
            {
                x: 1026,
                y: 276,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_8: [
            {
                x: 59,
                y: 313,
                width: 23,
                height: 25,
                i: "a",
            },
            {
                x: 98,
                y: 314,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 178,
                y: 314,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 138,
                y: 314,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 219,
                y: 314,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_18: [
            {
                x: 328,
                y: 314,
                width: 23,
                height: 24,
                i: "a",
            },
            {
                x: 368,
                y: 314,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 407,
                y: 314,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 447,
                y: 314,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 488,
                y: 314,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_28: [
            {
                x: 597,
                y: 314,
                width: 23,
                height: 24,
                i: "a",
            },
            {
                x: 637,
                y: 314,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 677,
                y: 314,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 717,
                y: 314,
                width: 23,
                height: 24,
                i: "d",
            },
            {
                x: 757,
                y: 314,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_38: [
            {
                x: 867,
                y: 314,
                width: 23,
                height: 24,
                i: "a",
            },
            {
                x: 907,
                y: 314,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 947,
                y: 314,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 987,
                y: 314,
                width: 23,
                height: 24,
                i: "d",
            },
            {
                x: 1027,
                y: 314,
                width: 23,
                height: 24,
                i: "e",
            },
        ],
        q_9: [
            {
                x: 58,
                y: 351,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 98,
                y: 351,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 138,
                y: 352,
                width: 24,
                height: 23,
                i: "c",
            },
            {
                x: 178,
                y: 352,
                width: 24,
                height: 23,
                i: "d",
            },
            {
                x: 218,
                y: 352,
                width: 24,
                height: 23,
                i: "e",
            },
        ],
        q_19: [
            {
                x: 327,
                y: 352,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 368,
                y: 352,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 407,
                y: 352,
                width: 24,
                height: 24,
                i: "c",
            },
            {
                x: 448,
                y: 352,
                width: 23,
                height: 24,
                i: "d",
            },
            {
                x: 487,
                y: 352,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_29: [
            {
                x: 597,
                y: 352,
                width: 23,
                height: 24,
                i: "a",
            },
            {
                x: 637,
                y: 352,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 677,
                y: 352,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 717,
                y: 352,
                width: 24,
                height: 23,
                i: "d",
            },
            {
                x: 757,
                y: 352,
                width: 24,
                height: 23,
                i: "e",
            },
        ],
        q_39: [
            {
                x: 867,
                y: 352,
                width: 23,
                height: 23,
                i: "a",
            },
            {
                x: 907,
                y: 352,
                width: 23,
                height: 23,
                i: "b",
            },
            {
                x: 947,
                y: 352,
                width: 23,
                height: 23,
                i: "c",
            },
            {
                x: 987,
                y: 352,
                width: 23,
                height: 23,
                i: "d",
            },
            {
                x: 1026,
                y: 352,
                width: 24,
                height: 23,
                i: "e",
            },
        ],
        q_10: [
            {
                x: 58,
                y: 389,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 98,
                y: 389,
                width: 24,
                height: 24,
                i: "b",
            },
            {
                x: 138,
                y: 390,
                width: 24,
                height: 23,
                i: "c",
            },
            {
                x: 178,
                y: 390,
                width: 24,
                height: 23,
                i: "d",
            },
            {
                x: 218,
                y: 390,
                width: 24,
                height: 23,
                i: "e",
            },
        ],
        q_20: [
            {
                x: 327,
                y: 390,
                width: 24,
                height: 23,
                i: "a",
            },
            {
                x: 367,
                y: 390,
                width: 24,
                height: 23,
                i: "b",
            },
            {
                x: 407,
                y: 390,
                width: 24,
                height: 23,
                i: "c",
            },
            {
                x: 447,
                y: 390,
                width: 24,
                height: 24,
                i: "d",
            },
            {
                x: 487,
                y: 390,
                width: 24,
                height: 24,
                i: "e",
            },
        ],
        q_30: [
            {
                x: 597,
                y: 390,
                width: 24,
                height: 24,
                i: "a",
            },
            {
                x: 637,
                y: 390,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 677,
                y: 390,
                width: 23,
                height: 23,
                i: "c",
            },
            {
                x: 717,
                y: 390,
                width: 24,
                height: 23,
                i: "d",
            },
            {
                x: 757,
                y: 390,
                width: 24,
                height: 23,
                i: "e",
            },
        ],
        q_40: [
            {
                x: 867,
                y: 390,
                width: 23,
                height: 23,
                i: "a",
            },
            {
                x: 907,
                y: 389,
                width: 23,
                height: 24,
                i: "b",
            },
            {
                x: 947,
                y: 389,
                width: 23,
                height: 24,
                i: "c",
            },
            {
                x: 987,
                y: 390,
                width: 23,
                height: 23,
                i: "d",
            },
            {
                x: 1026,
                y: 390,
                width: 24,
                height: 23,
                i: "e",
            },
        ],
    };
}

function drawLocationText(resized, loc, options) {
    const text = `${loc}`;
    const textPosition = new cv.Point(options[0].x - 45, options[0].y + 10);
    cv.putText(resized, text, textPosition, cv.FONT_HERSHEY_SIMPLEX, 0.5, [255, 0, 0, 255], 2);
}

function processOptions(resized, loc, options) {
    let answers = [];

    options.forEach(opt => {
        const avg = calculatePercentageBlack(resized, opt);
        // console.log(`${loc} => ${opt.i}: ${avg.toFixed(2)}%`);

        if (avg >= 35) {
            cv.circle(resized, new cv.Point(opt.x + 10, opt.y + 10), 3, [0, 255, 0, 255], -1);
            answers[loc] = letterToNumber(opt.i); 
        }

        drawRectangle(resized, opt);
        drawOptionText(resized, opt);
    });

    sendAnswers(Object.values(answers));
}

function calculatePercentageBlack(src, loc) {
    const roi = src.roi(new cv.Rect(loc.x, loc.y, loc.width, loc.height));
    const gray = new cv.Mat();
    cv.cvtColor(roi, gray, cv.COLOR_RGBA2GRAY, 0);

    const thresh = new cv.Mat();
    cv.threshold(gray, thresh, 127, 255, cv.THRESH_BINARY);

    const totalPixels = thresh.rows * thresh.cols;
    const blackPixels = totalPixels - cv.countNonZero(thresh);
    const percentage = (blackPixels / totalPixels) * 100;

    cleanup([roi, gray, thresh]);
    return percentage;
}

function drawRectangle(resized, opt) {
    const point1 = new cv.Point(opt.x, opt.y);
    const point2 = new cv.Point(opt.x + opt.width, opt.y + opt.height);
    cv.rectangle(resized, point1, point2, new cv.Scalar(255, 0, 0, 255), 2);
}

function drawOptionText(resized, opt) {
    const text = `${opt.i}`;
    const textPosition = new cv.Point(opt.x, opt.y);
    cv.putText(resized, text, textPosition, cv.FONT_HERSHEY_SIMPLEX, 0.5, [0, 0, 255, 255], 2);
}

function cleanup(mats) {
    mats.forEach(mat => mat.delete());
}

function letterToNumber(letter) {
    letter = letter.toLowerCase();   
    return letter.charCodeAt(0) - 'a'.charCodeAt(0);
}