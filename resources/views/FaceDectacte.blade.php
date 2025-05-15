@extends('layouts.main')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="p-6 mb-6 bg-white rounded-lg shadow-lg">
        <h2 class="mb-4 text-2xl font-bold">Sistema de Detecção de Pessoas</h2>

        <div class="mb-4">
            <button id="startCamera" class="px-4 py-2 font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">
                Iniciar Câmera
            </button>
            <button id="stopCamera" class="hidden px-4 py-2 ml-2 font-semibold text-white bg-red-600 rounded hover:bg-red-700">
                Parar Câmera
            </button>
        </div>

        <div class="relative w-full">
            <div id="alertBox" class="hidden px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded">
                <strong class="font-bold">Alerta!</strong>
                <span class="block sm:inline">Pessoa detectada!</span>
            </div>

            <div class="relative overflow-hidden bg-black rounded-lg" style="max-height: 480px;">
                <video id="video" class="w-full" autoplay muted style="max-height: 480px;"></video>
                <canvas id="overlay" class="absolute top-0 left-0 w-full h-full"></canvas>
            </div>

            <div class="mt-4">
                <p id="status" class="text-gray-700">Câmera desativada</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
        document.addEventListener('DOMContentLoaded', async () => {

            const video = document.getElementById('video');
            const overlay = document.getElementById('overlay');
            const alertBox = document.getElementById('alertBox');
            const statusElement = document.getElementById('status');
            const startCameraBtn = document.getElementById('startCamera');
            const stopCameraBtn = document.getElementById('stopCamera');

            let stream = null;
            let detectionActive = false;
            let modelsLoaded = false;

            console.log('Página carregada. Iniciando configuração...');

            // Carregue os modelos do face-api.js
            async function loadModels() {
                statusElement.textContent = 'Carregando modelos de detecção...';
                console.log('Carregando modelos...');

                try {
                    // Carregue os modelos diretamente do CDN para evitar problemas com caminhos locais
                    await Promise.all([
                        faceapi.nets.tinyFaceDetector.load(),
                        faceapi.nets.faceLandmark68Net.load(),
                        faceapi.nets.faceRecognitionNet.load()
                    ]);

                    modelsLoaded = true;
                    statusElement.textContent = 'Modelos carregados. Pronto para detecção.';
                    console.log('Modelos carregados com sucesso');

                } catch (error) {

                    statusElement.textContent = `Erro ao carregar modelos: ${error.message}`;
                    console.error('Erro ao carregar modelos:', error);
                }
            }

            // Iniciar a câmera
            async function startCamera() {
                try {

                    console.log('Iniciando câmera...');
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'user' }
                    });

                    video.srcObject = stream;
                    video.onloadedmetadata = () => {
                        video.play();

                        // Ajustar tamanho do canvas sobreposto ao vídeo
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

                console.log('Parando câmera...');

                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    video.srcObject = null;
                    detectionActive = false;
                    startCameraBtn.classList.remove('hidden');
                    stopCameraBtn.classList.add('hidden');
                    statusElement.textContent = 'Câmera desativada';
                    alertBox.classList.add('hidden');

                    // Limpar o canvas
                    const context = overlay.getContext('2d');
                    context.clearRect(0, 0, overlay.width, overlay.height);
                }
            }

            // Iniciar detecção de faces
            async function startDetection() {
                
                detectionActive = true;
                console.log('Iniciando detecção de faces');

                // Loop de detecção
                const detectFaces = async () => {
                    if (!detectionActive) return;

                    try {
                        if (video.paused || video.ended || !video.videoWidth) {
                            requestAnimationFrame(detectFaces);
                            return;
                        }

                        // Verificar se o vídeo está pronto
                        console.log('Detectando rostos...');

                        const detections = await faceapi.detectAllFaces(
                            video,
                            new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 })
                        );

                        console.log('Detecções:', detections);

                        const context = overlay.getContext('2d');
                        context.clearRect(0, 0, overlay.width, overlay.height);

                        // Ajuste de escala para o canvas
                        const displaySize = { width: overlay.width, height: overlay.height };

                        // Desenhar as detecções
                        if (detections.length > 0) {
                            // Mostrar alerta quando pessoas são detectadas
                            alertBox.classList.remove('hidden');

                            // Desenhar retângulos nas faces
                            detections.forEach(detection => {
                                const box = detection.box;
                                // Desenhar retângulo
                                context.beginPath();
                                context.rect(
                                    box.x,
                                    box.y,
                                    box.width,
                                    box.height
                                );
                                context.lineWidth = 3;
                                context.strokeStyle = 'red';
                                context.stroke();
                            });

                            statusElement.textContent = `Pessoas detectadas: ${detections.length}`;
                        } else {
                            // Ocultar alerta quando não há detecções
                            alertBox.classList.add('hidden');
                            statusElement.textContent = 'Monitorando...';
                        }
                    } catch (error) {
                        console.error('Erro na detecção:', error);
                    }

                    // Continuar o loop
                    if (detectionActive) {
                        requestAnimationFrame(detectFaces);
                    }
                };

                // Iniciar o loop de detecção
                detectFaces();
            }

            // Event listeners para os botões
            startCameraBtn.addEventListener('click', startCamera);
            stopCameraBtn.addEventListener('click', stopCamera);

            // Carregar modelos ao iniciar a página
            loadModels();

            console.log('Configuração concluída. Aguardando interação do usuário...');
        });
</script>
@endsection