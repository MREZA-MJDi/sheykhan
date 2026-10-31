@extends('layouts.teacher')
@section('title', 'دانش‌آموزان')
@section('heading', 'دانش‌آموزان')
@section('content')
    <x-teacher.section-head title="دانش‌آموزان کلاس‌های من" subtitle="فهرست فقط شامل اعضای فعال کلاس‌هایی است که به تو تخصیص داده شده‌اند." />
    <form method="GET" class="tp-toolbar">
        <input type="search" name="q" value="{{ $studentSearch ?? request('q') }}" placeholder="نام، ایمیل یا شماره دانش‌آموزی…" aria-label="جستجوی دانش‌آموز">
        <select name="sort" aria-label="مرتب‌سازی"><option value="name" @selected(($studentSort ?? 'name')==='name')>نام</option><option value="progress" @selected(($studentSort ?? '')==='progress')>تعداد پیشرفت‌های درس</option><option value="assignments" @selected(($studentSort ?? '')==='assignments')>تحویل تکالیف</option><option value="exams" @selected(($studentSort ?? '')==='exams')>تلاش‌های آزمون</option></select>
        <select name="direction" aria-label="جهت مرتب‌سازی"><option value="asc" @selected(($studentDirection ?? 'asc')==='asc')>صعودی</option><option value="desc" @selected(($studentDirection ?? '')==='desc')>نزولی</option></select>
        <button class="tp-btn" type="submit">جستجو</button>
    </form>
    <x-teacher.card title="فهرست دانش‌آموزان">
        <x-teacher.table :heads="['دانش‌آموز','شماره دانش‌آموزی','ثبت پیشرفت درس','تحویل تکلیف','تلاش آزمون']">
            @forelse ($students as $student)
                @php($studentAvatar = $student->studentProfile?->media?->first())
                <tr>
                    <td><span class="tp-cell"><x-teacher.avatar :name="$student->name" :photo="$studentAvatar && $studentAvatar->visibility === 'public' ? $studentAvatar->url() : null" :size="38" /><strong>{{ $student->name }}</strong></span></td>
                    <td>{{ $student->studentProfile?->student_number ?? '—' }}</td>
                    <td>{{ \App\Support\PersianUi::digits($student->progress_items_count) }}</td>
                    <td>{{ \App\Support\PersianUi::digits($student->assignment_submissions_count) }}</td>
                    <td>{{ \App\Support\PersianUi::digits($student->exam_attempts_count) }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="tp-empty"><strong>دانش‌آموزی با این فیلتر پیدا نشد.</strong></div></td></tr>
            @endforelse
        </x-teacher.table>
        <div class="tp-pager">{{ $students->links() }}</div>
    </x-teacher.card>
@endsection