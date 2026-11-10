/* Role-separated member directory tabs. Each directory keeps its own filters and pagination state. */
export function bootOwnerPeopleDirectoryTabs(root = document) {
    root.querySelectorAll('[data-owner-people-directory-tabs]').forEach((tabs) => {
        if (tabs.dataset.ownerDirectoryTabsBound === 'true') return;

        const buttons = Array.from(tabs.querySelectorAll('[data-owner-directory-tab]'));
        const panels = Array.from(tabs.querySelectorAll('[data-owner-directory-panel]'));
        if (!buttons.length || !panels.length) return;

        const buttonFor = (key) => buttons.find((button) => button.dataset.ownerDirectoryTab === key);
        const panelFor = (key) => panels.find((panel) => panel.dataset.ownerDirectoryPanel === key);

        const activate = (key, moveFocus = false) => {
            const selectedButton = buttonFor(key);
            if (!selectedButton || !panelFor(key)) return;

            buttons.forEach((button) => {
                const active = button === selectedButton;
                button.setAttribute('aria-selected', active ? 'true' : 'false');
                button.tabIndex = active ? 0 : -1;
                button.classList.toggle('is-active', active);
            });

            panels.forEach((panel) => {
                const active = panel.dataset.ownerDirectoryPanel === key;
                panel.hidden = !active;
                panel.classList.toggle('is-active', active);
            });

            if (moveFocus) selectedButton.focus();
        };

        buttons.forEach((button, index) => {
            button.addEventListener('click', () => activate(button.dataset.ownerDirectoryTab));
            button.addEventListener('keydown', (event) => {
                if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
                event.preventDefault();

                const nextIndex = event.key === 'Home' ? 0
                    : event.key === 'End' ? buttons.length - 1
                    : (index + (event.key === 'ArrowRight' ? 1 : -1) + buttons.length) % buttons.length;

                activate(buttons[nextIndex].dataset.ownerDirectoryTab, true);
            });
        });

        const requested = tabs.dataset.initialDirectoryTab;
        activate(buttonFor(requested) ? requested : buttons[0].dataset.ownerDirectoryTab);
        tabs.dataset.ownerDirectoryTabsBound = 'true';
    });
}
