@extends('layouts.owner')

@section('title', 'ساخت تکلیف | شیخان')
@section('header-title', 'ساخت تکلیف')

@section('content')
<div class="space-y-6 panel-page-enter">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--panel-primary)]">کارتابل ادمین / منشی</p>
            <h1 class="mt-1 text-2xl font-black">ثبت تکلیف برای یک کلاس مشخص</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">مدرس از روی مدرس فعال همان کلاس تعیین می‌شود تا تکلیف فقط برای گروه آموزشی مجاز قابل مشاهده باشد.</p>
        </div>
        <a href="{{ route('owner.assignments.index', $academy) }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت به فهرست ←</a>
    </header>

    @if($classrooms->isEmpty())
        <section class="dashboard-panel p-6">
            <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center">
                <strong class="text-sm font-black">کلاس آماده‌ای برای ثبت تکلیف وجود ندارد.</strong>
                <p class="mt-2 text-xs leading-7 text-slate-500">ابتدا یک کلاس فعال بساز و دقیقاً یک مدرس فعال به آن اختصاص بده.</p>
                <a href="{{ route('owner.classrooms.index', $academy) }}" class="mt-3 inline-block font-black text-[var(--panel-primary)]">مدیریت کلاس‌ها ←</a>
            </div>
        </section>
    @else
        <section class="dashboard-panel p-5 sm:p-7">
            <form method="POST" action="{{ route('owner.assignments.store', $academy) }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
                @csrf
                <label class="grid gap-1 md:col-span-2">
                    <span class="text-xs font-bold">کلاس</span>
                    <select name="classroom_id" required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                        <option value="">انتخاب کلاس</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" @selected((string)old('classroom_id') === (string)$classroom->id)>
                                {{ $classroom->title }} · {{ $classroom->course?->title ?? 'دوره نامشخص' }} · {{ $classroom->teachers->pluck('name')->join('، ') ?: 'مدرس نامشخص' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="owner-field-help">تکلیف فقط برای اعضای همین کلاس نمایش داده می‌شود؛ کلاس انتخابی را با دوره و مدرس آن تطبیق بده.</small>
                    <x-owner.field-error field="classroom_id"/>
                </label>

                <label class="grid gap-1 md:col-span-2">
                    <span class="text-xs font-bold">عنوان تکلیف</span>
                    <input name="title" value="{{ old('title') }}" required maxlength="255" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm" placeholder="مثلاً حل تمرین فصل سوم">
                    <small class="owner-field-help">عنوان کوتاه و قابل تشخیص انتخاب کن تا دانش‌آموز تکلیف را در فهرست سریع پیدا کند.</small>
                    <x-owner.field-error field="title"/>
                </label>

                <label class="grid gap-1 md:col-span-2">
                    <span class="text-xs font-bold">دستورالعمل <span class="font-normal text-slate-400">(اگر فایل PDF نمی‌فرستی، این بخش را پر کن)</span></span>
                    <textarea name="instructions" rows="7" maxlength="30000" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm" placeholder="شرح تمرین، منابع لازم، فرمت تحویل و نکات مهم...">{{ old('instructions') }}</textarea>
                    <small class="owner-field-help">انتظارات، قالب پاسخ و معیار ارزیابی را بنویس. اگر راهنما را در فایل می‌فرستی، این بخش می‌تواند خلاصه باشد.</small>
                    <x-owner.field-error field="instructions"/>
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-bold">پیوست راهنما <span class="font-normal text-slate-400">(اختیاری · PDF یا TXT تا ۵۰ مگابایت)</span></span>
                    <input type="file" name="handout" accept=".pdf,.txt,application/pdf,text/plain" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <small class="owner-field-help">فقط PDF یا TXT پذیرفته می‌شود؛ حجم فایل حداکثر ۵۰ مگابایت است.</small>
                    <x-owner.field-error field="handout"/>
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-bold">مهلت تحویل <span class="font-normal text-slate-400">(اختیاری)</span></span>
                    <input type="datetime-local" name="due_at" value="{{ old('due_at') }}" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <small class="owner-field-help">اگر مهلت مشخص نکنی، تکلیف بدون موعد تحویل ثبت می‌شود.</small>
                    <x-owner.field-error field="due_at"/>
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-bold">نمره کل</span>
                    <input type="number" name="max_score" min="0" max="999999.99" step="0.01" value="{{ old('max_score', $assignment->max_score) }}" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <small class="owner-field-help">نمرهٔ کل از صفر تا ۹۹۹٬۹۹۹٫۹۹ است؛ اعشار تا دو رقم پذیرفته می‌شود.</small>
                    <x-owner.field-error field="max_score"/>
                </label>

                <label class="grid gap-1">
                    <span class="text-xs font-bold">وضعیت</span>
                    <select name="status" required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                        <option value="draft" @selected(old('status', $assignment->status) === 'draft')>پیش‌نویس</option>
                        <option value="published" @selected(old('status', $assignment->status) === 'published')>منتشرشده</option>
                        <option value="closed" @selected(old('status', $assignment->status) === 'closed')>بسته</option>
                    </select>
                    <small class="owner-field-help">پیش‌نویس برای آماده‌سازی است؛ منتشرشده در دسترس دانش‌آموز قرار می‌گیرد و بسته از ارسال جدید جلوگیری می‌کند.</small>
                    <x-owner.field-error field="status"/>
                </label>

                @if($errors->any())
                    <div class="rounded-xl bg-rose-50 p-4 text-xs leading-7 text-rose-700 md:col-span-2" role="alert">
                        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                    </div>
                @endif

                <div class="flex flex-wrap gap-2 md:col-span-2">
                    <button type="submit" class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">ثبت تکلیف</button>
                    <a href="{{ route('owner.assignments.index', $academy) }}" class="rounded-xl border border-slate-200 px-5 py-3 text-xs font-black text-slate-600">انصراف</a>
                </div>
            </form>
        </section>
    @endif
</div>
@endsection
