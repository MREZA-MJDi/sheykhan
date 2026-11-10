@php
    $learningAcademy = $academy ?? null;
    $learningUser = auth()->user();
    $learningSheets = [
        ['key' => 'courses', 'label' => 'دوره‌ها', 'permission' => 'courses.view', 'route' => 'owner.courses.index', 'scope' => 'global', 'icon' => '▤'],
        ['key' => 'classrooms', 'label' => 'کلاس‌ها', 'permission' => 'classrooms.view', 'route' => 'owner.classrooms.index', 'scope' => 'academy', 'icon' => '▦'],
        ['key' => 'schedules', 'label' => 'برنامه هفتگی', 'permission' => 'live_classes.view', 'route' => 'owner.schedules.index', 'scope' => 'academy', 'icon' => '◷'],
        ['key' => 'meetings', 'label' => 'جلسات آنلاین', 'permission' => 'live_classes.view', 'route' => 'owner.communication-workspace', 'scope' => 'academy', 'query' => ['tab' => 'meetings'], 'icon' => '↗'],
        ['key' => 'allocations', 'label' => 'تخصیص مدرس و هنرجو', 'permission' => ['teachers.manage', 'enrollments.manage'], 'route' => 'owner.people.index', 'scope' => 'academy', 'query' => ['tab' => 'management', 'sheet' => 'operations'], 'icon' => '⇄'],
        ['key' => 'assignments', 'label' => 'تکالیف و تصحیح', 'permission' => 'assignments.view', 'route' => 'owner.assignments.index', 'scope' => 'academy', 'icon' => '✓'],
        ['key' => 'exams', 'label' => 'آزمون و نتایج', 'permission' => 'exams.view', 'route' => 'owner.exams.index', 'scope' => 'academy', 'icon' => '✎'],
        ['key' => 'recordings', 'label' => 'ضبط جلسات', 'permission' => 'live_classes.view', 'route' => 'owner.recordings.index', 'scope' => 'academy', 'icon' => '▣'],
        ['key' => 'resources', 'label' => 'منابع آموزشی', 'permission' => 'resources.view', 'route' => 'owner.resources.index', 'scope' => 'academy', 'icon' => '▧'],
    ];
@endphp
<section class="mb-5 overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-l from-violet-50 via-white to-indigo-50 p-4 sm:p-5" style="--learning-accent:#6d55c7">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[11px] font-black tracking-wide text-violet-700">فضای کاری آموزش</p>
            <h2 class="mt-1 text-lg font-black text-slate-900">مدیریت مسیر یادگیری</h2>
            <p class="mt-1 text-xs leading-6 text-slate-600">دوره، کلاس، جلسه آنلاین، ارزیابی و محتوای آموزشی در یک نوار کاری مشترک؛ دسترسی هر شیت بر اساس مجوز حساب کنترل می‌شود.</p>
        </div>
        @if($learningAcademy)
            <span class="inline-flex min-h-9 items-center rounded-xl border border-violet-100 bg-white/80 px-3 text-xs font-bold text-violet-800">{{ $learningAcademy->name }}</span>
        @endif
    </div>
    <nav class="mt-4 flex flex-wrap gap-2" aria-label="شیت‌های فضای کاری آموزش">
        @foreach($learningSheets as $sheet)
            @if(collect((array) $sheet['permission'])->contains(fn ($permission) => $learningUser?->hasPermission($permission)) && ($sheet['scope'] === 'global' || $learningAcademy))
                <a href="{{ $sheet['scope'] === 'global' ? route($sheet['route']) : route($sheet['route'], array_merge([$learningAcademy], $sheet['query'] ?? [])) }}"
                   @if(($activeSheet ?? null) === $sheet['key']) aria-current="page" @endif
                   class="inline-flex min-h-10 items-center gap-2 rounded-xl px-4 text-xs font-black transition {{ ($activeSheet ?? null) === $sheet['key'] ? 'bg-violet-700 text-white shadow-sm' : 'border border-violet-100 bg-white text-slate-700 hover:border-violet-300 hover:text-violet-800' }}">
                    <span aria-hidden="true">{{ $sheet['icon'] }}</span>{{ $sheet['label'] }}
                </a>
            @endif
        @endforeach
    </nav>
</section>
