// Per-student status control. Field names match AttendanceRequest exactly.
export function initAttendance() {
  document.querySelectorAll('[data-tp-attendance]').forEach((form) => {
    const setStatus = (card, status) => {
      if (!card) return;
      card.dataset.status = status;
      const hidden = card.querySelector('input[type="hidden"][name^="attendance["]');
      if (hidden) hidden.value = status;
      card.querySelectorAll('[data-tp-set]').forEach((button) => {
        const active = button.dataset.tpSet === status;
        button.classList.toggle('is-on', active);
        button.setAttribute('aria-pressed', String(active));
      });
      const minutes = card.querySelector('input[name^="attendance_minutes["]');
      if (minutes) {
        minutes.disabled = status === 'absent' || status === 'excused';
        if (status === 'absent' || status === 'excused') minutes.value = '';
      }
    };

    form.addEventListener('click', (event) => {
      const button = event.target.closest('[data-tp-set]');
      if (button) setStatus(button.closest('[data-tp-stu]'), button.dataset.tpSet);
      if (event.target.closest('[data-tp-all-present]')) {
        form.querySelectorAll('[data-tp-stu]').forEach((card) => setStatus(card, 'present'));
      }
    });

    form.querySelectorAll('[data-tp-stu]').forEach((card) => {
      const active = card.querySelector('[data-tp-set].is-on');
      if (active) setStatus(card, active.dataset.tpSet);
    });
  });
}