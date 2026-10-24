document.addEventListener('DOMContentLoaded', () => {
    const dashboard = document.querySelector('[data-teacher-dashboard]');
    const shell = document.body;

    if (!dashboard || !shell.classList.contains('teacher-dashboard-shell')) return;

    const themeKey = 'sheykhan.teacher.theme';
    const themeButtons = document.querySelectorAll('[data-teacher-theme-toggle]');

    const applyTheme = (value) => {
        const theme = value === 'dark' ? 'dark' : 'light';
        shell.dataset.theme = theme;
        themeButtons.forEach((button) => {
            button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
            button.setAttribute('aria-label', theme === 'dark' ? 'فعال‌کردن تم روشن' : 'فعال‌کردن تم تیره');
            button.title = theme === 'dark' ? 'فعال‌کردن تم روشن' : 'فعال‌کردن تم تیره';
            const moon = button.querySelector('[data-theme-moon]');
            const sun = button.querySelector('[data-theme-sun]');
            if (moon) moon.hidden = theme === 'dark';
            if (sun) sun.hidden = theme !== 'dark';
        });
    };

    let savedTheme = 'light';
    try {
        savedTheme = localStorage.getItem(themeKey) || 'light';
    } catch {
        // Keep the dashboard usable if browser storage is disabled.
    }
    applyTheme(savedTheme);

    themeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const nextTheme = shell.dataset.theme === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
            try {
                localStorage.setItem(themeKey, nextTheme);
            } catch {
                // Keep the selected theme for this page view.
            }
        });
    });

    const search = document.querySelector('[data-dashboard-search]');
    const searchable = Array.from(dashboard.querySelectorAll('[data-dashboard-searchable]'));
    const staticEmptyStates = dashboard.querySelectorAll('.tdb-empty:not([data-chart-empty]):not([data-dashboard-search-empty])');
    const searchEmptyState = dashboard.querySelector('[data-dashboard-search-empty]');

    if (search) {
        search.addEventListener('input', () => {
            const query = search.value.trim().toLocaleLowerCase('fa');
            let matches = 0;

            searchable.forEach((item) => {
                const matchesQuery = query.length === 0
                    || item.textContent.toLocaleLowerCase('fa').includes(query);
                item.hidden = !matchesQuery;
                if (matchesQuery) matches += 1;
            });

            staticEmptyStates.forEach((state) => {
                state.hidden = query.length > 0;
            });
            if (searchEmptyState) {
                searchEmptyState.hidden = query.length === 0 || matches > 0;
            }
        });
    }

    const buttons = dashboard.querySelectorAll('[data-teacher-range]');
    const columns = Array.from(dashboard.querySelectorAll('[data-chart-column]'));
    const emptyState = dashboard.querySelector('[data-chart-empty]');
    let datasets = {};
    try {
        datasets = JSON.parse(dashboard.dataset.chart || '{}');
    } catch {
        datasets = {};
    }

    const persianDigits = (value) => String(value).replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
    const applyChart = (range) => {
        const dataset = datasets[range] || {};
        const values = Array.isArray(dataset.values) ? dataset.values : [];
        const labels = Array.isArray(dataset.labels) ? dataset.labels : [];

        buttons.forEach((button) => {
            const selected = button.dataset.teacherRange === range;
            button.classList.toggle('active', selected);
            button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });

        columns.forEach((column, index) => {
            const exists = index < values.length;
            column.hidden = !exists;
            if (!exists) return;

            const raw = Number(values[index]);
            const value = Number.isFinite(raw) ? Math.max(0, Math.min(100, raw)) : 0;
            const bar = column.querySelector('[data-chart-bar]');
            const label = column.querySelector('[data-chart-label]');
            const valueLabel = column.querySelector('[data-chart-value]');
            if (bar) bar.style.height = String(value) + '%';
            if (label) label.textContent = labels[index] == null ? '' : labels[index];
            if (valueLabel) valueLabel.textContent = persianDigits(String(Math.round(value * 10) / 10)) + '٪';
        });

        if (emptyState) emptyState.hidden = values.length > 0;
        const chartArea = dashboard.querySelector('[data-teacher-bars]');
        if (chartArea) {
            chartArea.setAttribute('aria-label', values.length
                ? 'نمودار فعالیت ثبت‌شده در بازه ' + (range === 'month' ? 'ماهانه' : 'هفتگی')
                : 'برای این بازه داده‌ای ثبت نشده است');
        }
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => applyChart(button.dataset.teacherRange || 'week'));
    });
    applyChart('week');

    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('#teacher-dashboard-sidebar');
    const backdrop = document.querySelector('[data-sidebar-close]');

    const closeSidebar = () => {
        shell.classList.remove('tdb-sidebar-open');
        if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', 'false');
        if (backdrop) backdrop.hidden = true;
    };
    const openSidebar = () => {
        shell.classList.add('tdb-sidebar-open');
        if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', 'true');
        if (backdrop) backdrop.hidden = false;
    };

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            if (shell.classList.contains('tdb-sidebar-open')) closeSidebar();
            else openSidebar();
        });
        sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeSidebar));
    }
    if (backdrop) backdrop.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeSidebar();
    });
});
