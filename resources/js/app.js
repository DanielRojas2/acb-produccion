import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

import { inicializarEscenario } from './escenario-echo';

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarEscenario);
} else {
    inicializarEscenario();
}

Alpine.start();