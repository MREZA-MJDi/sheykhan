/* Shared application behavior: calendars, Persian money, Alpine and responsive panel shell. */
import './persian-calendar.js';
import './persian-money.js';
import { bootPanel } from './panel-base.js';


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

    import('./shaka-player.js')
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
