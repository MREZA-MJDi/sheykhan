// File picker feedback; the server remains authoritative for MIME, size and access rules.
const sizeLabel = (bytes) => `${Math.ceil(bytes / 1024).toLocaleString('fa-IR')} KB`;

export function initFiles() {
  document.addEventListener('change', (event) => {
    const input = event.target.closest('[data-tp-files]');
    if (!input) return;
    const root = input.closest('[data-tp-dropzone]') || input.closest('.tp-drop')?.parentElement || input.parentElement;
    const list = root?.querySelector('[data-tp-files-list]');
    if (!list) return;
    list.replaceChildren(...Array.from(input.files || []).map((file) => {
      const chip = document.createElement('span');
      chip.className = 'tp-chip';
      chip.textContent = `📎 ${file.name} · ${sizeLabel(file.size)}`;
      return chip;
    }));
  });
}