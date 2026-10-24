@extends('layouts.owner')
@section('title','اعضای آموزشگاه | شیخان')
@section('header-title','اعضای آموزشگاه')
@section('content')
<div class="space-y-6 panel-page-enter">
<div><p class="text-xs font-bold text-[var(--panel-primary)]">مدیریت افراد</p><h1 class="mt-1 text-2xl font-black">{{ $academy->name }}</h1><p class="mt-2 text-sm text-slate-500">مدرس‌ها، دانش‌آموزها، والدین، اختصاص مسئول کلاس و ثبت‌نام امن.</p></div>

@if(empty($academicGradesAvailable) || empty($studentOnboardingAvailable))
<section class="owner-schema-warning" role="status" aria-live="polite">
    <strong>بخشی از امکانات ورود دانش‌آموز قدیمی موقتاً غیرفعال است.</strong>
    <p>فهرست اعضای آموزشگاه در دسترس است؛ برای فعال‌شدن همهٔ امکانات، Migrationهای مربوط به پایه‌های تحصیلی و سوابق ورود دانش‌آموز باید روی سرور بررسی شوند.</p>
    @if(empty($academicGradesAvailable))<p>ساختار جدول پایه‌های تحصیلی کامل نیست.</p>@endif
    @if(empty($studentOnboardingAvailable))<p>ساختار جدول سوابق ورود دانش‌آموز کامل نیست.</p>@endif
</section>
@endif

@if(session('temporary_parent_password'))
<section class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-950" role="status">
    <h2 class="font-black">رمز موقت والد — فقط همین یک‌بار</h2>
    <p class="mt-1 text-sm leading-6">این رمز را از راه امن به والد تحویل دهید. حساب مجبور است در اولین ورود رمز را عوض کند؛ برای امنیت، صفحه را دوباره بارگذاری نکنید تا رمز از پیام موقت حذف شود.</p>
    <div class="mt-3 grid gap-2 sm:grid-cols-2">
        <div class="rounded-xl bg-white/80 p-3"><span class="block text-xs text-amber-800">ایمیل</span><strong class="mt-1 block break-all text-sm">{{ session('temporary_parent_email') }}</strong></div>
        <div class="rounded-xl bg-white/80 p-3"><span class="block text-xs text-amber-800">رمز موقت</span><code class="mt-1 block break-all text-base font-black">{{ session('temporary_parent_password') }}</code></div>
    </div>
</section>
@endif

<section class="dashboard-panel p-5 sm:p-7">
    <div>
        <h2 class="text-base font-black">ساخت حساب والد و اتصال به دانش‌آموز</h2>
        <p class="mt-1 max-w-3xl text-xs leading-6 text-slate-500">اگر ایمیل از قبل متعلق به حساب والد فعال باشد، همان حساب متصل می‌شود و رمز آن تغییر نمی‌کند. در غیر این صورت حساب والد ساخته می‌شود و رمز موقت یک‌باره نمایش داده خواهد شد.</p>
    </div>
    <form method="POST" action="{{ route('owner.people.store-parent', $academy) }}" class="mt-5 grid gap-3 sm:grid-cols-2" data-confirm="حساب والد ساخته یا به دانش‌آموز متصل شود؟">
        @csrf
        <label class="grid gap-1 text-xs font-bold">نام والد
            <input name="name" value="{{ old('name') }}" minlength="2" maxlength="100" required autocomplete="name" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
            <small class="owner-field-help">نام و نام خانوادگی والد را وارد کن؛ این نام در ارتباط‌ها و فهرست اعضا نمایش داده می‌شود.</small>
            @error('name')<span class="font-normal text-rose-600">{{ $message }}</span>@enderror
        </label>
        <label class="grid gap-1 text-xs font-bold">ایمیل والد
            <input type="email" name="email" value="{{ old('email') }}" maxlength="255" required autocomplete="email" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
            <small class="owner-field-help">ایمیل معتبر و تحت کنترل والد را ثبت کن؛ برای ورود به حساب و بازیابی دسترسی استفاده می‌شود.</small>
            @error('email')<span class="font-normal text-rose-600">{{ $message }}</span>@enderror
        </label>
        <label class="grid gap-1 text-xs font-bold">موبایل (اختیاری)
            <input type="tel" name="mobile" value="{{ old('mobile') }}" maxlength="30" autocomplete="tel" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
            <small class="owner-field-help">اختیاری؛ شماره‌ای وارد کن که برای هماهنگی با خانواده قابل استفاده باشد.</small>
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
        <label class="grid gap-1 text-xs font-bold sm:col-span-2">دانش‌آموز این آموزشگاه
            <select name="student_id" required class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm">
                <option value="">انتخاب دانش‌آموز فعال</option>
                @foreach($students as $studentOption)
                    <option value="{{ $studentOption->id }}" @selected((string) old('student_id') === (string) $studentOption->id)>{{ $studentOption->name }}</option>
                @endforeach
            </select>
            <small class="owner-field-help">فقط دانش‌آموز فعال همین آموزشگاه را انتخاب کن؛ ارتباط والد به همین دانش‌آموز ثبت می‌شود.</small>
            @error('student_id')<span class="font-normal text-rose-600">{{ $message }}</span>@enderror
            @if($students->isEmpty())<span class="font-normal text-amber-700">ابتدا یک دانش‌آموز فعال به آموزشگاه اضافه کنید.</span>@endif
        </label>
        <div class="sm:col-span-2"><button @disabled($students->count() === 0) class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white">ساخت حساب / اتصال والد</button></div>
    </form>
</section>
<div class="grid gap-5 xl:grid-cols-3">
<section class="dashboard-panel p-5 sm:p-7"><div><h2 class="text-base font-black">ساخت حساب مدرس</h2><p class="mt-1 text-xs text-slate-500">حساب، نقش، پروفایل و membership در یک transaction.</p></div><form method="POST" action="{{ route('owner.people.store-teacher',$academy) }}" class="mt-5 grid gap-3" data-confirm="حساب مدرس ساخته شود؟">@csrf
<label class="grid gap-1"><span class="text-xs font-bold">نام و نام خانوادگی</span><input name="name" value="{{ old('name') }}" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">نام کامل مدرس را وارد کن؛ در فهرست دوره‌ها و کلاس‌ها نمایش داده می‌شود.</small><x-owner.field-error field="name"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">ایمیل</span><input name="email" type="email" value="{{ old('email') }}" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">ایمیل یکتا و قابل‌دسترسی مدرس؛ برای ورود به پنل استفاده خواهد شد.</small><x-owner.field-error field="email"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">رمز عبور</span><input name="password" type="password" required autocomplete="new-password" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">رمز قوی و اختصاصی بساز؛ آن را از راه امن به مدرس تحویل بده.</small><x-owner.field-error field="password"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">تکرار رمز عبور</span><input name="password_confirmation" type="password" required autocomplete="new-password" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">همان رمز بالا را دوباره وارد کن تا اشتباه تایپی مشخص شود.</small></label>
<label class="grid gap-1"><span class="text-xs font-bold">تخصص</span><input name="specialization" value="{{ old('specialization') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">حوزه تخصصی اصلی مدرس، مانند ریاضی، فیزیک یا زبان.</small><x-owner.field-error field="specialization"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">سابقه (سال)</span><input name="experience_years" type="number" min="0" max="80" value="{{ old('experience_years') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">سابقهٔ تدریس را به سال و در بازهٔ صفر تا ۸۰ وارد کن.</small><x-owner.field-error field="experience_years"/></label>
<button class="rounded-xl bg-slate-900 px-4 py-3 text-xs font-black text-white">ساخت حساب مدرس</button></form></section>

<section class="dashboard-panel p-5 sm:p-7">
<div><h2 class="text-base font-black">اختصاص مدرس مسئول به کلاس</h2><p class="mt-1 text-xs leading-6 text-slate-500">هر کلاس یک مدرس فعال مسئول دارد. اتصال مدرس به دوره نیز در همین عملیات تکمیل می‌شود.</p></div>
<form method="POST" action="{{ route('owner.people.assign-class-teacher',$academy) }}" class="mt-5 grid gap-3">@csrf
<label class="grid gap-1"><span class="text-xs font-bold">کلاس فعال</span><select name="classroom_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@forelse($classroomOptions as $classroom)<option value="{{ $classroom->id }}">{{ $classroom->title }} — {{ $classroom->course?->title ?? 'بدون دوره' }}</option>@empty<option value="">کلاس فعالی وجود ندارد</option>@endforelse</select><small class="owner-field-help">فقط کلاس فعال آموزشگاه را انتخاب کن؛ این عملیات مدرس مسئول آن را تعیین می‌کند.</small><x-owner.field-error field="classroom_id"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">مدرس فعال</span><select name="teacher_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@forelse($teacherOptions as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@empty<option value="">مدرسی وجود ندارد</option>@endforelse</select><small class="owner-field-help">مدرس باید فعال و عضو همین آموزشگاه باشد؛ اتصال به دوره هم‌زمان تکمیل می‌شود.</small><x-owner.field-error field="teacher_id"/></label>
<button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white" @disabled(!$teacherOptions->count() || !$classroomOptions->count())>ثبت مدرس کلاس</button></form></section>

<section class="dashboard-panel p-5 sm:p-7"><div><h2 class="text-base font-black">ثبت‌نام دانش‌آموز</h2><p class="mt-1 text-xs text-slate-500">duplicate-safe، کنترل ظرفیت و ثبت مالی در backend.</p></div><form method="POST" action="{{ route('owner.people.enroll-student',$academy) }}" class="mt-5 grid gap-3">@csrf
<input type="hidden" name="idempotency_key" value="{{ old('idempotency_key',(string)\Illuminate\Support\Str::uuid()) }}">
<div class="owner-student-picker" data-student-picker data-options-url="{{ route('owner.people.student-options',$academy) }}">
<label for="owner-enrollment-student-search" class="mb-1 block text-xs font-bold">دانش‌آموز (جست‌وجوی صفحه‌بندی‌شده)</label>
<input id="owner-enrollment-student-search" type="search" data-student-search value="{{ old('student_label') }}" autocomplete="off" aria-autocomplete="list" aria-controls="owner-enrollment-student-results" placeholder="نام یا ایمیل را برای جست‌وجو وارد کن…" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
<input type="hidden" name="student_id" data-student-id value="{{ old('student_id') }}">
<input type="hidden" name="student_label" data-student-label value="{{ old('student_label') }}">
<div id="owner-enrollment-student-results" data-student-results class="owner-student-picker-results" role="listbox"></div>
<button type="button" data-student-more class="owner-student-picker-more" hidden>نمایش نتایج بعدی</button>
<p data-student-status class="owner-student-picker-status" aria-live="polite">برای جلوگیری از بارگذاری همه اعضا، حداقل دو حرف از نام یا ایمیل را برای جست‌وجو وارد کن؛ ایمیل در نتایج نمایش داده نمی‌شود.</p>
<x-owner.field-error field="student_id"/></div>
<label class="grid gap-1"><span class="text-xs font-bold">دوره</span><select id="enrollment-course" name="course_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@forelse($courseOptions as $course)<option value="{{ $course->id }}">{{ $course->title }} — {{ $course->isFree() ? 'رایگان' : \App\Support\PersianUi::money($course->price) }}</option>@empty<option value="">دوره‌ای وجود ندارد</option>@endforelse</select><small class="owner-field-help">دوره‌ای را انتخاب کن که ثبت‌نام دانش‌آموز در آن انجام می‌شود؛ مبلغ و دسترسی براساس دوره بررسی می‌شوند.</small><x-owner.field-error field="course_id"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">کلاس</span><select data-classroom-select data-course-select="enrollment-course" name="classroom_id" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="" selected disabled>ابتدا کلاس را انتخاب کن</option>@foreach($classroomOptions as $classroom)<option data-course-id="{{ $classroom->course_id }}" value="{{ $classroom->id }}">{{ $classroom->title }} — {{ $classroom->capacity??'بدون محدودیت' }}</option>@endforeach</select><span data-no-classrooms hidden class="text-xs text-slate-500">برای این دوره کلاسی تعریف نشده است.</span><small class="owner-field-help">کلاسی را انتخاب کن که به همین دوره تعلق دارد؛ کلاس‌های دیگر معتبر نیستند.</small><x-owner.field-error field="classroom_id"/></label>
<label class="grid gap-1"><span class="text-xs font-bold">مبلغ پرداختی (تومان)</span><input name="paid_amount" type="number" data-money-input data-money-unit="تومان" step="0.01" min="0" value="{{ old('paid_amount') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">مبلغ پرداخت‌شده را به تومان وارد کن؛ اگر پرداختی ثبت نشده است خالی بگذار.</small><x-owner.field-error field="paid_amount"/></label>
<button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white" @disabled(!$courseOptions->count())>ثبت‌نام دانش‌آموز</button></form></section>
</div>

@if(!empty($academicGradesAvailable) && !empty($studentOnboardingAvailable))
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
            <input name="name" value="{{ old('name') }}" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm" autocomplete="name"><small class="owner-field-help">نام را مطابق اطلاعاتی وارد کن که امکان تطبیق آن با سوابق قدیمی وجود دارد.</small>
            <x-owner.field-error field="name"/>
        </label>

        <label class="grid gap-1">
            <span class="text-xs font-bold">کد ملی</span>
            <input name="national_id" value="{{ old('national_id') }}" required inputmode="numeric" maxlength="10" class="rounded-xl border border-slate-200 px-3 py-3 text-sm" autocomplete="off"><small class="owner-field-help">کد ملی فقط برای تطبیق هویت استفاده می‌شود؛ مقدار خام ذخیره نمی‌شود. عدد ۱۰ رقمی را دقیق وارد کن.</small>
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
            <small class="owner-field-help">پایهٔ تحصیلی فعلی دانش‌آموز را انتخاب کن تا سابقهٔ مهاجرت درست ثبت شود.</small>
            <x-owner.field-error field="grade_id"/>
        </label>

        <label class="grid gap-1">
            <span class="text-xs font-bold">موبایل <span class="font-normal text-slate-400">(اختیاری)</span></span>
            <input name="mobile" value="{{ old('mobile') }}" inputmode="tel" class="rounded-xl border border-slate-200 px-3 py-3 text-sm" autocomplete="tel"><small class="owner-field-help">اختیاری؛ فقط شماره‌ای را وارد کن که برای تماس با خانواده معتبر است.</small>
            <x-owner.field-error field="mobile"/>
        </label>

        <label class="grid gap-1 md:col-span-2">
            <span class="text-xs font-bold">نام مدرسه <span class="font-normal text-slate-400">(اختیاری)</span></span>
            <input name="school_name" value="{{ old('school_name') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">اختیاری؛ نام مدرسهٔ قبلی برای تکمیل سوابق آموزشی.</small>
            <x-owner.field-error field="school_name"/>
        </label>

        <label class="grid gap-1 md:col-span-2">
            <span class="text-xs font-bold">یادداشت اپراتور <span class="font-normal text-slate-400">(اختیاری)</span></span>
            <textarea name="notes" rows="3" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ old('notes') }}</textarea><small class="owner-field-help">اختیاری؛ فقط نکتهٔ اجرایی لازم برای بررسی ثبت کن و از درج اطلاعات حساس غیرضروری پرهیز کن.</small>
            <x-owner.field-error field="notes"/>
        </label>

        <button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white md:col-span-2">
            ثبت / به‌روزرسانی دانش‌آموز قدیمی
        </button>
    </form>
</section>
@else
<section class="dashboard-panel p-5 sm:p-7">
    <h2 class="text-base font-black">ورود دانش‌آموز قدیمی</h2>
    <p class="mt-2 text-sm leading-7 text-slate-600">این عملیات تا آماده‌شدن ساختار دیتابیس پایه‌های تحصیلی و سوابق ورود، غیرفعال است. ابتدا Migrationهای مربوط را در سرور بررسی کن.</p>
</section>
@endif

@if(!empty($studentOnboardingAvailable))
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
    <p class="mt-2 text-sm text-slate-600">به دلیل آماده‌نبودن جدول سوابق، تاریخچه فعلاً قابل نمایش نیست. فهرست سایر اعضا در دسترس است.</p>
</section>
@endif

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
            <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3">
                <div class="min-w-0">
                    <strong class="text-xs">{{ $person->name }}</strong>
                    @if($title !== 'دانش‌آموزان')
                        <span class="mt-1 block break-all text-[9px] text-slate-500">{{ $person->email }}</span>
                    @endif
                </div>
                @if($title === 'مدرس‌ها')
                    <form method="POST" action="{{ route('owner.people.archive-teacher', [$academy, $person]) }}" class="shrink-0">
                        @csrf
                        @method('PATCH')
                        <button class="rounded-lg border border-amber-200 bg-white px-2.5 py-2 text-[9px] font-black text-amber-700">آرشیو عضویت</button>
                    </form>
                @endif
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
                <form method="POST" action="{{ route('owner.people.restore-teacher', [$academy, $teacher]) }}" class="shrink-0">
                    @csrf
                    @method('PATCH')
                    <button class="rounded-lg border border-emerald-200 bg-white px-2.5 py-2 text-[9px] font-black text-emerald-700">بازگردانی</button>
                </form>
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