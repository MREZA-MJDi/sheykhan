import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.classList.add('panel-ready');
});

Alpine.start();
