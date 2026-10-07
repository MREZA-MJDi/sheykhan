import Alpine from 'alpinejs';

let alpineBooted = false;

function bootAlpine() {
    if (alpineBooted) return;

    window.Alpine = window.Alpine || Alpine;

    if (!window.Alpine?.start) return;

    /*
     * Start Alpine exactly once per document.
     * Public and panel layouts can each load their own entrypoint,
     * so the guard prevents duplicate initialization errors.
     */
    if (!window.Alpine.started) {
        window.Alpine.start();
    }

    alpineBooted = true;
}

export function bootPanel() {
    bootAlpine();

    const openButton = document.querySelector('[data-role-menu-open]');
    const sidebar = document.querySelector('.role-sidebar');

    if (!openButton || !sidebar) return;

    const setMenuState = (open) => {
        document.body.classList.toggle('role-menu-open', open);
        openButton.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    openButton.addEventListener('click', () => {
        setMenuState(!document.body.classList.contains('role-menu-open'));
    });

    document.addEventListener('click', (event) => {
        if (!document.body.classList.contains('role-menu-open')) return;

        if (
            sidebar.contains(event.target) ||
            openButton.contains(event.target)
        ) {
            return;
        }

        setMenuState(false);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenuState(false);
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1050) {
            setMenuState(false);
        }
    });
}

export function closePanelMenu() {
    document.body.classList.remove('role-menu-open');

    document
        .querySelector('[data-role-menu-open]')
        ?.setAttribute('aria-expanded', 'false');
}