/* Shared Owner/SEO shell behavior. Page-specific features stay in their own modules. */
(() => {
    'use strict';

    const root = document.body;
    if (!root || root.dataset.ownerAdminShellBooted === 'true') return;
    root.dataset.ownerAdminShellBooted = 'true';

    const $ = (selector, scope = document) => scope.querySelector(selector);
    const $$ = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

    const sidebar = $('#side');
    const burger = $('#burger');

    burger?.addEventListener('click', () => {
        const open = sidebar?.classList.toggle('open') ?? false;
        burger.setAttribute('aria-expanded', String(open));
    });

    document.addEventListener('click', (event) => {
        if (!sidebar || !burger || !sidebar.classList.contains('open')) return;
        if (sidebar.contains(event.target) || burger.contains(event.target)) return;
        sidebar.classList.remove('open');
        burger.setAttribute('aria-expanded', 'false');
    });

    $$('[data-group-toggle]').forEach((button) => {
        const group = button.closest('.nav-group');
        const controlledId = button.getAttribute('aria-controls');
        const content = (controlledId && document.getElementById(controlledId)) || $('.nav-group-items', group);
        const state = $('.gh em', button);

        button.addEventListener('click', () => {
            const expanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', String(!expanded));
            if (content) content.hidden = expanded;
            if (state) state.textContent = expanded ? '+' : '−';
        });
    });

    const bell = $('#bell');
    const pop = $('#pop');
    bell?.addEventListener('click', () => {
        const isHidden = pop?.hidden ?? true;
        if (pop) pop.hidden = !isHidden;
        bell.setAttribute('aria-expanded', String(isHidden));
    });

    document.addEventListener('click', (event) => {
        if (!pop || pop.hidden || bell?.contains(event.target) || pop.contains(event.target)) return;
        pop.hidden = true;
        bell?.setAttribute('aria-expanded', 'false');
    });

    const dialog = $('#pal');
    const searchButton = $('#srch');
    const searchInput = $('#pq');
    const resultList = $('#pl');
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
            if (sidebar?.classList.contains('open')) {
                sidebar.classList.remove('open');
                burger?.setAttribute('aria-expanded', 'false');
                burger?.focus({ preventScroll: true });
            }
        }

        if (!dialog?.open || !['ArrowDown', 'ArrowUp'].includes(event.key)) return;
        const links = $$('#pl a');
        if (!links.length) return;
        event.preventDefault();
        const activeIndex = links.findIndex((link) => link.classList.contains('on'));
        const nextIndex = event.key === 'ArrowDown'
            ? (activeIndex + 1) % links.length
            : (activeIndex <= 0 ? links.length - 1 : activeIndex - 1);
        links.forEach((link, index) => {
            link.classList.toggle('on', index === nextIndex);
            if (index === nextIndex) link.scrollIntoView({ block: 'nearest' });
        });
    });

    const targetNode = $('#timer-data');
    const timerText = $('#rt');
    const ring = $('#rg');
    const timerTarget = Number(targetNode?.dataset.target || 0);
    const numberFormatter = new Intl.NumberFormat('fa-IR', { minimumIntegerDigits: 2, useGrouping: false });
    const circumference = 2 * Math.PI * 48;
    let timerInterval = null;

    const renderTimer = () => {
        if (!timerTarget || !timerText || !ring) return;
        const remaining = Math.max(0, timerTarget - Math.floor(Date.now() / 1000));
        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);
        const seconds = remaining % 60;
        timerText.textContent = [hours, minutes, seconds].map((value) => numberFormatter.format(value)).join(':');
        ring.style.strokeDashoffset = String(circumference * (1 - Math.min(1, remaining / (24 * 3600))));

        if (remaining === 0) {
            const label = $('.tm small');
            if (label) label.textContent = 'زمان شروع کلاس رسیده است';
            window.clearInterval(timerInterval);
        }
    };

    if (timerTarget > 0) {
        renderTimer();
        timerInterval = window.setInterval(renderTimer, 1000);
    }

    const dashboard = $('#main');
    if (dashboard && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        requestAnimationFrame(() => dashboard.classList.add('in'));
    }

    $$('.fill').forEach((bar) => {
        const target = bar.style.width;
        bar.style.width = '0';
        requestAnimationFrame(() => { bar.style.width = target; });
    });
})();
