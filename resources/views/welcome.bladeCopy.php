<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Detecção de Pessoas Autorizadas</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script> --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="p-4 text-white bg-blue-600 shadow-md">
            <div class="container flex justify-between mx-auto">
                <div class="text-lg font-semibold">Sistema de Controle de Acesso</div>
            </div>
        </nav>

        <main class="container p-4 mx-auto">
            <div class="max-w-4xl mx-auto">
                <div class="p-6 mb-6 bg-white rounded-lg shadow-lg">
                    <h2 class="mb-4 text-2xl font-bold">Sistema de Detecção de Pessoas Autorizadas</h2>

                    <div class="flex flex-wrap gap-2 mb-4">
                        <button id="startCamera" class="px-4 py-2 font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">
                            Iniciar Câmera
                        </button>
                        <button id="stopCamera" class="hidden px-4 py-2 font-semibold text-white bg-red-600 rounded hover:bg-red-700">
                            Parar Câmera
                        </button>
                        <button id="addAuthorized" class="px-4 py-2 font-semibold text-white bg-green-600 rounded hover:bg-green-700">
                            Adicionar Pessoa Autorizada
                        </button>
                        <button id="clearAuthorized" class="px-4 py-2 font-semibold text-white bg-yellow-600 rounded hover:bg-yellow-700">
                            Limpar Cadastros
                        </button>
                    </div>

                    <div id="addPersonForm" class="hidden p-4 mb-4 border rounded bg-gray-50">
                        <h3 class="mb-2 text-lg font-semibold">Adicionar Pessoa Autorizada</h3>
                        <div class="mb-4">
                            <label for="personName" class="block mb-1 text-gray-700">Nome da Pessoa:</label>
                            <input type="text" id="personName" class="w-full px-3 py-2 border rounded" placeholder="Nome completo">
                        </div>
                        <div class="mb-4">
                            <button id="capturePhoto" class="px-4 py-2 font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-700">
                                Capturar Foto
                            </button>
                            <button id="saveAuthorized" class="hidden px-4 py-2 ml-2 font-semibold text-white bg-green-600 rounded hover:bg-green-700">
                                Salvar Pessoa
                            </button>
                        </div>
                        <div id="capturedPreview" class="hidden">
                            <h4 class="mb-2 text-sm font-medium">Foto Capturada:</h4>
                            <canvas id="capturedCanvas" class="border" width="320" height="240"></canvas>
                        </div>
                    </div>

                    <div class="relative w-full">
                        <div id="alertBox" class="hidden px-4 py-3 mb-4 rounded-md">
                            <strong class="mr-1 font-bold" id="alertTitle"></strong>
                            <span id="alertMessage"></span>
                        </div>

                        <div class="relative overflow-hidden bg-black rounded-lg" style="max-height: 480px;">
                            <video id="video" class="w-full" style="max-height: 480px;" autoplay muted></video>
                            <canvas id="overlay" class="absolute top-0 left-0 w-full h-full"></canvas>
                        </div>

                        <div class="mt-4">
                            <p id="status" class="text-gray-700">Câmera desativada</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="mb-2 text-lg font-semibold">Pessoas Autorizadas</h3>
                        <div id="authorizedList" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <p id="noAuthorizedMsg" class="text-gray-500">Nenhuma pessoa autorizada cadastrada.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // Elementos DOM
            const video = document.getElementById('video');
            const overlay = document.getElementById('overlay');
            const alertBox = document.getElementById('alertBox');
            const alertTitle = document.getElementById('alertTitle');
            const alertMessage = document.getElementById('alertMessage');
            const statusElement = document.getElementById('status');
            const startCameraBtn = document.getElementById('startCamera');
            const stopCameraBtn = document.getElementById('stopCamera');
            const addAuthorizedBtn = document.getElementById('addAuthorized');
            const clearAuthorizedBtn = document.getElementById('clearAuthorized');
            const addPersonForm = document.getElementById('addPersonForm');
            const capturePhotoBtn = document.getElementById('capturePhoto');
            const saveAuthorizedBtn = document.getElementById('saveAuthorized');
            const capturedPreview = document.getElementById('capturedPreview');
            const capturedCanvas = document.getElementById('capturedCanvas');
            const personNameInput = document.getElementById('personName');
            const authorizedList = document.getElementById('authorizedList');
            const noAuthorizedMsg = document.getElementById('noAuthorizedMsg');

            // Variáveis globais
            let stream = null;
            let detectionActive = false;
            let modelsLoaded = false;
            let capturedFaceDescriptor = null;
            let authorizedPersons = [];
            let faceDetector = null;
            let faceMatcher = null;

            // Carregar configurações salvas do localStorage
            loadAuthorizedPersons();
            updateAuthorizedList();

            // Carregar modelos do face-api.js
            async function loadModels() {
                statusElement.textContent = 'Carregando modelos de detecção facial...';
                try {
                    // Carregar modelos necessários para detecção e reconhecimento facial
                    await Promise.all([
                        faceapi.nets.tinyFaceDetector.load(),
                        faceapi.nets.faceLandmark68Net.load(),
                        faceapi.nets.faceRecognitionNet.load(),
                        faceapi.nets.ssdMobilenetv1.load()
                    ]);

                    // Criar o detector facial
                    faceDetector = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });

                    // Criar o matcher para pessoas autorizadas
                    updateFaceMatcher();

                    modelsLoaded = true;
                    statusElement.textContent = 'Modelos carregados. Pronto para detecção.';
                } catch (error) {
                    statusElement.textContent = `Erro ao carregar modelos: ${error.message}`;
                    console.error('Erro ao carregar modelos:', error);
                }
            }

            // Atualizar o face matcher com as pessoas autorizadas
            function updateFaceMatcher() {
                if (authorizedPersons.length > 0) {
                    const labeledDescriptors = authorizedPersons.map(person => {
                        return new faceapi.LabeledFaceDescriptors(
                            person.name,
                            [new Float32Array(person.descriptor)]
                        );
                    });
                    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);
                } else {
                    faceMatcher = null;
                }
            }

            // Iniciar a câmera
            async function startCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'user' }
                    });

                    video.srcObject = stream;
                    video.onloadedmetadata = () => {
                        video.play();

                        // Ajustar tamanho do canvas para o vídeo
                        overlay.width = video.videoWidth;
                        overlay.height = video.videoHeight;
                        overlay.style.width = video.clientWidth + 'px';
                        overlay.style.height = video.clientHeight + 'px';

                        startCameraBtn.classList.add('hidden');
                        stopCameraBtn.classList.remove('hidden');
                        statusElement.textContent = 'Câmera ativada. Iniciando detecção...';

                        // Iniciar detecção após garantir que o vídeo está pronto
                        startDetection();
                    };

                    // Espere o carregamento dos modelos se necessário
                    if (!modelsLoaded) {
                        await loadModels();
                    }

                } catch (error) {
                    statusElement.textContent = `Erro ao acessar a câmera: ${error.message}`;
                    console.error('Erro ao acessar a câmera:', error);
                }
            }

            // Parar a câmera
            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    video.srcObject = null;
                    detectionActive = false;
                    startCameraBtn.classList.remove('hidden');
                    stopCameraBtn.classList.add('hidden');
                    statusElement.textContent = 'Câmera desativada';
                    hideAlert();

                    // Limpar o canvas
                    const context = overlay.getContext('2d');
                    context.clearRect(0, 0, overlay.width, overlay.height);
                }
            }

            // Iniciar detecção de faces
            async function startDetection() {
                detectionActive = true;

                // Loop de detecção
                const detectFaces = async () => {
                    if (!detectionActive) return;

                    try {
                        if (video.paused || video.ended || !video.videoWidth) {
                            requestAnimationFrame(detectFaces);
                            return;
                        }

                        // Detectar faces com landmarks e descritores para reconhecimento
                        const detections = await faceapi.detectAllFaces(video, faceDetector)
                            .withFaceLandmarks()
                            .withFaceDescriptors();

                        const context = overlay.getContext('2d');
                        context.clearRect(0, 0, overlay.width, overlay.height);

                        // Ajustar dimensões para exibição
                        const displaySize = { width: overlay.width, height: overlay.height };
                        const resizedDetections = faceapi.resizeResults(detections, displaySize);

                        // Verificar se há faces detectadas
                        if (resizedDetections.length > 0) {
                            // Para cada face detectada, verificar se é uma pessoa autorizada
                            let authorizedCount = 0;
                            let unauthorizedCount = 0;

                            resizedDetections.forEach(detection => {
                                // Verificar se temos pessoas autorizadas para comparar
                                if (faceMatcher) {
                                    // Comparar com as pessoas autorizadas
                                    const match = faceMatcher.findBestMatch(detection.descriptor);
                                    const isAuthorized = match.label !== 'unknown';

                                    // Desenhar o retângulo com cor diferente baseado na autorização
                                    const boxColor = isAuthorized ? 'green' : 'red';
                                    const label = isAuthorized ? match.label : 'Não Autorizado';

                                    // Desenhar retângulo
                                    drawFaceBox(context, detection.detection.box, boxColor, label);

                                    // Contar pessoas autorizadas e não autorizadas
                                    if (isAuthorized) {
                                        authorizedCount++;
                                    } else {
                                        unauthorizedCount++;
                                    }
                                } else {
                                    // Se não temos pessoas autorizadas cadastradas, apenas marcar como desconhecido
                                    drawFaceBox(context, detection.detection.box, 'yellow', 'Desconhecido');
                                }
                            });

                            // Atualizar o status
                            statusElement.textContent = `Detecção: ${resizedDetections.length} pessoas (${authorizedCount} autorizadas, ${unauthorizedCount} não autorizadas)`;

                            // Mostrar alerta se houver pessoas não autorizadas
                            if (unauthorizedCount > 0 && faceMatcher) {
                                showAlert('error', 'Acesso Negado', `${unauthorizedCount} pessoa(s) não autorizada(s) detectada(s)!`);
                            } else if (authorizedCount > 0) {
                                showAlert('success', 'Acesso Permitido', 'Pessoa autorizada detectada.');
                            } else {
                                hideAlert();
                            }
                        } else {
                            // Nenhuma face detectada
                            hideAlert();
                            statusElement.textContent = 'Monitorando...';
                        }
                    } catch (error) {
                        console.error('Erro na detecção:', error);
                    }

                    // Continuar o loop de detecção
                    if (detectionActive) {
                        requestAnimationFrame(detectFaces);
                    }
                };

                // Iniciar o loop de detecção
                detectFaces();
            }

            // Função para desenhar caixa ao redor do rosto
            function drawFaceBox(context, box, color, label) {
                // Desenhar retângulo
                context.beginPath();
                context.rect(box.x, box.y, box.width, box.height);
                context.lineWidth = 3;
                context.strokeStyle = color;
                context.stroke();

                // Desenhar fundo para o texto
                context.fillStyle = color;
                context.fillRect(box.x, box.y - 20, context.measureText(label).width + 10, 20);

                // Desenhar texto do rótulo
                context.fillStyle = 'white';
                context.font = '16px Arial';
                context.fillText(label, box.x + 5, box.y - 5);
            }

            // Mostrar alerta
            function showAlert(type, title, message) {
                alertTitle.textContent = title;
                alertMessage.textContent = message;

                alertBox.className = 'py-3 px-4 mb-4 rounded-md';

                if (type === 'error') {
                    alertBox.classList.add('bg-red-100', 'border', 'border-red-400', 'text-red-700');
                } else if (type === 'success') {
                    alertBox.classList.add('bg-green-100', 'border', 'border-green-400', 'text-green-700');
                } else {
                    alertBox.classList.add('bg-yellow-100', 'border', 'border-yellow-400', 'text-yellow-700');
                }

                alertBox.classList.remove('hidden');
            }

            // Esconder alerta
            function hideAlert() {
                alertBox.classList.add('hidden');
            }

            // Capturar foto do rosto para pessoa autorizada
            async function capturePhoto() {
                if (!video.srcObject) {
                    showAlert('error', 'Erro', 'A câmera não está ativa. Inicie a câmera primeiro.');
                    return;
                }

                try {
                    // Capturar frame do vídeo
                    capturedCanvas.getContext('2d').drawImage(
                        video,
                        0, 0,
                        capturedCanvas.width,
                        capturedCanvas.height
                    );

                    // Detectar face na captura
                    const detection = await faceapi.detectSingleFace(capturedCanvas, faceDetector)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (detection) {
                        // Armazenar o descritor facial
                        capturedFaceDescriptor = Array.from(detection.descriptor);

                        // Mostrar a captura e o botão de salvar
                        capturedPreview.classList.remove('hidden');
                        saveAuthorizedBtn.classList.remove('hidden');

                        // Desenhar retângulo na captura
                        const ctx = capturedCanvas.getContext('2d');
                        const box = detection.detection.box;
                        ctx.beginPath();
                        ctx.rect(
                            box.x,
                            box.y,
                            box.width,
                            box.height
                        );
                        ctx.lineWidth = 2;
                        ctx.strokeStyle = 'green';
                        ctx.stroke();
                    } else {
                        showAlert('error', 'Erro', 'Nenhum rosto detectado na captura. Tente novamente.');
                    }
                } catch (error) {
                    showAlert('error', 'Erro', `Falha ao capturar foto: ${error.message}`);
                }
            }

            // Salvar pessoa autorizada
            function saveAuthorizedPerson() {
                const name = personNameInput.value.trim();

                if (!name) {
                    showAlert('error', 'Erro', 'Digite o nome da pessoa.');
                    return;
                }

                if (!capturedFaceDescriptor) {
                    showAlert('error', 'Erro', 'Nenhum rosto foi capturado. Capture uma foto primeiro.');
                    return;
                }

                // Criar objeto da pessoa autorizada
                const person = {
                    name: name,
                    descriptor: capturedFaceDescriptor,
                    dateAdded: new Date().toISOString()
                };

                // Adicionar à lista de pessoas autorizadas
                authorizedPersons.push(person);

                // Salvar no localStorage
                saveAuthorizedPersons();

                // Atualizar o face matcher
                updateFaceMatcher();

                // Atualizar interface
                updateAuthorizedList();

                // Limpar formulário
                resetAddPersonForm();

                // Fechar formulário
                addPersonForm.classList.add('hidden');

                // Mostrar mensagem de sucesso
                showAlert('success', 'Sucesso', `${name} foi adicionado(a) à lista de pessoas autorizadas.`);
                setTimeout(hideAlert, 3000);
            }

            // Limpar formulário de adição de pessoa
            function resetAddPersonForm() {
                personNameInput.value = '';
                capturedFaceDescriptor = null;
                capturedPreview.classList.add('hidden');
                saveAuthorizedBtn.classList.add('hidden');

                const ctx = capturedCanvas.getContext('2d');
                ctx.clearRect(0, 0, capturedCanvas.width, capturedCanvas.height);
            }

            // Atualizar lista de pessoas autorizadas na interface
            function updateAuthorizedList() {
                // Limpar lista atual
                while (authorizedList.firstChild) {
                    if (authorizedList.firstChild === noAuthorizedMsg) {
                        break;
                    }
                    authorizedList.removeChild(authorizedList.firstChild);
                }

                // Mostrar mensagem se não há pessoas autorizadas
                if (authorizedPersons.length === 0) {
                    noAuthorizedMsg.classList.remove('hidden');
                } else {
                    noAuthorizedMsg.classList.add('hidden');

                    // Adicionar cada pessoa autorizada à lista
                    authorizedPersons.forEach((person, index) => {
                        const card = document.createElement('div');
                        card.className = 'bg-white border rounded-lg p-4 shadow-sm';

                        // Formatação de data
                        const dateAdded = new Date(person.dateAdded);
                        const formattedDate = dateAdded.toLocaleDateString('pt-BR', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        card.innerHTML = `
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="text-lg font-semibold">${person.name}</h4>
                                    <p class="text-sm text-gray-500">Adicionado em: ${formattedDate}</p>
                                </div>
                                <button class="text-red-500 hover:text-red-700" data-index="${index}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        `;

                        // Adicionar evento para remover pessoa
                        const removeButton = card.querySelector('button');
                        removeButton.addEventListener('click', () => {
                            removePerson(index);
                        });

                        authorizedList.appendChild(card);
                    });
                }
            }

            // Remover pessoa da lista de autorizados
            function removePerson(index) {
                if (confirm(`Remover ${authorizedPersons[index].name} da lista de pessoas autorizadas?`)) {
                    authorizedPersons.splice(index, 1);
                    saveAuthorizedPersons();
                    updateFaceMatcher();
                    updateAuthorizedList();
                }
            }

            // Carregar pessoas autorizadas do localStorage
            function loadAuthorizedPersons() {
                const savedPersons = localStorage.getItem('authorizedPersons');
                if (savedPersons) {
                    authorizedPersons = JSON.parse(savedPersons);
                }
            }

            // Salvar pessoas autorizadas no localStorage
            function saveAuthorizedPersons() {
                localStorage.setItem('authorizedPersons', JSON.stringify(authorizedPersons));
            }

            // Limpar todas as pessoas autorizadas
            function clearAllAuthorized() {
                if (confirm('Tem certeza que deseja remover todas as pessoas autorizadas?')) {
                    authorizedPersons = [];
                    saveAuthorizedPersons();
                    updateFaceMatcher();
                    updateAuthorizedList();
                    showAlert('success', 'Sucesso', 'Todos os registros foram removidos.');
                    setTimeout(hideAlert, 3000);
                }
            }

            // Event listeners
            startCameraBtn.addEventListener('click', startCamera);
            stopCameraBtn.addEventListener('click', stopCamera);
            addAuthorizedBtn.addEventListener('click', () => {
                addPersonForm.classList.toggle('hidden');
                resetAddPersonForm();
            });
            clearAuthorizedBtn.addEventListener('click', clearAllAuthorized);
            capturePhotoBtn.addEventListener('click', capturePhoto);
            saveAuthorizedBtn.addEventListener('click', saveAuthorizedPerson);

            // Carregar modelos ao iniciar
            loadModels();
        });
    </script>
</body>
</html>