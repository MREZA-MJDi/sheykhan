@php
    $learningAcademy = $academy ?? null;
    $canViewCourses = auth()->user()?->hasPermission('courses.view') ?? false;
    $canViewClassrooms = auth()->user()?->hasPermission('classrooms.view') ?? false;
@endphp
<section class="mb-5 overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-l from-violet-50 via-white to-indigo-50 p-4 sm:p-5" style="--learning-accent:#6d55c7">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[11px] font-black tracking-wide text-violet-700">مرکز آموزش و برنامه‌ریزی</p>
            <h2 class="mt-1 text-lg font-black text-slate-900">مدیریت دوره‌ها و کلاس‌ها</h2>
            <p class="mt-1 text-xs leading-6 text-slate-600">ساختار آموزشی، گروه‌های کلاسی و ظرفیت یادگیری را از یک فضای کاری دنبال کن.</p>
        </div>
        @if($learningAcademy)
            <span class="inline-flex min-h-9 items-center rounded-xl border border-violet-100 bg-white/80 px-3 text-xs font-bold text-violet-800">{{ $learningAcademy->name }}</span>
        @endif
    </div>
    <nav class="mt-4 flex flex-wrap gap-2" aria-label="شیت‌های مرکز آموزش">
        @if($canViewCourses)
            <a href="{{ route('owner.courses.index') }}" @if($activeSheet === 'courses') aria-current="page" @endif
               class="inline-flex min-h-10 items-center gap-2 rounded-xl px-4 text-xs font-black transition {{ $activeSheet === 'courses' ? 'bg-violet-700 text-white shadow-sm' : 'border border-violet-100 bg-white text-slate-700 hover:border-violet-300 hover:text-violet-800' }}">
                <span aria-hidden="true">▤</span> دوره‌ها
            </a>
        @endif
        @if($canViewClassrooms && $learningAcademy)
            <a href="{{ route('owner.classrooms.index', $learningAcademy) }}" @if($activeSheet === 'classrooms') aria-current="page" @endif
               class="inline-flex min-h-10 items-center gap-2 rounded-xl px-4 text-xs font-black transition {{ $activeSheet === 'classrooms' ? 'bg-violet-700 text-white shadow-sm' : 'border border-violet-100 bg-white text-slate-700 hover:border-violet-300 hover:text-violet-800' }}">
                <span aria-hidden="true">▦</span> کلاس‌ها و گروه‌ها
            </a>
        @endif
    </nav>
</section>
