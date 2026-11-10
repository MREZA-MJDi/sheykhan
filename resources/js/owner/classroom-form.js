/* Classroom form dependencies: keep teacher choices aligned with the selected course. */
export function bootOwnerClassroomForms(root = document) {
    root.querySelectorAll('[data-owner-classroom-form]').forEach((form) => {
        if (form.dataset.classroomFormBound === 'true') return;

        const course = form.querySelector('[data-classroom-course]');
        const hint = form.querySelector('#owner-teacher-hint');
        const options = Array.from(form.querySelectorAll('[data-classroom-teachers] [data-teacher-option]'));
        if (!course || !options.length) return;

        const syncTeacherOptions = () => {
            const selected = course.selectedOptions?.[0];
            const allowed = new Set(
                (selected?.dataset.teacherIds || '')
                    .split(',')
                    .map((value) => Number(value))
                    .filter(Boolean)
            );

            let count = 0;
            options.forEach((wrapper) => {
                const teacherId = Number(wrapper.dataset.teacherOption);
                const input = wrapper.querySelector('input[name="teacher_ids[]"]');
                if (!input) return;

                const enabled = allowed.has(teacherId);
                wrapper.hidden = !enabled;
                input.disabled = !enabled;
                if (!enabled && input.checked) input.checked = false;
                if (enabled) count += 1;
            });

            if (hint) {
                hint.textContent = selected?.value
                    ? (count ? count + ' مدرس قابل انتخاب' : 'برای این دوره هنوز مدرسی اختصاص داده نشده است')
                    : '';
            }
        };

        course.addEventListener('change', syncTeacherOptions);
        syncTeacherOptions();
        form.dataset.classroomFormBound = 'true';
    });
}

const initialize = () => bootOwnerClassroomForms();
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true });
} else {
    initialize();
}
window.addEventListener('owner:modal:mounted', (event) => {
    const root = event.detail?.root;
    if (root) bootOwnerClassroomForms(root);
});
