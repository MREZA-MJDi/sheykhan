<?php

// One source of truth for the student sidebar and mobile tab bar.
// Every route below is part of the existing Laravel application.
return [
    ['key' => 'dashboard', 'label' => 'خانه یادگیری', 'icon' => 'i-home', 'route' => 'student.dashboard', 'active' => ['student.dashboard'], 'tab' => true],
    ['group' => 'یادگیری'],
    ['key' => 'courses', 'label' => 'دوره‌های من', 'icon' => 'i-book', 'route' => 'student.courses.index', 'active' => ['student.courses.*', 'student.lessons.*']],
    ['key' => 'live', 'label' => 'کلاس‌های من', 'icon' => 'i-video', 'route' => 'student.live-classes.index', 'active' => ['student.live-classes.*', 'student.schedule.join']],
    ['key' => 'assignments', 'label' => 'تکالیف', 'icon' => 'i-task', 'route' => 'student.assignments.index', 'active' => ['student.assignments.*'], 'tab' => true],
    ['key' => 'exams', 'label' => 'آزمون‌ها', 'icon' => 'i-exam', 'route' => 'student.exams.index', 'active' => ['student.exams.*', 'student.exam-attempts.*'], 'tab' => true],
    ['key' => 'resources', 'label' => 'جزوه‌ها و منابع', 'icon' => 'i-file', 'route' => 'student.resources.index', 'active' => ['student.resources.*']],
    ['key' => 'library', 'label' => 'کتابخانه و خریدها', 'icon' => 'i-cart', 'route' => 'library.index', 'active' => ['library.*']],
    ['key' => 'shop', 'label' => 'فروشگاه و ثبت‌نام', 'icon' => 'i-cart', 'route' => 'store.index', 'active' => ['store.*']],
    ['group' => 'ارتباط'],
    ['key' => 'messages', 'label' => 'پیام به مدرس‌ها', 'icon' => 'i-msg', 'route' => 'student.messages.index', 'active' => ['student.messages.*', 'student.teacher-messages.*']],
    ['group' => 'عملکرد و حساب'],
    ['key' => 'results', 'label' => 'نمرات و عملکرد', 'icon' => 'i-chart', 'route' => 'student.results.index', 'active' => ['student.results.*']],
    ['key' => 'growth', 'label' => 'رشد تحصیلی', 'icon' => 'i-rocket', 'route' => 'student.growth', 'active' => ['student.growth'], 'tab' => true],
    ['key' => 'attendance', 'label' => 'حضور و غیاب', 'icon' => 'i-cal', 'route' => 'student.attendance.index', 'active' => ['student.attendance.*']],
    ['key' => 'achievements', 'label' => 'دستاوردها', 'icon' => 'i-trophy', 'route' => 'student.achievements.index', 'active' => ['student.achievements.*']],
    ['key' => 'notes', 'label' => 'یادداشت‌ها', 'icon' => 'i-note', 'route' => 'student.notes.index', 'active' => ['student.notes.*']],
    ['key' => 'profile', 'label' => 'حساب کاربری', 'icon' => 'i-user', 'route' => 'student.profile.edit', 'active' => ['student.profile.*']],
];
