import './bootstrap';
import './face-recognition.js';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Código de inicialização, se necessário
document.addEventListener('DOMContentLoaded', function() {
    // Inicialização global do aplicativo
    console.log('Aplicativo inicializado');
});