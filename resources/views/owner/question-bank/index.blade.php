@extends('layouts.owner')

@section('title', 'بانک سؤال | شیخان')
@section('header-title', 'بانک سؤال')

@section('content')
<div class="space-y-6 panel-page-enter">
    @include('owner.partials.learning-workspace-tabs', ['academy' => $academy, 'activeSheet' => 'question-bank'])
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--panel-primary)]">آزمون و ارزیابی</p>
            <h1 class="mt-1 text-2xl font-black">سؤال‌های آماده برای آزمون‌ها</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">سؤال‌ها را یک‌بار ذخیره کن و هر زمان خواستی در آزمون‌های مختلف به کار ببر. اگر بعداً سؤال این فهرست را تغییر بدهی، آزمون‌هایی که قبلاً ساخته‌ای تغییر نمی‌کنند.</p>
        </div>
        <span class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold">{{ $questions->total() }} سؤال ثبت‌شده</span>
    </header>

    @if(auth()->user()->hasPermission('exams.manage'))
        <section class="dashboard-panel p-5">
            <h2 class="font-black">ثبت سؤال جدید</h2>
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
                    <textarea name="options_text" rows="3" class="rounded-xl border border-slate-200 p-3 text-sm" placeholder="هر گزینه را در یک خط بنویس"></textarea>
                    <small class="font-normal text-slate-500">برای هر گزینه، یک خط جدا در نظر بگیر.</small>
                </label>
                <label class="grid gap-1 text-xs font-bold">پاسخ‌های صحیح
                    <textarea name="correct_answers_text" rows="3" class="rounded-xl border border-slate-200 p-3 text-sm" placeholder="هر پاسخ صحیح را در یک خط وارد کن"></textarea>
                    <small class="font-normal text-slate-500">اگر سؤال تشریحی است، این قسمت را خالی بگذار.</small>
                </label>
                <label class="grid gap-1 text-xs font-bold">پاسخ مورد انتظار
                    <textarea name="expected_answer" rows="2" maxlength="15000" class="rounded-xl border border-slate-200 p-3 text-sm"></textarea>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="grid gap-1 text-xs font-bold">نمره پیشنهادی<input type="number" name="default_score" min="0" max="999999.99" step="0.01" value="1" required class="rounded-xl border border-slate-200 p-3 text-sm"></label>
                    <label class="grid gap-1 text-xs font-bold">سطح دشواری<select name="difficulty" class="rounded-xl border border-slate-200 p-3 text-sm"><option value="easy">آسان</option><option value="medium" selected>متوسط</option><option value="hard">سخت</option></select></label>
                </div>
                @if($errors->any())<div role="alert" class="md:col-span-2 rounded-xl bg-rose-50 p-3 text-xs leading-6 text-rose-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                <div class="md:col-span-2"><button class="rounded-xl bg-[var(--panel-primary)] px-5 py-3 text-xs font-black text-white">ذخیره در بانک سؤال</button></div>
            </form>
        </section>
    @endif

    <form method="GET" class="dashboard-panel grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-5">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="جست‌وجو در سؤال‌ها" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
        <select name="type" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">همه نوع سؤال</option>@foreach(['text'=>'تشریحی','single'=>'تک‌گزینه‌ای','multiple'=>'چندپاسخی','checkbox'=>'چک‌باکس'] as $key=>$label)<option value="{{ $key }}" @selected(($filters['type'] ?? '') === $key)>{{ $label }}</option>@endforeach</select>
        <select name="topic" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">همه موضوع‌ها</option>@foreach($topics as $topic)<option value="{{ $topic }}" @selected(($filters['topic'] ?? '') === $topic)>{{ $topic }}</option>@endforeach</select>
        <select name="difficulty" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">همه سطح‌ها</option><option value="easy" @selected(($filters['difficulty'] ?? '') === 'easy')>آسان</option><option value="medium" @selected(($filters['difficulty'] ?? '') === 'medium')>متوسط</option><option value="hard" @selected(($filters['difficulty'] ?? '') === 'hard')>سخت</option></select>
        <div class="flex gap-2"><select name="sort" class="min-w-0 flex-1 rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="recent">جدیدترین</option><option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>قدیمی‌ترین</option><option value="question" @selected(($filters['sort'] ?? '') === 'question')>متن سؤال</option></select><button class="rounded-xl bg-slate-900 px-4 text-xs font-black text-white">اعمال</button></div>
    </form>

    <section class="space-y-3">
        @forelse($questions as $question)
            <article class="dashboard-panel flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap gap-2 text-[11px] font-bold text-slate-500"><span>{{ ['text'=>'تشریحی','single'=>'تک‌گزینه‌ای','multiple'=>'چندپاسخی','checkbox'=>'چک‌باکس'][$question->type] ?? $question->type }}</span><span>· {{ $question->topic ?: 'بدون موضوع مشخص' }}</span><span>· {{ ['easy'=>'آسان','medium'=>'متوسط','hard'=>'سخت'][$question->difficulty] ?? $question->difficulty }}</span><span>· {{ $question->default_score }} امتیاز</span></div>
                    <h3 class="mt-2 whitespace-pre-line break-words text-sm font-bold leading-7">{{ $question->question }}</h3>
                    @if($question->options)<p class="mt-2 text-xs leading-6 text-slate-500">{{ implode(' · ', $question->options) }}</p>@endif
                </div>
                @if(auth()->user()->hasPermission('exams.manage'))
                    <details class="w-full sm:max-w-md">
                        <summary class="cursor-pointer rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-bold">تغییر سؤال</summary>
                        <form method="POST" action="{{ route('owner.question-bank.update', [$academy, $question->id]) }}" class="mt-2 grid gap-2 rounded-xl border border-slate-100 bg-slate-50 p-3">
                            @csrf @method('PATCH')
                            <input type="hidden" name="type" value="{{ $question->type }}">
                            <textarea name="question" required maxlength="10000" rows="3" class="rounded-lg border border-slate-200 p-2 text-sm">{{ $question->question }}</textarea>
                            @if($question->type !== 'text')
                                <label class="grid gap-1 text-xs font-bold">گزینه‌ها<textarea name="options_text" rows="3" class="rounded-lg border border-slate-200 p-2 text-sm">{{ implode("\n", $question->options ?? []) }}</textarea></label>
                                <label class="grid gap-1 text-xs font-bold">پاسخ‌های صحیح<textarea name="correct_answers_text" rows="2" class="rounded-lg border border-slate-200 p-2 text-sm">{{ implode("\n", $question->correct_answers ?? []) }}</textarea></label>
                            @endif
                            <input name="expected_answer" value="{{ $question->expected_answer }}" maxlength="15000" placeholder="پاسخ مورد انتظار" class="rounded-lg border border-slate-200 p-2 text-sm">
                            <div class="grid grid-cols-3 gap-2">
                                <input type="number" name="default_score" min="0" max="999999.99" step="0.01" value="{{ $question->default_score }}" required class="rounded-lg border border-slate-200 p-2 text-sm">
                                <input name="topic" maxlength="120" value="{{ $question->topic }}" placeholder="موضوع" class="rounded-lg border border-slate-200 p-2 text-sm">
                                <select name="difficulty" class="rounded-lg border border-slate-200 p-2 text-sm"><option value="easy" @selected($question->difficulty === 'easy')>آسان</option><option value="medium" @selected($question->difficulty === 'medium')>متوسط</option><option value="hard" @selected($question->difficulty === 'hard')>سخت</option></select>
                            </div>
                            <button class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white">ذخیره تغییرات</button>
                        </form>
                    </details>
                    <form method="POST" action="{{ route('owner.question-bank.destroy', [$academy, $question->id]) }}" onsubmit="return confirm('این سؤال انتقال به بایگانی شود؟ دیگر برای آزمون‌های جدید نمایش داده نمی‌شود.')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">انتقال به بایگانی</button></form>
                @endif
            </article>
        @empty
            <div class="dashboard-panel p-8 text-center text-sm text-slate-500">هنوز سؤالی ثبت نکرده‌ای. از بخش «ثبت سؤال جدید» اولین سؤال را اضافه کن.</div>
        @endforelse
        <div>{{ $questions->links() }}</div>
    </section>
</div>
@endsection
