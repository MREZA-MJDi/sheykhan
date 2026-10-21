@extends('layouts.parent')

@section('title', 'درخواست جلسه مشاوره | شیخان')
@section('header-title', 'درخواست جلسه مشاوره')

@section('content')
<div class="space-y-6">
    <header class="dashboard-panel p-5 sm:p-7">
        <p class="text-xs font-black text-[var(--panel-primary)]">پشتیبانی آموزشی</p>
        <h1 class="mt-2 text-2xl font-black text-[var(--color-text)]">درخواست جلسه مشاوره</h1>
        <p class="mt-3 max-w-3xl text-sm leading-7 text-[var(--color-text-muted)]">زمان انتخابی شما ترجیح اولیه است، نه رزرو قطعی. آموزشگاه درخواست را بررسی می‌کند و اگر زمان جلسه نهایی شود، تاریخ و توضیحات در همین صفحه به‌روزرسانی می‌شود.</p>
    </header>

    <section class="dashboard-panel p-5 sm:p-6">
        <h2 class="text-lg font-black">درخواست جدید</h2>
        @if($targets->isEmpty())
            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-7 text-amber-900">هنوز فرزند فعالی که به یک آموزشگاه متصل باشد برای این حساب پیدا نشد.</div>
        @else
            <form method="POST" action="{{ route('parent.counseling.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <label class="grid gap-2 text-sm font-bold">
                    <span>فرزند و آموزشگاه</span>
                    <select name="target" required class="min-h-12 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                        <option value="">انتخاب کنید</option>
                        @foreach($targets as $target)
                            <option value="{{ $target->academy_id }}:{{ $target->student_id }}" @selected(old('target') === $target->academy_id . ':' . $target->student_id)>{{ $target->student_name }} — {{ $target->academy_name }}</option>
                        @endforeach
                    </select>
                    @error('target')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-2 text-sm font-bold">
                    <span>زمان پیشنهادی (اختیاری)</span>
                    <input type="datetime-local" name="preferred_at" value="{{ old('preferred_at') }}" min="{{ now()->format('Y-m-d\TH:i') }}" class="min-h-12 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                    @error('preferred_at')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-2 text-sm font-bold md:col-span-2">
                    <span>موضوع جلسه</span>
                    <input name="subject" value="{{ old('subject') }}" required maxlength="180" class="min-h-12 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="مثلاً: برنامه‌ریزی درسی و پیگیری پیشرفت">
                    @error('subject')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-2 text-sm font-bold md:col-span-2">
                    <span>توضیحات تکمیلی</span>
                    <textarea name="details" maxlength="3000" rows="4" class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm leading-7" placeholder="در صورت نیاز، زمینه‌ی درخواست را توضیح دهید.">{{ old('details') }}</textarea>
                    @error('details')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                </label>
                <div class="md:col-span-2 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs leading-6 text-slate-500">لطفاً اطلاعات حساس غیرضروری را در توضیحات وارد نکنید.</p>
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[var(--panel-primary)] px-5 py-2.5 text-sm font-black text-white transition hover:opacity-90">ثبت درخواست مشاوره</button>
                </div>
            </form>
        @endif
    </section>

    <section class="space-y-4" aria-labelledby="parent-counseling-history-title">
        <div><h2 id="parent-counseling-history-title" class="text-lg font-black">پیگیری درخواست‌ها</h2><p class="mt-1 text-sm text-slate-500">درخواست‌ها تا زمان مشخص‌شدن نتیجه در تاریخچه باقی می‌مانند.</p></div>
        @php($labels = ['requested' => 'در انتظار بررسی', 'scheduled' => 'زمان جلسه تعیین شده', 'completed' => 'برگزارشده', 'declined' => 'پذیرفته نشد', 'cancelled' => 'لغوشده'])
        @forelse($requests as $item)
            <article class="dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><p class="text-xs font-bold text-[var(--panel-primary)]">{{ $item->academy?->name }} · {{ $item->student?->name }}</p><h3 class="mt-1 text-base font-black">{{ $item->subject }}</h3></div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $labels[$item->status] ?? 'در حال پیگیری' }}</span>
                </div>
                @if($item->details)<p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $item->details }}</p>@endif
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">زمان پیشنهادی شما</p><p class="mt-1 text-sm font-bold">{{ $item->preferred_at ? \App\Support\PersianUi::date($item->preferred_at) . ' - ' . \App\Support\PersianUi::time($item->preferred_at) : 'زمانی پیشنهاد نشده است' }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">زمان نهایی جلسه</p><p class="mt-1 text-sm font-bold">{{ $item->scheduled_at ? \App\Support\PersianUi::date($item->scheduled_at) . ' - ' . \App\Support\PersianUi::time($item->scheduled_at) : 'هنوز تعیین نشده است' }}</p></div>
                </div>
                @if($item->response_notes)<div class="mt-3 rounded-xl border border-blue-100 bg-blue-50 p-3"><p class="text-xs font-black text-blue-800">توضیح آموزشگاه</p><p class="mt-1 whitespace-pre-line text-sm leading-7 text-blue-950">{{ $item->response_notes }}</p></div>@endif
            </article>
        @empty
            <div class="dashboard-panel p-8 text-center"><h3 class="font-black">درخواستی ثبت نشده است.</h3><p class="mt-2 text-sm text-slate-500">در صورت نیاز از فرم بالای صفحه استفاده کنید.</p></div>
        @endforelse
        <div>{{ $requests->links() }}</div>
    </section>
</div>
@endsection
