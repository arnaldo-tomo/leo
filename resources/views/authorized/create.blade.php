@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Adicionar Pessoa Autorizada</h1>
    <p class="text-gray-600">Cadastre uma nova pessoa com acesso ao sistema.</p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <!-- Formulário de Cadastro -->
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <form id="personForm" action="{{ route('authorized.store') }}" method="POST">
            @csrf

            <!-- Campos -->
            <div class="mb-4">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-700">Nome Completo</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="access_level" class="block mb-2 text-sm font-medium text-gray-700">Nível de Acesso</label>
                <select id="access_level" name="access_level" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="standard" {{ old('access_level') == 'standard' ? 'selected' : '' }}>Padrão</option>
                    <option value="restricted" {{ old('access_level') == 'restricted' ? 'selected' : '' }}>Restrito</option>
                    <option value="admin" {{ old('access_level') == 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
                @error('access_level')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="notes" class="block mb-2 text-sm font-medium text-gray-700">Observações (opcional)</label>
                <textarea id="notes" name="notes" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
            </div>

            <!-- Campos ocultos para o descritor facial e foto -->
            <input type="hidden" id="face_descriptor" name="face_descriptor">
            <input type="hidden" id="photo_data" name="photo_data">

            <!-- Botões -->
            <div class="flex justify-between mt-6">
                <a href="{{ route('authorized.index') }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit" id="saveButton" disabled
                    class="px-4 py-2 text-white transition duration-150 bg-indigo-600 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-indigo-700">
                    Salvar Pessoa
                </button>
            </div>
        </form>
    </div>

    <!-- Captura de Foto -->
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-700">Captura de Foto</h2>

        <div class="flex flex-wrap gap-2 mb-4">
            <button id="startCameraBtn" class="px-4 py-2 font-semibold text-white transition duration-150 bg-indigo-600 rounded hover:bg-indigo-700">
                <svg class="inline-block w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Iniciar Câmera
            </button>
            <button id="stopCameraBtn" class="hidden px-4 py-2 font-semibold text-white transition duration-150 bg-red-600 rounded hover:bg-red-700">
                <svg class="inline-block w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                </svg>
                Parar Câmera
            </button>
        </div>

        <div id="alertBox" class="hidden p-4 mb-4 rounded-md">
            <div class="flex">
                <div id="alertIcon" class="flex-shrink-0"></div>
                <div class="ml-3">
                    <p id="alertTitle" class="text-sm font-bold"></p>
                    <p id="alertMessage" class="text-sm"></p>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden bg-black rounded-lg aspect-video">
            <video id="video" class="object-cover w-full" autoplay muted></video>
            <canvas id="overlay" class="absolute top-0 left-0 w-full h-full"></canvas>

            <div id="loadingIndicator" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-75">
                <div class="text-center text-white">
                    <svg class="inline-block w-10 h-10 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p id="loadingText">Carregando modelos...</p>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <p id="status" class="text-gray-700">Câmera desativada</p>
        </div>

        <div class="mt-4 mb-6">
            <button id="captureBtn" class="flex items-center px-4 py-2 font-semibold text-white transition duration-150 bg-green-600 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-green-700" disabled>
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Capturar Foto
            </button>
        </div>

        <div id="capturedImageContainer" class="hidden">
            <h3 class="mb-2 text-sm font-medium text-gray-700">Foto Capturada:</h3>
            <div class="relative overflow-hidden bg-gray-100 border border-gray-300 rounded-lg aspect-video">
                <canvas id="capturedCanvas" class="w-full h-full"></canvas>
            </div>
            <p id="faceStatus" class="mt-2 text-sm text-gray-500">Aguardando captura de foto...</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    // Elementos DOM
    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay');
    const capturedCanvas = document.getElementById('capturedCanvas');
    const faceStatus = document.getElementById('faceStatus');
    const alertBox = document.getElementById('alertBox');
    const alertTitle = document.getElementById('alertTitle');
    const alertMessage = document.getElementById('alertMessage');
    const alertIcon = document.getElementById('alertIcon');
    const statusElement = document.getElementById('status');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const stopCameraBtn = document.getElementById('stopCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadingText = document.getElementById('loadingText');
    const capturedImageContainer = document.getElementById('capturedImageContainer');
    const faceDescriptorInput = document.getElementById('face_descriptor');
    const photoDataInput = document.getElementById('photo_data');
    const saveButton = document.getElementById('saveButton');

    // Variáveis globais
    let stream = null;
    let modelsLoaded = false;
    let faceDetector = null;

    // Atualizar hora do sistema a cada segundo
    const systemTime = document.createElement('span');
    setInterval(() => {
        const now = new Date();
        systemTime.textContent = `${now.toLocaleDateString('pt-BR')} ${now.toLocaleTimeString('pt-BR')}`;
    }, 1000);

    // Carregar modelos do face-api.js
    async function loadModels() {
        loadingText.textContent = 'Carregando modelos de reconhecimento facial...';

        try {
            // Definir caminho para modelos
            const MODEL_URL = '{{ asset("js/face-api-models") }}';

            // Carregar todos os modelos necessários
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);

            // Inicializar detector
            faceDetector = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });

            modelsLoaded = true;
            loadingIndicator.style.display = 'none';
            captureBtn.disabled = false;
        } catch (error) {
            console.error('Erro ao carregar modelos:', error);
            loadingText.textContent = `Erro: ${error.message}`;
        }
    }

    // Iniciar câmera
    async function startCamera() {
        try {
            // Verificar se modelos foram carregados
            if (!modelsLoaded) {
                await loadModels();
            }

            // Acessar câmera
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
            });

            // Configurar vídeo
            video.srcObject = stream;

            // Importante: aguardar o vídeo estar pronto antes de continuar
            await new Promise(resolve => {
                video.onloadedmetadata = () => {
                    video.play().then(resolve);
                };
            });

            // Verificar se o vídeo tem dimensões válidas
            if (!video.videoWidth || !video.videoHeight) {
                throw new Error('Não foi possível obter dimensões válidas do vídeo');
            }

            // Ajustar tamanho do canvas
            overlay.width = video.videoWidth;
            overlay.height = video.videoHeight;

            // Ajustar tamanho do canvas de captura
            capturedCanvas.width = video.videoWidth;
            capturedCanvas.height = video.videoHeight;

            // Atualizar interface
            startCameraBtn.classList.add('hidden');
            stopCameraBtn.classList.remove('hidden');
            captureBtn.disabled = false;

            // Iniciar detecção em tempo real
            startFaceDetection();
        } catch (error) {
            console.error('Erro ao iniciar câmera:', error);
            showAlert('error', 'Erro', `Não foi possível acessar a câmera: ${error.message}`);
        }
    }

    // Parar a câmera
    function stopCamera() {
        if (stream) {
            // Parar streaming
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;

            // Atualizar estados
            startCameraBtn.classList.remove('hidden');
            stopCameraBtn.classList.add('hidden');
            captureBtn.disabled = true;
            statusElement.textContent = 'Câmera desativada';

            // Limpar canvas
            const context = overlay.getContext('2d');
            context.clearRect(0, 0, overlay.width, overlay.height);

            // Esconder alerta
            hideAlert();
        }
    }

    // Detecção de faces em tempo real
    async function startFaceDetection() {
        const detectFaces = async () => {
            if (!video.srcObject) return;

            try {
                // Detectar faces
                const detections = await faceapi.detectAllFaces(video, faceDetector);

                // Limpar canvas
                const context = overlay.getContext('2d');
                context.clearRect(0, 0, overlay.width, overlay.height);

                // Se houver faces, desenhar retângulos
                if (detections && detections.length > 0) {
                    detections.forEach(detection => {
                        const box = detection.box;

                        // Desenhar retângulo
                        context.beginPath();
                        context.rect(box.x, box.y, box.width, box.height);
                        context.lineWidth = 3;
                        context.strokeStyle = '#10B981'; // Verde
                        context.stroke();
                    });

                    statusElement.textContent = `Detectado: ${detections.length} rosto(s)`;
                } else {
                    statusElement.textContent = 'Nenhum rosto detectado';
                }

                // Continuar loop se câmera estiver ativa
                if (video.srcObject) {
                    requestAnimationFrame(detectFaces);
                }
            } catch (error) {
                console.error('Erro na detecção:', error);
                statusElement.textContent = `Erro na detecção: ${error.message}`;
            }
        };

        detectFaces();
    }

    // Capturar foto
    async function capturePhoto() {
        try {
            // Verificar se o vídeo está pronto
            if (!video.videoWidth || !video.videoHeight) {
                showAlert('warning', 'Câmera não está pronta', 'Aguarde um momento e tente novamente.');
                return;
            }

            // Desenhar frame atual no canvas
            const context = capturedCanvas.getContext('2d');

            // Definir dimensões corretas do canvas
            capturedCanvas.width = video.videoWidth;
            capturedCanvas.height = video.videoHeight;

            // Desenhar o vídeo no canvas
            context.drawImage(video, 0, 0, capturedCanvas.width, capturedCanvas.height);

            // Mostrar container de imagem capturada
            capturedImageContainer.classList.remove('hidden');

            // Tentar detectar face na imagem capturada
            faceStatus.textContent = 'Processando imagem...';
            faceStatus.className = 'mt-2 text-sm text-yellow-500';

            // Aguardar um momento para garantir que a imagem está pronta
            await new Promise(resolve => setTimeout(resolve, 100));

            // Detectar face com landmarks e descritor
            const detection = await faceapi.detectSingleFace(capturedCanvas, faceDetector)
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (detection) {
                // Armazenar descritor facial
                const descriptor = Array.from(detection.descriptor);
                faceDescriptorInput.value = JSON.stringify(descriptor);

                // Armazenar imagem para envio
                photoDataInput.value = capturedCanvas.toDataURL('image/png');

                // Atualizar status e habilitar botão de salvar
                faceStatus.textContent = 'Rosto detectado com sucesso!';
                faceStatus.className = 'mt-2 text-sm text-green-500';
                saveButton.disabled = false;

                // Desenhar retângulo na face
                const box = detection.detection.box;
                context.beginPath();
                context.rect(box.x, box.y, box.width, box.height);
                context.lineWidth = 3;
                context.strokeStyle = '#10B981'; // Verde
                context.stroke();
            } else {
                // Falha na detecção
                faceStatus.textContent = 'Nenhum rosto detectado. Tente novamente em uma posição diferente.';
                faceStatus.className = 'mt-2 text-sm text-red-500';
                faceDescriptorInput.value = '';
                photoDataInput.value = '';
                saveButton.disabled = true;
            }
        } catch (error) {
            console.error('Erro na captura:', error);
            faceStatus.textContent = `Erro na captura: ${error.message}`;
            faceStatus.className = 'mt-2 text-sm text-red-500';
        }
    }

    // Mostrar alerta na interface
    function showAlert(type, title, message) {
        alertTitle.textContent = title;
        alertMessage.textContent = message;

        // Limpar classes anteriores
        alertBox.className = 'p-4 mb-4 rounded-md flex';

        // Definir estilo baseado no tipo
        if (type === 'error') {
            alertBox.classList.add('bg-red-100', 'border', 'border-red-400', 'text-red-700');
            alertIcon.innerHTML = `
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            `;
        } else if (type === 'success') {
            alertBox.classList.add('bg-green-100', 'border', 'border-green-400', 'text-green-700');
            alertIcon.innerHTML = `
                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            `;
        } else {
            alertBox.classList.add('bg-yellow-100', 'border', 'border-yellow-400', 'text-yellow-700');
            alertIcon.innerHTML = `
                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            `;
        }

        // Mostrar alerta
        alertBox.classList.remove('hidden');
    }

    // Esconder alerta
    function hideAlert() {
        alertBox.classList.add('hidden');
    }

    // Validação de formulário
    document.getElementById('personForm').addEventListener('submit', function(e) {
        if (!faceDescriptorInput.value || !photoDataInput.value) {
            e.preventDefault();
            showAlert('error', 'Erro', 'Por favor, capture uma foto válida com detecção facial antes de salvar.');
        } else if (!document.getElementById('name').value.trim()) {
            e.preventDefault();
            showAlert('error', 'Erro', 'Por favor, insira o nome da pessoa.');
        }
    });

    // Eventos
    startCameraBtn.addEventListener('click', startCamera);
    stopCameraBtn.addEventListener('click', stopCamera);
    captureBtn.addEventListener('click', capturePhoto);

    // Inicializar - carregar modelos
    loadModels();
});
</script>
@endpush