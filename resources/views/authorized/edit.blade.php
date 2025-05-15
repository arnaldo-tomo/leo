@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Editar Pessoa Autorizada</h1>
    <p class="text-gray-600">Atualize os dados ou a foto de {{ $authorizedPerson->name }}.</p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <!-- Formulário de Edição -->
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <form id="personForm" action="{{ route('authorized.update', $authorizedPerson) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Campos -->
            <div class="mb-4">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-700">Nome Completo</label>
                <input type="text" id="name" name="name" value="{{ old('name', $authorizedPerson->name) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="access_level" class="block mb-2 text-sm font-medium text-gray-700">Nível de Acesso</label>
                <select id="access_level" name="access_level" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="standard" {{ old('access_level', $authorizedPerson->access_level) == 'standard' ? 'selected' : '' }}>Padrão</option>
                    <option value="restricted" {{ old('access_level', $authorizedPerson->access_level) == 'restricted' ? 'selected' : '' }}>Restrito</option>
                    <option value="admin" {{ old('access_level', $authorizedPerson->access_level) == 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
                @error('access_level')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="notes" class="block mb-2 text-sm font-medium text-gray-700">Observações (opcional)</label>
                <textarea id="notes" name="notes" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $authorizedPerson->notes) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="active" value="1" {{ old('active', $authorizedPerson->active) ? 'checked' : '' }}
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Ativo</span>
                </label>
            </div>

            <!-- Campos ocultos para o descritor facial e foto (se atualizado) -->
            <input type="hidden" id="face_descriptor" name="face_descriptor">
            <input type="hidden" id="photo_data" name="photo_data">

            <!-- Botões -->
            <div class="flex justify-between mt-6">
                <a href="{{ route('authorized.index') }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit" id="saveButton"
                    class="px-4 py-2 text-white transition duration-150 bg-indigo-600 rounded-md hover:bg-indigo-700">
                    Atualizar Pessoa
                </button>
            </div>
        </form>
    </div>

    <!-- Foto Atual e Captura -->
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-700">Foto Atual</h2>

        <div class="flex justify-center mb-6">
            @if($authorizedPerson->photo_path)
            <img src="{{ asset('storage/' . $authorizedPerson->photo_path) }}" alt="{{ $authorizedPerson->name }}" class="object-cover w-40 h-40 rounded-lg shadow">
            @else
            <div class="flex items-center justify-center w-40 h-40 text-white bg-indigo-300 rounded-lg shadow">
                <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
            </div>
            @endif
        </div>

        <h2 class="mb-4 text-lg font-semibold text-gray-700">Atualizar Foto (opcional)</h2>

        <div class="mb-4">
            <button id="startCameraBtn" class="flex items-center px-4 py-2 font-semibold text-white transition duration-150 bg-indigo-600 rounded-md hover:bg-indigo-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Iniciar Câmera
            </button>
            <button id="stopCameraBtn" class="flex items-center hidden px-4 py-2 font-semibold text-white transition duration-150 bg-red-600 rounded-md hover:bg-red-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                </svg>
                Parar Câmera
            </button>
        </div>

        <div id="cameraContainer" class="relative overflow-hidden bg-black rounded-lg aspect-video">
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

        <div class="mt-4 mb-6">
            <button id="captureBtn" class="flex items-center px-4 py-2 font-semibold text-white transition duration-150 bg-green-600 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-green-700" disabled>
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Capturar Nova Foto
            </button>
        </div>

        <div id="capturedImageContainer" class="hidden">
            <h3 class="mb-2 text-sm font-medium text-gray-700">Nova Foto Capturada:</h3>
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
    const startCameraBtn = document.getElementById('startCameraBtn');
    const stopCameraBtn = document.getElementById('stopCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadingText = document.getElementById('loadingText');
    const capturedImageContainer = document.getElementById('capturedImageContainer');

    // Campos do formulário
    const faceDescriptorInput = document.getElementById('face_descriptor');
    const photoDataInput = document.getElementById('photo_data');

    // Variáveis de estado
    let stream = null;
    let modelsLoaded = false;
    let faceDetector = null;

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
                video: { facingMode: 'user' }
            });

            // Configurar vídeo
            video.srcObject = stream;
            await video.play();

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
            alert(`Não foi possível acessar a câmera: ${error.message}`);
        }
    }

    // Parar câmera
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;

            // Atualizar interface
            startCameraBtn.classList.remove('hidden');
            stopCameraBtn.classList.add('hidden');
            captureBtn.disabled = true;

            // Limpar canvas
            const context = overlay.getContext('2d');
            context.clearRect(0, 0, overlay.width, overlay.height);
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
                }

                // Continuar loop se câmera estiver ativa
                if (video.srcObject) {
                    requestAnimationFrame(detectFaces);
                }
            } catch (error) {
                console.error('Erro na detecção:', error);
            }
        };

        detectFaces();
    }

    // Capturar foto
    async function capturePhoto() {
        try {
            // Desenhar frame atual no canvas
            const context = capturedCanvas.getContext('2d');
            context.drawImage(video, 0, 0, capturedCanvas.width,**