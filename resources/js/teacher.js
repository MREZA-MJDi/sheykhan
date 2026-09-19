import { bootPanel } from './panel-base.js';

document.addEventListener('DOMContentLoaded', () => {
    bootPanel();

    const dashboard = document.querySelector('[data-teacher-dashboard]');
    if (!dashboard) return;

    const buttons = dashboard.querySelectorAll('[data-teacher-range]');
    const bars = dashboard.querySelectorAll('[data-teacher-bars] .teacher-bar');

    let datasets = { week: [], month: [] };

    try {
        datasets = JSON.parse(dashboard.dataset.chart || '{}');
    } catch {
        datasets = { week: [], month: [] };
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            buttons.forEach((item) => item.classList.remove('active'));
            button.classList.add('active');

            const values = datasets[button.dataset.teacherRange] || [];
            bars.forEach((bar, index) => {
                bar.style.height = String(values[index] || 0) + '%';
            });
        });
    });
});
