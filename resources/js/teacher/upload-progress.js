// Upload progress for Teacher forms only. Server validation/access remains authoritative.
export function initTeacherUploadProgress() {
  document.querySelectorAll('form[data-upload-progress]').forEach((form) => {
    if (form.dataset.tpUploadProgressBound === '1') return;
    form.dataset.tpUploadProgressBound = '1';
    const panel = form.querySelector('[data-upload-progress-ui]');
    if (!panel) return;
    const statusPanel = panel.querySelector('[data-upload-progress-status]');
    const list = panel.querySelector('[data-upload-file-list]');
    const summary = panel.querySelector('[data-upload-summary]');
    const track = panel.querySelector('[data-upload-progress-track]');
    const bar = panel.querySelector('[data-upload-progress-bar]');
    const percent = panel.querySelector('[data-upload-progress-percent]');
    const message = panel.querySelector('[data-upload-progress-message]');
    const error = panel.querySelector('[data-upload-progress-error]');
    const submitters = Array.from(form.querySelectorAll('button[type="submit"],input[type="submit"]'));

    const paint = (value, copy) => {
      const bounded = Math.max(0, Math.min(100, Number(value) || 0));
      if (bar) bar.style.width = `${bounded}%`;
      if (track) track.setAttribute('aria-valuenow', String(Math.round(bounded)));
      if (percent) percent.textContent = `${Math.round(bounded).toLocaleString('fa-IR')}٪`;
      if (message) message.textContent = copy || '';
      if (statusPanel) statusPanel.hidden = false;
    };

    form.addEventListener('submit', (event) => {
      const files = Array.from(form.querySelectorAll('input[type="file"]'))
        .flatMap((input) => Array.from(input.files || []));
      if (!files.length) return;
      if (form.dataset.tpUploading === '1') {
        event.preventDefault();
        return;
      }
      event.preventDefault();
      form.dataset.tpUploading = '1';
      form.setAttribute('aria-busy', 'true');
      panel.hidden = false;
      if (error) { error.hidden = true; error.textContent = ''; }
      if (list) {
        list.replaceChildren(...files.map((file) => {
          const item = document.createElement('div');
          item.textContent = `${file.name} · ${Math.max(1, Math.ceil(file.size / 1024)).toLocaleString('fa-IR')} KB`;
          return item;
        }));
      }
      if (summary) summary.textContent = `${files.length.toLocaleString('fa-IR')} فایل برای ارسال`;
      submitters.forEach((button) => {
        button.disabled = true;
        if (button.tagName === 'BUTTON') {
          button.dataset.tpOriginalText = button.textContent;
          button.textContent = 'در حال ارسال…';
        }
      });
      paint(0, 'در حال ارسال فایل‌ها…');

      const xhr = new XMLHttpRequest();
      xhr.open((form.method || 'POST').toUpperCase(), form.action, true);
      xhr.withCredentials = true;
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.setRequestHeader('Accept', 'text/html,application/xhtml+xml');
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
      if (csrf) xhr.setRequestHeader('X-CSRF-TOKEN', csrf);

      const restore = () => {
        form.dataset.tpUploading = '0';
        form.removeAttribute('aria-busy');
        submitters.forEach((button) => {
          button.disabled = false;
          if (button.tagName === 'BUTTON' && button.dataset.tpOriginalText) {
            button.textContent = button.dataset.tpOriginalText;
            delete button.dataset.tpOriginalText;
          }
        });
      };
      const fail = (copy) => {
        restore();
        if (error) { error.textContent = copy; error.hidden = false; }
        paint(0, 'ارسال انجام نشد');
      };

      xhr.upload.addEventListener('progress', (progressEvent) => {
        if (!progressEvent.lengthComputable || progressEvent.total <= 0) return;
        const value = progressEvent.loaded / progressEvent.total * 100;
        paint(value, value >= 100 ? 'فایل ارسال شد؛ سرور در حال پردازش است…' : 'در حال ارسال فایل‌ها…');
      });
      xhr.addEventListener('load', () => {
        if (xhr.status >= 200 && xhr.status < 400) {
          paint(100, 'ارسال کامل شد؛ صفحه در حال به‌روزرسانی است…');
          window.location.assign(xhr.responseURL || window.location.href);
          return;
        }
        fail(xhr.status === 419 ? 'نشست منقضی شده است؛ صفحه را تازه‌سازی کن.'
          : xhr.status === 413 ? 'حجم فایل از محدودیت سرور بیشتر است.'
          : xhr.status === 422 ? 'فرمت یا اطلاعات فایل معتبر نیست؛ خطاهای فرم را بررسی کن.'
          : 'ارسال فایل انجام نشد؛ دوباره تلاش کن.');
      });
      xhr.addEventListener('error', () => fail('ارتباط با سرور قطع شد؛ فایل‌ها ثبت نشده‌اند.'));
      xhr.addEventListener('abort', () => fail('ارسال فایل لغو شد.'));
      xhr.send(new FormData(form));
    });
  });
}