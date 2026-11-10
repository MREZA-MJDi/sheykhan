/* Accessible, reusable tabs for Owner workspaces, including nested sheets. */
export function bootOwnerPeopleTabs(root = document) {
    root.querySelectorAll('[data-owner-people-tabs]').forEach((tabs) => {
        if (tabs.dataset.ownerPeopleTabsBound === 'true') return;

        // A parent workspace must not capture controls belonging to a nested workspace.
        const owned = (selector) => Array.from(tabs.querySelectorAll(selector))
            .filter((element) => element.closest('[data-owner-people-tabs]') === tabs);
        const buttons = owned('[data-owner-tab-button]');
        const panels = owned('[data-owner-tab-panel]');
        if (!buttons.length || !panels.length) return;

        const queryParam = tabs.dataset.queryParam || null;
        const activate = (key, moveFocus = false, updateUrl = true) => {
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

            if (queryParam && updateUrl) {
                const url = new URL(window.location.href);
                url.searchParams.set(queryParam, key);
                window.history.replaceState(window.history.state, '', url);
            }
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

        const params = new URLSearchParams(window.location.search);
        const requested = params.get(queryParam || '') || tabs.dataset.initialTab;
        const key = buttons.some((button) => button.dataset.ownerTabButton === requested)
            ? requested
            : buttons[0].dataset.ownerTabButton;
        activate(key, false, false);
        tabs.dataset.ownerPeopleTabsBound = 'true';
    });
}

const initialize = () => bootOwnerPeopleTabs();
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true });
} else {
    initialize();
}
