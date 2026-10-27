/* Real-data owner dashboard shell. No demo records, mock routes, or placeholder actions. */
(() => {
  'use strict';
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));
  const sidebar = $('#side');
  const burger = $('#burger');
  const bell = $('#bell');
  const pop = $('#pop');
  const dialog = $('#pal');
  const searchButton = $('#srch');
  const searchInput = $('#pq');
  const resultList = $('#pl');

  const closeSidebar = () => {
    sidebar?.classList.remove('open');
    burger?.setAttribute('aria-expanded', 'false');
  };
  burger?.addEventListener('click', () => {
    const isOpen = sidebar?.classList.toggle('open') ?? false;
    burger.setAttribute('aria-expanded', String(isOpen));
  });
  document.addEventListener('click', (event) => {
    if (sidebar?.classList.contains('open') && !sidebar.contains(event.target) && !burger?.contains(event.target)) closeSidebar();
    if (pop && !pop.hidden && !pop.contains(event.target) && !bell?.contains(event.target)) {
      pop.hidden = true;
      bell?.setAttribute('aria-expanded', 'false');
    }
  });

  $$('[data-group-toggle]').forEach((button) => {
    const group = button.closest('.nav-group');
    const content = $('.nav-group-items', group);
    const icon = $('em', button);
    button.addEventListener('click', () => {
      const expanded = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', String(!expanded));
      if (content) content.hidden = expanded;
      if (icon) icon.textContent = expanded ? '+' : '−';
    });
  });

  bell?.addEventListener('click', () => {
    const show = pop?.hidden ?? true;
    if (pop) pop.hidden = !show;
    bell.setAttribute('aria-expanded', String(show));
  });

  const sourceLinks = $$('#nv a.ni').map((link) => ({
    href: link.href,
    label: (link.dataset.navLabel || $('span', link)?.textContent || link.textContent || '').trim(),
    icon: $('svg use', link)?.getAttribute('href') || '#i-arr',
    current: link.getAttribute('aria-current') === 'page',
  }));
  const renderResults = (term = '') => {
    if (!resultList) return;
    const normalized = term.trim().toLocaleLowerCase('fa');
    const matches = sourceLinks.filter((item) => item.label.toLocaleLowerCase('fa').includes(normalized));
    resultList.replaceChildren();
    if (!matches.length) {
      const empty = document.createElement('li');
      empty.className = 'em';
      empty.textContent = 'صفحه‌ای با این عنوان پیدا نشد.';
      resultList.append(empty);
      return;
    }
    matches.slice(0, 18).forEach((item, index) => {
      const li = document.createElement('li');
      const link = document.createElement('a');
      const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
      icon.setAttribute('class', 'ic');
      use.setAttribute('href', item.icon);
      icon.append(use);
      link.href = item.href;
      link.classList.toggle('on', index === 0);
      link.append(icon, document.createTextNode(item.label));
      if (item.current) {
        const current = document.createElement('small');
        current.textContent = 'صفحه فعلی';
        link.append(current);
      }
      li.append(link);
      resultList.append(li);
    });
  };
  const openPalette = () => {
    if (!dialog) return;
    renderResults('');
    if (!dialog.open) dialog.showModal();
    if (searchInput) {
      searchInput.value = '';
      searchInput.focus();
    }
  };
  searchButton?.addEventListener('click', openPalette);
  searchInput?.addEventListener('input', () => renderResults(searchInput.value));
  resultList?.addEventListener('click', (event) => {
    if (event.target.closest('a')) dialog?.close();
  });

  document.addEventListener('keydown', (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      event.preventDefault();
      openPalette();
    }
    if (event.key === 'Escape') {
      if (pop && !pop.hidden) {
        pop.hidden = true;
        bell?.setAttribute('aria-expanded', 'false');
      }
      closeSidebar();
    }
    if (!dialog?.open || !['ArrowDown', 'ArrowUp'].includes(event.key)) return;
    const links = $$('#pl a');
    if (!links.length) return;
    event.preventDefault();
    const current = links.findIndex((link) => link.classList.contains('on'));
    const next = event.key === 'ArrowDown' ? (current + 1) % links.length : (current <= 0 ? links.length - 1 : current - 1);
    links.forEach((link, index) => {
      link.classList.toggle('on', index === next);
      if (index === next) link.scrollIntoView({ block: 'nearest' });
    });
  });
  searchInput?.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      const active = $('#pl a.on');
      if (active) {
        event.preventDefault();
        active.click();
      }
    }
  });

  const target = Number($('#timer-data')?.dataset.target || 0);
  const timerText = $('#rt');
  const ring = $('#rg');
  const circumference = 2 * Math.PI * 48;
  const formatDigits = (value) => String(value).padStart(2, '0').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
  let timerId = null;
  const updateTimer = () => {
    if (!target || !timerText || !ring) return;
    const remaining = Math.max(0, target - Math.floor(Date.now() / 1000));
    const hours = Math.floor(remaining / 3600);
    const minutes = Math.floor((remaining % 3600) / 60);
    const seconds = remaining % 60;
    timerText.textContent = [hours, minutes, seconds].map(formatDigits).join(':');
    ring.style.strokeDashoffset = String(circumference * (1 - Math.min(1, remaining / 86400)));
    if (remaining === 0) {
      const label = $('.tm small');
      if (label) label.textContent = 'زمان شروع کلاس رسیده است';
      window.clearInterval(timerId);
    }
  };
  if (target > 0) {
    updateTimer();
    timerId = window.setInterval(updateTimer, 1000);
  }

  const main = $('#main');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (main && !reduceMotion) requestAnimationFrame(() => main.classList.add('in'));
  $$('.admin-dashboard .fill').forEach((bar) => {
    const targetWidth = bar.style.width;
    if (reduceMotion) return;
    bar.style.width = '0';
    requestAnimationFrame(() => { bar.style.width = targetWidth; });
  });
})();
