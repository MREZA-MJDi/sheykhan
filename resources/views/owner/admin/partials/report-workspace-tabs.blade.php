<section class="owner-workspace-tabs mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-l from-slate-50 via-white to-indigo-50 p-4 sm:p-5" aria-label="بخش‌های گزارش مدیریتی">
    <div><p class="text-[11px] font-black text-slate-600">گزارش‌های آموزشگاه</p><h2 class="mt-1 text-lg font-black text-slate-900">نمایش عملکرد آموزشگاه</h2><p class="mt-1 text-xs leading-6 text-slate-600">گزارش‌های مختلف را در همین صفحه ببین؛ هر فهرست صفحه‌های جداگانه دارد.</p></div>
    <nav class="mt-4 flex flex-wrap gap-2">
        @foreach([['#overview','نمای کلی'],['#teacher-performance','عملکرد مدرس‌ها'],['#course-performance','عملکرد دوره‌ها'],['#classroom-performance','گزارش کلاس‌ها'],['#enrollments','ثبت‌نام‌ها و پرداخت']] as [$href,$label])
            <a href="{{ $href }}" class="inline-flex min-h-9 items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700 hover:border-indigo-300 hover:text-indigo-700">{{ $label }}</a>
        @endforeach
    </nav>
</section>
