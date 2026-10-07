@extends('layouts.owner')
@section('title','اعضای آموزشگاه | شیخان')
@section('header-title','اعضای آموزشگاه')
@section('content')
<div class="space-y-6 panel-page-enter">
<div><p class="text-xs font-bold text-[var(--panel-primary)]">مدیریت افراد</p><h1 class="mt-1 text-2xl font-black">{{ $academy->name }}</h1><p class="mt-2 text-sm text-slate-500">مدرس‌ها، دانش‌آموزها، والدین، اتصال مدرس به دوره و ثبت‌نام امن.</p></div>
<div class="grid gap-5 xl:grid-cols-3">
<section class="dashboard-panel p-5 sm:p-7"><div><h2 class="text-base font-black">ساخت حساب مدرس</h2><p class="mt-1 text-xs text-slate-500">حساب، نقش، پروفایل و membership در یک transaction.</p></div><form method="POST" action="{{ route('owner.people.store-teacher',$academy) }}" class="mt-5 grid gap-3" data-confirm="حساب مدرس ساخته شود؟">@csrf
<label class="grid gap-1"><span class="text-xs font-bold">نام و نام خانوادگی</span><input name="name" value="{{ old('name') }}" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><x-owner.field-error field="name"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">ایمیل</span><input name="email" type="email" value="{{ old('email') }}" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><x-owner.field-error field="email"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">رمز عبور</span><input name="password" type="password" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><x-owner.field-error field="password"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">تکرار رمز عبور</span><input name="password_confirmation" type="password" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm"></label>
<label class="grid gap-1"><span class="text-xs font-bold">تخصص</span><input name="specialization" value="{{ old('specialization') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><x-owner.field-error field="specialization"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">سابقه (سال)</span><input name="experience_years" type="number" min="0" max="80" value="{{ old('experience_years') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><x-owner.field-error field="experience_years"/></label>
<button class="rounded-xl bg-slate-900 px-4 py-3 text-xs font-black text-white">ساخت حساب مدرس</button></form></section>

<section class="dashboard-panel p-5 sm:p-7"><div><h2 class="text-base font-black">اختصاص مدرس به دوره</h2><p class="mt-1 text-xs text-slate-500">فقط مدرس فعال و دوره همین آموزشگاه.</p></div><form method="POST" action="{{ route('owner.people.assign-teacher',$academy) }}" class="mt-5 grid gap-3">@csrf
<label class="grid gap-1"><span class="text-xs font-bold">مدرس</span><select name="teacher_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@forelse($teacherOptions as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@empty<option value="">مدرسی وجود ندارد</option>@endforelse</select><x-owner.field-error field="teacher_id"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">دوره</span><select name="course_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@forelse($courseOptions as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@empty<option value="">دوره‌ای وجود ندارد</option>@endforelse</select><x-owner.field-error field="course_id"/></label>
<button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white" @disabled(!$teacherOptions->count() || !$courseOptions->count())>اختصاص دوره</button></form></section>

<section class="dashboard-panel p-5 sm:p-7"><div><h2 class="text-base font-black">ثبت‌نام دانش‌آموز</h2><p class="mt-1 text-xs text-slate-500">duplicate-safe، کنترل ظرفیت و ثبت مالی در backend.</p></div><form method="POST" action="{{ route('owner.people.enroll-student',$academy) }}" class="mt-5 grid gap-3">@csrf
<input type="hidden" name="idempotency_key" value="{{ old('idempotency_key',(string)\Illuminate\Support\Str::uuid()) }}">
<label class="grid gap-1"><span class="text-xs font-bold">دانش‌آموز</span><select name="student_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@forelse($students as $student)<option value="{{ $student->id }}">{{ $student->name }}</option>@empty<option value="">دانش‌آموزی وجود ندارد</option>@endforelse</select><x-owner.field-error field="student_id"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">دوره</span><select id="enrollment-course" name="course_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@forelse($courseOptions as $course)<option value="{{ $course->id }}">{{ $course->title }} — {{ $course->isFree()?'رایگان':number_format((float)$course->price,0,'.',',').' تومان' }}</option>@empty<option value="">دوره‌ای وجود ندارد</option>@endforelse</select><x-owner.field-error field="course_id"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">کلاس</span><select data-classroom-select data-course-select="enrollment-course" name="classroom_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">بدون کلاس</option>@foreach($courseOptions as $course)@foreach($course->classrooms as $classroom)<option data-course-id="{{ $course->id }}" value="{{ $classroom->id }}">{{ $classroom->title }} — {{ $classroom->capacity??'بدون محدودیت' }}</option>@endforeach @endforeach</select><span data-no-classrooms hidden class="text-xs text-slate-500">برای این دوره کلاسی تعریف نشده است.</span><x-owner.field-error field="classroom_id"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">مبلغ پرداختی</span><input name="paid_amount" type="number" step="0.01" min="0" value="{{ old('paid_amount') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><x-owner.field-error field="paid_amount"/></label>
<button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white" @disabled(!$students->count() || !$courseOptions->count())>ثبت‌نام دانش‌آموز</button></form></section>
</div>

<section class="dashboard-panel p-5 sm:p-7">
    <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--panel-primary)]">مهاجرت تدریجی اطلاعات</p>
            <h2 class="mt-1 text-base font-black">ورود دانش‌آموز قدیمی</h2>
            <p class="mt-1 text-xs leading-6 text-slate-500">
                با نام، کد ملی و پایه، دانش‌آموز را پیدا یا ایجاد کن. کد ملی خام در سیستم ذخیره نمی‌شود و عملیات قابل پیگیری است.
            </p>
        </div>
        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-[9px] font-black text-emerald-700">
            امن · بدون SQL
        </span>
    </div>

    <form method="POST" action="{{ route('owner.people.legacy-students.store', $academy) }}" class="mt-5 grid gap-4 md:grid-cols-2" data-confirm="اطلاعات دانش‌آموز ثبت و به‌روزرسانی شود؟">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">

        <label class="grid gap-1">
            <span class="text-xs font-bold">نام و نام خانوادگی</span>
            <input name="name" value="{{ old('name') }}" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm" autocomplete="name">
            <x-owner.field-error field="name"/>
        </label>

        <label class="grid gap-1">
            <span class="text-xs font-bold">کد ملی</span>
            <input name="national_id" value="{{ old('national_id') }}" required inputmode="numeric" maxlength="10" class="rounded-xl border border-slate-200 px-3 py-3 text-sm" autocomplete="off">
            <span class="text-[9px] text-slate-400">فقط برای تطبیق هویت؛ مقدار خام ذخیره نمی‌شود.</span>
            <x-owner.field-error field="national_id"/>
        </label>

        <label class="grid gap-1">
            <span class="text-xs font-bold">پایه تحصیلی</span>
            <select name="grade_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                <option value="">انتخاب پایه</option>
                @foreach($grades as $grade)
                    <option value="{{ $grade->id }}" @selected((string) old('grade_id') === (string) $grade->id)>{{ $grade->title }}</option>
                @endforeach
            </select>
            <x-owner.field-error field="grade_id"/>
        </label>

        <label class="grid gap-1">
            <span class="text-xs font-bold">موبایل <span class="font-normal text-slate-400">(اختیاری)</span></span>
            <input name="mobile" value="{{ old('mobile') }}" inputmode="tel" class="rounded-xl border border-slate-200 px-3 py-3 text-sm" autocomplete="tel">
            <x-owner.field-error field="mobile"/>
        </label>

        <label class="grid gap-1 md:col-span-2">
            <span class="text-xs font-bold">نام مدرسه <span class="font-normal text-slate-400">(اختیاری)</span></span>
            <input name="school_name" value="{{ old('school_name') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            <x-owner.field-error field="school_name"/>
        </label>

        <label class="grid gap-1 md:col-span-2">
            <span class="text-xs font-bold">یادداشت اپراتور <span class="font-normal text-slate-400">(اختیاری)</span></span>
            <textarea name="notes" rows="3" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ old('notes') }}</textarea>
            <x-owner.field-error field="notes"/>
        </label>

        <button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white md:col-span-2">
            ثبت / به‌روزرسانی دانش‌آموز قدیمی
        </button>
    </form>
</section>

<section class="dashboard-panel p-5 sm:p-7">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="font-black">آخرین عملیات مهاجرت</h2>
            <p class="mt-1 text-xs text-slate-500">سابقه ثبت دانش‌آموزان قدیمی در این آموزشگاه.</p>
        </div>
        <span class="rounded-full bg-slate-50 px-2.5 py-1 text-[9px] font-bold text-slate-500">{{ $onboardings->total() }}</span>
    </div>

    <div class="mt-4 grid gap-2">
        @forelse($onboardings as $entry)
            <article class="flex flex-col gap-2 rounded-xl bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <strong class="text-xs">{{ $entry->student?->name ?? $entry->entered_name }}</strong>
                    <p class="mt-1 text-[9px] text-slate-500">
                        پایه {{ $entry->requestedGrade?->title ?? '—' }}
                        · ثبت توسط {{ $entry->admin?->name ?? '—' }}
                    </p>
                </div>
                <div class="text-[9px] text-slate-500">
                    {{ optional($entry->activated_at)->format('Y/m/d H:i') }}
                    <span class="mx-1">·</span>
                    <span class="font-bold text-emerald-700">{{ $entry->status === 'activated' ? 'فعال' : $entry->status }}</span>
                </div>
            </article>
        @empty
            <div class="rounded-xl bg-slate-50 p-5 text-center text-xs text-slate-500">
                هنوز عملیات مهاجرتی ثبت نشده است.
            </div>
        @endforelse
    </div>

    @if($onboardings->hasPages())
        <div class="mt-4 border-t border-slate-100 pt-3 text-xs">
            {{ $onboardings->withQueryString()->links() }}
        </div>
    @endif
</section>

<div class="mb-2 flex items-center gap-2">
    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-black text-emerald-700">عضو فعال</span>
    <span class="text-[10px] text-slate-400">افراد فعال آموزشگاه</span>
</div>
<div class="grid gap-4 xl:grid-cols-3">
@foreach([['مدرس‌ها',$teachers],['دانش‌آموزان',$students],['والدین',$parents]] as [$title,$items])
<section class="dashboard-panel p-5">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-black">{{ $title }}</h2>
        <span class="rounded-full bg-slate-50 px-2.5 py-1 text-[9px] font-bold text-slate-500">{{ $items->total() }}</span>
    </div>
    <div class="mt-4 grid gap-2">
        @forelse($items as $person)
            <div class="rounded-xl bg-slate-50 p-3">
                <strong class="text-xs">{{ $person->name }}</strong>
                <span class="mt-1 block text-[9px] text-slate-500">{{ $person->email }}</span>
            </div>
        @empty
            <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">عضوی نیست.</div>
        @endforelse
    </div>
    @if($items->hasPages())
        <div class="mt-4 border-t border-slate-100 pt-3 text-xs">
            {{ $items->withQueryString()->links() }}
        </div>
    @endif
</section>
@endforeach
</div>

<section class="dashboard-panel p-5">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="font-black">مدرس‌های آرشیوشده</h2>
            <p class="mt-1 text-xs text-slate-500">سابقه مدرس حفظ شده و با «آرشیوشده» از اعضای فعال جدا شده است.</p>
        </div>
        <span class="rounded-full bg-slate-50 px-2.5 py-1 text-[9px] font-bold text-slate-500">{{ $archivedTeachers->total() }}</span>
    </div>
    <div class="mt-4 grid gap-2">
        @forelse($archivedTeachers as $teacher)
            <div class="flex flex-col gap-2 rounded-xl bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <strong class="text-xs">{{ $teacher->name }}</strong>
                    <span class="mt-1 block text-[9px] text-slate-500">{{ $teacher->email }}</span>
                </div>
                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[9px] font-black text-amber-700">آرشیوشده</span>
            </div>
        @empty
            <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">مدرس آرشیوشده‌ای وجود ندارد.</div>
        @endforelse
    </div>
    @if($archivedTeachers->hasPages())
        <div class="mt-4 border-t border-slate-100 pt-3 text-xs">{{ $archivedTeachers->withQueryString()->links() }}</div>
    @endif
</section>

</div>
@endsection