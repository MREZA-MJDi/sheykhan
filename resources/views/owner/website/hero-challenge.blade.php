@extends('layouts.seo-specialist')

@section('title', 'ویرایش معمای Hero | شیخان')
@section('header-title', 'ویرایش معمای Hero')

@section('content')
<div class="page-enter space-y-6">
    <section class="website-center-hero">
        <div>
            <span class="website-kicker">بانک معماهای صفحه اصلی</span>
            <h1>{{ $challenge->title }}</h1>
            <p>متن، دنباله، گزینه‌ها، پاسخ صحیح و ترتیب نمایش را کنترل کن. پاسخ صحیح به مرورگر بازگردانده نمی‌شود.</p>
        </div>
        <a href="{{ route('owner.website.hero-challenges.index') }}" class="website-open-btn">بازگشت به بانک معماها</a>
    </section>

    <form method="POST" action="{{ route('owner.website.hero-challenge.update', $challenge) }}" class="space-y-5">
        @csrf
        @method('PATCH')
        <section class="dashboard-panel space-y-4 p-5 sm:p-7">
            <div><h2 class="text-lg font-black">محتوای معما</h2><p class="mt-1 text-xs leading-7 text-slate-500">دنباله را با ویرگول جدا کن.</p></div>
            <label class="block space-y-2"><span class="text-sm font-bold">عنوان</span>
                <input class="ui-control px-3" name="title" maxlength="160" required value="{{ old('title', $challenge->title) }}">
            </label>
            <label class="block space-y-2"><span class="text-sm font-bold">صورت سؤال</span>
                <textarea class="ui-control px-3 py-2" name="prompt" rows="3" maxlength="1000" required>{{ old('prompt', $challenge->prompt) }}</textarea>
            </label>
            <label class="block space-y-2"><span class="text-sm font-bold">دنباله یا داده‌های نمایشی</span>
                <input class="ui-control px-3" name="sequence_text" value="{{ old('sequence_text', implode(', ', $challenge->sequence ?? [])) }}" placeholder="۳، ۶، ۱۲، ۲۴">
            </label>
            <label class="block space-y-2"><span class="text-sm font-bold">توضیح پاسخ</span>
                <textarea class="ui-control px-3 py-2" name="explanation" rows="4" maxlength="2000" required>{{ old('explanation', $challenge->explanation) }}</textarea>
            </label>
        </section>

        <section class="dashboard-panel space-y-4 p-5 sm:p-7">
            <div><h2 class="text-lg font-black">گزینه‌های پاسخ</h2><p class="mt-1 text-xs text-slate-500">چهار گزینه باید کامل باشند و فقط یک پاسخ صحیح انتخاب شود.</p></div>
            @php($optionMap = $challenge->options->keyBy('label'))
            @foreach(['A' => 'گزینه اول', 'B' => 'گزینه دوم', 'C' => 'گزینه سوم', 'D' => 'گزینه چهارم'] as $key => $label)
                <label class="grid gap-2 sm:grid-cols-[110px_minmax(0,1fr)] sm:items-center">
                    <span class="text-sm font-bold">{{ $label }}</span>
                    <input class="ui-control px-3" name="options[{{ $key }}]" required maxlength="160" value="{{ old('options.'.$key, $optionMap->get($key)?->content) }}">
                </label>
            @endforeach
            <label class="block space-y-2"><span class="text-sm font-bold">پاسخ صحیح</span>
                <select class="ui-control px-3" name="correct_option" required>
                    @foreach(['A' => 'گزینه اول', 'B' => 'گزینه دوم', 'C' => 'گزینه سوم', 'D' => 'گزینه چهارم'] as $key => $label)
                        <option value="{{ $key }}" @selected(old('correct_option', $challenge->options->firstWhere('is_correct', true)?->label) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </section>

        <section class="dashboard-panel grid gap-4 p-5 sm:grid-cols-2 sm:p-7">
            <label class="block space-y-2"><span class="text-sm font-bold">ترتیب نمایش</span>
                <input class="ui-control px-3" type="number" min="1" max="999" name="sort_order" required value="{{ old('sort_order', $challenge->sort_order) }}">
            </label>
            <label class="flex items-center gap-3 text-sm font-bold sm:self-end sm:pb-3">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $challenge->is_active))>
                نمایش معما در چرخه Hero
            </label>
        </section>

        <div class="flex flex-wrap justify-end gap-2">
            <a href="{{ route('owner.website.hero-challenges.index') }}" class="course-secondary-btn">انصراف</a>
            <button type="submit" class="course-primary-btn">ذخیره تغییرات</button>
        </div>
    </form>
</div>
@endsection
