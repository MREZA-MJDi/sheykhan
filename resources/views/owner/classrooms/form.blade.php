@extends('layouts.owner')

@section('title', ($classroom->exists ? 'ویرایش کلاس' : 'ساخت کلاس').' | شیخان')
@section('header-title', $classroom->exists ? 'ویرایش کلاس' : 'ساخت کلاس')

@section('content')
@php
    $selectedTeacherIds = old('teacher_ids', $selectedTeacherIds ?? []);
    $selectedTeacherIds = array_map('intval', (array) $selectedTeacherIds);
@endphp

<div class="mx-auto max-w-5xl space-y-6 panel-page-enter">
    <div>
        <p class="text-xs font-black text-[var(--panel-primary)]">مدیریت کلاس</p>
        <h1 class="mt-1 text-2xl font-black">{{ $classroom->exists ? 'ویرایش '.$classroom->title : 'ساخت کلاس جدید' }}</h1>
        <p class="mt-2 text-sm leading-7 text-slate-500">
            مشخصات اجرایی کلاس را ثبت کن. مدرس باید عضو فعال آموزشگاه و از قبل به دوره انتخاب‌شده اختصاص داده شده باشد.
        </p>
    </div>

    <form method="POST"
          action="{{ $classroom->exists ? route('owner.classrooms.update', [$academy, $classroom]) : route('owner.classrooms.store', $academy) }}"
          class="space-y-5">
        @csrf
        @if($classroom->exists)
            @method('PATCH')
        @endif

        <section class="dashboard-panel p-5 sm:p-7">
            <div class="grid gap-5 md:grid-cols-2">
                <label class="grid gap-1">
                    <span class="text-xs font-black">عنوان کلاس</span>
                    <input name="title" value="{{ old('title', $classroom->title) }}" required
                           class="rounded-xl border border-slate-200 px-3 py-3 text-sm outline-none focus:border-[var(--panel-primary)]"
                           placeholder="مثلاً کلاس وب مقدماتی">
                    <small class="owner-field-help">نامی انتخاب کن که پایه، سطح یا گروه را برای تیم آموزش سریع مشخص کند.</small>
                    @error('title')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-black">کد داخلی کلاس · خودکار</span>
                    <input name="code" value="{{ old('code', $classroom->code) }}" required readonly dir="ltr"
                           class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-black tracking-wider text-slate-700 outline-none"
                           aria-describedby="owner-classroom-code-help">
                    <small id="owner-classroom-code-help" class="owner-field-help">کد را سامانه تولید می‌کند؛ نیازی به ساخت یا حفظ‌کردن کد دستی نیست.</small>
                    @error('code')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
                </label>

                <div class="grid gap-2 md:col-span-2" data-academy-option-picker
                     data-endpoint="{{ route('owner.form-options', $academy) }}" data-type="classroom-courses">
                    <label for="owner-classroom-course-search" class="text-xs font-black">جست‌وجوی دوره</label>
                    <input id="owner-classroom-course-search" type="search" data-academy-option-search
                           autocomplete="off" maxlength="80" placeholder="نام دوره را بنویس…"
                           class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <select id="owner-classroom-course" name="course_id" required data-academy-option-select
                            class="rounded-xl border border-slate-200 px-3 py-3 text-sm outline-none focus:border-[var(--panel-primary)]">
                        <option data-option-placeholder value="">انتخاب دوره</option>
                        @foreach($courses as $course)
                            <option data-dynamic-academy-option="1" value="{{ $course->id }}"
                                @selected((int) old('course_id', $classroom->course_id) === (int) $course->id)>
                                {{ $course->title }}{{ $course->status !== 'published' ? ' · پیش‌نویس' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p data-academy-option-status class="text-[11px] text-slate-500" role="status" aria-live="polite">برای پیدا کردن دوره، دست‌کم دو حرف از عنوان آن را جست‌وجو کن.</p>
                    <button type="button" data-academy-option-more hidden class="w-fit rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold">نمایش دوره‌های بعدی</button>
                    <small class="owner-field-help">فقط دوره‌های همین آموزشگاه نمایش داده می‌شوند؛ با جست‌وجو لازم نیست فهرست کامل بارگذاری شود.</small>
                    @error('course_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-2 md:col-span-2" data-academy-option-picker
                     data-endpoint="{{ route('owner.form-options', $academy) }}" data-type="classroom-teachers"
                     data-depends-on-course="true">
                    <label for="owner-classroom-teacher-search" class="text-xs font-black">جست‌وجوی مدرس</label>
                    <input id="owner-classroom-teacher-search" type="search" data-academy-option-search
                           autocomplete="off" maxlength="80" placeholder="نام مدرس را بنویس…"
                           class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <select id="owner-classroom-teacher" name="teacher_ids[]" required data-academy-option-select
                            class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                        <option data-option-placeholder value="">ابتدا دوره را انتخاب کن</option>
                        @foreach($teachers as $teacher)
                            <option data-dynamic-academy-option="1" value="{{ $teacher->id }}"
                                @selected(in_array((int) $teacher->id, $selectedTeacherIds, true))>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    <p data-academy-option-status class="text-[11px] text-slate-500" role="status" aria-live="polite">پس از انتخاب دوره، مدرس‌های اختصاص‌یافته به همان دوره را جست‌وجو کن.</p>
                    <button type="button" data-academy-option-more hidden class="w-fit rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold">نمایش مدرس‌های بعدی</button>
                    <small class="owner-field-help">فقط مدرس فعال همان دوره و آموزشگاه قابل انتخاب است؛ یک مدرس را انتخاب کن.</small>
                    @error('teacher_ids')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
                </div>

                <label class="grid gap-1">
                    <span class="text-xs font-black">پایه</span>
                    <select name="grade_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                        <option value="">بدون پایه</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}" @selected((int) old('grade_id', $classroom->grade_id) === (int) $grade->id)>
                                {{ $grade->title }}
                            </option>
                        @endforeach
                    </select>
                    <small class="owner-field-help">این مقدار اختیاری است و برای دسته‌بندی آموزشی و گزارش‌ها به کار می‌رود.</small>
                    @error('grade_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-black">سال تحصیلی</span>
                    <select name="academic_year_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                        <option value="">بدون سال تحصیلی</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((int) old('academic_year_id', $classroom->academic_year_id) === (int) $year->id)>
                                {{ $year->title }}{{ $year->is_current ? ' · جاری' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="owner-field-help">سال تحصیلی را برای مرتب‌سازی و گزارش‌گیری انتخاب کن؛ در صورت نیاز خالی بگذار.</small>
                    @error('academic_year_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-black">ظرفیت</span>
                    <input name="capacity" type="number" min="1" max="10000"
                           value="{{ old('capacity', $classroom->capacity) }}"
                           class="rounded-xl border border-slate-200 px-3 py-3 text-sm"
                           placeholder="مثلاً 20">
                    <small class="owner-field-help">بیشترین تعداد عضو فعال است؛ ظرفیت را کمتر از تعداد ثبت‌نام‌های فعلی قرار نده.</small>
                    @error('capacity')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-black">وضعیت</span>
                    <select name="status" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                        <option value="active" @selected(old('status', $classroom->status ?? 'active') === 'active')>فعال</option>
                        <option value="archived" @selected(old('status', $classroom->status ?? 'active') === 'archived')>آرشیو</option>
                    </select>
                    <small class="owner-field-help">با آرشیو کردن، کلاس از عملیات جاری خارج می‌شود؛ سوابق آن حفظ می‌شوند.</small>
                    @error('status')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-black">شروع اجرایی</span>
                    <input name="starts_at" type="datetime-local"
                           value="{{ old('starts_at', $classroom->starts_at?->format('Y-m-d\\TH:i')) }}"
                           class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <small class="owner-field-help">زمان شروع اختیاری است و برای نمایش برنامهٔ کلاس به کار می‌رود.</small>
                    @error('starts_at')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-black">پایان اجرایی</span>
                    <input name="ends_at" type="datetime-local"
                           value="{{ old('ends_at', $classroom->ends_at?->format('Y-m-d\\TH:i')) }}"
                           class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <small class="owner-field-help">اگر پایان را وارد می‌کنی، باید بعد از زمان شروع باشد.</small>
                    @error('ends_at')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
                </label>

                <label class="grid gap-1 md:col-span-2">
                    <span class="text-xs font-black">توضیحات</span>
                    <textarea name="description" rows="5"
                              class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ old('description', $classroom->description) }}</textarea>
                    <small class="owner-field-help">توضیح اجرایی کلاس را بنویس و اطلاعات محرمانهٔ دانش‌آموزان را در این بخش قرار نده.</small>
                    @error('description')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
                </label>
            </div>
        </section>

        <div class="flex flex-wrap gap-2">
            <button type="submit"
                    class="rounded-xl bg-slate-900 px-5 py-3 text-xs font-black text-white"
                    data-confirm="{{ $classroom->exists ? 'اطلاعات کلاس به‌روزرسانی شود؟' : 'کلاس جدید ساخته شود؟' }}">
                {{ $classroom->exists ? 'ذخیره تغییرات' : 'ساخت کلاس' }}
            </button>
            <a href="{{ $classroom->exists ? route('owner.classrooms.show', [$academy, $classroom]) : route('owner.classrooms.index', $academy) }}"
               class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-xs font-black text-slate-700">
                انصراف
            </a>
        </div>
    </form>
</div>
@endsection

