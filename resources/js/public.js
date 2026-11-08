import './shared/persian-money.js';
import './home.js';
import './public-site.js';

document.addEventListener('click', (event) => {
    const dismiss = event.target.closest('[data-ui-dismiss], [data-ui-flash-close]');

    if (!dismiss) return;

    dismiss.closest('[data-ui-dismissible]')?.remove();
});