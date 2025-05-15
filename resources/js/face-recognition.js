/**
 * FaceAccess - Módulo de reconhecimento facial
 * Scripts para reconhecimento facial usando face-api.js
 */

// Configurações globais
const FACE_DETECTION_OPTIONS = {
    scoreThreshold: 0.5,
    inputSize: 320
};

// Variáveis globais
let modelsLoaded = false;
let authorizedPersons = [];
let faceMatcher = null;

/**
 * Carrega os modelos do face-api.js
 * @param {string} modelsUrl - URL da pasta que contém os modelos
 * @returns {Promise} - Promise que resolve quando todos os modelos são carregados
 */
async function loadFaceApiModels(modelsUrl) {
    try {
        // Registrar início do carregamento
        console.log('Carregando modelos de reconhecimento facial...');

        // Carregar todos os modelos necessários
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(modelsUrl),
            faceapi.nets.faceLandmark68Net.loadFromUri(modelsUrl),
            faceapi.nets.faceRecognitionNet.loadFromUri(modelsUrl),
            faceapi.nets.ssdMobilenetv1.loadFromUri(modelsUrl)
        ]);

        // Registrar sucesso no carregamento
        console.log('Modelos carregados com sucesso!');
        modelsLoaded = true;

        return true;
    } catch (error) {
        console.error('Erro ao carregar modelos:', error);
        throw error;
    }
}

/**
 * Cria um matcher para comparar faces com pessoas autorizadas
 * @param {Array} persons - Array de pessoas autorizadas com descritores faciais
 * @returns {FaceMatcher|null} - Objeto FaceMatcher ou null se não houver pessoas
 */
function createFaceMatcher(persons) {
    if (!Array.isArray(persons) || persons.length === 0) {
        console.warn('Nenhuma pessoa autorizada fornecida para criar o matcher');
        return null;
    }

    try {
        // Criar descritores rotulados para cada pessoa
        const labeledDescriptors = persons.map(person => {
            // Verificar se o descritor existe e é válido
            if (!person.face_descriptor || !Array.isArray(person.face_descriptor)) {
                console.warn(`Descritor facial inválido para pessoa ID ${person.id}`);
                return null;
            }

            // Criar descritor rotulado usando o ID como rótulo
            return new faceapi.LabeledFaceDescriptors(
                person.id.toString(),
                [new Float32Array(person.face_descriptor)]
            );
        }).filter(Boolean); // Remover valores nulos

        // Verificar se há descritores válidos
        if (labeledDescriptors.length === 0) {
            console.warn('Nenhum descritor facial válido encontrado');
            return null;
        }

        // Criar e retornar o matcher
        return new faceapi.FaceMatcher(labeledDescriptors, 0.6);
    } catch (error) {
        console.error('Erro ao criar face matcher:', error);
        return null;
    }
}

/**
 * Detecta faces em uma imagem ou vídeo
 * @param {HTMLImageElement|HTMLVideoElement|HTMLCanvasElement} input - Elemento contendo a imagem
 * @param {Object} options - Opções de detecção
 * @returns {Promise<Array>} - Array de detecções com descritores
 */
async function detectFaces(input, options = {}) {
    // Verificar se os modelos foram carregados
    if (!modelsLoaded) {
        throw new Error('Os modelos de reconhecimento facial não foram carregados');
    }

    try {
        // Configurar o detector
        const detectorOptions = new faceapi.TinyFaceDetectorOptions(options);

        // Detectar faces com landmarks e descritores
        const detections = await faceapi.detectAllFaces(input, detectorOptions)
            .withFaceLandmarks()
            .withFaceDescriptors();

        return detections;
    } catch (error) {
        console.error('Erro na detecção de faces:', error);
        throw error;
    }
}

/**
 * Compara uma face detectada com pessoas autorizadas
 * @param {Object} detection - Detecção contendo descritor facial
 * @param {FaceMatcher} matcher - Objeto FaceMatcher para comparação
 * @returns {Object} - Resultado da comparação com melhor correspondência
 */
function matchFace(detection, matcher) {
    if (!matcher) {
        return { label: 'unknown', distance: 1.0 };
    }

    try {
        return matcher.findBestMatch(detection.descriptor);
    } catch (error) {
        console.error('Erro ao comparar faces:', error);
        return { label: 'unknown', distance: 1.0 };
    }
}

/**
 * Desenha uma caixa delimitadora ao redor de uma face detectada
 * @param {CanvasRenderingContext2D} context - Contexto do canvas para desenho
 * @param {Object} box - Caixa delimitadora da face
 * @param {string} color - Cor da caixa (ex: 'red', '#FF0000')
 * @param {string} label - Texto a ser exibido acima da caixa
 */
function drawFaceBox(context, box, color, label) {
    // Configurar estilo
    context.lineWidth = 3;
    context.strokeStyle = color;
    context.fillStyle = color;
    context.font = '16px Arial, sans-serif';

    // Desenhar retângulo
    context.beginPath();
    context.rect(box.x, box.y, box.width, box.height);
    context.stroke();

    // Desenhar fundo para o texto
    const textWidth = context.measureText(label).width;
    context.fillRect(box.x, box.y - 30, textWidth + 10, 30);

    // Desenhar texto
    context.fillStyle = 'white';
    context.fillText(label, box.x + 5, box.y - 10);
}

/**
 * Inicia a captura de vídeo da webcam
 * @param {HTMLVideoElement} videoElement - Elemento de vídeo para exibir a webcam
 * @returns {Promise<MediaStream>} - Stream da webcam
 */
async function startWebcam(videoElement) {
    try {
        // Obter acesso à webcam
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user' }
        });

        // Configurar elemento de vídeo
        videoElement.srcObject = stream;

        // Aguardar o vídeo estar pronto
        return new Promise((resolve) => {
            videoElement.onloadedmetadata = () => {
                videoElement.play();
                resolve(stream);
            };
        });
    } catch (error) {
        console.error('Erro ao acessar webcam:', error);
        throw error;
    }
}

/**
 * Para a captura de vídeo da webcam
 * @param {MediaStream} stream - Stream da webcam
 */
function stopWebcam(stream) {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
}

/**
 * Captura um frame do vídeo para um canvas
 * @param {HTMLVideoElement} videoElement - Elemento de vídeo da webcam
 * @param {HTMLCanvasElement} canvasElement - Elemento canvas para captura
 * @returns {string} - Dados da imagem em formato base64
 */
function captureVideoFrame(videoElement, canvasElement) {
    // Ajustar tamanho do canvas para o vídeo
    canvasElement.width = videoElement.videoWidth;
    canvasElement.height = videoElement.videoHeight;

    // Desenhar frame no canvas
    const context = canvasElement.getContext('2d');
    context.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

    // Retornar dados da imagem
    return canvasElement.toDataURL('image/png');
}

/**
 * Carrega pessoas autorizadas da API
 * @param {string} url - URL da API para obter pessoas autorizadas
 * @returns {Promise<Array>} - Array de pessoas autorizadas
 */
async function loadAuthorizedPersons(url) {
    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();
        authorizedPersons = data;

        return data;
    } catch (error) {
        console.error('Erro ao carregar pessoas autorizadas:', error);
        throw error;
    }
}

/**
 * Registra um acesso no sistema
 * @param {Object} accessData - Dados do acesso (id, nome, status, foto)
 * @param {string} url - URL da API para registrar acesso
 * @param {string} csrfToken - Token CSRF para proteção
 * @returns {Promise<Object>} - Resposta da API
 */
async function recordAccess(accessData, url, csrfToken) {
    try {
        // Criar FormData com os dados do acesso
        const formData = new FormData();
        formData.append('authorized_person_id', accessData.id || '');
        formData.append('person_name', accessData.name || '');
        formData.append('status', accessData.status);
        formData.append('photo_data', accessData.photo);
        formData.append('_token', csrfToken);

        // Enviar para API
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Erro ao registrar acesso:', error);
        throw error;
    }
}

// Exportar funções
window.FaceRecognition = {
    loadModels: loadFaceApiModels,
    createMatcher: createFaceMatcher,
    detectFaces: detectFaces,
    matchFace: matchFace,
    drawFaceBox: drawFaceBox,
    startWebcam: startWebcam,
    stopWebcam: stopWebcam,
    captureFrame: captureVideoFrame,
    loadAuthorizedPersons: loadAuthorizedPersons,
    recordAccess: recordAccess
};