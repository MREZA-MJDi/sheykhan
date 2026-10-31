/**
 * Framework- and theme-neutral behavior primitives.
 *
 * This module owns interaction contracts only (keyboard, focus, confirmation,
 * dismissal and duplicate-submit protection). It intentionally imports no CSS
 * and never assigns visual styles or panel theme classes.
 */

const boundRoots = new WeakSet();
const boundForms = new WeakSet();
let runtimeBound = false;
let runtimeScheduled = false;

const asElement = (target) => target instanceof Element ? target : target?.parentElement;

export function initializeTabs(root, options = {}) {
    if (!root || boundRoots.has(root)) return false;

    const triggerSelector = options.triggerSelector || '[data-tab-trigger]';
    const panelSelector = options.panelSelector || '[data-tab-panel]';
    const triggers = Array.from(root.querySelectorAll(triggerSelector));
    const panels = Array.from(root.querySelectorAll(panelSelector));
    if (!triggers.length || !panels.length) return false;

    const triggerKey = options.triggerKey || ((element) => element.dataset.tabTrigger || '');
    const panelKey = options.panelKey || ((element) => element.dataset.tabPanel || '');
    const activeClass = options.activeClass || '';
    const requested = options.initialKey ?? root.dataset.initialTab;
    const available = new Set(triggers.map(triggerKey));
    const initialKey = available.has(String(requested || ''))
        ? String(requested)
        : triggerKey(triggers[0]);

    const activate = (key, moveFocus = false) => {
        const activeTrigger = triggers.find((trigger) => triggerKey(trigger) === key);
        if (!activeTrigger) return;

        triggers.forEach((trigger) => {
            const active = trigger === activeTrigger;
            trigger.setAttribute('role', 'tab');
            trigger.setAttribute('aria-selected', active ? 'true' : 'false');
            trigger.tabIndex = active ? 0 : -1;
            if (activeClass) trigger.classList.toggle(activeClass, active);
        });

        panels.forEach((panel) => {
            const active = panelKey(panel) === key;
            panel.hidden = !active;
            panel.setAttribute('role', 'tabpanel');
            if (activeClass) panel.classList.toggle(activeClass, active);
        });

        if (moveFocus) activeTrigger.focus();
    };

    triggers.forEach((trigger, index) => {
        trigger.addEventListener('click', () => activate(triggerKey(trigger)));
        trigger.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();

            const nextIndex = event.key === 'Home'
                ? 0
                : event.key === 'End'
                    ? triggers.length - 1
                    : (index + (event.key === 'ArrowRight' ? 1 : -1) + triggers.length) % triggers.length;

            activate(triggerKey(triggers[nextIndex]), true);
        });
    });

    activate(initialKey);
    boundRoots.add(root);
    return true;
}

export function guardFormOnce(form, options = {}) {
    if (!(form instanceof HTMLFormElement) || boundForms.has(form)) return false;
    if (form.matches('[data-upload-progress]')) return false;

    const pendingText = options.pendingText || '';
    const submitControls = () => Array.from(form.querySelectorAll(
        'button[type="submit"], button:not([type]), input[type="submit"]'
    ));

    const restore = () => {
        form.dataset.sharedSubmitLocked = 'false';
        form.removeAttribute('aria-busy');

        submitControls().forEach((control) => {
            if (control.dataset.sharedWasDisabled !== undefined) {
                control.disabled = control.dataset.sharedWasDisabled === 'true';
                delete control.dataset.sharedWasDisabled;
            }
            control.removeAttribute('aria-busy');

            if (control.dataset.sharedOriginalLabel !== undefined) {
                if (control instanceof HTMLInputElement) {
                    control.value = control.dataset.sharedOriginalLabel;
                } else {
                    control.textContent = control.dataset.sharedOriginalLabel;
                }
                delete control.dataset.sharedOriginalLabel;
            }
        });
    };

    form.addEventListener('submit', (event) => {
        if (form.dataset.sharedSubmitLocked === 'true') {
            event.preventDefault();
            return;
        }

        if (event.defaultPrevented || !form.checkValidity()) return;

        form.dataset.sharedSubmitLocked = 'true';
        form.setAttribute('aria-busy', 'true');

        submitControls().forEach((control) => {
            control.dataset.sharedWasDisabled = String(control.disabled);
            control.disabled = true;
            control.setAttribute('aria-busy', 'true');

            if (pendingText) {
                control.dataset.sharedOriginalLabel = control instanceof HTMLInputElement
                    ? control.value
                    : control.textContent;
                if (control instanceof HTMLInputElement) control.value = pendingText;
                else control.textContent = pendingText;
            }
        });
    });

    window.addEventListener('pageshow', restore);
    boundForms.add(form);
    return true;
}

function bootConfirmationBehavior() {
    document.addEventListener('click', (event) => {
        const target = asElement(event.target)?.closest('[data-confirm]');
        if (!target) return;

        const form = target instanceof HTMLButtonElement || target instanceof HTMLInputElement
            ? target.form
            : target.closest('form');

        if (form?.dataset.confirmSubmit === 'true') return;

        const message = target.dataset.confirm || 'از انجام این عملیات مطمئن هستید؟';
        if (!window.confirm(message)) {
            event.preventDefault();
            event.stopPropagation();
        }
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.confirmSubmit !== 'true') return;
        const message = form.dataset.confirm || 'از انجام این عملیات مطمئن هستید؟';
        if (!window.confirm(message)) event.preventDefault();
    }, true);
}

function bootDismissBehavior() {
    document.addEventListener('click', (event) => {
        const button = asElement(event.target)?.closest('[data-ui-dismiss], [data-ui-flash-close]');
        if (!button) return;

        const selector = button.dataset.uiDismissTarget;
        const target = selector
            ? document.querySelector(selector)
            : button.closest('[data-ui-dismissible], .ui-flash');

        if (!target) return;
        target.remove();
    });
}

function bootGenericTabs() {
    document.querySelectorAll('[data-behavior-tabs]').forEach((root) => {
        initializeTabs(root);
    });
}

function bootSubmitGuards() {
    document.querySelectorAll('form[data-submit-once]').forEach((form) => {
        guardFormOnce(form);
    });
}

function initialize() {
    runtimeScheduled = false;
    if (runtimeBound) return;
    runtimeBound = true;

    bootConfirmationBehavior();
    bootDismissBehavior();
    bootGenericTabs();
    bootSubmitGuards();
}

export function bootSharedBehaviors() {
    if (runtimeBound || runtimeScheduled) return;

    if (document.readyState === 'loading') {
        runtimeScheduled = true;
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
        return;
    }

    initialize();
}
