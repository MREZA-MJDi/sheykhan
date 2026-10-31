(() => {
  'use strict';
  const root = document.documentElement;
  const side = document.getElementById('side');
  const overlay = document.getElementById('teacher-overlay');
  const toast = (message) => {
    let node = document.getElementById('teacher-toast');
    if (!node) { node = document.createElement('div'); node.id = 'teacher-toast'; node.className = 'tg'; node.setAttribute('role', 'status'); node.setAttribute('aria-live', 'polite'); document.body.appendChild(node); }
    node.textContent = message; node.hidden = false;
    window.clearTimeout(node._hideTimer);
    node._hideTimer = window.setTimeout(() => { node.hidden = true; }, 2600);
  };
  const menu = (open) => {
    side?.classList.toggle('open', Boolean(open));
    overlay?.classList.toggle('open', Boolean(open));
    overlay?.setAttribute('aria-hidden', String(!open));
    document.body.classList.toggle('teacher-menu-open', Boolean(open));
  };
  document.querySelectorAll('[data-teacher-menu-open]').forEach((button) => button.addEventListener('click', () => menu(true)));
  document.querySelectorAll('[data-teacher-menu-close]').forEach((button) => button.addEventListener('click', () => menu(false)));
  overlay?.addEventListener('click', () => menu(false));
  document.addEventListener('keydown', (event) => { if (event.key === 'Escape') menu(false); });

  const themeButton = document.querySelector('[data-teacher-theme]');
  const setTheme = (theme) => {
    const value = theme === 'dark' ? 'dark' : 'light';
    root.dataset.theme = value;
    try { localStorage.setItem('sheykhan.teacher.theme', value); } catch (_) {}
    if (themeButton) {
      themeButton.setAttribute('aria-pressed', String(value === 'dark'));
      themeButton.setAttribute('aria-label', value === 'dark' ? 'فعال‌کردن تم روشن' : 'فعال‌کردن تم تیره');
      themeButton.title = value === 'dark' ? 'تم روشن' : 'تم تیره';
    }
  };
  try { setTheme(localStorage.getItem('sheykhan.teacher.theme') || root.dataset.theme || 'light'); }
  catch (_) { setTheme('light'); }
  themeButton?.addEventListener('click', () => setTheme(root.dataset.theme === 'dark' ? 'light' : 'dark'));

  document.addEventListener('click', (event) => {
    const control = event.target.closest('[data-confirm]');
    if (control && !window.confirm(control.dataset.confirm || 'از انجام این عملیات مطمئن هستید؟')) {
      event.preventDefault(); event.stopPropagation();
    }
  }, true);

  document.querySelectorAll('[data-teacher-search]').forEach((input) => {
    const scope = document.querySelector(input.dataset.teacherSearchScope || '[data-teacher-searchable-scope]') || document.querySelector('main');
    if (!scope) return;
    input.addEventListener('input', () => {
      const query = input.value.trim().toLocaleLowerCase('fa').replace(/[يى]/g, 'ی').replace(/ك/g, 'ک');
      let count = 0;
      scope.querySelectorAll('[data-searchable], tbody tr, .teacher-workspace-item').forEach((item) => {
        const match = !query || item.textContent.toLocaleLowerCase('fa').replace(/[يى]/g, 'ی').replace(/ك/g, 'ک').includes(query);
        item.hidden = !match; if (match) count++;
      });
      const empty = scope.querySelector('[data-search-empty]');
      if (empty) empty.hidden = !query || count > 0;
    });
  });

  document.querySelectorAll('[data-teacher-preview]').forEach((input) => {
    input.addEventListener('change', () => {
      const output = document.querySelector(input.dataset.teacherPreview);
      if (!output) return;
      output.replaceChildren();
      [...(input.files || [])].forEach((file) => {
        const row = document.createElement('span'); row.className = 'chip';
        row.textContent = file.name + ' · ' + Math.max(1, Math.ceil(file.size / 1024)) + ' KB';
        output.appendChild(row);
      });
    });
  });

  document.querySelectorAll('[data-teacher-image-preview]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files?.[0]; if (!file) return;
      if (!file.type.startsWith('image/')) { toast('فقط فایل تصویری مجاز است.'); input.value = ''; return; }
      const max = Number(input.dataset.maxBytes || 5 * 1024 * 1024);
      if (file.size > max) { toast('حجم تصویر از حد مجاز بیشتر است.'); input.value = ''; return; }
      const target = document.querySelector(input.dataset.teacherImagePreview);
      if (target) { const url = URL.createObjectURL(file); target.src = url; target.onload = () => URL.revokeObjectURL(url); }
    });
  });

  document.querySelectorAll('[data-chart]').forEach((chart) => {
    let data = {}; try { data = JSON.parse(chart.dataset.chart || '{}'); } catch (_) {}
    const bars = chart.querySelector('[data-chart-bars]');
    const empty = chart.querySelector('[data-chart-empty]');
    const buttons = [...chart.querySelectorAll('[data-chart-range]')];
    const render = (range) => {
      const current = data[range] || {labels:[],values:[]}; const labels = current.labels || []; const values = current.values || [];
      bars?.replaceChildren();
      const hasValues = values.some((v) => Number(v) > 0);
      if (empty) empty.hidden = hasValues;
      if (!bars || !hasValues) return;
      values.slice(0, 14).forEach((value, index) => {
        const val = Math.max(0, Math.min(100, Number(value) || 0));
        const column = document.createElement('div'); const number = document.createElement('b');
        const fill = document.createElement('i'); const label = document.createElement('small');
        number.textContent = String(Math.round(val)).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
        fill.style.height = val + '%'; label.textContent = String(labels[index] ?? '');
        column.append(number, fill, label); bars.appendChild(column);
      });
    };
    buttons.forEach((button) => button.addEventListener('click', () => {
      buttons.forEach((item) => { const active = item === button; item.classList.toggle('on', active); item.setAttribute('aria-pressed', String(active)); });
      render(button.dataset.chartRange || 'week');
    }));
    render('week');
  });

  document.querySelectorAll('[data-tab-target]').forEach((button) => button.addEventListener('click', () => {
    const group = button.closest('[data-tab-group]') || document;
    group.querySelectorAll('[data-tab-target]').forEach((item) => { const active = item === button; item.classList.toggle('on', active); item.setAttribute('aria-selected', String(active)); });
    group.querySelectorAll('[data-tab-panel]').forEach((panel) => { panel.hidden = panel.dataset.tabPanel !== button.dataset.tabTarget; });
  }));

  document.querySelectorAll('form[data-disable-on-submit]').forEach((form) => form.addEventListener('submit', () => {
    if (!form.reportValidity()) return;
    const button = form.querySelector('[type="submit"]'); if (button) { button.disabled = true; button.dataset.originalText = button.textContent; button.textContent = 'در حال ثبت…'; }
  }));
  window.teacherToast = toast;
})();