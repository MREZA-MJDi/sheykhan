/* Shared Owner/SEO upload previews and safe form-submission feedback. */
import { guardFormOnce } from './shared/behavior-runtime.js';
import './shared/file-preview.js';
/* New owner admin page behavior. Server routes and form submissions stay authoritative. */
(() => {
  'use strict';
  document.querySelectorAll('form[method="POST"],form[method="post"]').forEach((form) => {
    if (form.action.includes('/logout')) return;
    guardFormOnce(form, { pendingText: 'در حال ذخیره…' });
  });
})();
