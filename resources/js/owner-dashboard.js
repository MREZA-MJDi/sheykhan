import './shared/persian-calendar.js';
import './shared/persian-money.js';
import './owner-admin-common.js';
import './owner-admin-shell.js';
import './owner-record-modal.js';
import './owner/exam-builder.js';
import './owner/academy-form-options.js';
import { bootPanelRuntime, closePanelMenu } from './panel-runtime.js';
export { closePanelMenu };

bootPanelRuntime();

/* Real dashboard range selection: drag either handle to summarize saved attendance records. */
(() => {
    'use strict';

    const chart = document.querySelector('[data-owner-range-chart]');
    if (!chart || chart.dataset.ownerRangeBound === 'true') return;
    chart.dataset.ownerRangeBound = 'true';

    let values = [];
    let labels = [];
    try {
        values = JSON.parse(chart.dataset.rangeValues || '[]');
        labels = JSON.parse(chart.dataset.rangeLabels || '[]');
    } catch {
        return;
    }
    if (!Array.isArray(values) || values.length < 2) return;
    if (!Array.isArray(labels)) labels = [];

    values = values.map((value) => (value === null || value === '' || !Number.isFinite(Number(value)) ? null : Number(value)));
    const numeric = values.filter((value) => value !== null);
    const minimum = numeric.length ? Math.min(...numeric) : 0;
    const maximum = numeric.length ? Math.max(...numeric) : 100;
    const spread = maximum - minimum;
    const width = 520;
    const chartHeight = 150;
    const initialStart = values.length >= 5 ? 2 : 0;
    const initialEnd = values.length >= 5 ? values.length - 3 : values.length - 1;
    const indices = { start: initialStart, end: initialEnd };
    let dragging = null;

    const summary = document.querySelector('#owner-trend-range-summary');
    const toFaDigits = (value) => String(value).replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
    const clampIndex = (index) => Math.max(0, Math.min(values.length - 1, index));
    const xForIndex = (index) => (clampIndex(index) / (values.length - 1)) * width;

    const nearestValueIndex = (index) => {
        if (values[clampIndex(index)] !== null) return clampIndex(index);
        for (let distance = 1; distance < values.length; distance += 1) {
            const before = index - distance;
            const after = index + distance;
            if (before >= 0 && values[before] !== null) return before;
            if (after < values.length && values[after] !== null) return after;
        }
        return -1;
    };

    const yForValue = (value) => {
        const normalized = spread > 0 ? (value - minimum) / spread : .5;
        return chartHeight - 12 - normalized * (chartHeight - 24);
    };

    const renderHandle = (name) => {
        const group = chart.querySelector(`[data-range-handle="${name}"]`);
        if (!group) return;

        const index = clampIndex(indices[name]);
        const nearest = nearestValueIndex(index);
        const x = xForIndex(index);
        const y = nearest >= 0 ? yForValue(values[nearest]) : chartHeight / 2;
        const line = group.querySelector('line');
        const circle = group.querySelector('circle');

        line?.setAttribute('x1', String(x));
        line?.setAttribute('x2', String(x));
        circle?.setAttribute('cx', String(x));
        circle?.setAttribute('cy', String(y));
        group.setAttribute('aria-valuenow', String(index));
        group.setAttribute('aria-valuetext', labels[index] || toFaDigits(index + 1));
    };

    const renderSummary = () => {
        if (!summary) return;

        const first = Math.min(indices.start, indices.end);
        const last = Math.max(indices.start, indices.end);
        const selected = values.slice(first, last + 1).filter((value) => value !== null);

        if (!selected.length) {
            summary.textContent = 'برای این بازه رکورد حضور و غیاب معتبری ثبت نشده است.';
            return;
        }

        const average = selected.reduce((total, value) => total + value, 0) / selected.length;
        const from = labels[first] || toFaDigits(first + 1);
        const until = labels[last] || toFaDigits(last + 1);
        summary.textContent = `از ${from} تا ${until} · میانگین حضور ${toFaDigits(average.toFixed(1))}٪ · ${toFaDigits(selected.length)} هفته دارای داده`;
    };

    const setIndexFromPointer = (name, event) => {
        const rect = chart.getBoundingClientRect();
        if (!rect.width) return;
        const x = Math.max(0, Math.min(width, ((event.clientX - rect.left) / rect.width) * width));
        indices[name] = clampIndex(Math.round((x / width) * (values.length - 1)));
        renderHandle(name);
        renderSummary();
    };

    ['start', 'end'].forEach((name) => {
        const handle = chart.querySelector(`[data-range-handle="${name}"]`);
        if (!handle) return;

        handle.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            if (event.key === 'Home') indices[name] = 0;
            else if (event.key === 'End') indices[name] = values.length - 1;
            else indices[name] = clampIndex(indices[name] + (['ArrowRight', 'ArrowUp'].includes(event.key) ? 1 : -1));
            renderHandle(name);
            renderSummary();
        });
    });

    chart.addEventListener('pointerdown', (event) => {
        const handle = event.target.closest('[data-range-handle]');
        if (!handle) return;
        dragging = handle.dataset.rangeHandle;
        chart.setPointerCapture?.(event.pointerId);
        setIndexFromPointer(dragging, event);
        event.preventDefault();
    });
    chart.addEventListener('pointermove', (event) => {
        if (dragging) setIndexFromPointer(dragging, event);
    });
    const stopDragging = () => { dragging = null; };
    chart.addEventListener('pointerup', stopDragging);
    chart.addEventListener('pointercancel', stopDragging);
    chart.addEventListener('lostpointercapture', stopDragging);

    renderHandle('start');
    renderHandle('end');
    renderSummary();
})();
