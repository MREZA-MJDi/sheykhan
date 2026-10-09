@extends('layouts.owner')
@section('title', 'افتخارآفرینان و رضایتمندی | شیخان')
@section('header-title', 'افتخارآفرینان و رضایتمندی')
@section('content')
<div class="owner-form-shell space-y-6">
    <header class="owner-form-head">
        <div>
            <span>محتوای اعتمادساز</span>
            <h1>افتخارآفرینان و تجربه خانواده‌ها</h1>
            <p>برای انتشار هر نام یا فایل رسانه‌ای، سابقه رضایت باید ثبت شود. موارد پیش‌نویس از صفحه عمومی مخفی می‌مانند.</p>
        </div>
        <a href="{{ route('owner.dashboard') }}">داشبورد</a>
    </header>

    @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="grid gap-6 xl:grid-cols-2">
        <form method="POST" enctype="multipart/form-data" action="{{ route('owner.achievements.store', $academy) }}" class="owner-form-section space-y-4">
            @csrf
            <div><h2>ثبت افتخارآفرین</h2><p class="mt-1 text-xs leading-6 text-slate-500">نام و نام مدرسه فقط بعد از ثبت تأیید رضایت اولیا قابل انتشار است.</p></div>
            <label><span>نام و نام خانوادگی نمایشی</span><input name="display_name" value="{{ old('display_name') }}" maxlength="160" required></label>
            <div class="owner-form-two">
                <label><span>نوع قبولی</span><select name="achievement_type" required><option value="gifted_school" @selected(old('achievement_type')==='gifted_school')>آزمون تیزهوشان</option><option value="sample_school" @selected(old('achievement_type')==='sample_school')>مدارس نمونه دولتی</option></select></label>
                <label><span>پایه (اختیاری)</span><select name="grade_id"><option value="">انتخاب پایه</option>@foreach($grades as $grade)<option value="{{ $grade->id }}" @selected((string)old('grade_id')===(string)$grade->id)>{{ $grade->title }}</option>@endforeach</select></label>
            </div>
            <label><span>نام مدرسه قبولی</span><input name="school_name" value="{{ old('school_name') }}" maxlength="180" required></label>
            <label><span>عنوان کوتاه</span><input name="title" value="{{ old('title') }}" maxlength="255" placeholder="قبولی در آزمون ورودی" required></label>
            <label><span>توضیح</span><textarea name="description" rows="3" maxlength="2000">{{ old('description') }}</textarea></label>
            <label><span>تصویر (اختیاری)</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
            <label><span>مرجع داخلی رضایت</span><input name="publication_consent_reference" value="{{ old('publication_consent_reference') }}" maxlength="160" placeholder="شناسه پرونده / تاریخ دریافت رضایت‌نامه" required></label>
            <label><span>روش نگهداری رضایت</span><select name="publication_consent_method" required><option value="written">فرم کتبی امضاشده</option><option value="paper">پرونده کاغذی</option><option value="email">ایمیل ثبت‌شده</option><option value="message">پیام ثبت‌شده</option></select></label>
            <label class="owner-check"><input type="checkbox" name="publication_consent_confirmed" value="1" required><span>تأیید می‌کنم اجازه انتشار نام، مدرسه و تصویر را در پرونده نگه‌داری می‌کنیم.</span></label>
            <label class="owner-check"><input type="checkbox" name="guardian_consent_confirmed" value="1" required><span>برای دانش‌آموز زیر سن قانونی، رضایت ولی/سرپرست معتبر دریافت شده است.</span></label>
            <label class="owner-check"><input type="checkbox" name="publish" value="1" @checked(old('publish'))><span>انتشار عمومی بعد از ثبت رضایت</span></label>
            <button type="submit" class="owner-primary-btn w-full">ذخیره افتخارآفرین</button>
        </form>

        <form method="POST" enctype="multipart/form-data" action="{{ route('owner.testimonials.store', $academy) }}" class="owner-form-section space-y-4">
            @csrf
            <div><h2>ثبت تجربه والد یا دانش‌آموز</h2><p class="mt-1 text-xs leading-6 text-slate-500">اسکرین‌شات، فایل صوتی و کلیپ ویدئویی قابل ثبت است. فایل‌ها تا انتشار، خصوصی می‌مانند.</p></div>
            <label><span>نام نمایشی</span><input name="display_name" value="{{ old('display_name') }}" maxlength="160" required></label>
            <label><span>نقش</span><select name="role" required><option value="parent" @selected(old('role','parent')==='parent')>والد دانش‌آموز</option><option value="student" @selected(old('role')==='student')>دانش‌آموز</option></select></label>
            <label><span>متن رضایتمندی</span><textarea name="content_text" rows="4" maxlength="3000" required>{{ old('content_text') }}</textarea></label>
            <label><span>اسکرین‌شات / تصویر</span><input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"></label>
            <label><span>صوت رضایتمندی</span><input type="file" name="audio_file" accept=".mp3,.m4a,.aac,.ogg,.wav,audio/*"><small>حداکثر ۵۰ مگابایت</small></label>
            <label><span>کلیپ ویدئویی</span><input type="file" name="video_file" accept=".mp4,.webm,.mov,video/*"><small>حداکثر ۲۰۰ مگابایت</small></label>
            <label><span>مرجع داخلی رضایت</span><input name="publication_consent_reference" value="{{ old('publication_consent_reference') }}" maxlength="160" placeholder="شناسه پرونده / تاریخ دریافت رضایت‌نامه" required></label>
            <label><span>روش نگهداری رضایت</span><select name="publication_consent_method" required><option value="written">فرم کتبی امضاشده</option><option value="paper">پرونده کاغذی</option><option value="email">ایمیل ثبت‌شده</option><option value="message">پیام ثبت‌شده</option></select></label>
            <label class="owner-check"><input type="checkbox" name="publication_consent_confirmed" value="1" required><span>رضایت ثبت‌شده برای انتشار متن و رسانه انتخابی در پرونده موجود است.</span></label>
            <label class="owner-check"><input type="checkbox" name="guardian_consent_confirmed" value="1" required><span>اگر محتوای دانش‌آموز زیر سن قانونی را نشان می‌دهد، رضایت ولی/سرپرست معتبر دریافت شده است.</span></label>
            <label class="owner-check"><input type="checkbox" name="publish" value="1" @checked(old('publish'))><span>انتشار عمومی بعد از ثبت رضایت</span></label>
            <button type="submit" class="owner-primary-btn w-full">ذخیره تجربه</button>
        </form>
    </div>

    <section class="owner-form-section">
        <h2>افتخارآفرینان ثبت‌شده</h2>
        <div class="space-y-3">
            @forelse($achievements as $item)
                <article class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 p-4">
                    <div><strong>{{ $item->display_name }}</strong><p class="mt-1 text-xs text-slate-500">{{ $item->school_name }} · {{ $item->title }} · {{ $item->status }}</p><p class="mt-1 text-xs text-slate-500">رضایت: {{ $item->publication_consent_reference ?: 'ثبت نشده' }}</p></div>
                    @if($item->status === 'published')<form method="POST" action="{{ route('owner.achievements.withdraw', [$academy, $item]) }}">@csrf @method('PATCH')<button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">حذف از نمایش</button></form>@endif
                </article>
            @empty<p class="rounded-xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">هنوز موردی ثبت نشده است.</p>@endforelse
        </div>
    </section>

    <section class="owner-form-section">
        <h2>تجربه‌های ثبت‌شده</h2>
        <div class="space-y-3">
            @forelse($testimonials as $item)
                <article class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 p-4">
                    <div><strong>{{ $item->display_name }}</strong><p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($item->content_text, 150) }} · {{ $item->status }}</p><p class="mt-1 text-xs text-slate-500">رسانه: {{ $item->media->count() }} · رضایت: {{ $item->publication_consent_reference ?: 'ثبت نشده' }}</p></div>
                    @if($item->status === 'approved')<form method="POST" action="{{ route('owner.testimonials.withdraw', [$academy, $item]) }}">@csrf @method('PATCH')<button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">حذف از نمایش</button></form>@endif
                </article>
            @empty<p class="rounded-xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">هنوز موردی ثبت نشده است.</p>@endforelse
        </div>
    </section>
</div>
@endsection
