export function bootPanel() {
    const openButton = document.querySelector('[data-role-menu-open]');

    openButton?.addEventListener('click', () => {
        document.body.classList.toggle('role-menu-open');
        openButton.setAttribute(
            'aria-expanded',
            document.body.classList.contains('role-menu-open') ? 'true' : 'false'
        );
    });

    document.addEventListener('click', (event) => {
        if (!document.body.classList.contains('role-menu-open')) return;

        const sidebar = document.querySelector('.role-sidebar');

        if (!sidebar || sidebar.contains(event.target) || openButton?.contains(event.target)) {
            return;
        }

        document.body.classList.remove('role-menu-open');
        openButton?.setAttribute('aria-expanded', 'false');
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1050) {
            document.body.classList.remove('role-menu-open');
            openButton?.setAttribute('aria-expanded', 'false');
        }
    });
}
