import './shared/persian-calendar.js';
import './shared/persian-money.js';
import './owner-admin-common.js';
import './seo/blog-cover-preview.js';
import { bootPanelRuntime, closePanelMenu } from './panel-runtime.js';
export { closePanelMenu };

const bootSeoWorkspace = () => {
    const sidebar = document.querySelector('.seo-sidebar');
    const opener = document.querySelector('[data-seo-menu-toggle]');
    const backdrop = document.querySelector('[data-seo-menu-close]');
    if (!sidebar || !opener || opener.dataset.bound === 'true') return;
    opener.dataset.bound = 'true';

    const close = () => {
        sidebar.classList.remove('is-open');
        backdrop?.classList.remove('is-visible');
        opener.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('seo-menu-open');
    };

    opener.addEventListener('click', () => {
        const open = !sidebar.classList.contains('is-open');
        sidebar.classList.toggle('is-open', open);
        backdrop?.classList.toggle('is-visible', open);
        opener.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('seo-menu-open', open);
    });
    backdrop?.addEventListener('click', close);
    sidebar.querySelectorAll('a[href]').forEach((link) => link.addEventListener('click', close));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
            close();
            opener.focus({ preventScroll: true });
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootSeoWorkspace, { once: true });
} else {
    bootSeoWorkspace();
}
bootPanelRuntime();
