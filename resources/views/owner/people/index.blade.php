@extends('layouts.owner')
@section('title','اعضای آموزشگاه | شیخان')
@section('header-title','اعضای آموزشگاه')
@section('content')
<div class="space-y-6 panel-page-enter owner-people-workspace">
<div><p class="text-xs font-bold text-[var(--panel-primary)]">مدیریت افراد</p><h1 class="mt-1 text-2xl font-black">{{ $academy->name }}</h1><p class="mt-2 text-sm text-slate-500">مدرس‌ها، دانش‌آموزها، والدین، اختصاص مسئول کلاس و ثبت‌نام امن.</p></div>

@if(empty($academicGradesAvailable) || empty($studentOnboardingAvailable) || empty($studentOnboardingHistoryAvailable))
<section class="owner-schema-warning" role="status" aria-live="polite">
    <strong>بخشی از امکانات ورود دانش‌آموز قدیمی موقتاً غیرفعال است.</strong>
    <p>فهرست اعضای آموزشگاه در دسترس است؛ برای فعال‌شدن تاریخچه ورود دانش‌آموز، Migrationهای پایه‌های تحصیلی و سوابق ورود باید هر دو روی سرور کامل باشند.</p>
    @if(empty($academicGradesAvailable))<p>ساختار جدول پایه‌های تحصیلی کامل نیست.</p>@endif
    @if(empty($studentOnboardingAvailable))<p>ساختار جدول سوابق ورود دانش‌آموز کامل نیست.</p>@endif
</section>
@endif

@if(session('temporary_parent_password'))
<section class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-950" role="status">
    <h2 class="font-black">رمز موقت والد — فقط همین یک‌بار</h2>
    <p class="mt-1 text-sm leading-6">این رمز را از راه امن به والد تحویل دهید. حساب مجبور است در اولین ورود رمز را عوض کند؛ برای امنیت، صفحه را دوباره بارگذاری نکنید تا رمز از پیام موقت حذف شود.</p>
    <div class="mt-3 grid gap-2 sm:grid-cols-2">
        <div class="rounded-xl bg-white/80 p-3"><span class="block text-xs text-amber-800">شماره موبایل / نام کاربری</span><strong class="mt-1 block break-all text-sm">{{ session('temporary_parent_mobile') }}</strong></div>
        <div class="rounded-xl bg-white/80 p-3"><span class="block text-xs text-amber-800">رمز موقت</span><code class="mt-1 block break-all text-base font-black">{{ session('temporary_parent_password') }}</code></div>
    </div>
</section>
@endif


@if(session('temporary_teacher_password'))
<section class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-950" role="status" aria-live="polite">
    <h2 class="font-black">اطلاعات ورود مدرس — فقط همین یک‌بار</h2>
    <p class="mt-1 text-sm">این اطلاعات را از مسیر امن به مدرس تحویل بده. رمز در اولین ورود باید تغییر کند.</p>
    <div class="mt-3 grid gap-2 sm:grid-cols-3">
        <div class="rounded-xl bg-white/80 p-3"><span class="block text-xs">نام</span><strong>{{ session('temporary_teacher_name') }}</strong></div>
        <div class="rounded-xl bg-white/80 p-3"><span class="block text-xs">موبایل</span><strong dir="ltr">{{ session('temporary_teacher_mobile') }}</strong></div>
        <div class="rounded-xl bg-white/80 p-3"><span class="block text-xs">رمز اولیه</span><code dir="ltr">{{ session('temporary_teacher_password') }}</code></div>
    </div>
</section>
@endif

<div class="owner-people-tabs owner-people-workspace-panel">
    <header class="owner-people-actionbar">
        <div><strong>عملیات اعضا</strong><span>ثبت حساب و اتصال اعضا در پنجره شناور انجام می‌شود.</span></div>
        <div class="owner-people-actionbar__buttons">
            @if(auth()->user()->hasPermission('onboarding.manage'))
                <button type="button" class="owner-modal-trigger owner-modal-trigger--primary" data-owner-modal-open="owner-student-create-modal">＋ دانش‌آموز جدید</button>
            @endif
            @if(auth()->user()->hasPermission('parents.manage'))
                <button type="button" class="owner-modal-trigger" data-owner-modal-open="owner-parent-create-modal">＋ والد جدید</button>
            @endif
            @if(auth()->user()->hasPermission('teachers.manage'))
                <button type="button" class="owner-modal-trigger" data-owner-modal-open="owner-teacher-create-modal">＋ مدرس جدید</button>
                <button type="button" class="owner-modal-trigger" data-owner-modal-open="owner-class-teacher-modal">اختصاص مدرس کلاس</button>
            @endif
            @if(auth()->user()->hasPermission('enrollments.manage'))
                <button type="button" class="owner-modal-trigger" data-owner-modal-open="owner-enrollment-modal">＋ ثبت‌نام</button>
            @endif
            @if(!empty($academicGradesAvailable) && !empty($studentOnboardingAvailable) && !empty($studentOnboardingHistoryAvailable))
                <button type="button" class="owner-modal-trigger" data-owner-modal-open="owner-legacy-student-modal">ورود دانش‌آموز قدیمی</button>
            @endif
        </div>
    </header>

    <dialog class="owner-floating-modal" id="owner-student-create-modal" aria-labelledby="owner-student-create-title" @if(session('temporary_student_password') || ($errors->any() && request('tab') === 'create-student')) data-owner-modal-open-on-load @endif>
        <header class="owner-floating-modal__header">
            <div><span class="owner-record-modal-kicker">حساب جدید</span><h2 id="owner-student-create-title">ساخت حساب دانش‌آموز</h2></div>
            <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
        </header>
        <div class="owner-floating-modal__body">
        <header class="owner-student-create-head">
            <div><span>حساب جدید</span><h2>ساخت حساب دانش‌آموز</h2><p>اطلاعات اصلی را وارد کن؛ کد دانش‌آموز و رمز اولیه را سامانه می‌سازد.</p></div>
            <span class="owner-student-create-badge">رمز یک‌بار نمایش داده می‌شود</span>
        </header>

        @if(session('temporary_student_password'))
            <section class="owner-student-credentials" role="status" aria-live="polite">
                <div><strong>حساب ساخته شد</strong><p>این اطلاعات را از راه امن تحویل بده. رمز پس از خروج از این صفحه دوباره نمایش داده نمی‌شود.</p></div>
                <div class="owner-student-credential-grid">
                    <div><span>نام دانش‌آموز</span><strong>{{ session('temporary_student_name') }}</strong></div>
                    <div><span>شماره موبایل / نام کاربری</span><strong dir="ltr">{{ session('temporary_student_mobile') }}</strong></div>
                    <div><span>کد دانش‌آموز</span><strong dir="ltr">{{ session('temporary_student_number') }}</strong></div>
                    <div><span>رمز اولیه</span><code dir="ltr">{{ session('temporary_student_password') }}</code></div>
                </div>
                <p class="owner-student-credential-warning">دانش‌آموز در اولین ورود باید رمز را تغییر دهد.</p>
            </section>
        @endif

        @if($errors->any())
            <div class="owner-student-create-errors" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
        @endif

        <form method="POST" action="{{ route('owner.people.store-student', $academy) }}?tab=create-student" class="owner-student-create-form" autocomplete="off">
            @csrf
            <div class="owner-student-form-field"><label for="new-student-first-name">نام</label><input id="new-student-first-name" name="first_name" value="{{ old('first_name') }}" minlength="2" maxlength="100" required autocomplete="given-name" placeholder="آرش">@error('first_name')<small>{{ $message }}</small>@enderror</div>
            <div class="owner-student-form-field"><label for="new-student-last-name">نام خانوادگی</label><input id="new-student-last-name" name="last_name" value="{{ old('last_name') }}" minlength="2" maxlength="120" required autocomplete="family-name" placeholder="محمدی">@error('last_name')<small>{{ $message }}</small>@enderror</div>
            <div class="owner-student-form-field"><label for="new-student-national-id">کد ملی</label><input id="new-student-national-id" name="national_id" value="{{ old('national_id') }}" inputmode="numeric" pattern="[0-9۰-۹٠-٩]{10}" maxlength="10" minlength="10" required autocomplete="off" dir="ltr" placeholder="۱۰ رقم"><small class="owner-student-national-id-help">فقط برای تطبیق هویت؛ کد ملی خام ذخیره نمی‌شود.</small>@error('national_id')<small>{{ $message }}</small>@enderror</div>
            
            <div class="owner-student-form-field"><label for="new-student-mobile">شماره موبایل</label><input id="new-student-mobile" type="tel" name="mobile" value="{{ old('mobile') }}" inputmode="tel" maxlength="11" required autocomplete="tel" placeholder="۰۹۱۲…">@error('mobile')<small>{{ $message }}</small>@enderror</div>
            <div class="owner-student-form-field"><label for="new-student-grade">پایه تحصیلی</label><select id="new-student-grade" name="grade_id" required><option value="">انتخاب پایه</option>@foreach($grades as $grade)<option value="{{ $grade->id }}" @selected((string)old('grade_id') === (string)$grade->id)>{{ $grade->title }}</option>@endforeach</select>@error('grade_id')<small>{{ $message }}</small>@enderror</div>
            <div class="owner-student-form-field"><label for="new-student-birth">تاریخ تولد <em>اختیاری</em></label><input id="new-student-birth" type="date" name="birth_date" value="{{ old('birth_date') }}" autocomplete="bday">@error('birth_date')<small>{{ $message }}</small>@enderror</div>
            <div class="owner-student-form-field"><label for="new-student-school">نام مدرسه <em>اختیاری</em></label><input id="new-student-school" name="school_name" value="{{ old('school_name') }}" maxlength="255" placeholder="نام مدرسه">@error('school_name')<small>{{ $message }}</small>@enderror</div>
            <div class="owner-student-form-field owner-student-notes-field"><label for="new-student-notes">یادداشت اجرایی <em>اختیاری</em></label><textarea id="new-student-notes" name="notes" rows="2" maxlength="2000" placeholder="نکته‌ای که برای پیگیری لازم است">{{ old('notes') }}</textarea>@error('notes')<small>{{ $message }}</small>@enderror</div>
            <footer class="owner-student-form-footer">
                <div><strong>امنیت حساب</strong><span>رمز تصادفی قوی ساخته می‌شود و در اولین ورود باید عوض شود.</span></div>
                <button type="submit" @disabled($grades->isEmpty())><span>＋</span> ساخت حساب دانش‌آموز</button>
            </footer>
            @if($grades->isEmpty())<p class="owner-student-no-grades">پایه فعالی در سامانه تعریف نشده است.</p>@endif
        </form>
        </div>
    </dialog>

    <div class="owner-people-management-content">


<dialog class="owner-floating-modal" id="owner-parent-create-modal" aria-labelledby="owner-parent-create-title" @if(old('form_modal') === 'parent' && $errors->any()) data-owner-modal-open-on-load @endif>
    <header class="owner-floating-modal__header">
        <div><span class="owner-record-modal-kicker">حساب جدید</span><h2 id="owner-parent-create-title">ساخت حساب والد</h2></div>
        <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
    </header>
    <div class="owner-floating-modal__body">
<section class="dashboard-panel p-5 sm:p-7">
    <div>
        <h2 class="text-base font-black">ساخت حساب والد و اتصال به دانش‌آموز</h2>
        <p class="mt-1 max-w-3xl text-xs leading-6 text-slate-500">اگر شماره موبایل از قبل متعلق به حساب والد فعال باشد، همان حساب متصل می‌شود و رمز آن تغییر نمی‌کند. در غیر این صورت حساب والد ساخته می‌شود و رمز موقت یک‌باره نمایش داده خواهد شد.</p>
    </div>
    <form method="POST" action="{{ route('owner.people.store-parent', $academy) }}" class="mt-5 grid gap-3 sm:grid-cols-2" data-confirm="حساب والد ساخته یا به دانش‌آموز متصل شود؟">
        @csrf
        <input type="hidden" name="form_modal" value="parent">
        <label class="grid gap-1 text-xs font-bold">نام
            <input name="first_name" value="{{ old('first_name') }}" minlength="2" maxlength="60" required autocomplete="given-name" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
            @error('first_name')<span class="font-normal text-rose-600">{{ $message }}</span>@enderror
        </label>
        <label class="grid gap-1 text-xs font-bold">نام خانوادگی
            <input name="last_name" value="{{ old('last_name') }}" minlength="2" maxlength="80" required autocomplete="family-name" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
            @error('last_name')<span class="font-normal text-rose-600">{{ $message }}</span>@enderror
        </label>
        <label class="grid gap-1 text-xs font-bold">شماره موبایل والد
            <input type="tel" name="mobile" value="{{ old('mobile') }}" maxlength="11" required inputmode="tel" dir="ltr" autocomplete="tel" placeholder="۰۹۱۲…" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
            <small class="owner-field-help">شماره موبایل یکتا و در دسترس والد؛ شناسه ورود به حساب خواهد بود.</small>
            @error('mobile')<span class="font-normal text-rose-600">{{ $message }}</span>@enderror
        </label>
        <label class="grid gap-1 text-xs font-bold">نوع ارتباط با دانش‌آموز
            <select name="relation" required class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm">
                <option value="مادر" @selected(old('relation') === 'مادر')>مادر</option>
                <option value="پدر" @selected(old('relation') === 'پدر')>پدر</option>
                <option value="سرپرست" @selected(old('relation') === 'سرپرست')>سرپرست</option>
                <option value="سایر" @selected(old('relation') === 'سایر')>سایر</option>
            </select>
            <small class="owner-field-help">نسبت والد با دانش‌آموز را مطابق ارتباط واقعی انتخاب کن.</small>
            @error('relation')<span class="font-normal text-rose-600">{{ $message }}</span>@enderror
        </label>
        <div class="grid gap-2 text-xs font-bold sm:col-span-2">
            <label for="owner-parent-student-search">دانش‌آموز این آموزشگاه</label>
            <div class="owner-student-picker owner-student-picker--parent" data-student-picker
                 data-options-url="{{ route('owner.people.student-options', $academy) }}"
                 data-student-initial-browse="true">
                <input id="owner-parent-student-search" type="search" data-student-search
                       value="{{ old('student_label') }}" autocomplete="off" spellcheck="false"
                       aria-autocomplete="list" aria-controls="owner-parent-student-results"
                       aria-expanded="false" placeholder="نام یا شماره موبایل دانش‌آموز را جست‌وجو کن…"
                       class="min-h-11 w-full rounded-xl border border-slate-200 px-3 text-sm">
                <input type="hidden" name="student_id" data-student-id value="{{ old('student_id') }}">
                <input type="hidden" name="student_label" data-student-label value="{{ old('student_label') }}">
                <div id="owner-parent-student-results" data-student-results class="owner-student-picker-results"
                     role="listbox" aria-label="دانش‌آموزان فعال این آموزشگاه" hidden></div>
                <button type="button" data-student-more class="owner-student-picker-more" hidden>نمایش دانش‌آموزان بعدی</button>
                <div class="owner-student-picker-selection" data-student-selection
                     @if(!old('student_id') || !old('student_label')) hidden @endif aria-live="polite">
                    <div class="owner-student-picker-selection__copy">
                        <small>دانش‌آموز انتخاب‌شده</small>
                        <strong data-student-selected-name>{{ old('student_label', '—') }}</strong>
                        <span data-student-selected-mobile></span>
                    </div>
                    <button type="button" class="owner-student-picker-clear" data-student-clear>تغییر انتخاب</button>
                </div>
                <p data-student-status class="owner-student-picker-status" role="status" aria-live="polite">
                    در حال دریافت فهرست دانش‌آموزان فعال…
                </p>
            </div>
            <small class="owner-field-help">فقط دانش‌آموز فعال همین آموزشگاه نمایش داده می‌شود. ابتدا ۲۰ نفر نمایش داده می‌شوند؛ برای تعداد بیشتر «نمایش دانش‌آموزان بعدی» را بزن یا با نام و موبایل جست‌وجو کن.</small>
            @error('student_id')<span role="alert" class="font-normal text-rose-600">{{ $message }}</span>@enderror
            @if($students->isEmpty())<span class="font-normal text-amber-700">ابتدا یک دانش‌آموز فعال به آموزشگاه اضافه کنید.</span>@endif
        </div>
        <div class="sm:col-span-2"><button @disabled($students->count() === 0) class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white">ساخت حساب / اتصال والد</button></div>
    </form>
</section>
    </div>
</dialog>
<div class="owner-people-operation-dialogs">
<dialog class="owner-floating-modal" id="owner-teacher-create-modal" aria-labelledby="owner-teacher-create-modal-title" @if(old('form_modal') === 'teacher' && $errors->any()) data-owner-modal-open-on-load @endif>
    <header class="owner-floating-modal__header">
        <div><span class="owner-record-modal-kicker">مدیریت اعضا</span><h2 id="owner-teacher-create-modal-title">ساخت حساب مدرس</h2></div>
        <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
    </header>
    <div class="owner-floating-modal__body">
<section class="dashboard-panel p-5 sm:p-7"><div><h2 class="text-base font-black">ساخت حساب مدرس</h2><p class="mt-1 text-xs text-slate-500">حساب، نقش، پروفایل و membership در یک transaction.</p></div><form method="POST" action="{{ route('owner.people.store-teacher',$academy) }}" class="mt-5 grid gap-3" data-confirm="حساب مدرس ساخته شود؟">@csrf
<input type="hidden" name="form_modal" value="teacher">
<label class="grid gap-1"><span class="text-xs font-bold">نام</span><input name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@error('first_name')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</label>
<label class="grid gap-1"><span class="text-xs font-bold">نام خانوادگی</span><input name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@error('last_name')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</label>
<label class="grid gap-1"><span class="text-xs font-bold">شماره موبایل</span><input name="mobile" type="tel" inputmode="tel" dir="ltr" value="{{ old('mobile') }}" maxlength="11" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">شماره موبایل یکتا؛ شناسه ورود مدرس خواهد بود.</small>@error('mobile')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</label>
<p class="text-xs text-slate-500">رمز اولیه به‌صورت تصادفی ساخته می‌شود و باید در اولین ورود تغییر کند.</p>
<label class="grid gap-1"><span class="text-xs font-bold">تخصص</span><input name="specialization" value="{{ old('specialization') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">حوزه تخصصی اصلی مدرس، مانند ریاضی، فیزیک یا زبان.</small>@error('specialization')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</label>
<label class="grid gap-1"><span class="text-xs font-bold">سابقه (سال)</span><input name="experience_years" type="number" min="0" max="80" value="{{ old('experience_years') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">سابقهٔ تدریس را به سال و در بازهٔ صفر تا ۸۰ وارد کن.</small>@error('experience_years')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</label>
<button class="rounded-xl bg-slate-900 px-4 py-3 text-xs font-black text-white">ساخت حساب مدرس</button></form></section>
    </div>
</dialog>

<dialog class="owner-floating-modal" id="owner-class-teacher-modal" aria-labelledby="owner-class-teacher-modal-title" @if(old('form_modal') === 'assign' && $errors->any()) data-owner-modal-open-on-load @endif>
    <header class="owner-floating-modal__header">
        <div><span class="owner-record-modal-kicker">مدیریت اعضا</span><h2 id="owner-class-teacher-modal-title">اختصاص مدرس مسئول به کلاس</h2></div>
        <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
    </header>
    <div class="owner-floating-modal__body">
<section class="dashboard-panel p-5 sm:p-7">
<div><h2 class="text-base font-black">اختصاص مدرس مسئول به کلاس</h2><p class="mt-1 text-xs leading-6 text-slate-500">هر کلاس یک مدرس فعال مسئول دارد. اتصال مدرس به دوره نیز در همین عملیات تکمیل می‌شود.</p></div>
<form method="POST" action="{{ route('owner.people.assign-class-teacher',$academy) }}" class="mt-5 grid gap-3">@csrf
<input type="hidden" name="form_modal" value="assign">
<label class="grid gap-1"><span class="text-xs font-bold">کلاس فعال</span><select name="classroom_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@forelse($classroomOptions as $classroom)<option value="{{ $classroom->id }}">{{ $classroom->title }} — {{ $classroom->course?->title ?? 'بدون دوره' }}</option>@empty<option value="">کلاس فعالی وجود ندارد</option>@endforelse</select><small class="owner-field-help">فقط کلاس فعال آموزشگاه را انتخاب کن؛ این عملیات مدرس مسئول آن را تعیین می‌کند.</small>@error('classroom_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</label>
<label class="grid gap-1"><span class="text-xs font-bold">مدرس فعال</span><select name="teacher_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@forelse($teacherOptions as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@empty<option value="">مدرسی وجود ندارد</option>@endforelse</select><small class="owner-field-help">مدرس باید فعال و عضو همین آموزشگاه باشد؛ اتصال به دوره هم‌زمان تکمیل می‌شود.</small>@error('teacher_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</label>
<button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white" @disabled(!$teacherOptions->count() || !$classroomOptions->count())>ثبت مدرس کلاس</button></form></section>
    </div>
</dialog>

<dialog class="owner-floating-modal" id="owner-enrollment-modal" aria-labelledby="owner-enrollment-modal-title" @if(old('form_modal') === 'enrollment' && $errors->any()) data-owner-modal-open-on-load @endif>
    <header class="owner-floating-modal__header">
        <div><span class="owner-record-modal-kicker">مدیریت اعضا</span><h2 id="owner-enrollment-modal-title">ثبت‌نام دانش‌آموز</h2></div>
        <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
    </header>
    <div class="owner-floating-modal__body">
<section class="dashboard-panel p-5 sm:p-7"><div><h2 class="text-base font-black">ثبت‌نام دانش‌آموز</h2><p class="mt-1 text-xs text-slate-500">duplicate-safe، کنترل ظرفیت و ثبت مالی در backend.</p></div><form method="POST" action="{{ route('owner.people.enroll-student',$academy) }}" class="mt-5 grid gap-3">@csrf
<input type="hidden" name="form_modal" value="enrollment">
<input type="hidden" name="idempotency_key" value="{{ old('idempotency_key',(string)\Illuminate\Support\Str::uuid()) }}">
<div class="owner-student-picker" data-student-picker data-options-url="{{ route('owner.people.student-options',$academy) }}">
<label for="owner-enrollment-student-search" class="mb-1 block text-xs font-bold">دانش‌آموز (جست‌وجوی صفحه‌بندی‌شده)</label>
<input id="owner-enrollment-student-search" type="search" data-student-search value="{{ old('student_label') }}" autocomplete="off" aria-autocomplete="list" aria-controls="owner-enrollment-student-results" placeholder="نام، موبایل یا کد ملی را جست‌وجو کن…" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
<input type="hidden" name="student_id" data-student-id value="{{ old('student_id') }}">
<input type="hidden" name="student_label" data-student-label value="{{ old('student_label') }}">
<div id="owner-enrollment-student-results" data-student-results class="owner-student-picker-results" role="listbox"></div>
<button type="button" data-student-more class="owner-student-picker-more" hidden>نمایش نتایج بعدی</button>
<p data-student-status class="owner-student-picker-status" aria-live="polite">برای جلوگیری از بارگذاری همه اعضا، حداقل دو نویسه از نام، موبایل یا ۱۰ رقم کد ملی را برای جست‌وجو وارد کن؛ در نتایج فقط نام و شماره موبایل نمایش داده می‌شود.</p>
@error('student_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</div>
<label class="grid gap-1"><span class="text-xs font-bold">دوره</span><select id="enrollment-course" name="course_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@forelse($courseOptions as $course)<option value="{{ $course->id }}">{{ $course->title }} — {{ $course->isFree() ? 'رایگان' : (is_numeric($course->price) ? \App\Support\PersianUi::money($course->price) : 'قیمت تعیین نشده') }}</option>@empty<option value="">دوره‌ای وجود ندارد</option>@endforelse</select><small class="owner-field-help">دوره‌ای را انتخاب کن که ثبت‌نام دانش‌آموز در آن انجام می‌شود؛ مبلغ و دسترسی براساس دوره بررسی می‌شوند.</small>@error('course_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</label>
<label class="grid gap-1"><span class="text-xs font-bold">کلاس</span><select data-classroom-select data-course-select="enrollment-course" name="classroom_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="" selected disabled>ابتدا کلاس را انتخاب کن</option>@foreach($classroomOptions as $classroom)<option data-course-id="{{ $classroom->course_id }}" value="{{ $classroom->id }}">{{ $classroom->title }} — {{ $classroom->capacity??'بدون محدودیت' }}</option>@endforeach</select><span data-no-classrooms hidden class="text-xs text-slate-500">برای این دوره کلاسی تعریف نشده است.</span><small class="owner-field-help">کلاسی را انتخاب کن که به همین دوره تعلق دارد؛ کلاس‌های دیگر معتبر نیستند.</small>@error('classroom_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</label>
<label class="grid gap-1"><span class="text-xs font-bold">مبلغ پرداختی (تومان)</span><input name="paid_amount" type="number" data-money-input data-money-unit="تومان" step="0.01" min="0" value="{{ old('paid_amount') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">مبلغ پرداخت‌شده را به تومان وارد کن؛ اگر پرداختی ثبت نشده است خالی بگذار.</small>@error('paid_amount')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</label>
<button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white" @disabled(!$courseOptions->count())>ثبت‌نام دانش‌آموز</button></form></section>
    </div>
</dialog>
</div>

@if(!empty($academicGradesAvailable) && !empty($studentOnboardingAvailable) && !empty($studentOnboardingHistoryAvailable))
<dialog class="owner-floating-modal" id="owner-legacy-student-modal" aria-labelledby="owner-legacy-student-title" @if(old('form_modal') === 'legacy' && $errors->any()) data-owner-modal-open-on-load @endif>
    <header class="owner-floating-modal__header">
        <div><span class="owner-record-modal-kicker">تکمیل سوابق</span><h2 id="owner-legacy-student-title">ورود دانش‌آموز قدیمی</h2></div>
        <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
    </header>
    <div class="owner-floating-modal__body">
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
        <input type="hidden" name="form_modal" value="legacy">
        <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">

        <label class="grid gap-1">
            <span class="text-xs font-bold">نام و نام خانوادگی</span>
            <input name="name" value="{{ old('name') }}" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm" autocomplete="name"><small class="owner-field-help">نام را مطابق اطلاعاتی وارد کن که امکان تطبیق آن با سوابق قدیمی وجود دارد.</small>
            @error('name')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
        </label>

        <label class="grid gap-1">
            <span class="text-xs font-bold">کد ملی</span>
            <input name="national_id" value="{{ old('national_id') }}" required inputmode="numeric" maxlength="10" class="rounded-xl border border-slate-200 px-3 py-3 text-sm" autocomplete="off"><small class="owner-field-help">کد ملی فقط برای تطبیق هویت استفاده می‌شود؛ مقدار خام ذخیره نمی‌شود. عدد ۱۰ رقمی را دقیق وارد کن.</small>
            <span class="text-[9px] text-slate-400">فقط برای تطبیق هویت؛ مقدار خام ذخیره نمی‌شود.</span>
            @error('national_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
        </label>

        <label class="grid gap-1">
            <span class="text-xs font-bold">پایه تحصیلی</span>
            <select name="grade_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                <option value="">انتخاب پایه</option>
                @foreach($grades as $grade)
                    <option value="{{ $grade->id }}" @selected((string) old('grade_id') === (string) $grade->id)>{{ $grade->title }}</option>
                @endforeach
            </select>
            <small class="owner-field-help">پایهٔ تحصیلی فعلی دانش‌آموز را انتخاب کن تا سابقهٔ مهاجرت درست ثبت شود.</small>
            @error('grade_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
        </label>

        <label class="grid gap-1">
            <span class="text-xs font-bold">موبایل <span class="font-normal text-slate-400">(اختیاری)</span></span>
            <input name="mobile" value="{{ old('mobile') }}" inputmode="tel" class="rounded-xl border border-slate-200 px-3 py-3 text-sm" autocomplete="tel"><small class="owner-field-help">اختیاری؛ فقط شماره‌ای را وارد کن که برای تماس با خانواده معتبر است.</small>
            @error('mobile')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
        </label>

        <label class="grid gap-1 md:col-span-2">
            <span class="text-xs font-bold">نام مدرسه <span class="font-normal text-slate-400">(اختیاری)</span></span>
            <input name="school_name" value="{{ old('school_name') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">اختیاری؛ نام مدرسهٔ قبلی برای تکمیل سوابق آموزشی.</small>
            @error('school_name')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
        </label>

        <label class="grid gap-1 md:col-span-2">
            <span class="text-xs font-bold">یادداشت اپراتور <span class="font-normal text-slate-400">(اختیاری)</span></span>
            <textarea name="notes" rows="3" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ old('notes') }}</textarea><small class="owner-field-help">اختیاری؛ فقط نکتهٔ اجرایی لازم برای بررسی ثبت کن و از درج اطلاعات حساس غیرضروری پرهیز کن.</small>
            @error('notes')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
        </label>

        <button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white md:col-span-2">
            ثبت / به‌روزرسانی دانش‌آموز قدیمی
        </button>
    </form>
</section>
    </div>
</dialog>
@else
<section class="dashboard-panel p-5 sm:p-7">
    <h2 class="text-base font-black">ورود دانش‌آموز قدیمی</h2>
    <p class="mt-2 text-sm leading-7 text-slate-600">این عملیات تا آماده‌شدن ساختار دیتابیس پایه‌های تحصیلی و سوابق ورود، غیرفعال است. ابتدا Migrationهای مربوط را در سرور بررسی کن.</p>
</section>
@endif

@if(!empty($studentOnboardingHistoryAvailable))
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
                    {{ \App\Support\PersianUi::dateTime($entry->activated_at) }}
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
@else
<section class="dashboard-panel p-5 sm:p-7">
    <h2 class="font-black">سوابق ورود دانش‌آموز</h2>
    <p class="mt-2 text-sm text-slate-600">به دلیل کامل نبودن ساختار پایه‌های تحصیلی یا سوابق ورود، تاریخچه فعلاً قابل نمایش نیست. فهرست سایر اعضا در دسترس است.</p>
</section>
@endif

<div class="mb-2 flex items-center gap-2">
    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-black text-emerald-700">عضو فعال</span>
    <span class="text-[10px] text-slate-400">افراد فعال آموزشگاه</span>
</div>
<div class="owner-people-directory-tabs" data-owner-people-tabs data-initial-tab="{{ request('members_tab', 'students') }}">
    <nav class="owner-people-directory-tabbar" role="tablist" aria-label="فهرست اعضای آموزشگاه">
        <button type="button" role="tab" id="owner-members-students-tab" aria-controls="owner-members-students-panel" data-owner-tab-button="students" aria-selected="{{ request('members_tab', 'students') === 'students' ? 'true' : 'false' }}">دانش‌آموزان <span>{{ $students->total() }}</span></button>
        <button type="button" role="tab" id="owner-members-parents-tab" aria-controls="owner-members-parents-panel" data-owner-tab-button="parents" aria-selected="{{ request('members_tab') === 'parents' ? 'true' : 'false' }}">اولیا <span>{{ $parents->total() }}</span></button>
        <button type="button" role="tab" id="owner-members-active-teachers-tab" aria-controls="owner-members-active-teachers-panel" data-owner-tab-button="active-teachers" aria-selected="{{ request('members_tab') === 'active-teachers' ? 'true' : 'false' }}">مدرس‌ها <span>{{ $teachers->total() }}</span></button>
        <button type="button" role="tab" id="owner-members-archived-tab" aria-controls="owner-members-archived-teachers-panel" data-owner-tab-button="archived-teachers" aria-selected="{{ request('members_tab') === 'archived-teachers' ? 'true' : 'false' }}">مدرس‌های آرشیوشده <span>{{ $archivedTeachers->total() }}</span></button>
    </nav>
@foreach([['دانش‌آموزان',$students,'students'],['اولیا',$parents,'parents'],['مدرس‌ها',$teachers,'active-teachers']] as [$title,$items,$directoryRole])
<section class="owner-people-directory-tabpanel" id="owner-members-{{ $directoryRole }}-panel" role="tabpanel" aria-labelledby="owner-members-{{ $directoryRole === 'active-teachers' ? 'active-teachers' : $directoryRole }}-tab" data-owner-tab-panel="{{ $directoryRole }}" @if(request('members_tab', 'students') !== $directoryRole) hidden @endif>
<section class="dashboard-panel p-5" data-owner-people-directory data-directory-role="{{ $directoryRole }}" data-directory-url="{{ route('owner.people.data', ['academy' => $academy, 'role' => $directoryRole]) }}">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-black">{{ $title }}</h2>
        <span data-directory-count class="rounded-full bg-slate-50 px-2.5 py-1 text-[9px] font-bold text-slate-500">{{ $items->total() }}</span>
    </div>
    <div class="mt-4 grid gap-2 sm:grid-cols-[minmax(0,1fr)_140px_100px_100px_auto]" data-directory-controls>
        <label class="grid gap-1 text-[10px] font-bold text-slate-500">جست‌وجو
            <input type="search" data-directory-query maxlength="120" autocomplete="off" placeholder="نام، موبایل یا کد ملی…" aria-label="جست‌وجو در {{ $title }}" class="min-h-9 rounded-lg border border-slate-200 px-2.5 text-xs">
        </label>
        <label class="grid gap-1 text-[10px] font-bold text-slate-500">مرتب‌سازی
            <select data-directory-sort class="min-h-9 rounded-lg border border-slate-200 px-2.5 text-xs">
                <option value="name">نام و نام خانوادگی</option><option value="mobile">موبایل</option><option value="joined_at">تاریخ عضویت</option><option value="created_at">تاریخ ثبت</option>
            </select>
        </label>
        <label class="grid gap-1 text-[10px] font-bold text-slate-500">جهت
            <select data-directory-direction class="min-h-9 rounded-lg border border-slate-200 px-2.5 text-xs"><option value="asc">صعودی</option><option value="desc">نزولی</option></select>
        </label>
        <label class="grid gap-1 text-[10px] font-bold text-slate-500">تعداد در صفحه
            <select data-directory-per-page class="min-h-9 rounded-lg border border-slate-200 px-2.5 text-xs"><option value="25">۲۵</option><option value="50">۵۰</option><option value="100">۱۰۰</option></select>
        </label>
        <button type="button" data-directory-reset class="self-end rounded-lg border border-slate-200 bg-white px-3 py-2 text-[10px] font-bold">پاک‌کردن</button>
    </div>
    <p data-directory-status class="mt-2 text-[10px] text-slate-500" role="status" aria-live="polite">نمایش داده‌های همین آموزشگاه؛ برای جست‌وجو عبارت را وارد کن.</p>
    <div class="mt-4 grid gap-2" data-directory-results>
        @forelse($items as $person)
            @php($personJoinedAt = $person->pivot?->joined_at)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3">
                <div class="min-w-0">
                    <strong class="text-xs">{{ $person->name }}</strong>
                    @if($person->mobile)
                        <span class="mt-1 block text-[11px] text-slate-500" dir="ltr">{{ $person->mobile }}</span>
                    @endif
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    @if($directoryRole === 'active-teachers')
                        <form method="POST" action="{{ route('owner.people.archive-teacher', [$academy, $person]) }}" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <button class="rounded-lg border border-amber-200 bg-white px-2.5 py-2 text-[9px] font-black text-amber-700">آرشیو عضویت</button>
                        </form>
                    @endif
                    <button type="button" class="owner-person-preview-trigger" data-owner-person-preview
                        data-person-name="{{ $person->name }}" data-person-mobile="{{ $person->mobile }}"
                        data-person-role="{{ $directoryRole }}" data-person-account-status="{{ $person->status }}"
                        data-person-membership-status="{{ $person->pivot?->status }}"
                        data-person-joined-at="{{ $personJoinedAt ? \Illuminate\Support\Carbon::parse($personJoinedAt)->toIso8601String() : '' }}"
                        aria-haspopup="dialog">پیش‌نمایش</button>
                </div>
            </div>
        @empty
            <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">عضوی نیست.</div>
        @endforelse
    </div>
    <nav data-directory-pagination hidden class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-3" aria-label="صفحه‌بندی فهرست">
        <button type="button" data-directory-prev disabled class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold disabled:opacity-40">قبلی</button>
        <span data-directory-page-info class="text-[10px] text-slate-500" aria-live="polite">صفحه ۱</span>
        <button type="button" data-directory-next disabled class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold disabled:opacity-40">بعدی</button>
    </nav>
    @if($items->hasPages())
        <div data-directory-server-pagination class="mt-4 border-t border-slate-100 pt-3 text-xs">
            {{ $items->withQueryString()->links() }}
        </div>
    @endif
</section>
@endforeach

<section class="owner-people-directory-tabpanel" id="owner-members-archived-teachers-panel" role="tabpanel" aria-labelledby="owner-members-archived-tab" data-owner-tab-panel="archived-teachers" @if(request('members_tab', 'students') !== 'archived-teachers') hidden @endif>
<section class="dashboard-panel p-5" data-owner-people-directory data-directory-role="archived-teachers" data-directory-url="{{ route('owner.people.data', ['academy' => $academy, 'role' => 'archived-teachers']) }}">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="font-black">مدرس‌های آرشیوشده</h2>
            <p class="mt-1 text-xs text-slate-500">سابقه مدرس حفظ شده و با «آرشیوشده» از اعضای فعال جدا شده است.</p>
        </div>
        <span data-directory-count class="rounded-full bg-slate-50 px-2.5 py-1 text-[9px] font-bold text-slate-500">{{ $archivedTeachers->total() }}</span>
    </div>
    <div class="mt-4 grid gap-2 sm:grid-cols-[minmax(0,1fr)_140px_100px_auto]" data-directory-controls>
        <label class="grid gap-1 text-[10px] font-bold text-slate-500">جست‌وجو
            <input type="search" data-directory-query maxlength="120" autocomplete="off" placeholder="نام، موبایل یا کد ملی…" aria-label="جست‌وجو در مدرس‌های آرشیوشده" class="min-h-9 rounded-lg border border-slate-200 px-2.5 text-xs">
        </label>
        <label class="grid gap-1 text-[10px] font-bold text-slate-500">مرتب‌سازی
            <select data-directory-sort class="min-h-9 rounded-lg border border-slate-200 px-2.5 text-xs">
                <option value="name">نام و نام خانوادگی</option><option value="mobile">موبایل</option><option value="joined_at">تاریخ عضویت</option><option value="created_at">تاریخ ثبت</option>
            </select>
        </label>
        <label class="grid gap-1 text-[10px] font-bold text-slate-500">جهت
            <select data-directory-direction class="min-h-9 rounded-lg border border-slate-200 px-2.5 text-xs"><option value="asc">صعودی</option><option value="desc">نزولی</option></select>
        </label>
        <button type="button" data-directory-reset class="self-end rounded-lg border border-slate-200 bg-white px-3 py-2 text-[10px] font-bold">پاک‌کردن</button>
    </div>
    <p data-directory-status class="mt-2 text-[10px] text-slate-500" role="status" aria-live="polite">برای پیدا کردن مدرس آرشیوشده جست‌وجو کن.</p>
    <div class="mt-4 grid gap-2" data-directory-results>
        @forelse($archivedTeachers as $teacher)
            @php($teacherJoinedAt = $teacher->pivot?->joined_at)
            <div class="flex flex-col gap-2 rounded-xl bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <strong class="text-xs">{{ $teacher->name }}</strong>
                    <span class="mt-1 block text-[9px] text-slate-500" dir="ltr">{{ $teacher->mobile }}</span>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <form method="POST" action="{{ route('owner.people.restore-teacher', [$academy, $teacher]) }}" class="shrink-0">
                        @csrf
                        @method('PATCH')
                        <button class="rounded-lg border border-emerald-200 bg-white px-2.5 py-2 text-[9px] font-black text-emerald-700">بازگردانی</button>
                    </form>
                    <button type="button" class="owner-person-preview-trigger" data-owner-person-preview
                        data-person-name="{{ $teacher->name }}" data-person-mobile="{{ $teacher->mobile }}"
                        data-person-role="archived-teachers" data-person-account-status="{{ $teacher->status }}"
                        data-person-membership-status="{{ $teacher->pivot?->status }}"
                        data-person-joined-at="{{ $teacherJoinedAt ? \Illuminate\Support\Carbon::parse($teacherJoinedAt)->toIso8601String() : '' }}"
                        aria-haspopup="dialog">پیش‌نمایش</button>
                </div>
            </div>
        @empty
            <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">مدرس آرشیوشده‌ای وجود ندارد.</div>
        @endforelse
    </div>
    <button type="button" data-directory-more hidden class="mt-3 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold">نمایش نتایج بعدی</button>
    @if($archivedTeachers->hasPages())
        <div data-directory-server-pagination class="mt-4 border-t border-slate-100 pt-3 text-xs">{{ $archivedTeachers->withQueryString()->links() }}</div>
    @endif
</section>
</section>

</div>
    </div>
</div>
</div>
<dialog class="owner-person-preview-dialog" data-owner-person-preview-dialog aria-labelledby="owner-person-preview-title">
    <div class="owner-person-preview-dialog__inner">
        <header class="owner-person-preview-dialog__head">
            <div><span>پرونده عضو آموزشگاه</span><h2 id="owner-person-preview-title">پیش‌نمایش اطلاعات</h2></div>
            <button type="button" class="owner-person-preview-dialog__close" data-owner-person-preview-close aria-label="بستن پیش‌نمایش">×</button>
        </header>
        <dl class="owner-person-preview-grid">
            <div><dt>نام و نام خانوادگی</dt><dd data-preview-name>—</dd></div>
            <div><dt>نقش</dt><dd data-preview-role>—</dd></div>
            <div><dt>شماره موبایل</dt><dd data-preview-mobile dir="ltr">ثبت نشده</dd></div>
            <div><dt>وضعیت حساب کاربری</dt><dd data-preview-account-status>—</dd></div>
            <div><dt>وضعیت عضویت در آموزشگاه</dt><dd data-preview-membership-status>—</dd></div>
            <div><dt>تاریخ عضویت</dt><dd data-preview-joined-at>ثبت نشده</dd></div>
        </dl>
        <footer class="owner-person-preview-dialog__foot">
            <span>اطلاعات از پرونده همین آموزشگاه دریافت شده است.</span>
            <button type="button" class="owner-person-preview-trigger" data-owner-person-preview-close>بستن</button>
        </footer>
    </div>
</dialog>
@push('scripts')
    @vite(['resources/js/owner-people-directory.js'])
@endpush

@endsection