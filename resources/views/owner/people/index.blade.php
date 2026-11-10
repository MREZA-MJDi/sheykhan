@extends('layouts.owner')
@section('title','اعضای آموزشگاه | شیخان')
@section('header-title','اعضای آموزشگاه')
@section('content')
@php($activePeopleTab = 'management')
<div class="space-y-6 panel-page-enter owner-people-workspace">
<div><p class="text-xs font-bold text-[var(--panel-primary)]">مدیریت افراد</p><h1 class="mt-1 text-2xl font-black">{{ $academy->name }}</h1><p class="mt-2 text-sm text-slate-500">مدرس‌ها، دانش‌آموزها و اولیا؛ همراه با ثبت‌نام و مدیریت کلاس‌ها.</p></div>

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
    <h2 class="font-black">رمز اولیه اولیا — فقط همین یک‌بار</h2>
    <p class="mt-1 text-sm leading-6">این رمز را از راه امن به اولیا تحویل دهید. حساب مجبور است در اولین ورود رمز را عوض کند؛ برای امنیت، صفحه را دوباره بارگذاری نکنید تا رمز از پیام موقت حذف شود.</p>
    <div class="mt-3 grid gap-2 sm:grid-cols-2">
        <div class="rounded-xl bg-white/80 p-3"><span class="block text-xs text-amber-800">شماره موبایل / نام کاربری</span><strong class="mt-1 block break-all text-sm">{{ session('temporary_parent_mobile') }}</strong></div>
        <div class="rounded-xl bg-white/80 p-3"><span class="block text-xs text-amber-800">رمز موقت</span><code class="mt-1 block break-all text-base font-black" dir="ltr">{{ session('temporary_parent_password') }}</code><button type="button" data-copy-value="{{ session('temporary_parent_password') }}" class="mt-2 rounded-lg border border-amber-300 px-3 py-2 text-xs font-bold">کپی رمز</button></div>
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

<div class="owner-people-tabs owner-people-root-tabs" data-owner-people-tabs data-initial-tab="management">
    <nav class="owner-people-tabbar" role="tablist" aria-label="مدیریت اعضای آموزشگاه">
        <button type="button" role="tab" id="owner-people-management-tab" aria-controls="owner-people-management-panel" data-owner-tab-button="management" aria-selected="{{ $activePeopleTab === 'management' ? 'true' : 'false' }}">
            <span class="owner-people-tab-icon">☷</span><span>اعضا و عملیات</span>
        </button>
    </nav>

    <div class="owner-people-tab-panel" id="owner-people-management-panel" role="tabpanel" aria-labelledby="owner-people-management-tab" data-owner-tab-panel="management" @if($activePeopleTab !== 'management') hidden @endif>

<dialog class="owner-floating-modal" id="owner-student-create-modal" aria-labelledby="owner-student-create-title" @if(session('temporary_student_password') || ($errors->any() && request('tab') === 'create-student')) data-owner-modal-open-on-load @endif>
        <header class="owner-floating-modal__header">
            <div><span class="owner-record-modal-kicker">حساب جدید</span><h2 id="owner-student-create-title">ساخت حساب دانش‌آموز</h2><p class="owner-modal-subtitle">کد دانش‌آموز و رمز اولیه توسط سامانه ساخته می‌شود.</p></div>
            <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
        </header>
        <div class="owner-floating-modal__body">
        <p class="owner-student-create-badge mb-3">رمز اولیه فقط یک‌بار نمایش داده می‌شود.</p>

        @if(session('temporary_student_password'))
            <section class="owner-student-credentials" role="status" aria-live="polite">
                <div><strong>حساب ساخته شد</strong><p>این اطلاعات را از راه امن تحویل بده. رمز پس از خروج از این صفحه دوباره نمایش داده نمی‌شود.</p></div>
                <div class="owner-student-credential-grid">
                    <div><span>نام دانش‌آموز</span><strong>{{ session('temporary_student_name') }}</strong></div>
                    <div><span>شماره موبایل / نام کاربری</span><strong dir="ltr">{{ session('temporary_student_mobile') }}</strong></div>
                    <div><span>کد دانش‌آموز</span><strong dir="ltr">{{ session('temporary_student_number') }}</strong></div>
                    <div><span>رمز اولیه</span><code dir="ltr">{{ session('temporary_student_password') }}</code><button type="button" data-copy-value="{{ session('temporary_student_password') }}" class="mt-2 rounded-lg border border-amber-300 px-3 py-2 text-xs font-bold">کپی رمز</button></div>
                </div>
                <p class="owner-student-credential-warning">دانش‌آموز در اولین ورود باید رمز را تغییر دهد.</p>
            </section>
        @endif

        @if($errors->any())
            <div class="owner-student-create-errors" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
        @endif

        <form method="POST" action="{{ route('owner.people.store-student', $academy) }}?tab=create-student" class="owner-student-create-form" autocomplete="off">
            @csrf
        <input type="hidden" name="form_modal" value="parent">
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



    

<div class="owner-people-tabs owner-people-tabs--sheets" data-owner-people-tabs data-query-param="sheet" data-initial-tab="{{ request('sheet', 'directory') }}">
    <nav class="owner-people-tabbar" role="tablist" aria-label="بخش‌های مدیریت اعضا">
        <button type="button" role="tab" data-owner-tab-button="directory" aria-selected="true">فهرست و بایگانی</button>
        <button type="button" role="tab" data-owner-tab-button="create-accounts" aria-selected="false">ثبت مدرس و ولی</button>
        <button type="button" role="tab" data-owner-tab-button="operations" aria-selected="false">عملیات و سوابق</button>
    </nav>
    <section class="owner-people-tab-panel" role="tabpanel" data-owner-tab-panel="directory">
<section class="owner-people-directory-first">
    <header class="owner-people-directory-head mb-3">
    <div><h2 class="text-lg font-black text-slate-900">فهرست اعضای آموزشگاه</h2><p class="mt-1 text-sm text-slate-600">هر گروه فهرست و صفحه‌بندی مستقل دارد؛ جست‌وجو فقط در همان نقش انجام می‌شود.</p></div>
    @if(auth()->user()->hasPermission('onboarding.manage'))
        <button type="button" class="owner-modal-trigger owner-modal-trigger--primary" data-owner-modal-open="owner-student-create-modal">＋ دانش‌آموز جدید</button>
    @endif
</header>
    <div class="mb-2 flex items-center gap-2">
    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-black text-emerald-700">عضو فعال</span>
    <span class="text-[10px] text-slate-400">افراد فعال آموزشگاه</span>
</div>
<div class="owner-people-directory-tabs" data-owner-people-tabs data-query-param="members_tab" data-initial-tab="{{ request('members_tab', 'students') }}">
    <nav class="owner-people-directory-tabbar" role="tablist" aria-label="فهرست اعضای آموزشگاه">
        <button type="button" role="tab" id="owner-members-students-tab" data-owner-tab-button="students" aria-controls="owner-members-students-panel" aria-selected="true">دانش‌آموزان <span>{{ $students->total() }}</span></button>
        <button type="button" role="tab" id="owner-members-parents-tab" data-owner-tab-button="parents" aria-controls="owner-members-parents-panel" aria-selected="false">اولیا <span>{{ $parents->total() }}</span></button>
        <button type="button" role="tab" id="owner-members-active-teachers-tab" data-owner-tab-button="active-teachers" aria-controls="owner-members-active-teachers-panel" aria-selected="false">مدرس‌ها <span>{{ $teachers->total() }}</span></button>
        <button type="button" role="tab" id="owner-members-archived-teachers-tab" data-owner-tab-button="archived-teachers" aria-controls="owner-members-archived-teachers-panel" aria-selected="false">مدرس‌های آرشیوشده <span>{{ $archivedTeachers->total() }}</span></button>
    </nav>
@foreach([['دانش‌آموزان',$students,'students'],['اولیا',$parents,'parents'],['مدرس‌ها',$teachers,'active-teachers']] as [$title,$items,$directoryRole])
<section class="owner-people-directory-tabpanel" id="owner-members-{{ $directoryRole }}-panel" role="tabpanel" aria-labelledby="owner-members-{{ $directoryRole }}-tab" data-owner-tab-panel="{{ $directoryRole }}" @if(request('members_tab', 'students') !== $directoryRole) hidden @endif>
<section class="dashboard-panel p-5" data-owner-people-directory data-directory-role="{{ $directoryRole }}" data-directory-url="{{ route('owner.people.data', ['academy' => $academy, 'role' => $directoryRole]) }}">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-black">{{ $title }}</h2>
        <span data-directory-count class="rounded-full bg-slate-50 px-2.5 py-1 text-[9px] font-bold text-slate-500">{{ $items->total() }}</span>
    </div>
    @include('owner.people.partials.directory-controls', ['title' => $title, 'showPerPage' => true])
    <p data-directory-status class="mt-2 text-[10px] text-slate-500" role="status" aria-live="polite">نمایش داده‌های همین آموزشگاه؛ برای جست‌وجو عبارت را وارد کن.</p>
    <div class="mt-4 grid gap-2" data-directory-results>
        @forelse($items as $person)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3">
                <div class="min-w-0">
                    <strong class="text-xs">{{ $person->name }}</strong>
                    @if($person->mobile)
                        <span class="mt-1 block text-[11px] text-slate-500" dir="ltr">{{ $person->mobile }}</span>
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
</section>
@endforeach
<section class="owner-people-directory-tabpanel" id="owner-members-archived-teachers-panel" role="tabpanel" aria-labelledby="owner-members-archived-teachers-tab" data-owner-tab-panel="archived-teachers" @if(request('members_tab', 'students') !== 'archived-teachers') hidden @endif>
<section class="dashboard-panel p-5" data-owner-people-directory data-directory-role="archived-teachers" data-directory-url="{{ route('owner.people.data', ['academy' => $academy, 'role' => 'archived-teachers']) }}">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="font-black">مدرس‌های آرشیوشده</h2>
            <p class="mt-1 text-xs text-slate-500">سابقه مدرس حفظ شده و با «آرشیوشده» از اعضای فعال جدا شده است.</p>
        </div>
        <span data-directory-count class="rounded-full bg-slate-50 px-2.5 py-1 text-[9px] font-bold text-slate-500">{{ $archivedTeachers->total() }}</span>
    </div>
    @include('owner.people.partials.directory-controls', ['title' => 'مدرس‌های آرشیوشده', 'showPerPage' => false])
    <p data-directory-status class="mt-2 text-[10px] text-slate-500" role="status" aria-live="polite">برای پیدا کردن مدرس آرشیوشده جست‌وجو کن.</p>
    <div class="mt-4 grid gap-2" data-directory-results>
        @forelse($archivedTeachers as $teacher)
            <div class="flex flex-col gap-2 rounded-xl bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <strong class="text-xs">{{ $teacher->name }}</strong>
                    <span class="mt-1 block text-[9px] text-slate-500">{{ $teacher->mobile }}</span>
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
    <nav data-directory-pagination hidden class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-3" aria-label="صفحه‌بندی مدرس‌های آرشیوشده">
        <button type="button" data-directory-prev disabled class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold disabled:opacity-40">قبلی</button>
        <span data-directory-page-info class="text-[10px] text-slate-500" aria-live="polite">صفحه ۱</span>
        <button type="button" data-directory-next disabled class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold disabled:opacity-40">بعدی</button>
    </nav>
    @if($archivedTeachers->hasPages())
        <div data-directory-server-pagination class="mt-4 border-t border-slate-100 pt-3 text-xs">{{ $archivedTeachers->withQueryString()->links() }}</div>
    @endif
</section>
</section>
</div>
    </section>
    <section class="owner-people-tab-panel" role="tabpanel" data-owner-tab-panel="create-accounts">
        <header class="owner-people-actionbar">
            <div><strong>ساخت اعضای جدید</strong><span>فرم‌های ثبت والد و مدرس در پنجرهٔ شناور باز می‌شوند.</span></div>
            <div class="owner-people-actionbar__buttons">
                @if(auth()->user()->hasPermission('parents.manage'))
                    <button type="button" class="owner-modal-trigger owner-modal-trigger--primary" data-owner-modal-open="owner-parent-create-modal">＋ ساخت والد</button>
                @endif
                @if(auth()->user()->hasPermission('teachers.manage'))
                    <button type="button" class="owner-modal-trigger" data-owner-modal-open="owner-teacher-create-modal">＋ ساخت مدرس</button>
                @endif
            </div>
        </header>
<dialog class="owner-floating-modal" id="owner-parent-create-modal" aria-labelledby="owner-parent-create-title" @if($errors->any() && old('form_modal') === 'parent') data-owner-modal-open-on-load @endif>
    <header class="owner-floating-modal__header">
        <div><span class="owner-record-modal-kicker">حساب جدید</span><h2 id="owner-parent-create-title">ساخت حساب والد</h2></div>
        <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
    </header>
    <div class="owner-floating-modal__body">
<section class="dashboard-panel p-5 sm:p-7">
    <div>
        <h2 class="sr-only">ثبت اولیا و اتصال به دانش‌آموز</h2>
        <p class="mt-1 max-w-3xl text-xs leading-6 text-slate-500">اگر این والد قبلاً در سامانه ثبت شده باشد، حسابش به دانش‌آموز وصل می‌شود. در غیر این صورت حساب تازه ساخته می‌شود و رمز اولیه نمایش داده خواهد شد.</p>
    </div>
    <form method="POST" action="{{ route('owner.people.store-parent', $academy) }}?modal=1" class="mt-5 grid gap-3 sm:grid-cols-2" data-confirm="حساب والد ساخته یا به دانش‌آموز متصل شود؟">
        @csrf
        <div class="owner-student-picker sm:col-span-2" data-student-picker data-options-url="{{ route('owner.people.student-options',$academy) }}">
            <label for="owner-parent-student-search" class="mb-1 block text-xs font-bold">اول دانش‌آموز را انتخاب کن</label>
            <input id="owner-parent-student-search" type="search" data-student-search value="{{ old('student_label') }}" autocomplete="off" aria-autocomplete="list" aria-controls="owner-parent-student-results" placeholder="نام، موبایل یا کد ملی را جست‌وجو کن…" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
            <input type="hidden" name="student_id" data-student-id value="{{ old('student_id') }}">
            <input type="hidden" name="student_label" data-student-label value="{{ old('student_label') }}">
            <div id="owner-parent-student-results" data-student-results class="owner-student-picker-results" role="listbox"></div>
            <button type="button" data-student-more class="owner-student-picker-more" hidden>نمایش نتایج بعدی</button>
            <p data-student-status class="owner-student-picker-status" aria-live="polite">برای جست‌وجو نام، شماره موبایل یا کد ملی را وارد کن.</p>
            @error('student_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
        </div>
        <label class="grid gap-1 text-xs font-bold">نام
            <input name="first_name" value="{{ old('first_name') }}" minlength="2" maxlength="60" required autocomplete="given-name" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
            @error('first_name')<span class="font-normal text-rose-600">{{ $message }}</span>@enderror
        </label>
        <label class="grid gap-1 text-xs font-bold">نام خانوادگی
            <input name="last_name" value="{{ old('last_name') }}" minlength="2" maxlength="80" required autocomplete="family-name" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
            @error('last_name')<span class="font-normal text-rose-600">{{ $message }}</span>@enderror
        </label>
        <label class="grid gap-1 text-xs font-bold">شماره موبایل اولیا
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

        <div class="sm:col-span-2"><button type="submit" class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white">ثبت اولیا و اتصال به دانش‌آموز</button></div>
    </form>
</section>
    </div>
</dialog>

<dialog class="owner-floating-modal" id="owner-teacher-create-modal" aria-labelledby="owner-teacher-create-title" @if($errors->any() && old('form_modal') === 'teacher') data-owner-modal-open-on-load @endif>
    <header class="owner-floating-modal__header">
        <div><span class="owner-record-modal-kicker">حساب جدید</span><h2 id="owner-teacher-create-title">ساخت حساب مدرس</h2></div>
        <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
    </header>
    <div class="owner-floating-modal__body">
<section class="dashboard-panel p-5 sm:p-7"><div><h2 class="sr-only">ساخت حساب مدرس</h2><p class="mt-1 text-xs text-slate-500">حساب، نقش، پروفایل و membership در یک transaction.</p></div><form method="POST" action="{{ route('owner.people.store-teacher',$academy) }}?modal=1" class="mt-5 grid gap-3" data-confirm="حساب مدرس ساخته شود؟">@csrf
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

    </section>
    <section class="owner-people-tab-panel" role="tabpanel" data-owner-tab-panel="operations">
<details class="owner-people-operations" open>
    <summary>افزودن عضو و عملیات مدیریتی</summary>
    <div class="owner-people-operations-body">
        <header class="owner-people-actionbar mb-4">
            <div><strong>عملیات و سوابق</strong><span>فرم‌های اجرایی در پنجرهٔ شناور باز می‌شوند.</span></div>
            <div class="owner-people-actionbar__buttons">
                <button type="button" class="owner-modal-trigger owner-modal-trigger--primary" data-owner-modal-open="owner-class-teacher-modal">اختصاص مدرس کلاس</button>
                <button type="button" class="owner-modal-trigger" data-owner-modal-open="owner-enrollment-modal">＋ ثبت‌نام دانش‌آموز</button>
                @if(!empty($academicGradesAvailable) && !empty($studentOnboardingAvailable) && !empty($studentOnboardingHistoryAvailable))
                    <button type="button" class="owner-modal-trigger" data-owner-modal-open="owner-legacy-student-modal">ورود دانش‌آموز قدیمی</button>
                @endif
            </div>
        </header>
<dialog class="owner-floating-modal" id="owner-class-teacher-modal" aria-labelledby="owner-class-teacher-title" @if($errors->any() && old('form_modal') === 'assign') data-owner-modal-open-on-load @endif>
    <header class="owner-floating-modal__header">
        <div><span class="owner-record-modal-kicker">مدیریت کلاس</span><h2 id="owner-class-teacher-title">اختصاص مدرس مسئول</h2></div>
        <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
    </header>
    <div class="owner-floating-modal__body">
<dialog class="owner-floating-modal" id="owner-legacy-student-modal" aria-labelledby="owner-legacy-student-title" @if($errors->any() && old('form_modal') === 'legacy') data-owner-modal-open-on-load @endif>
    <header class="owner-floating-modal__header">
        <div><span class="owner-record-modal-kicker">تکمیل سوابق</span><h2 id="owner-legacy-student-title">ورود دانش‌آموز قدیمی</h2></div>
        <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
    </header>
    <div class="owner-floating-modal__body">
<section class="dashboard-panel p-5 sm:p-7">
<div><h2 class="text-base font-black">اختصاص مدرس مسئول به کلاس</h2><p class="mt-1 text-xs leading-6 text-slate-500">هر کلاس یک مدرس فعال مسئول دارد. اتصال مدرس به دوره نیز در همین عملیات تکمیل می‌شود.</p></div>
<form method="POST" action="{{ route('owner.people.assign-class-teacher',$academy) }}?modal=1" class="mt-5 grid gap-3">@csrf
<input type="hidden" name="form_modal" value="legacy">
<input type="hidden" name="form_modal" value="assign">
<div class="owner-lookup-picker" data-lookup-picker data-lookup-type="classrooms" data-options-url="{{ route('owner.people.lookup-options', [$academy, 'classrooms']) }}" >
<label for="owner-assignment-classroom-search" class="mb-1 block text-xs font-bold">کلاس فعال</label>
<input id="owner-assignment-classroom-search" type="search" data-lookup-search autocomplete="off" placeholder="برای جست‌وجو حداقل دو حرف وارد کن…" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
<input type="hidden" name="classroom_id" data-lookup-value>
<div data-lookup-results class="owner-student-picker-results" role="listbox"></div>
<button type="button" data-lookup-more class="owner-student-picker-more" hidden>نمایش نتایج بعدی</button>
<p data-lookup-status class="owner-student-picker-status" aria-live="polite">نتایج به‌صورت صفحه‌بندی‌شده دریافت می‌شوند.</p>
@error('classroom_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
</div>
<div class="owner-lookup-picker" data-lookup-picker data-lookup-type="teachers" data-options-url="{{ route('owner.people.lookup-options', [$academy, 'teachers']) }}" >
<label for="owner-assignment-teacher-search" class="mb-1 block text-xs font-bold">مدرس فعال</label>
<input id="owner-assignment-teacher-search" type="search" data-lookup-search autocomplete="off" placeholder="برای جست‌وجو حداقل دو حرف وارد کن…" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
<input type="hidden" name="teacher_id" data-lookup-value required>
<div data-lookup-results class="owner-student-picker-results" role="listbox"></div>
<button type="button" data-lookup-more class="owner-student-picker-more" hidden>نمایش نتایج بعدی</button>
<p data-lookup-status class="owner-student-picker-status" aria-live="polite">نتایج به‌صورت صفحه‌بندی‌شده دریافت می‌شوند.</p>
@error('teacher_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
</div>
<button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">ثبت مدرس کلاس</button></div>
</dialog>

</form></section>

<dialog class="owner-floating-modal" id="owner-enrollment-modal" aria-labelledby="owner-enrollment-title" @if($errors->any() && old('form_modal') === 'enrollment') data-owner-modal-open-on-load @endif>
    <header class="owner-floating-modal__header">
        <div><span class="owner-record-modal-kicker">عضویت در کلاس</span><h2 id="owner-enrollment-title">ثبت‌نام دانش‌آموز</h2></div>
        <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
    </header>
    <div class="owner-floating-modal__body">
<section class="dashboard-panel p-5 sm:p-7"><div><h2 class="text-base font-black">ثبت‌نام دانش‌آموز</h2><p class="mt-1 text-xs text-slate-500">ثبت‌نام امن با بررسی ظرفیت کلاس و مبلغ پرداخت.</p></div><form method="POST" action="{{ route('owner.people.enroll-student',$academy) }}?modal=1" class="mt-5 grid gap-3">@csrf
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
<div class="owner-lookup-picker" data-lookup-picker data-lookup-type="courses" data-options-url="{{ route('owner.people.lookup-options', [$academy, 'courses']) }}" >
<label for="enrollment-course" class="mb-1 block text-xs font-bold">دوره</label>
<input id="enrollment-course" type="search" data-lookup-search autocomplete="off" placeholder="برای جست‌وجو حداقل دو حرف وارد کن…" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
<input type="hidden" name="course_id" data-lookup-value required>
<div data-lookup-results class="owner-student-picker-results" role="listbox"></div>
<button type="button" data-lookup-more class="owner-student-picker-more" hidden>نمایش نتایج بعدی</button>
<p data-lookup-status class="owner-student-picker-status" aria-live="polite">نتایج به‌صورت صفحه‌بندی‌شده دریافت می‌شوند.</p>
@error('course_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
</div>
<div class="owner-lookup-picker" data-lookup-picker data-lookup-type="classrooms" data-options-url="{{ route('owner.people.lookup-options', [$academy, 'classrooms']) }}" data-course-input="enrollment-course">
<label for="enrollment-classroom-search" class="mb-1 block text-xs font-bold">کلاس</label>
<input id="enrollment-classroom-search" type="search" data-lookup-search autocomplete="off" placeholder="برای جست‌وجو حداقل دو حرف وارد کن…" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
<input type="hidden" name="classroom_id" data-lookup-value required>
<div data-lookup-results class="owner-student-picker-results" role="listbox"></div>
<button type="button" data-lookup-more class="owner-student-picker-more" hidden>نمایش نتایج بعدی</button>
<p data-lookup-status class="owner-student-picker-status" aria-live="polite">نتایج به‌صورت صفحه‌بندی‌شده دریافت می‌شوند.</p>
@error('classroom_id')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
</div><small class="owner-field-help">کلاس‌های دوره انتخاب‌شده را جست‌وجو کن.</small>
<label class="grid gap-1"><span class="text-xs font-bold">مبلغ پرداختی (تومان)</span><input name="paid_amount" type="number" data-money-input data-money-unit="تومان" step="0.01" min="0" value="{{ old('paid_amount') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><small class="owner-field-help">مبلغ پرداخت‌شده را به تومان وارد کن؛ اگر پرداختی ثبت نشده است خالی بگذار.</small>@error('paid_amount')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror</label>
<button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">ثبت‌نام دانش‌آموز</button></div>
</dialog>
</form></section>
</div>

@if(!empty($academicGradesAvailable) && !empty($studentOnboardingAvailable) && !empty($studentOnboardingHistoryAvailable))
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

    <form method="POST" action="{{ route('owner.people.legacy-students.store', $academy) }}?modal=1" class="mt-5 grid gap-4 md:grid-cols-2" data-confirm="اطلاعات دانش‌آموز ثبت و به‌روزرسانی شود؟">
        @csrf
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
</section></div>
</dialog>
@else
<section class="dashboard-panel p-5 sm:p-7">
    <h2 class="text-base font-black">ورود دانش‌آموز قدیمی</h2>
    <p class="mt-2 text-sm leading-7 text-slate-600">این عملیات تا آماده‌شدن اطلاعات پایه‌های تحصیلی و سوابق ورود، غیرفعال است. ابتدا به‌روزرسانی‌های لازم را در سرور بررسی کن.</p>
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

    </div>
</details>
    </section>
</div>

    </div>
</div>
</div>
@push('scripts')
    @vite(['resources/js/owner-people-directory.js'])
@endpush

@endsection