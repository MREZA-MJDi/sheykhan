/* Parent panel JS entry. Imports shared calendar/number helpers only. */
import './persian-calendar.js';
import './persian-money.js';
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



const bootOwnerPeopleTabs = () => {
    document.querySelectorAll('[data-owner-people-tabs]').forEach((root) => {
        const buttons = Array.from(root.querySelectorAll('[data-owner-tab-button]'));
        const panels = Array.from(root.querySelectorAll('[data-owner-tab-panel]'));
        if (!buttons.length || !panels.length) return;

        const activate = (key, moveFocus = false) => {
            buttons.forEach((button) => {
                const active = button.dataset.ownerTabButton === key;
                button.setAttribute('aria-selected', active ? 'true' : 'false');
                button.tabIndex = active ? 0 : -1;
                button.classList.toggle('is-active', active);
            });
            panels.forEach((panel) => {
                const active = panel.dataset.ownerTabPanel === key;
                panel.hidden = !active;
                panel.classList.toggle('is-active', active);
            });
            if (moveFocus) buttons.find((button) => button.dataset.ownerTabButton === key)?.focus();
        };

        buttons.forEach((button, index) => {
            button.addEventListener('click', () => activate(button.dataset.ownerTabButton));
            button.addEventListener('keydown', (event) => {
                if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
                event.preventDefault();
                const nextIndex = event.key === 'Home' ? 0
                    : event.key === 'End' ? buttons.length - 1
                    : (index + (event.key === 'ArrowRight' ? 1 : -1) + buttons.length) % buttons.length;
                activate(buttons[nextIndex].dataset.ownerTabButton, true);
            });
        });

        const requested = root.dataset.initialTab;
        activate(buttons.some((button) => button.dataset.ownerTabButton === requested) ? requested : buttons[0].dataset.ownerTabButton);
    });
};

const bootRoleMobileNavigation = () => {
    document.querySelectorAll('[data-role-more-panel]').forEach((panel) => {
        const opener = document.querySelector('[data-role-more-open][aria-controls="' + panel.id + '"]');
        const dialog = panel.querySelector('.role-mobile-more-dialog');
        const closers = panel.querySelectorAll('[data-role-more-close]');
        if (!opener || !dialog) return;

        let returnFocusTo = null;
        const close = () => {
            if (panel.hidden) return;
            panel.hidden = true;
            opener.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('role-mobile-more-open');
            returnFocusTo?.focus({ preventScroll: true });
        };

        opener.addEventListener('click', () => {
            returnFocusTo = opener;
            panel.hidden = false;
            opener.setAttribute('aria-expanded', 'true');
            document.body.classList.add('role-mobile-more-open');
            dialog.focus({ preventScroll: true });
        });
        closers.forEach((button) => button.addEventListener('click', close));
        panel.addEventListener('click', (event) => {
            if (event.target === panel) close();
        });
        dialog.querySelectorAll('a[href]').forEach((link) => {
            link.addEventListener('click', () => {
                panel.hidden = true;
                opener.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('role-mobile-more-open');
            });
        });
        document.addEventListener('keydown', (event) => {
            if (panel.hidden) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                close();
                return;
            }
            if (event.key !== 'Tab') return;
            const focusable = Array.from(panel.querySelectorAll('a[href],button:not([disabled]):not(.role-mobile-more-backdrop),[tabindex="0"]'))
                .filter((element) => element.getClientRects().length > 0);
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    });
};

const bootShakaPlayerComponents = () => {
    if (!document.querySelector('[data-shaka-player-root]')) return;

    import('./parent-shaka-player.js')
        .then(({ bootShakaPlayers }) => bootShakaPlayers())
        .catch((error) => {
            console.error('[Sheykhan Shaka] Failed to load player module', error);
            document.querySelectorAll('[data-shaka-status]').forEach((status) => {
                status.textContent = 'پلیر بارگذاری نشد. منابع فرانت‌اند را بررسی و صفحه را دوباره بارگذاری کنید.';
                status.dataset.tone = 'error';
                status.hidden = false;
            });
        });
};

const bootSharedFoundation = () => {
    bootPanel();
    bootOwnerPeopleTabs();
    bootRoleMobileNavigation();
    bootShakaPlayerComponents();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootSharedFoundation, { once: true });
} else {
    bootSharedFoundation();
}


document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.classList.add('parent-ready');
});
