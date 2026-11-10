/* One-time credentials: copy only on explicit user action. */
document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-value]');
    if (!button) return;
    const value = button.getAttribute('data-copy-value') || '';
    if (!value) return;
    const original = button.textContent;
    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(value);
        } else {
            const field = document.createElement('textarea');
            field.value = value;
            field.setAttribute('readonly', '');
            field.style.position = 'fixed';
            field.style.opacity = '0';
            document.body.append(field);
            field.select();
            const copied = document.execCommand('copy');
            field.remove();
            if (!copied) throw new Error('copy-failed');
        }
        button.textContent = 'کپی شد';
    } catch {
        button.textContent = 'کپی خودکار در دسترس نیست';
    }
    window.setTimeout(() => { button.textContent = original; }, 1800);
});
