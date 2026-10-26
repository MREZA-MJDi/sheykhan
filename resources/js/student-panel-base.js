import Alpine from 'alpinejs';

const ALPINE_BOOT_FLAG = '__sheykhanAlpineBooted';

function bootAlpine() {
    if (window[ALPINE_BOOT_FLAG]) return;

    const alpine = window.Alpine || Alpine;
    window.Alpine = alpine;

    if (!alpine || typeof alpine.start !== 'function') return;

    alpine.start();
    window[ALPINE_BOOT_FLAG] = true;
}

function bootRoleAvatarFallback() {
    // This shared recovery behavior belongs only to Student and Teacher panels.
    if (!document.body.matches('.student-shell, .teacher-shell')) return;

    document.querySelectorAll('img[data-avatar-image]').forEach((image) => {
        if (image.dataset.roleAvatarFallbackBound === 'true') return;

        const fallback = image.parentElement?.querySelector('[data-avatar-fallback]');
        if (!fallback) return;

        image.dataset.roleAvatarFallbackBound = 'true';
        const showFallback = () => {
            image.hidden = true;
            fallback.hidden = false;
        };

        image.addEventListener('error', showFallback, { once: true });
        if (image.complete && image.naturalWidth === 0) showFallback();
    });
}

function bootProfileAvatarFallback() {
    document.querySelectorAll('img[data-profile-avatar]').forEach((image) => {
        if (image.dataset.avatarFallbackBound === 'true') return;

        image.dataset.avatarFallbackBound = 'true';
        const fallbackUrl = image.dataset.fallbackSrc || '';

        const recover = () => {
            const currentSrc = image.getAttribute('src') || '';
            if (fallbackUrl && currentSrc !== fallbackUrl && image.dataset.avatarFallbackAttempted !== 'true') {
                image.dataset.avatarFallbackAttempted = 'true';
                image.src = fallbackUrl;
                return;
            }

            image.hidden = true;
        };

        image.addEventListener('error', recover);
        if (image.complete && image.naturalWidth === 0) recover();
    });
}

export function bootPanel() {
    bootRoleAvatarFallback();
    bootProfileAvatarFallback();
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

        if (sidebar.contains(event.target) || openButton.contains(event.target)) {
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