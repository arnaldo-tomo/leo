@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Monitoramento em Tempo Real</h1>
    <p class="text-gray-600">Controle de acesso com reconhecimento facial.</p>
</div>

<div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
    <!-- Câmera e Detecção -->
    <div class="lg:col-span-2">
        <div class="p-6 bg-white rounded-lg shadow-sm">
            <div class="flex flex-wrap gap-2 mb-4">
                <button id="startCamera"
                    class="px-4 py-2 font-semibold text-white transition duration-150 bg-indigo-600 rounded hover:bg-indigo-700">
                    <svg class="inline-block w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                        </path>
                    </svg>
                    Iniciar Câmera
                </button>
                <button id="stopCamera"
                    class="hidden px-4 py-2 font-semibold text-white transition duration-150 bg-red-600 rounded hover:bg-red-700">
                    <svg class="inline-block w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                    </svg>
                    Parar Câmera
                </button>
                <button id="reloadData"
                    class="px-4 py-2 font-semibold text-white transition duration-150 bg-green-600 rounded hover:bg-green-700">
                    <svg class="inline-block w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Recarregar Dados
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

                <div id="loadingIndicator"
                    class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-75">
                    <div class="text-center text-white">
                        <svg class="inline-block w-10 h-10 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p id="loadingText">Carregando...</p>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <p id="status" class="text-gray-700">Câmera desativada</p>
            </div>

            <div id="debugInfo" class="hidden p-4 mt-4 bg-gray-100 rounded-lg">
                <h3 class="mb-2 text-sm font-semibold">Informações de Depuração</h3>
                <pre id="debugData"
                    class="h-32 p-3 overflow-auto text-xs text-white whitespace-pre-wrap bg-gray-800 rounded"></pre>
            </div>
        </div>
    </div>

    <!-- Logs Recentes e Controles -->
    <div>
        <div class="p-6 mb-6 bg-white rounded-lg shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-700">Acessos Recentes</h2>
                <button id="refreshLogs"
                    class="p-1 text-indigo-600 rounded-full hover:text-indigo-800 hover:bg-indigo-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                </button>
            </div>
            <div id="recentLogs" class="space-y-4">
                <p class="text-gray-500">Carregando registros de acesso...</p>
            </div>
        </div>

        <div class="p-6 bg-white rounded-lg shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-700">Informações de Sistema</h2>
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                    <span id="modelStatus">Modelos não carregados</span>
                </li>
                <li class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                        </path>
                    </svg>
                    <span id="personCount">Carregando pessoas...</span>
                </li>
                <li class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span id="systemTime"></span>
                </li>
                <li class="flex items-center mt-3">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="debugMode" class="w-4 h-4 text-indigo-600 form-checkbox">
                        <span class="ml-2 text-sm text-gray-600">Modo de Depuração</span>
                    </label>
                </li>
            </ul>
        </div>

        <!-- Lista de Pessoas Cadastradas -->
        <div class="p-6 mt-6 bg-white rounded-lg shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-700">Pessoas Cadastradas</h2>
            <div id="personList" class="overflow-auto max-h-60">
                <p class="text-gray-500">Carregando lista de pessoas...</p>
            </div>
        </div>
    </div>
</div>

<!-- Container para notificações -->
<div id="notifications-container" class="fixed z-50 space-y-2 top-4 right-4 w-80"></div>


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async () => {
    // Elementos DOM
    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay');
    const alertBox = document.getElementById('alertBox');
    const alertTitle = document.getElementById('alertTitle');
    const alertMessage = document.getElementById('alertMessage');
    const alertIcon = document.getElementById('alertIcon');
    const statusElement = document.getElementById('status');
    const startCameraBtn = document.getElementById('startCamera');
    const stopCameraBtn = document.getElementById('stopCamera');
    const reloadDataBtn = document.getElementById('reloadData');
    const refreshLogsBtn = document.getElementById('refreshLogs');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const loadingText = document.getElementById('loadingText');
    const modelStatus = document.getElementById('modelStatus');
    const personCount = document.getElementById('personCount');
    const systemTime = document.getElementById('systemTime');
    const recentLogs = document.getElementById('recentLogs');
    const personList = document.getElementById('personList');
    const debugModeCheckbox = document.getElementById('debugMode');
    const debugInfo = document.getElementById('debugInfo');
    const debugData = document.getElementById('debugData');

    // Variáveis globais
    let stream = null;
    let detectionActive = false;
    let modelsLoaded = false;
    let authorizedPersons = [];
    let faceDetector = null;
    let faceMatcher = null;
    let lastDetectionTime = 0;
    let faceDetectionInterval = 1000; // Intervalo mínimo entre detecções para log (em ms)
    let debugMode = false;

    // Atualizar hora do sistema a cada segundo
    setInterval(() => {
        const now = new Date();
        systemTime.textContent = `${now.toLocaleDateString('pt-BR')} ${now.toLocaleTimeString('pt-BR')}`;
    }, 1000);

    // Ativar/desativar modo de depuração
    debugModeCheckbox.addEventListener('change', function() {
        debugMode = this.checked;
        debugInfo.classList.toggle('hidden', !debugMode);

        if (debugMode) {
            logDebug('Modo de depuração ativado');
        }
    });

    // Função para registrar informações de depuração
    function logDebug(message, data = null) {
        if (!debugMode) return;

        const timestamp = new Date().toLocaleTimeString();
        let logMessage = `[${timestamp}] ${message}`;

        if (data) {
            if (typeof data === 'object') {
                logMessage += '\n' + JSON.stringify(data, null, 2);
            } else {
                logMessage += '\n' + data;
            }
        }

        debugData.textContent = logMessage + '\n\n' + debugData.textContent;
    }

    // Carregar pessoas autorizadas da API
    async function loadAuthorizedPersons() {
        try {
            logDebug('Carregando pessoas autorizadas...');

            const response = await fetch("{{ route('authorized.get-all') }}");
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }

            // Mostrar dados brutos para depuração
            const rawData = await response.text();
            logDebug('Dados brutos da API:', rawData);

            // Tentar converter para JSON
            let data;
            try {
                data = JSON.parse(rawData);
            } catch (e) {
                throw new Error(`Falha ao converter resposta para JSON: ${e.message}. Dados recebidos: ${rawData.substring(0, 200)}...`);
            }

            authorizedPersons = data;

            // Verificar se há dados válidos
            if (!Array.isArray(authorizedPersons)) {
                throw new Error(`Formato de dados inválido: não é um array. Recebido: ${typeof authorizedPersons}`);
            }

            // Log detalhado para cada pessoa para diagnóstico
            authorizedPersons.forEach(person => {
                let descriptorInfo = "sem descritor";

                if (person.face_descriptor) {
                    if (typeof person.face_descriptor === 'string') {
                        try {
                            const parsed = JSON.parse(person.face_descriptor);
                            descriptorInfo = `string JSON com ${parsed.length} elementos`;

                            // Mostrar uma amostra dos primeiros valores para verificar formato
                            logDebug(`Amostra do descritor de ${person.name}: ${JSON.stringify(parsed.slice(0, 5))}...`);
                        } catch (e) {
                            descriptorInfo = `string inválida: ${person.face_descriptor.substring(0, 20)}...`;
                        }
                    } else if (Array.isArray(person.face_descriptor)) {
                        descriptorInfo = `array com ${person.face_descriptor.length} elementos`;

                        // Mostrar uma amostra dos primeiros valores
                        logDebug(`Amostra do descritor de ${person.name}: ${JSON.stringify(person.face_descriptor.slice(0, 5))}...`);
                    } else {
                        descriptorInfo = `tipo inesperado: ${typeof person.face_descriptor}`;
                    }
                }

                logDebug(`Pessoa: ID=${person.id}, Nome=${person.name}, Descritor=${descriptorInfo}`);
            });

            // Verificar se há descritores faciais válidos
            const validPersons = authorizedPersons.filter(person => {
                if (!person.face_descriptor) return false;

                // Tentar converter para verificar validade
                try {
                    let descriptor = person.face_descriptor;

                    if (typeof descriptor === 'string') {
                        descriptor = JSON.parse(descriptor);
                    }

                    return Array.isArray(descriptor) && descriptor.length >= 128;
                } catch (e) {
                    logDebug(`Erro ao validar descritor da pessoa ${person.id}: ${e.message}`);
                    return false;
                }
            });

            if (validPersons.length === 0 && authorizedPersons.length > 0) {
                logDebug('ATENÇÃO: Existem pessoas cadastradas, mas nenhuma com descritores válidos');
                logDebug('Você precisa cadastrar os descritores faciais para cada pessoa');
                throw new Error('Nenhum descritor facial válido encontrado nas pessoas cadastradas');
            }

            logDebug(`${authorizedPersons.length} pessoas carregadas`, authorizedPersons);
            logDebug(`${validPersons.length} pessoas com descritores faciais válidos`);

            // Atualizar contador e lista de pessoas
            personCount.textContent = `${authorizedPersons.length} pessoas autorizadas (${validPersons.length} com face)`;
            updatePersonList();

            return authorizedPersons;
        } catch (error) {
            console.error('Erro ao carregar pessoas autorizadas:', error);
            logDebug('Erro ao carregar pessoas autorizadas', error.message);
            personCount.textContent = 'Erro ao carregar pessoas';
            showAlert('error', 'Erro', `Falha ao carregar pessoas autorizadas: ${error.message}`);
            return [];
        }
    }

    // Atualizar lista de pessoas na interface
    function updatePersonList() {
        personList.innerHTML = '';

        if (authorizedPersons.length === 0) {
            personList.innerHTML = '<p class="text-gray-500">Nenhuma pessoa cadastrada.</p>';
            return;
        }

        // Criar tabela
        const table = document.createElement('table');
        table.className = 'min-w-full';

        // Cabeçalho
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr class="bg-gray-100">
                <th class="px-3 py-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">ID</th>
                <th class="px-3 py-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nome</th>
                <th class="px-3 py-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
            </tr>
        `;
        table.appendChild(thead);

        // Corpo da tabela
        const tbody = document.createElement('tbody');
        authorizedPersons.forEach(person => {
            const tr = document.createElement('tr');
            tr.className = 'border-t hover:bg-gray-50';

            // Verificar se o descritor é válido
            const hasValidDescriptor = person.face_descriptor &&
                                      Array.isArray(person.face_descriptor) &&
                                      person.face_descriptor.length > 0;

            tr.innerHTML = `
                <td class="px-3 py-2 text-sm text-gray-500">${person.id}</td>
                <td class="px-3 py-2 text-sm font-medium text-gray-900">${person.name}</td>
                <td class="px-3 py-2">
                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full ${hasValidDescriptor ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${hasValidDescriptor ? 'Válido' : 'Inválido'}
                    </span>
                </td>
            `;

            tbody.appendChild(tr);
        });
        table.appendChild(tbody);

        personList.appendChild(table);
    }

    // Carregar logs recentes
    async function loadRecentLogs() {
        try {
            logDebug('Carregando logs recentes...');

            const response = await fetch("{{ route('access.recent-logs') }}");
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }

            const logs = await response.json();

            // Limpar logs atuais
            recentLogs.innerHTML = '';

            if (logs.length === 0) {
                recentLogs.innerHTML = '<p class="text-gray-500">Nenhum acesso registrado recentemente.</p>';
                return;
            }

            logDebug(`${logs.length} logs recentes carregados`);

            // Adicionar cada log
            logs.forEach(log => {
                const statusColor = log.status === 'authorized' ? 'bg-green-100 text-green-800' :
                                  (log.status === 'unauthorized' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');

                const statusText = log.status === 'authorized' ? 'Autorizado' :
                                 (log.status === 'unauthorized' ? 'Não Autorizado' : 'Desconhecido');

                const logItem = document.createElement('div');
                logItem.className = 'flex items-center p-3 bg-gray-50 rounded-lg';

                // Formatação da data
                const accessTime = new Date(log.access_time);
                const formattedTime = `${accessTime.toLocaleDateString('pt-BR')} ${accessTime.toLocaleTimeString('pt-BR')}`;

                logItem.innerHTML = `
                    <div class="flex-shrink-0 w-10 h-10 mr-3">
                        ${log.photo_path ?
                          `<img src="{{ asset('storage') }}/${log.photo_path}" class="object-cover w-10 h-10 rounded-full">` :
                          '<div class="flex items-center justify-center w-10 h-10 bg-gray-300 rounded-full"><span class="text-gray-500">?</span></div>'}
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium">${log.person_name || (log.authorized_person ? log.authorized_person.name : 'Desconhecido')}</div>
                        <div class="flex items-center">
                            <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full ${statusColor}">
                                ${statusText}
                            </span>
                            <span class="ml-2 text-xs text-gray-500">${formattedTime}</span>
                        </div>
                    </div>
                `;

                recentLogs.appendChild(logItem);
            });
        } catch (error) {
            console.error('Erro ao carregar logs recentes:', error);
            logDebug('Erro ao carregar logs recentes', error.message);
            recentLogs.innerHTML = '<p class="text-red-500">Erro ao carregar logs recentes.</p>';
        }
    }

    // Mostrar alerta
    function showAlert(type, title, message) {
        // Define cores e ícones com base no tipo
        const colors = {
            success: 'bg-green-100 text-green-800',
            error: 'bg-red-100 text-red-800',
            warning: 'bg-yellow-100 text-yellow-800',
            info: 'bg-blue-100 text-blue-800'
        };

        const icons = {
            success: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
            error: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>',
            warning: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
            info: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>'
        };

        // Aplica as configurações
        alertBox.className = `alert-box p-4 border-l-4 ${colors[type]} border-l-4 border-${type === 'warning' ? 'yellow' : (type === 'info' ? 'blue' : (type === 'success' ? 'green' : 'red'))}-500`;
        alertIcon.innerHTML = icons[type];
        alertTitle.textContent = title;
        alertMessage.textContent = message;

        // Exibe o alerta
        alertBox.classList.remove('hidden');
    }

    // Esconder alerta
    function hideAlert() {
        alertBox.classList.add('hidden');
    }

    // Desenhar caixa ao redor da face
    function drawFaceBox(context, box, color, label) {
        const { x, y, width, height } = box;

        // Desenhar caixa
        context.lineWidth = 3;
        context.strokeStyle = color;
        context.strokeRect(x, y, width, height);

        // Desenhar rótulo
        context.fillStyle = color;
        context.fillRect(x, y - 30, context.measureText(label).width + 10, 30);
        context.fillStyle = '#FFFFFF';
        context.font = '16px Arial';
        context.fillText(label, x + 5, y - 10);
    }

    // Registrar acesso
    async function recordAccess(personId, personName, status, imageData) {
        // Verificar se o intervalo mínimo entre registros foi atingido
        const now = Date.now();
        if (now - lastDetectionTime < faceDetectionInterval) {
            logDebug('Intervalo mínimo entre detecções não atingido');
            return;
        }

        lastDetectionTime = now;

        try {
            logDebug(`Registrando acesso: ${status} para ID ${personId || 'N/A'}`);

            // Criar dados de acesso
            const formData = new FormData();
            formData.append('status', status);

            if (personId) {
                formData.append('person_id', personId);
            }

            if (personName) {
                formData.append('person_name', personName);
            }

            // Converter dataURL para Blob
            if (imageData) {
                const blob = await (async () => {
                    const res = await fetch(imageData);
                    return res.blob();
                })();
                formData.append('photo', blob, 'detection.png');
            }

            // Enviar para API
            await fetch("{{ route('access.record') }}", {
                method: 'POST',
                body: formData
            });

            logDebug('Acesso registrado com sucesso');

            // Recarregar logs após o registro
            loadRecentLogs();
        } catch (error) {
            console.error('Erro ao registrar acesso:', error);
            logDebug('Erro ao registrar acesso', error.message);
        }
    }

    // Mostrar notificação
    function showNotification(message, imageData, type) {
        if (Notification.permission === 'granted') {
            const notificationOptions = {
                body: message,
                icon: imageData || '/favicon.ico'
            };

            new Notification('Sistema de Acesso', notificationOptions);
        }
    }

    // Atualizar Face Matcher
    function updateFaceMatcher(persons) {
        if (!persons || persons.length === 0) {
            logDebug('Nenhuma pessoa disponível para atualizar o matcher');
            return;
        }

        try {
            logDebug('Atualizando face matcher...');

            // Filtrar pessoas com descritores válidos
            const validPersons = persons.filter(person => {
                if (!person.face_descriptor) {
                    logDebug(`Pessoa ${person.id} (${person.name}) não tem descritor facial`);
                    return false;
                }

                // Verificar formato do descritor
                try {
                    let descriptor;

                    // Se o descritor é uma string, tentar parsear como JSON
                    if (typeof person.face_descriptor === 'string') {
                        logDebug(`Pessoa ${person.id} (${person.name}) tem descritor em formato string, convertendo...`);
                        descriptor = JSON.parse(person.face_descriptor);
                    } else {
                        descriptor = person.face_descriptor;
                    }

                    // Verificar se é um array válido
                    if (!Array.isArray(descriptor) || descriptor.length < 128) {
                        logDebug(`Pessoa ${person.id} (${person.name}) tem descritor inválido: ${descriptor.length} elementos`);
                        return false;
                    }

                    // Salvar o descritor já convertido para uso futuro
                    person.processedDescriptor = Float32Array.from(descriptor);
                    return true;
                } catch (error) {
                    logDebug(`Erro ao processar descritor da pessoa ${person.id} (${person.name}): ${error.message}`);
                    return false;
                }
            });

            logDebug(`${validPersons.length} de ${persons.length} pessoas têm descritores válidos`);

            if (validPersons.length === 0) {
                logDebug('Nenhum descritor facial válido encontrado');
                return;
            }

            // Criar descritores para FaceMatcher
            const labeledDescriptors = validPersons.map(person => {
                logDebug(`Adicionando ${person.name} (ID: ${person.id}) ao matcher`);

                return new faceapi.LabeledFaceDescriptors(
                    person.id.toString(),
                    [person.processedDescriptor]
                );
            });

            // Log para o primeiro descritor como amostra
            if (labeledDescriptors.length > 0) {
                const sampleDescriptor = labeledDescriptors[0].descriptors[0];
                logDebug(`Amostra de descritor (primeiros 5 valores): ${Array.from(sampleDescriptor.slice(0, 5))}`);
            }

            // Criar face matcher com limiar mais permissivo (0.45 em vez de 0.6)
            faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.45);
            logDebug(`Face matcher atualizado com ${validPersons.length} pessoas e limiar 0.45`);
        } catch (error) {
            console.error('Erro ao atualizar face matcher:', error);
            logDebug('Erro ao atualizar face matcher', error.message);
        }
    }

    // Carregar modelos do face-api.js
    async function loadModels() {
        try {
            loadingIndicator.classList.remove('hidden');
            loadingText.textContent = 'Carregando modelos...';
            modelStatus.textContent = 'Carregando...';
            logDebug('Carregando modelos face-api.js...');

            // Verificar se faceapi existe
            if (!window.faceapi) {
                throw new Error('face-api.js não está carregado. Verifique se o script está incluído na página.');
            }

            // Definir o caminho dos modelos
            const modelsPath  = '{{ asset("js/face-api-models") }}';
            logDebug(`Caminho dos modelos: ${modelsPath}`);

            // Carregar modelos
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(modelsPath),
                faceapi.nets.faceLandmark68Net.loadFromUri(modelsPath),
                faceapi.nets.faceRecognitionNet.loadFromUri(modelsPath)
            ]);

            // Verificar se os modelos foram carregados corretamente
            if (!faceapi.nets.ssdMobilenetv1.isLoaded) {
                throw new Error('Modelo de detecção de face não carregado corretamente');
            }

            if (!faceapi.nets.faceLandmark68Net.isLoaded) {
                throw new Error('Modelo de landmarks faciais não carregado corretamente');
            }

            if (!faceapi.nets.faceRecognitionNet.isLoaded) {
                throw new Error('Modelo de reconhecimento facial não carregado corretamente');
            }

            // Configurar detector
            faceDetector = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 });

            // Atualizar status
            modelsLoaded = true;
            modelStatus.textContent = 'Modelos carregados';
            logDebug('Modelos carregados com sucesso');

            // Carregar pessoas autorizadas
            const persons = await loadAuthorizedPersons();

            // Configurar matcher
            updateFaceMatcher(persons);

            // Carregar logs recentes
            await loadRecentLogs();

            // Esconder indicador de carregamento
            loadingIndicator.classList.add('hidden');

            // Habilitar controles
            startCameraBtn.disabled = false;
            reloadDataBtn.disabled = false;
            refreshLogsBtn.disabled = false;
        } catch (error) {
            console.error('Erro ao carregar modelos:', error);
            logDebug('Erro ao carregar modelos', error.message);
            modelStatus.textContent = `Erro: ${error.message}`;
            loadingIndicator.classList.add('hidden');
            showAlert('error', 'Erro', `Falha ao carregar modelos: ${error.message}`);
        }
    }

    // Iniciar a câmera
    async function startCamera() {
        try {
            if (!modelsLoaded) {
                throw new Error('Modelos não carregados. Aguarde o carregamento completo.');
            }

            logDebug('Iniciando câmera...');
            statusElement.textContent = 'Inicializando câmera...';

            // Solicitar acesso à câmera
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                }
            });

            // Configurar vídeo
            video.srcObject = stream;

            // Aguardar carregamento do vídeo
            await new Promise(resolve => {
                video.onloadedmetadata = () => {
                    resolve();
                };
            });

            // Iniciar reprodução
            await video.play();

            // Ajustar tamanho do canvas de sobreposição
            overlay.width = video.videoWidth;
            overlay.height = video.videoHeight;

            logDebug(`Vídeo inicializado: ${video.videoWidth}x${video.videoHeight}`);

            // Atualizar interface
            startCameraBtn.classList.add('hidden');
            stopCameraBtn.classList.remove('hidden');
            statusElement.textContent = 'Câmera ativa. Detectando faces...';

            // Iniciar detecção
            startDetection();
        } catch (error) {
            console.error('Erro ao iniciar câmera:', error);
            logDebug('Erro ao iniciar câmera', error.message);
            statusElement.textContent = `Erro ao acessar a câmera: ${error.message}`;
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
            detectionActive = false;
            startCameraBtn.classList.remove('hidden');
            stopCameraBtn.classList.add('hidden');
            statusElement.textContent = 'Câmera desativada';

            // Limpar canvas
            const context = overlay.getContext('2d');
            context.clearRect(0, 0, overlay.width, overlay.height);

            // Esconder alerta
            hideAlert();

            logDebug('Câmera parada');
        }
    }

    // Iniciar detecção de faces
    async function startDetection() {
        detectionActive = true;
        logDebug('Iniciando detecção de faces');

        // Loop de detecção
        const detectFaces = async () => {
            if (!detectionActive) return;

            try {
                // Verificar se vídeo está pronto
                if (video.paused || video.ended || !video.videoWidth) {
                    requestAnimationFrame(detectFaces);
                    return;
                }

                // Detectar faces com landmarks e descritores
                const detections = await faceapi.detectAllFaces(video, faceDetector)
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                // Limpar canvas
                const context = overlay.getContext('2d');
                context.clearRect(0, 0, overlay.width, overlay.height);

                // Verificar se foram detectadas faces
                if (detections.length > 0) {
                    logDebug(`Detectado ${detections.length} face(s)`);

                    // Contadores
                    let authorizedCount = 0;
                    let unauthorizedCount = 0;

                    // Para cada face detectada
                    for (const detection of detections) {
                        // Verificar se temos matcher configurado e pessoas cadastradas
                        if (faceMatcher && authorizedPersons.length > 0) {
                            // Obter descritor da face detectada
                            const faceDescriptor = detection.descriptor;

                            // Comparar com faces autorizadas
                            const match = faceMatcher.findBestMatch(faceDescriptor);
                            logDebug(`Melhor correspondência: ${match.label}, distância: ${match.distance.toFixed(4)}`);

                            const isAuthorized = match.label !== 'unknown';

                            // Encontrar pessoa correspondente no array
                            let person = null;
                            if (isAuthorized) {
                                person = authorizedPersons.find(p => p.id.toString() === match.label);
                                if (!person) {
                                    logDebug(`Pessoa com ID ${match.label} não encontrada no array`);
                                }
                            }

                            // Determinar cor e texto
                            const boxColor = isAuthorized ? '#10B981' : '#EF4444'; // Verde ou vermelho
                            const label = person ? person.name : (isAuthorized ? 'Autorizado' : 'Não Autorizado');

                            // Desenhar caixa
                            drawFaceBox(context, detection.detection.box, boxColor, label);

                            // Contar resultados
                            if (isAuthorized) {
                                authorizedCount++;

                                // Capturar imagem para log
                                const canvas = document.createElement('canvas');
                                canvas.width = video.videoWidth;
                                canvas.height = video.videoHeight;
                                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                                const imageData = canvas.toDataURL('image/png');

                                // Registrar acesso autorizado
                                recordAccess(person.id, person.name, 'authorized', imageData);

                                // Notificar
                                showNotification(`Acesso permitido: ${person.name}`, imageData, 'success');
                            } else {
                                unauthorizedCount++;

                                // Capturar imagem para log
                                const canvas = document.createElement('canvas');
                                canvas.width = video.videoWidth;
                                canvas.height = video.videoHeight;
                                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                                const imageData = canvas.toDataURL('image/png');

                                // Registrar acesso não autorizado
                                recordAccess(null, null, 'unauthorized', imageData);

                                // Notificar
                                showNotification('Acesso negado: Pessoa não autorizada', imageData, 'error');
                            }
                        } else {
                            // Sem pessoas cadastradas ou matcher configurado
                            logDebug('Nenhum matcher configurado ou nenhuma pessoa cadastrada');

                            // Desenhar caixa em amarelo (status desconhecido)
                            drawFaceBox(context, detection.detection.box, '#F59E0B', 'Desconhecido');

                            // Capturar imagem para log
                            const canvas = document.createElement('canvas');
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                            const imageData = canvas.toDataURL('image/png');

                            // Registrar acesso
                            recordAccess(null, null, 'unknown', imageData);

                            // Notificar
                            showNotification('Pessoa desconhecida detectada', imageData, 'warning');
                        }
                    }

                    // Atualizar status
                    statusElement.textContent = `Detecção: ${detections.length} face(s) (${authorizedCount} autorizada(s), ${unauthorizedCount} não autorizada(s))`;

                    // Mostrar alerta apropriado na interface
                    if (unauthorizedCount > 0 && authorizedPersons.length > 0) {
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
                logDebug('Erro na detecção de faces', error.message);
                statusElement.textContent = `Erro na detecção: ${error.message}`;
            }

            // Continuar o loop
            if (detectionActive) {
                requestAnimationFrame(detectFaces);
            }
        };

        // Iniciar o loop
        detectFaces();
    }

    // Recarregar dados de pessoas
    async function reloadData() {
        try {
            logDebug('Recarregando dados...');
            showAlert('info', 'Recarregando', 'Atualizando dados do sistema...');

            // Recarregar pessoas autorizadas
            const persons = await loadAuthorizedPersons();

            // Atualizar matcher
            updateFaceMatcher(persons);

            // Carregar logs recentes
            await loadRecentLogs();

            showAlert('success', 'Atualizado', 'Dados do sistema atualizados com sucesso');
            setTimeout(hideAlert, 3000);
        } catch (error) {
            console.error('Erro ao recarregar dados:', error);
            logDebug('Erro ao recarregar dados', error.message);
            showAlert('error', 'Erro', `Falha ao recarregar dados: ${error.message}`);
        }
    }

    // Eventos
    startCameraBtn.addEventListener('click', startCamera);
    stopCameraBtn.addEventListener('click', stopCamera);
    reloadDataBtn.addEventListener('click', reloadData);
    refreshLogsBtn.addEventListener('click', loadRecentLogs);

    // Inicializar
    loadModels();

    // Configurar atualização automática a cada 30 segundos
    setInterval(loadRecentLogs, 30000);
});
</script>
@endpush

@endsection