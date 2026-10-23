import test from 'node:test';
import assert from 'node:assert/strict';

import { formatMoneyInput, normalizeMoneyInput } from '../../resources/js/persian-money.js';

test('normalizes Persian and Arabic digits with localized separators', () => {
    assert.equal(normalizeMoneyInput('۱٬۲۳۴٬۵۶۷'), '1234567');
    assert.equal(normalizeMoneyInput('١٬٢٣٤٬٥٦٧'), '1234567');
    assert.equal(normalizeMoneyInput('۱٬۲۳۴٫۵۰'), '1234.50');
});

test('formats grouped amounts with Persian digits and preserves entered decimals', () => {
    assert.equal(formatMoneyInput('1234567'), '۱٬۲۳۴٬۵۶۷');
    assert.equal(formatMoneyInput('1234.5'), '۱٬۲۳۴٫۵');
    assert.equal(formatMoneyInput(''), '');
});

test('normalization removes extra separators and caps decimal precision at two places', () => {
    assert.equal(normalizeMoneyInput('1,234,567.89.2'), '1234567.89');
    assert.equal(normalizeMoneyInput('0001234'), '1234');
});
