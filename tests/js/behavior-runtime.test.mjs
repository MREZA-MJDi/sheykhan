import test from 'node:test';
import assert from 'node:assert/strict';

import { shouldConfirmOnClick } from '../../resources/js/shared/behavior-runtime.js';

test('a form-level confirmation is not triggered by clicks on form fields', () => {
    const form = { tagName: 'FORM', dataset: { confirm: 'حساب مدرس ساخته شود؟' } };

    assert.equal(shouldConfirmOnClick(form), false);
});

test('an explicitly confirmed button or link still confirms on click', () => {
    assert.equal(shouldConfirmOnClick({
        tagName: 'BUTTON',
        dataset: { confirm: 'حذف شود؟' },
    }), true);
    assert.equal(shouldConfirmOnClick({
        tagName: 'A',
        dataset: { confirm: 'خارج می‌شوید؟' },
    }), true);
});

test('elements without a confirmation message do not open a dialog', () => {
    assert.equal(shouldConfirmOnClick({ tagName: 'INPUT', dataset: {} }), false);
});
