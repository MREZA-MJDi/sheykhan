/* Shared Owner/SEO upload previews and safe form-submission feedback. */
import { guardFormOnce } from './shared/behavior-runtime.js';
import './shared/file-preview.js';
import './owner/management-upload-previews.js';
import './owner/seo-site-editors.js';
/* New owner admin page behavior. Server routes and form submissions stay authoritative. */
(() => {
  'use strict';
  document.querySelectorAll('form[method="POST"],form[method="post"]').forEach((form) => {
    if (form.action.includes('/logout')) return;
    if (form.closest('dialog.owner-floating-modal, dialog[data-owner-record-modal]')) return;
    guardFormOnce(form, { pendingText: 'در حال ذخیره…' });
  });
})();
