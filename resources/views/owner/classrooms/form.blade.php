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
                    <x-owner.field-error field="title"/>
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-black">کد کلاس</span>
                    <input name="code" value="{{ old('code', $classroom->code) }}" required
                           class="rounded-xl border border-slate-200 px-3 py-3 text-sm outline-none focus:border-[var(--panel-primary)]"
                           placeholder="WEB-101">
                    <x-owner.field-error field="code"/>
                </label>

                <label class="grid gap-1 md:col-span-2">
                    <span class="text-xs font-black">دوره</span>
                    <select id="owner-classroom-course" name="course_id" required
                            class="rounded-xl border border-slate-200 px-3 py-3 text-sm outline-none focus:border-[var(--panel-primary)]">
                        <option value="">انتخاب دوره</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}"
                                    data-teacher-ids="{{ $course->teachers->pluck('id')->implode(',') }}"
                                @selected((int) old('course_id', $classroom->course_id) === (int) $course->id)>
                                {{ $course->title }}{{ $course->status !== 'published' ? ' · پیش‌نویس' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <x-owner.field-error field="course_id"/>
                </label>

                <div class="md:col-span-2">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <span class="text-xs font-black">مدرس‌های کلاس</span>
                            <p class="mt-1 text-[10px] text-slate-500">فقط مدرس‌های اختصاص‌یافته به همان دوره قابل انتخاب‌اند.</p>
                        </div>
                        <span id="owner-teacher-hint" class="text-[10px] text-slate-400"></span>
                    </div>

                    <div id="owner-teacher-list" class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach($teachers as $teacher)
                            <label data-teacher-option="{{ $teacher->id }}"
                                   class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white p-3">
                                <input type="checkbox"
                                       name="teacher_ids[]"
                                       value="{{ $teacher->id }}"
                                       @checked(in_array((int) $teacher->id, $selectedTeacherIds, true))
                                       class="h-4 w-4 rounded border-slate-300 text-[var(--panel-primary)]">
                                <span class="text-xs font-bold text-slate-700">{{ $teacher->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-owner.field-error field="teacher_ids"/>
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
                    <x-owner.field-error field="grade_id"/>
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
                    <x-owner.field-error field="academic_year_id"/>
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-black">ظرفیت</span>
                    <input name="capacity" type="number" min="1" max="10000"
                           value="{{ old('capacity', $classroom->capacity) }}"
                           class="rounded-xl border border-slate-200 px-3 py-3 text-sm"
                           placeholder="مثلاً 20">
                    <x-owner.field-error field="capacity"/>
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-black">وضعیت</span>
                    <select name="status" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                        <option value="active" @selected(old('status', $classroom->status ?? 'active') === 'active')>فعال</option>
                        <option value="archived" @selected(old('status', $classroom->status ?? 'active') === 'archived')>آرشیو</option>
                    </select>
                    <x-owner.field-error field="status"/>
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-black">شروع اجرایی</span>
                    <input name="starts_at" type="datetime-local"
                           value="{{ old('starts_at', $classroom->starts_at?->format('Y-m-d\\TH:i')) }}"
                           class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <x-owner.field-error field="starts_at"/>
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-black">پایان اجرایی</span>
                    <input name="ends_at" type="datetime-local"
                           value="{{ old('ends_at', $classroom->ends_at?->format('Y-m-d\\TH:i')) }}"
                           class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <x-owner.field-error field="ends_at"/>
                </label>

                <label class="grid gap-1 md:col-span-2">
                    <span class="text-xs font-black">توضیحات</span>
                    <textarea name="description" rows="5"
                              class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ old('description', $classroom->description) }}</textarea>
                    <x-owner.field-error field="description"/>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const course = document.getElementById('owner-classroom-course');
    const hint = document.getElementById('owner-teacher-hint');
    const options = [...document.querySelectorAll('[data-teacher-option]')];

    const syncTeacherOptions = () => {
        const selected = course?.selectedOptions?.[0];
        const allowed = new Set(
            (selected?.dataset.teacherIds || '')
                .split(',')
                .map((value) => Number(value))
                .filter(Boolean)
        );

        let count = 0;

        options.forEach((wrapper) => {
            const teacherId = Number(wrapper.dataset.teacherOption);
            const checkbox = wrapper.querySelector('input[type="checkbox"]');
            const enabled = allowed.has(teacherId);

            wrapper.hidden = !enabled;
            checkbox.disabled = !enabled;

            if (!enabled && checkbox.checked) {
                checkbox.checked = false;
            }

            if (enabled) count += 1;
        });

        hint.textContent = selected?.value
            ? (count ? count + ' مدرس قابل انتخاب' : 'برای این دوره هنوز مدرسی اختصاص داده نشده است')
            : '';
    };

    course?.addEventListener('change', syncTeacherOptions);
    syncTeacherOptions();
});
</script>
@endpush
