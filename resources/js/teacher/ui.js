// Teacher-only shell interactions: mobile drawer, toast feedback and print.
export function toast(message) {
  const existing = document.querySelector('.tp-toast');
  existing?.remove();
  const element = document.createElement('div');
  element.className = 'tp-toast';
  element.setAttribute('role', 'status');
  element.textContent = message;
  document.body.appendChild(element);
  window.setTimeout(() => element.remove(), 2600);
}

export function initUi() {
  const side = document.getElementById('tp-side');
  const overlay = document.querySelector('.tp-overlay');
  const setDrawer = (open) => {
    side?.classList.toggle('is-open', open);
    overlay?.classList.toggle('is-open', open);
    document.body.classList.toggle('tp-drawer-open', open);
    if (open) side?.querySelector('a.tp-nav')?.focus();
  };

  document.addEventListener('click', (event) => {
    if (event.target.closest('[data-tp-drawer-open]')) return setDrawer(true);
    if (event.target.closest('[data-tp-drawer-close]') || event.target.closest('#tp-side .tp-nav')) return setDrawer(false);
    const toastTrigger = event.target.closest('[data-tp-toast]');
    if (toastTrigger) toast(toastTrigger.dataset.tpToast || 'انجام شد.');
    const fab = event.target.closest('[data-tp-fab]');
    const menu = document.getElementById('tp-fab-menu');
    if (fab) {
      const open = menu?.classList.toggle('is-open') ?? false;
      fab.setAttribute('aria-expanded', String(open));
    } else if (!event.target.closest('.tp-fab')) {
      menu?.classList.remove('is-open');
      document.querySelector('[data-tp-fab]')?.setAttribute('aria-expanded', 'false');
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setDrawer(false);
  });

  const flash = document.querySelector('[data-tp-flash]');
  if (flash?.dataset.tpFlash) toast(flash.dataset.tpFlash);
}