import { bootPanel } from './panel-base.js';

document.addEventListener('DOMContentLoaded', () => {
    bootPanel();

    document.documentElement.classList.add('owner-ready');

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirm || 'آیا از انجام این عملیات مطمئن هستید؟';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const enrollmentForm = document.querySelector('[data-owner-enrollment-form]');
    const courseSelect = enrollmentForm?.querySelector('[data-owner-enrollment-course]');
    const classroomSelect = enrollmentForm?.querySelector('[data-owner-enrollment-classroom]');
    const classroomsSource = document.querySelector('[data-owner-classrooms]');

    if (enrollmentForm && courseSelect && classroomSelect && classroomsSource) {
        let classrooms = [];

        try {
            classrooms = JSON.parse(classroomsSource.textContent || '[]');
        } catch {
            classrooms = [];
        }

        const renderClassrooms = () => {
            const selectedCourseId = String(courseSelect.value || '');
            classroomSelect.innerHTML = '<option value="">بدون کلاس</option>';

            classrooms
                .filter((classroom) => String(classroom.course_id) === selectedCourseId && classroom.status === 'active')
                .forEach((classroom) => {
                    const option = document.createElement('option');
                    option.value = classroom.id;
                    option.textContent = classroom.capacity
                        ? classroom.title + ' · ظرفیت ' + classroom.capacity
                        : classroom.title;
                    classroomSelect.appendChild(option);
                });
        };

        courseSelect.addEventListener('change', renderClassrooms);
        renderClassrooms();
    }
});
