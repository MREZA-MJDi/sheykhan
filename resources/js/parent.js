import { bootPanel } from './panel-base.js';

document.addEventListener('DOMContentLoaded', () => {
    bootPanel();
    document.documentElement.classList.add('parent-ready');
});
