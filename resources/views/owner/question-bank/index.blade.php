@extends('layouts.owner')

@section('title', 'بانک سؤال | شیخان')
@section('header-title', 'بانک سؤال')

@section('content')
<div class="space-y-6 panel-page-enter">
    @include('owner.partials.learning-workspace-tabs', ['academy' => $academy, 'activeSheet' => 'question-bank'])
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--panel-primary)]">آزمون و ارزیابی</p>
            <h1 class="mt-1 text-2xl font-black">بانک سؤال آموزشگاه</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">سؤال‌ها مستقل از آزمون نگهداری می‌شوند و می‌توان آن‌ها را در آزمون‌های مختلف استفاده کرد. نسخه‌ی واردشده به آزمون از تغییرات بعدی بانک مستقل است.</p>
        </div>
        <span class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold">{{ $questions->total() }} سؤال فعال</span>
    </header>

    @if(auth()->user()->hasPermission('exams.manage'))
        <section class="dashboard-panel p-5">
            <h2 class="font-black">افزودن سؤال به بانک</h2>
            <form method="POST" action="{{ route('owner.question-bank.store', $academy) }}" class="mt-4 grid gap-3 md:grid-cols-2">
                @csrf
                <label class="grid gap-1 text-xs font-bold">نوع سؤال
                    <select name="type" required class="rounded-xl border border-slate-200 p-3 text-sm">
                        <option value="text">تشریحی</option><option value="single">تک‌گزینه‌ای</option><option value="multiple">چندپاسخی</option><option value="checkbox">چندپاسخی (چک‌باکس)</option>
                    </select>
                </label>
                <label class="grid gap-1 text-xs font-bold">موضوع
                    <input name="topic" maxlength="120" class="rounded-xl border border-slate-200 p-3 text-sm" placeholder="مثلاً ریاضی · فصل دوم">
                </label>
                <label class="grid gap-1 text-xs font-bold md:col-span-2">متن سؤال
                    <textarea name="question" required maxlength="10000" rows="3" class="rounded-xl border border-slate-200 p-3 text-sm"></textarea>
                </label>
                <label class="grid gap-1 text-xs font-bold">گزینه‌ها
                    <textarea name="options[]" rows="3" class="rounded-xl border border-slate-200 p-3 text-sm" placeholder="هر گزینه را در یک خط وارد کن"></textarea>
                    <small class="font-normal text-slate-500">در این نسخه هر خط یک گزینه است.</small>
                </label>
                <label class="grid gap-1 text-xs font-bold">پاسخ‌های صحیح
                    <textarea name="correct_answers[]" rows="3" class="rounded-xl border border-slate-200 p-3 text-sm" placeholder="هر پاسخ صحیح را در یک خط وارد کن"></textarea>
                    <small class="font-normal text-slate-500">برای سؤال تشریحی خالی بماند.</small>
                </label>
                <label class="grid gap-1 text-xs font-bold">پاسخ مورد انتظار
                    <textarea name="expected_answer" rows="2" maxlength="15000" class="rounded-xl border border-slate-200 p-3 text-sm"></textarea>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="grid gap-1 text-xs font-bold">نمره پیش‌فرض<input type="number" name="default_score" min="0" max="999999.99" step="0.01" value="1" required class="rounded-xl border border-slate-200 p-3 text-sm"></label>
                    <label class="grid gap-1 text-xs font-bold">سطح دشواری<select name="difficulty" class="rounded-xl border border-slate-200 p-3 text-sm"><option value="easy">آسان</option><option value="medium" selected>متوسط</option><option value="hard">سخت</option></select></label>
                </div>
                @if($errors->any())<div role="alert" class="md:col-span-2 rounded-xl bg-rose-50 p-3 text-xs leading-6 text-rose-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                <div class="md:col-span-2"><button class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">ذخیره در بانک سؤال</button></div>
            </form>
        </section>
    @endif

    <form method="GET" class="dashboard-panel grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-5">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="جست‌وجوی متن سؤال" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
        <select name="type" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">همه نوع‌ها</option>@foreach(['text'=>'تشریحی','single'=>'تک‌گزینه‌ای','multiple'=>'چندپاسخی','checkbox'=>'چک‌باکس'] as $key=>$label)<option value="{{ $key }}" @selected(($filters['type'] ?? '') === $key)>{{ $label }}</option>@endforeach</select>
        <select name="topic" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">همه موضوع‌ها</option>@foreach($topics as $topic)<option value="{{ $topic }}" @selected(($filters['topic'] ?? '') === $topic)>{{ $topic }}</option>@endforeach</select>
        <select name="difficulty" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">همه دشواری‌ها</option><option value="easy" @selected(($filters['difficulty'] ?? '') === 'easy')>آسان</option><option value="medium" @selected(($filters['difficulty'] ?? '') === 'medium')>متوسط</option><option value="hard" @selected(($filters['difficulty'] ?? '') === 'hard')>سخت</option></select>
        <div class="flex gap-2"><select name="sort" class="min-w-0 flex-1 rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="recent">جدیدترین</option><option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>قدیمی‌ترین</option><option value="question" @selected(($filters['sort'] ?? '') === 'question')>متن سؤال</option></select><button class="rounded-xl bg-slate-900 px-4 text-xs font-black text-white">اعمال</button></div>
    </form>

    <section class="space-y-3">
        @forelse($questions as $question)
            <article class="dashboard-panel flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap gap-2 text-[11px] font-bold text-slate-500"><span>{{ ['text'=>'تشریحی','single'=>'تک‌گزینه‌ای','multiple'=>'چندپاسخی','checkbox'=>'چک‌باکس'][$question->type] ?? $question->type }}</span><span>· {{ $question->topic ?: 'بدون موضوع' }}</span><span>· {{ ['easy'=>'آسان','medium'=>'متوسط','hard'=>'سخت'][$question->difficulty] ?? $question->difficulty }}</span><span>· {{ $question->default_score }} امتیاز</span></div>
                    <h3 class="mt-2 whitespace-pre-line break-words text-sm font-bold leading-7">{{ $question->question }}</h3>
                    @if($question->options)<p class="mt-2 text-xs leading-6 text-slate-500">{{ implode(' · ', $question->options) }}</p>@endif
                </div>
                @if(auth()->user()->hasPermission('exams.manage'))
                    <form method="POST" action="{{ route('owner.question-bank.destroy', [$academy, $question->id]) }}" onsubmit="return confirm('این سؤال از بانک فعال خارج شود؟')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">بایگانی</button></form>
                @endif
            </article>
        @empty
            <div class="dashboard-panel p-8 text-center text-sm text-slate-500">هنوز سؤالی در بانک این آموزشگاه ثبت نشده است.</div>
        @endforelse
        <div>{{ $questions->links() }}</div>
    </section>
</div>
@endsection
