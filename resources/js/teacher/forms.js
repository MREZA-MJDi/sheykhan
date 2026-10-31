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
}