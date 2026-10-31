// Teacher-owned form behavior. Kept inside the teacher bundle; no Parent/Student runtime.
function bindCourseClassroomSelects() {
  document.querySelectorAll('[data-course-classroom-select]').forEach((classroomSelect) => {
    const selector = classroomSelect.dataset.courseSelect || '[name="course_id"]';
    const courseSelect = document.querySelector(selector);
    if (!courseSelect) return;

    const sync = () => {
      const courseId = String(courseSelect.value || '');
      Array.from(classroomSelect.options).forEach((option) => {
        const matches = !option.dataset.courseId || option.dataset.courseId === courseId;
        option.hidden = !matches;
        option.disabled = !matches;
        if (!matches && option.selected) classroomSelect.value = '';
      });
    };

    courseSelect.addEventListener('change', sync);
    sync();
  });
}

function bindFilePreviews() {
  document.querySelectorAll('input[type="file"][data-teacher-preview]').forEach((input) => {
    const selector = input.dataset.teacherPreview;
    const preview = selector ? document.querySelector(selector) : null;
    if (!preview || input.dataset.tpPreviewBound === '1') return;
    input.dataset.tpPreviewBound = '1';
    let objectUrl = null;

    input.addEventListener('change', () => {
      if (objectUrl) URL.revokeObjectURL(objectUrl);
      objectUrl = null;
      const file = input.files?.[0];
      if (!file) {
        preview.hidden = true;
        preview.replaceChildren();
        return;
      }

      preview.hidden = false;
      preview.replaceChildren();
      const copy = document.createElement('span');
      copy.className = 'tp-file-preview__name';
      copy.textContent = `${file.name} · ${Math.max(1, Math.ceil(file.size / 1024)).toLocaleString('fa-IR')} کیلوبایت`;
      preview.append(copy);

      if (file.type.startsWith('image/')) {
        const image = document.createElement('img');
        image.alt = 'پیش‌نمایش فایل انتخاب‌شده';
        objectUrl = URL.createObjectURL(file);
        image.src = objectUrl;
        preview.prepend(image);
      }
    });

    window.addEventListener('pagehide', () => {
      if (objectUrl) URL.revokeObjectURL(objectUrl);
    }, { once: true });
  });
}

function bindNativeUploadFeedback() {
  document.querySelectorAll('form[data-upload-progress]').forEach((form) => {
    if (form.dataset.tpUploadBound === '1') return;
    form.dataset.tpUploadBound = '1';
    form.addEventListener('submit', () => {
      const button = form.querySelector('button[type="submit"], input[type="submit"]');
      if (button && button.dataset.tpSubmitting !== '1') {
        button.dataset.tpSubmitting = '1';
        button.disabled = true;
        if (button.tagName === 'BUTTON') {
          button.dataset.tpLabel = button.textContent;
          button.textContent = 'در حال ارسال…';
        }
      }
    });
  });
}

export function initTeacherForms() {
  bindCourseClassroomSelects();
  bindFilePreviews();
  bindNativeUploadFeedback();
  bindMeetingProviderForms();
}

function bindMeetingProviderForms() {
  document.querySelectorAll('[data-meeting-provider-form]').forEach((form) => {
    const providerSelect = form.querySelector('[data-meeting-provider]');
    const meetingUrl = form.querySelector('[data-meeting-url]');
    const help = form.querySelector('[data-meeting-provider-help]');
    if (!providerSelect || !meetingUrl || form.dataset.tpProviderBound === '1') return;
    form.dataset.tpProviderBound = '1';

    const makeJitsiUrl = () => {
      const bytes = new Uint8Array(18);
      if (window.crypto?.getRandomValues) window.crypto.getRandomValues(bytes);
      else for (let i = 0; i < bytes.length; i += 1) bytes[i] = Math.floor(Math.random() * 256);
      const id = Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0')).join('');
      return 'https://meet.jit.si/Sheykhan-' + id;
    };
    const isJitsiUrl = (value) => {
      try {
        const url = new URL(value);
        return url.protocol === 'https:' && url.hostname === 'meet.jit.si';
      } catch { return false; }
    };
    const sync = () => {
      const provider = providerSelect.value;
      if (provider === 'Jitsi') {
        if (!isJitsiUrl(meetingUrl.value)) meetingUrl.value = makeJitsiUrl();
        meetingUrl.readOnly = true;
        meetingUrl.required = false;
        meetingUrl.placeholder = 'لینک یکتای Jitsi ساخته شد';
        if (help) help.textContent = 'لینک یکتای Jitsi همین حالا ساخته شد. این لینک برای همین جلسه یا بازه هفتگی ثابت می‌ماند.';
        return;
      }
      if (isJitsiUrl(meetingUrl.value)) meetingUrl.value = '';
      meetingUrl.readOnly = false;
      meetingUrl.required = Boolean(provider);
      meetingUrl.placeholder = provider === 'Google Meet'
        ? 'https://meet.google.com/abc-defg-hij'
        : (provider === 'Zoom' ? 'https://zoom.us/j/...' : 'https://...');
      const guidance = {
        'Google Meet': 'جلسه را در Google Meet بساز، لینک واقعی meet.google.com را کپی کن و اینجا قرار بده. شیخان بدون اتصال OAuth/Calendar نمی‌تواند از طرف حساب گوگل جلسه بسازد.',
        'Zoom': 'جلسه را در Zoom بساز، لینک شرکت‌کننده را کپی کن و اینجا قرار بده. ساخت خودکار جلسه Zoom نیازمند اتصال رسمی و مجوز حساب است.',
        'سایر': 'لینک HTTPS واقعی سرویس را اینجا قرار بده.',
      };
      if (help) help.textContent = guidance[provider] || 'ابتدا سرویس جلسه را انتخاب کن.';
    };
    providerSelect.addEventListener('change', sync);
    sync();
  });
}

