/* Owner people-page tab behavior. No presentation rules are defined here. */
export function bootOwnerPeopleTabs(root = document) {
    root.querySelectorAll('[data-owner-people-tabs]').forEach((tabs) => {
        if (tabs.dataset.ownerPeopleTabsBound === 'true') return;
        const buttons = Array.from(tabs.querySelectorAll('[data-owner-tab-button]'));
        const panels = Array.from(tabs.querySelectorAll('[data-owner-tab-panel]'));
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

        const requested = tabs.dataset.initialTab;
        activate(buttons.some((button) => button.dataset.ownerTabButton === requested)
            ? requested
            : buttons[0].dataset.ownerTabButton);
        tabs.dataset.ownerPeopleTabsBound = 'true';
    });
}

const initialize = () => bootOwnerPeopleTabs();
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true });
} else {
    initialize();
}
