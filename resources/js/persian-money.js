const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
const ARABIC_DIGITS = '٠١٢٣٤٥٦٧٨٩';

export function normalizeMoneyInput(value) {
    let normalized = String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => String(PERSIAN_DIGITS.indexOf(digit)))
        .replace(/[٠-٩]/g, (digit) => String(ARABIC_DIGITS.indexOf(digit)))
        .replace(/[٬,\s]/g, '')
        .replace(/٫/g, '.')
        .replace(/[^0-9.]/g, '');

    const decimalAt = normalized.indexOf('.');
    if (decimalAt !== -1) {
        normalized = normalized.slice(0, decimalAt + 1) + normalized.slice(decimalAt + 1).replace(/\./g, '');
    }

    const hasDecimal = normalized.includes('.');
    let [whole = '', fraction = ''] = normalized.split('.');
    whole = whole.replace(/^0+(?=\d)/, '');
    fraction = fraction.slice(0, 2);
    if (!whole && hasDecimal) whole = '0';

    return whole + (hasDecimal ? '.' + fraction : '');
}

export function formatMoneyInput(value) {
    const normalized = normalizeMoneyInput(value);
    if (!normalized) return '';

    const hasDecimal = normalized.includes('.');
    const [whole = '', fraction = ''] = normalized.split('.');
    const groupedWhole = new Intl.NumberFormat('fa-IR', {
        useGrouping: true,
        maximumFractionDigits: 0,
    }).format(Number(whole || '0'));

    if (!hasDecimal) return groupedWhole;

    const groupedFraction = fraction.replace(/[0-9]/g, (digit) => PERSIAN_DIGITS[Number(digit)]);
    return groupedWhole + '٫' + groupedFraction;
}

function countDigitsBefore(value, position) {
    return normalizeMoneyInput(String(value).slice(0, position)).replace(/\D/g, '').length;
}

function cursorForDigitCount(formatted, wantedCount) {
    if (wantedCount === 0) return 0;

    let seen = 0;
    for (let index = 0; index < formatted.length; index += 1) {
        if (/[0-9۰-۹٠-٩]/.test(formatted[index])) {
            seen += 1;
            if (seen >= wantedCount) return index + 1;
        }
    }

    return formatted.length;
}

function validationMessage(input, canonical) {
    if (!canonical || canonical === '0.' || canonical.endsWith('.')) return '';
    const amount = Number(canonical);
    if (!Number.isFinite(amount)) return 'مبلغ واردشده معتبر نیست.';

    const min = input.dataset.moneyMin;
    const max = input.dataset.moneyMax;
    const step = input.dataset.moneyStep;

    if (min !== undefined && min !== '' && amount < Number(min)) {
        return 'مبلغ نباید کمتر از ' + formatMoneyInput(min) + ' باشد.';
    }
    if (max !== undefined && max !== '' && amount > Number(max)) {
        return 'مبلغ نباید بیشتر از ' + formatMoneyInput(max) + ' باشد.';
    }

    const precision = canonical.split('.')[1]?.length || 0;
    const allowedPrecision = step && step.includes('.') ? step.split('.')[1].length : 0;
    if (precision > allowedPrecision) {
        return allowedPrecision
            ? 'مبلغ حداکثر ' + PERSIAN_DIGITS[allowedPrecision] + ' رقم اعشار می‌تواند داشته باشد.'
            : 'این مبلغ باید بدون رقم اعشار وارد شود.';
    }

    return '';
}

function enhanceMoneyInput(input) {
    if (input.dataset.moneyInputEnhanced === 'true' || input.type !== 'number') return;
    const originalName = input.name;
    if (!originalName || !input.form) return;

    const originalValue = input.value;
    const originalRequired = input.required;
    const originalId = input.id;
    const min = input.getAttribute('min');
    const max = input.getAttribute('max');
    const step = input.getAttribute('step') || '1';

    const submitted = document.createElement('input');
    submitted.type = 'hidden';
    submitted.name = originalName;
    submitted.value = normalizeMoneyInput(originalValue);
    submitted.defaultValue = submitted.value;
    submitted.dataset.moneySubmitted = originalName;

    input.type = 'text';
    input.name = '';
    input.inputMode = step.includes('.') ? 'decimal' : 'numeric';
    input.autocomplete = 'off';
    input.dir = 'ltr';
    input.style.textAlign = 'end';
    input.required = originalRequired;
    if (originalId) input.id = originalId;
    input.dataset.moneyInputEnhanced = 'true';
    input.dataset.moneyMin = min ?? '';
    input.dataset.moneyMax = max ?? '';
    input.dataset.moneyStep = step;
    input.removeAttribute('min');
    input.removeAttribute('max');
    input.removeAttribute('step');
    input.insertAdjacentElement('afterend', submitted);

    if (!input.getAttribute('aria-label') && input.dataset.moneyUnit) {
        input.setAttribute('aria-label', 'مبلغ به ' + input.dataset.moneyUnit);
    }

    const sync = (preserveCursor = false) => {
        const start = input.selectionStart ?? input.value.length;
        const digitsBefore = countDigitsBefore(input.value, start);
        const canonical = normalizeMoneyInput(input.value);
        const formatted = formatMoneyInput(canonical);
        input.value = formatted;
        submitted.value = canonical;
        submitted.disabled = input.disabled;
        input.setCustomValidity(validationMessage(input, canonical));

        if (preserveCursor && document.activeElement === input) {
            const next = cursorForDigitCount(formatted, digitsBefore);
            try { input.setSelectionRange(next, next); } catch { /* input may be unfocused */ }
        }
    };

    input.addEventListener('input', () => sync(true));
    input.addEventListener('change', () => sync(false));
    input.addEventListener('blur', () => sync(false));
    input.form.addEventListener('submit', (event) => {
        sync(false);
        if (!input.checkValidity()) {
            event.preventDefault();
            input.reportValidity();
        }
        submitted.disabled = input.disabled;
    });

    input.form.addEventListener('reset', () => {
        window.setTimeout(() => {
            input.value = formatMoneyInput(submitted.value);
            input.setCustomValidity('');
            submitted.disabled = input.disabled;
        }, 0);
    });

    sync(false);
}

function initPersianMoneyInputs(root = document) {
    root.querySelectorAll?.('input[type="number"][data-money-input]').forEach(enhanceMoneyInput);
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initPersianMoneyInputs(), { once: true });
    } else {
        initPersianMoneyInputs();
    }

    if (document.body && !document.body.dataset.persianMoneyObserver) {
        const observer = new MutationObserver((records) => {
            records.forEach((record) => {
                record.addedNodes.forEach((node) => {
                    if (!(node instanceof Element)) return;
                    if (node.matches('input[type="number"][data-money-input]')) enhanceMoneyInput(node);
                    initPersianMoneyInputs(node);
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
        document.body.dataset.persianMoneyObserver = 'true';
    }
}
