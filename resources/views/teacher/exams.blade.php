@extends('layouts.teacher')
@section('title', !empty($builderMode) ? ($exam->exists ? 'ویرایش آزمون' : 'ساخت آزمون') : 'آزمون‌ها')
@section('heading', !empty($builderMode) ? ($exam->exists ? 'ویرایش آزمون' : 'طراحی آزمون') : 'آزمون‌ها')
@section('content')
@if (!empty($builderMode))
    <div class="tp-toolbar"><div><h2>{{ $exam->exists ? 'ویرایش برگه آزمون' : 'آزمون جدید' }}</h2><p class="tp-muted">سؤال‌ها و پاسخ‌ها با ساختار اصلی ExamQuestionWriter ذخیره می‌شوند.</p></div><a class="tp-btn tp-btn--light" href="{{ route('teacher.exams.index') }}">بازگشت</a></div>
    <form method="POST" action="{{ $exam->exists ? route('teacher.exams.update', $exam) : route('teacher.exams.store') }}" enctype="multipart/form-data" data-tp-exam>
        @csrf @if($exam->exists) @method('PATCH') @endif
        <div class="tp-grid tp-grid--main tp-grid--start">
            <div class="tp-paper">
                <div class="tp-paper__head"><h2 data-tp-bind="title">{{ $exam->title ?: 'عنوان آزمون' }}</h2><small>{{ config('app.name', 'آکادمی شیخان') }}</small></div>
                <div class="tp-paper__meta">
                    <span>دوره: <b data-tp-bind="course">{{ $exam->course?->title ?? 'انتخاب نشده' }}</b></span>
                    <span>مدت: <b data-tp-bind="duration_minutes">{{ $exam->duration_minutes ?? 60 }}</b> دقیقه</span>
                    <span>مجموع نمره: <b data-tp-total>۰</b></span>
                    <span>نام دانش‌آموز: ........................</span>
                </div>
                <div data-tp-questions>
                    @foreach (old('questions', $initialQuestions ?? []) as $index => $question)
                        @include('teacher.partials.exam-question', ['i' => $index, 'q' => $question])
                    @endforeach
                </div>
                <div class="tp-empty" data-tp-empty-questions @if(count(old('questions', $initialQuestions ?? []))) hidden @endif><strong>هنوز سؤالی اضافه نشده است.</strong><span>از پنل تنظیمات یک سؤال جدید اضافه کن.</span></div>
            </div>
            <x-teacher.card title="مشخصات آزمون">
                <div class="tp-stack-form">
                    <x-teacher.field label="دوره"><select name="course_id" required data-tp-model="course" data-course-select>@foreach ($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id', $exam->course_id) == $course->id)>{{ $course->title }}</option>@endforeach</select></x-teacher.field>
                    <x-teacher.field label="کلاس / گروه (اختیاری)"><select name="classroom_id" data-course-classroom-select data-course-select="[name='course_id']"><option value="">همه کلاس‌های مجاز دوره</option>@foreach ($classrooms as $classroom)<option value="{{ $classroom->id }}" data-course-id="{{ $classroom->course_id }}" @selected(old('classroom_id', $exam->classroom_id) == $classroom->id)>{{ $classroom->title }}</option>@endforeach</select></x-teacher.field>
                    <x-teacher.field label="عنوان آزمون"><input name="title" value="{{ old('title', $exam->title) }}" required maxlength="255" data-tp-model="title"></x-teacher.field>
                    <x-teacher.field label="توضیحات"><textarea name="description" rows="3">{{ old('description', $exam->description) }}</textarea></x-teacher.field>
                    <x-teacher.field label="مدت آزمون (دقیقه)"><input type="number" name="duration_minutes" min="0" value="{{ old('duration_minutes', $exam->duration_minutes ?? 60) }}" data-tp-model="duration_minutes"></x-teacher.field>
                    <x-teacher.field label="تعداد دفعات مجاز"><input type="number" name="attempts_allowed" min="1" max="255" value="{{ old('attempts_allowed', $exam->attempts_allowed ?? 1) }}"></x-teacher.field>
                    <x-teacher.field label="شروع آزمون"><input type="datetime-local" name="starts_at" value="{{ old('starts_at', $exam->starts_at?->format('Y-m-d\\TH:i')) }}"></x-teacher.field>
                    <x-teacher.field label="پایان آزمون"><input type="datetime-local" name="ends_at" value="{{ old('ends_at', $exam->ends_at?->format('Y-m-d\\TH:i')) }}"></x-teacher.field>
                    <x-teacher.field label="وضعیت"><select name="status"><option value="draft" @selected(old('status',$exam->status)==='draft')>پیش‌نویس</option><option value="published" @selected(old('status',$exam->status)==='published')>منتشرشده</option><option value="closed" @selected(old('status',$exam->status)==='closed')>بسته</option></select></x-teacher.field>
                    <div><x-teacher.field label="فایل راهنمای آزمون (اختیاری)"><x-teacher.dropzone name="handout" :multiple="false" label="انتخاب فایل آزمون" /></x-teacher.field></div>
                    <div class="tp-question-total">مجموع نمره: <span data-tp-total>۰</span></div>
                    <x-teacher.button variant="light" data-tp-add-q="single">+ سؤال چهارگزینه‌ای</x-teacher.button>
                    <x-teacher.button variant="light" data-tp-add-q="multiple">+ سؤال چندپاسخی</x-teacher.button>
                    <x-teacher.button variant="light" data-tp-add-q="text">+ سؤال تشریحی</x-teacher.button>
                    <x-teacher.button type="submit">{{ $exam->exists ? 'ذخیره تغییرات' : 'ذخیره آزمون' }}</x-teacher.button>
                    <x-teacher.button variant="light" data-tp-print>چاپ برگه</x-teacher.button>
                </div>
            </x-teacher.card>
        </div>
        <template data-tp-tpl="single">@include('teacher.partials.exam-question', ['i' => '__i__', 'q' => ['type' => 'single', 'score' => 1, 'options' => ['', '', '', '']]])</template>
        <template data-tp-tpl="multiple">@include('teacher.partials.exam-question', ['i' => '__i__', 'q' => ['type' => 'multiple', 'score' => 1, 'options' => ['', '', '', '']]])</template>
        <template data-tp-tpl="text">@include('teacher.partials.exam-question', ['i' => '__i__', 'q' => ['type' => 'text', 'score' => 1]])</template>
    </form>
@else
    <div class="tp-toolbar"><div><h2>آزمون‌های من</h2><p class="tp-muted">مدیریت برگه‌ها، تلاش‌های ارسال‌شده و نمره‌دهی.</p></div>@if($tpUser->hasPermission('exams.manage'))<a class="tp-btn" href="{{ route('teacher.exams.create') }}">+ آزمون جدید</a>@endif</div>
    <div class="tp-metric-grid">
        <article class="tp-metric"><div><b>{{ \App\Support\PersianUi::digits($exams->total()) }}</b><span>کل آزمون‌ها</span></div></article>
        <article class="tp-metric"><div><b>{{ \App\Support\PersianUi::digits($exams->getCollection()->sum('attempts_count')) }}</b><span>تلاش ثبت‌شده</span></div></article>
        <article class="tp-metric"><div><b>{{ \App\Support\PersianUi::digits($exams->getCollection()->sum('submitted_attempts_count')) }}</b><span>ارسال‌شده</span></div></article>
        <article class="tp-metric"><div><b>{{ \App\Support\PersianUi::digits($exams->getCollection()->sum('graded_attempts_count')) }}</b><span>تصحیح‌شده</span></div></article>
    </div>
    <x-teacher.card title="فهرست آزمون‌ها">
        <x-teacher.table :heads="['آزمون','دوره / کلاس','سؤال','تلاش','تصحیح‌شده','وضعیت','اقدام']">
            @forelse ($exams as $item)
                @php($status = ['draft' => ['پیش‌نویس','warn'], 'published' => ['منتشرشده','ok'], 'closed' => ['بسته','bad']][$item->status] ?? [$item->status,'default'])
                <tr>
                    <td><strong>{{ $item->title }}</strong><small class="tp-muted" style="display:block">{{ $item->starts_at ? \App\Support\PersianUi::date($item->starts_at) : 'بدون زمان شروع' }}</small></td>
                    <td>{{ $item->course?->title }}<small class="tp-muted" style="display:block">{{ $item->classroom?->title ?? 'تمام دانش‌آموزان مجاز دوره' }}</small></td>
                    <td>{{ \App\Support\PersianUi::digits($item->questions_count) }}</td>
                    <td>{{ \App\Support\PersianUi::digits($item->attempts_count) }}</td>
                    <td>{{ \App\Support\PersianUi::digits($item->graded_attempts_count) }}</td>
                    <td><x-teacher.pill :type="$status[1]">{{ $status[0] }}</x-teacher.pill></td>
                    <td class="tp-status-row"><a class="tp-link" href="{{ route('teacher.exams.attempts', $item) }}">نتایج</a>@if($tpUser->hasPermission('exams.manage') && !$item->attempts_count)<a class="tp-link" href="{{ route('teacher.exams.edit', $item) }}">ویرایش</a>@endif</td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="tp-empty"><strong>آزمونی ثبت نشده است.</strong></div></td></tr>
            @endforelse
        </x-teacher.table>
        <div class="tp-pager">{{ $exams->links() }}</div>
    </x-teacher.card>
@endif
@endsection