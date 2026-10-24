@extends('layouts.teacher')
@section('title','دانش‌آموزان | شیخان')
@section('header-title','دانش‌آموزان')

@section('content')
@php
    $pageStudents = $students->getCollection();
    $pageProgressCount = $pageStudents->sum('progress_items_count');
    $pageExamAttempts = $pageStudents->sum('exam_attempts_count');
@endphp

<div class="teacher-workspace-page teacher-students-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">مرکز مدیریت کلاس</span>
            <h1 class="teacher-workspace-title">دانش‌آموزان من</h1>
            <p class="teacher-workspace-description">پیشرفت و فعالیت دانش‌آموزان کلاس‌های خودت را ببین؛ نتایج در صفحه‌های سبک و قابل جست‌وجو بارگذاری می‌شوند.</p>
        </div>
        <span class="teacher-workspace-chip">نمایش ۲۰ نفر در هر صفحه</span>
    </header>

    <section class="teacher-workspace-stats" aria-label="خلاصه فهرست دانش‌آموزان">
        <article class="teacher-workspace-stat">
            <small>دانش‌آموزان مطابق فیلتر</small>
            <strong>{{ App\Support\PersianUi::digits($students->total()) }}</strong>
            <span>عضویت فعال در کلاس‌های شما</span>
        </article>
        <article class="teacher-workspace-stat">
            <small>در این صفحه</small>
            <strong>{{ App\Support\PersianUi::digits($students->count()) }}</strong>
            <span>از حداکثر ۲۰ رکورد</span>
        </article>
        <article class="teacher-workspace-stat">
            <small>رکوردهای پیشرفت درس</small>
            <strong>{{ App\Support\PersianUi::digits($pageProgressCount) }}</strong>
            <span>مجموع این صفحه؛ نه نمره یا درصد یادگیری</span>
        </article>
        <article class="teacher-workspace-stat">
            <small>تلاش‌های آزمون</small>
            <strong>{{ App\Support\PersianUi::digits($pageExamAttempts) }}</strong>
            <span>مجموع فعالیت آزمون این صفحه</span>
        </article>
    </section>

    <section class="teacher-workspace-card" aria-labelledby="teacher-students-table-title">
        <div class="teacher-workspace-card-head">
            <div>
                <h2 id="teacher-students-table-title">فهرست دانش‌آموزان</h2>
                <p>جست‌وجو روی نام، ایمیل یا شماره دانش‌آموزی انجام می‌شود؛ فقط رکوردهای کلاس‌های فعال تحت مدیریت شما نمایش داده می‌شوند.</p>
            </div>
            <span class="teacher-workspace-chip">{{ App\Support\PersianUi::digits($students->total()) }} نفر</span>
        </div>

        <div class="teacher-student-roster-tools">
            <form method="GET" action="{{ route('teacher.students.index') }}" class="teacher-student-search" role="search">
                <input type="search" name="q" value="{{ $studentSearch }}" maxlength="100" placeholder="جست‌وجوی نام، ایمیل یا شماره دانش‌آموزی…" aria-label="جست‌وجوی دانش‌آموز">
                <input type="hidden" name="sort" value="{{ $studentSort }}">
                <input type="hidden" name="direction" value="{{ $studentDirection }}">
            </form>

            <nav class="teacher-student-sort" aria-label="مرتب‌سازی دانش‌آموزان">
                @foreach(['name'=>'نام','progress'=>'فعالیت درس','assignments'=>'ارسال تکلیف','exams'=>'تلاش آزمون'] as $key=>$label)
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>$key,'direction'=>$studentSort === $key && $studentDirection === 'asc' ? 'desc' : 'asc','page'=>null]) }}"
                       class="teacher-workspace-btn {{ $studentSort === $key ? 'primary' : 'secondary' }}"
                       @if($studentSort === $key) aria-current="true" @endif>
                        {{ $label }}{{ $studentSort === $key ? ($studentDirection === 'asc' ? ' ↑' : ' ↓') : '' }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="teacher-workspace-table-wrap">
            <table class="teacher-workspace-table">
                <thead>
                    <tr>
                        <th scope="col">دانش‌آموز</th>
                        <th scope="col">پایه</th>
                        <th scope="col">رکوردهای درس</th>
                        <th scope="col">ارسال تکلیف</th>
                        <th scope="col">تلاش آزمون</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($students as $student)
                    @php($studentAvatar = $student->studentProfile?->media?->first())
                    <tr>
                        <td>
                            <div class="teacher-student-name">
                                <img
                                    class="teacher-student-avatar"
                                    src="{{ $studentAvatar?->url() ?: asset('images/default-account-avatar.svg') }}"
                                    alt=""
                                    width="39"
                                    height="39"
                                    loading="lazy"
                                    decoding="async"
                                >
                                <span>
                                    <strong>{{ $student->name }}</strong>
                                    @if($student->email)
                                        <small style="display:block;margin-top:3px;color:#8993aa;font-size:10px">{{ $student->email }}</small>
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td>{{ $student->studentProfile?->gradeRelation?->title ?: $student->studentProfile?->grade ?: '—' }}</td>
                        <td>{{ App\Support\PersianUi::digits($student->progress_items_count) }}</td>
                        <td>{{ App\Support\PersianUi::digits($student->assignment_submissions_count) }}</td>
                        <td>{{ App\Support\PersianUi::digits($student->exam_attempts_count) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="teacher-workspace-empty">
                                <strong>{{ $studentSearch !== '' ? 'نتیجه‌ای پیدا نشد.' : 'هنوز دانش‌آموزی در این فهرست نیست.' }}</strong>
                                <p>{{ $studentSearch !== '' ? 'عبارت جست‌وجو را کوتاه‌تر کن یا با نام دیگری جست‌وجو کن.' : 'پس از عضویت فعال دانش‌آموز در یکی از کلاس‌های شما، نامش در این فهرست دیده می‌شود.' }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <footer class="teacher-workspace-pagination">
            <span class="teacher-student-count">
                @if($students->total() > 0)
                    نمایش {{ App\Support\PersianUi::digits($students->firstItem()) }} تا {{ App\Support\PersianUi::digits($students->lastItem()) }} از {{ App\Support\PersianUi::digits($students->total()) }} دانش‌آموز
                @else
                    هیچ رکوردی برای نمایش وجود ندارد
                @endif
            </span>
            @if($students->hasPages())
                <div>{{ $students->onEachSide(2)->links('components.navigation.pagination') }}</div>
            @endif
        </footer>
    </section>
</div>
@endsection
