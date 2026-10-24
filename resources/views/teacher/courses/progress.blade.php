@extends('layouts.teacher')
@section('title','پیشرفت دانش‌آموزان | شیخان')
@section('header-title','پیشرفت دانش‌آموزان')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">مانیتور یادگیری</span>
            <h1 class="teacher-workspace-title">{{ $course->title }}</h1>
            <p class="teacher-workspace-description">پیشرفت هر دانش‌آموز در درس‌های دوره از داده‌های واقعی مشاهده و تکمیل درس محاسبه می‌شود.</p>
        </div>
        <div class="teacher-workspace-actions">
            <a href="{{ route('teacher.courses.content',$course) }}" class="teacher-workspace-btn secondary">محتوای دوره</a>
            <a href="{{ route('teacher.courses.index') }}" class="teacher-workspace-btn primary">دوره‌ها</a>
        </div>
    </header>

    <aside class="teacher-workspace-card" role="note" aria-label="مبنای محاسبه شاخص‌های آموزشی" style="margin-bottom:16px;padding:16px 18px;border-right:4px solid #2563eb;background:#f8fbff">
        <h2 style="font-size:14px;font-weight:900;color:#1f2937;margin:0 0 8px">این درصد دقیقاً چه چیزی را می‌سنجد؟</h2>
        <p style="font-size:12px;line-height:1.9;color:#475569;margin:0">
            میانگین درصد پیشرفت ثبت‌شده برای درس‌های منتشرشدهٔ همین دوره است؛ درس منتشرشده‌ای که هنوز رکورد پیشرفت ندارد، ۰٪ حساب می‌شود. این عدد نمرهٔ آزمون یا نرخ حضور نیست.
        </p>
        <div style="display:grid;gap:6px;margin-top:10px;font-size:11px;line-height:1.9;color:#475569">
            <p style="margin:0"><strong>پیشرفت درس:</strong> درصد ثبت‌شده برای هر درس منتشرشده؛ این شاخص نشان می‌دهد مسیر محتوای درس چقدر جلو رفته است.</p>
            <p style="margin:0"><strong>حضور در کلاس:</strong> دقیقه‌های حضور واقعی ثبت‌شده ÷ دقیقه‌های برنامه‌ریزی‌شده. رکوردهای قدیمی که دقیقهٔ واقعی ندارند، حدس زده نمی‌شوند؛ غیبت موجه هم از مخرج نرخ حضور خارج است.</p>
            <p style="margin:0"><strong>نمره:</strong> از پاسخ تکلیف یا آزمونی می‌آید که مدرس ارزیابی کرده باشد؛ پاسخ تشریحی تا پیش از تصحیح نمرهٔ نهایی ندارد.</p>
        </div>
    </aside>

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head">
            <div><h2>جدول پیشرفت</h2><p>{{ App\Support\PersianUi::digits($lessons->count()) }} درس · هر صفحه حداکثر ۲۰ دانش‌آموز</p></div>
            <span class="teacher-workspace-chip">{{ App\Support\PersianUi::digits($students->total()) }} دانش‌آموز</span>
        </div>
        <div class="teacher-workspace-table-wrap">
            <table class="teacher-workspace-table" style="min-width:{{ max(760, 380 + ($lessons->count() * 75)) }}px">
                <thead>
                    <tr>
                        <th>دانش‌آموز</th>
                        @foreach($lessons as $lesson)<th style="text-align:center">{{ App\Support\PersianUi::digits($loop->iteration) }}</th>@endforeach
                        <th>میانگین</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($students as $student)
                    @php($summary = $studentSummary->firstWhere('student.id',$student->id))
                    <tr>
                        <td><strong>{{ $student->name }}</strong></td>
                        @foreach($lessons as $lesson)
                            @php($row = $progress[$student->id . ':' . $lesson->id] ?? null)
                            @php($percent = min(100,max(0,(float)($row->progress_percent ?? 0))))
                            <td style="text-align:center">
                                <div style="min-width:58px">
                                    <strong style="display:block;font-size:8px">{{ App\Support\PersianUi::digits(round($percent)) }}٪</strong>
                                    <span style="display:block;margin-top:3px;color:#929baa;font-size:6px">{{ $percent >= 100 ? 'کامل' : ($percent > 0 ? 'درحال یادگیری' : 'ندیده') }}</span>
                                    <span style="display:block;height:4px;margin-top:5px;border-radius:99px;background:#edf0f4;overflow:hidden"><span style="display:block;height:100%;width:{{ $percent }}%;background:#6554d9"></span></span>
                                </div>
                            </td>
                        @endforeach
                        <td style="text-align:center"><strong>{{ App\Support\PersianUi::digits($summary['progress'] ?? 0) }}٪</strong><div style="margin-top:3px;color:#929baa;font-size:7px">{{ App\Support\PersianUi::digits($summary['completed_lessons'] ?? 0) }} درس کامل</div></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $lessons->count()+2 }}"><div class="teacher-workspace-empty"><strong>دانش‌آموزی ثبت نشده است.</strong>پس از فعال‌شدن enrollment این جدول تکمیل می‌شود.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())<div class="teacher-workspace-pagination">{{ $students->links('components.navigation.pagination') }}</div>@endif
    </section>
</div>
@endsection
