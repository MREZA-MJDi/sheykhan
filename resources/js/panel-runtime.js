import { bootPanel, closePanelMenu } from './panel-base.js';
import { bootFileUploadProgress } from './shared/upload-progress.js';
import { bootSharedBehaviors, initializeTabs } from './shared/behavior-runtime.js';

export { closePanelMenu };

const bootOwnerPeopleTabs = () => {
    document.querySelectorAll('[data-owner-people-tabs]').forEach((root) => {
        initializeTabs(root, {
            triggerSelector: '[data-owner-tab-button]',
            panelSelector: '[data-owner-tab-panel]',
            triggerKey: (button) => button.dataset.ownerTabButton || '',
            panelKey: (panel) => panel.dataset.ownerTabPanel || '',
            initialKey: root.dataset.initialTab,
            activeClass: 'is-active',
        });
    });
};

const bootRoleMobileNavigation = () => {
    document.querySelectorAll('[data-role-more-panel]').forEach((panel) => {
        const opener = document.querySelector('[data-role-more-open][aria-controls="' + panel.id + '"]');
        const dialog = panel.querySelector('.role-mobile-more-dialog');
        const closers = panel.querySelectorAll('[data-role-more-close]');
        if (!opener || !dialog || panel.dataset.roleMoreBound === 'true') return;

        panel.dataset.roleMoreBound = 'true';
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
            const focusable = Array.from(panel.querySelectorAll(
                'a[href],button:not([disabled]):not(.role-mobile-more-backdrop),[tabindex="0"]'
            )).filter((element) => element.getClientRects().length > 0);
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

let runtimeBooted = false;
let runtimeScheduled = false;

function initializePanelRuntime() {
    runtimeScheduled = false;
    if (runtimeBooted) return;
    runtimeBooted = true;

    bootSharedBehaviors();
    bootPanel();
    bootOwnerPeopleTabs();
    bootRoleMobileNavigation();
    bootFileUploadProgress();
}

export function bootPanelRuntime() {
    if (runtimeBooted || runtimeScheduled) return;

    if (document.readyState === 'loading') {
        runtimeScheduled = true;
        document.addEventListener('DOMContentLoaded', initializePanelRuntime, { once: true });
        return;
    }

    initializePanelRuntime();
}
