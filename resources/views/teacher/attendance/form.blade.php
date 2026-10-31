@extends('layouts.teacher')
@section('title','حضور و غیاب | شیخان')
@section('header-title','حضور و غیاب')
@section('content')
<div class="teacher-form-shell">
    <div class="teacher-form-head"><div><span class="text-xs font-black text-[var(--panel-primary)]">{{ $classroom->course?->title }}</span><h1>{{ $classroom->title }}</h1><p>وضعیت حضور هر دانش‌آموز را برای یک تاریخ مشخص ثبت کن. تغییرات قبلی همان روز به‌روزرسانی می‌شوند.</p></div><a href="{{ route('teacher.classrooms.index') }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت به کلاس‌ها ←</a></div>
    <form method="POST" action="{{ route('teacher.classrooms.attendance.store',$classroom) }}" class="grid gap-4">
        @csrf
        <section class="teacher-form-section">
            <h2>تاریخ حضور و غیاب</h2>
            <div class="mt-5 max-w-md"><div class="teacher-jalali-field" data-jalali-field data-jalali-date-only="1">
    <label>
        <span>تاریخ</span>
        <div class="teacher-jalali-field-grid is-date-only">
            <input type="text" data-jalali-visible-date dir="ltr" inputmode="numeric" autocomplete="off" placeholder="۱۴۰۵/۰۷/۰۲" aria-label="تاریخ شمسی">
        </div>
        <input type="hidden" name="attendance_date" data-jalali-target value="{{ old('attendance_date',$attendanceDate) }}">
        <em>تاریخ به شمسی نمایش داده می‌شود و به شکل استاندارد در سیستم ذخیره می‌شود.</em>
        @error('attendance_date')<small class="teacher-field-error">{{ $message }}</small>@enderror
    </label>
</div></div>
        </section>
        <section class="teacher-form-section">
            <div class="flex items-center justify-between gap-3"><div><h2>وضعیت دانش‌آموزان</h2><p>فقط دانش‌آموزان فعال این کلاس در این فهرست قرار می‌گیرند.</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-[9px] font-black">{{ \App\Support\PersianUi::digits($classroom->students->count()) }} نفر</span></div>
            @if($scheduledMinutes > 0)
                <div class="mt-4 rounded-xl border border-sky-100 bg-sky-50 p-4 text-xs leading-7 text-sky-900">
                    <strong>مدت برنامه‌ریزی‌شده این روز: {{ \App\Support\PersianUi::digits($scheduledMinutes) }} دقیقه</strong>
                    <p>درصد حضور از «مجموع دقیقه‌های حضور واقعی ÷ مجموع دقیقه‌های برنامه‌ریزی‌شده» محاسبه می‌شود. برای حاضر، مقدار اولیه کل زمان کلاس است؛ اگر دانش‌آموز زودتر رفته یا با تأخیر آمده، دقیقه واقعی را اصلاح کن. برای تأخیر، ثبت دقیقه حضور الزامی است.</p>
                    <p>غیبت با صفر دقیقه محاسبه می‌شود. غیبت موجه از مخرج درصد حضور خارج است. اگر برنامه هفتگی ثبت نشده باشد، وضعیت ذخیره می‌شود ولی درصد زمانی برای این روز ساخته نمی‌شود.</p>
                </div>
            @else
                <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50 p-4 text-xs leading-7 text-amber-900">
                    <strong>مدت کلاس برای این روز مشخص نیست.</strong>
                    <p>وضعیت حضور را می‌توانی ثبت کنی؛ اما برای اندازه‌گیری درصد حضور بر اساس دقیقه، ابتدا در «برنامه هفتگی» ساعت شروع و پایان این کلاس را وارد کن. سامانه زمان حضور ثبت‌نشده را حدس نمی‌زند.</p>
                </div>
            @endif
            <div class="mt-5 grid gap-3">
                @forelse($classroom->students as $student)
                    @php($existing = $existingAttendance[$student->id] ?? null)
                    @php($currentStatus = old("attendance.{$student->id}", $existing?->status ?? ''))
                    @php($defaultMinutes = $existing?->attended_minutes ?? ($currentStatus === 'present' && $scheduledMinutes > 0 ? $scheduledMinutes : ($currentStatus === 'absent' && $scheduledMinutes > 0 ? 0 : '')))
                    @php($currentMinutes = old("attendance_minutes.{$student->id}", $defaultMinutes))
                    <article class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[minmax(0,1fr)_minmax(190px,.7fr)] sm:items-center">
                        <div class="min-w-0">
                            <strong class="block text-sm font-bold text-slate-900">{{ $student->name }}</strong>
                            <p class="mt-1 text-xs leading-6 text-slate-500">
                                @if($currentStatus === 'late')
                                    دقیقه حضور واقعی را وارد کن؛ تأخیر به‌تنهایی زمان حضور را مشخص نمی‌کند.
                                @elseif($currentStatus === 'absent')
                                    غیبت با صفر دقیقه حضور ثبت می‌شود.
                                @elseif($currentStatus === 'excused')
                                    غیبت موجه در درصد حضور محاسبه نمی‌شود.
                                @else
                                    وضعیت حضور را آگاهانه انتخاب کن؛ رکورد جدید به‌طور پیش‌فرض حاضر نیست.
                                @endif
                            </p>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <label class="grid gap-1 text-xs font-bold text-slate-700">
                                وضعیت
                                <select name="attendance[{{ $student->id }}]" required class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs">
                                    <option value="" @selected($currentStatus === '')>انتخاب وضعیت…</option>
                                    <option value="present" @selected($currentStatus === 'present')>حاضر</option>
                                    <option value="late" @selected($currentStatus === 'late')>با تأخیر</option>
                                    <option value="absent" @selected($currentStatus === 'absent')>غایب</option>
                                    <option value="excused" @selected($currentStatus === 'excused')>موجه</option>
                                </select>
                            </label>
                            <label class="grid gap-1 text-xs font-bold text-slate-700">
                                دقیقه حضور واقعی
                                <input
                                    type="number"
                                    name="attendance_minutes[{{ $student->id }}]"
                                    min="0"
                                    max="{{ max(0, $scheduledMinutes) }}"
                                    value="{{ $currentMinutes }}"
                                    @disabled($scheduledMinutes < 1)
                                    inputmode="numeric"
                                    placeholder="{{ $scheduledMinutes > 0 ? 'مثلاً '.$scheduledMinutes : 'نیازمند برنامه هفتگی' }}"
                                    class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs"
                                    aria-describedby="attendance-minutes-help-{{ $student->id }}"
                                >
                                <small id="attendance-minutes-help-{{ $student->id }}" class="font-normal text-slate-500">
                                    {{ $scheduledMinutes > 0 ? 'حداکثر '.\App\Support\PersianUi::digits($scheduledMinutes).' دقیقه' : 'ابتدا برنامه هفتگی را تنظیم کن' }}
                                </small>
                            </label>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl bg-slate-50 p-5 text-center text-xs text-slate-500">دانش‌آموز فعالی در این کلاس نیست؛ تا افزودن دانش‌آموز، حضور و غیابی برای ثبت وجود ندارد.</div>
                @endforelse
            </div>
        </section>
        @if($errors->any())<div class="rounded-2xl bg-rose-50 px-4 py-3 text-xs leading-7 text-rose-700">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        <div class="teacher-form-actions">
            <a href="{{ route('teacher.classrooms.index') }}">بازگشت بدون ثبت</a>
            <button type="submit" {{ $classroom->students->isEmpty() ? 'disabled' : '' }}>ثبت حضور و غیاب</button>
        </div>
    </form>
</div>
@endsection