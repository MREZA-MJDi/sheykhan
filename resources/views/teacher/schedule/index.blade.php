@extends('layouts.teacher')
@section('title','برنامه هفتگی | شیخان')
@section('header-title','برنامه هفتگی')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">برنامه‌ریزی</span>
            <h1 class="teacher-workspace-title">برنامه هفتگی</h1>
            <p class="teacher-workspace-description">زمان‌های ثابت هر کلاس را ثبت کن؛ همین برنامه در داشبورد استاد و مسیرهای آموزشی دانش‌آموز قابل استفاده است.</p>
        </div>
        <a href="{{ route('teacher.classrooms.index') }}" class="teacher-workspace-btn secondary">کلاس‌ها</a>
    </header>

    @if(session('success'))
        <div class="teacher-workspace-alert success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="teacher-workspace-alert error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head">
            <div><h2>افزودن زمان ثابت</h2><p>هر ردیف یک بازه تکرارشونده در هفته است.</p></div>
        </div>
        <form method="POST" action="{{ route('teacher.schedule.store') }}" class="teacher-workspace-form-grid">
            @csrf
            <label class="teacher-workspace-field">
                <span>کلاس</span>
                <select name="classroom_id" required>
                    <option value="">انتخاب کلاس</option>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->title }} · {{ $classroom->course?->title }}</option>
                    @endforeach
                </select>
            </label>
            <label class="teacher-workspace-field">
                <span>روز هفته</span>
                <select name="weekday" required>
                    @foreach([6=>'شنبه',0=>'یکشنبه',1=>'دوشنبه',2=>'سه‌شنبه',3=>'چهارشنبه',4=>'پنجشنبه',5=>'جمعه'] as $value=>$label)
                        <option value="{{ $value }}" @selected((string)old('weekday','6') === (string)$value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="teacher-workspace-field"><span>شروع</span><input type="time" name="start_time" value="{{ old('start_time') }}" required></label>
            <label class="teacher-workspace-field"><span>پایان</span><input type="time" name="end_time" value="{{ old('end_time') }}" required></label>
            <label class="teacher-workspace-field"><span>اتاق / عنوان جلسه <small>اختیاری</small></span><input name="room" value="{{ old('room') }}" placeholder="مثلاً کلاس ۳"></label>
            <label class="teacher-workspace-field"><span>لینک آنلاین <small>اختیاری</small></span><input name="meeting_url" type="url" dir="ltr" value="{{ old('meeting_url') }}" placeholder="https://..."></label>
            <div class="teacher-workspace-actions" style="grid-column:1/-1;justify-content:flex-end">
                <button type="submit" class="teacher-workspace-btn primary">افزودن به برنامه</button>
            </div>
        </form>
    </section>

    <section class="teacher-workspace-grid">
        @forelse($classrooms as $classroom)
            <article class="teacher-workspace-card">
                <div class="teacher-workspace-card-head">
                    <div><h2>{{ $classroom->title }}</h2><p>{{ $classroom->course?->title }}</p></div>
                    <span class="teacher-workspace-chip">{{ AppSupportPersianUi::digits($classroom->schedules->count()) }} زمان</span>
                </div>
                <div class="teacher-workspace-list">
                    @forelse($classroom->schedules->sortBy(['weekday','start_time']) as $schedule)
                        <div class="teacher-workspace-item">
                            <div class="teacher-workspace-item-main">
                                <strong class="teacher-workspace-item-title">{{ [0=>'یکشنبه',1=>'دوشنبه',2=>'سه‌شنبه',3=>'چهارشنبه',4=>'پنجشنبه',5=>'جمعه',6=>'شنبه'][$schedule->weekday] ?? 'روز نامشخص' }}</strong>
                                <div class="teacher-workspace-item-meta">
                                    <span>{{ AppSupportPersianUi::digits(IlluminateSupportCarbon::parse($schedule->start_time)->format('H:i')) }} تا {{ AppSupportPersianUi::digits(IlluminateSupportCarbon::parse($schedule->end_time)->format('H:i')) }}</span>
                                    <span>{{ $schedule->room ?: 'بدون اتاق' }}</span>
                                    @if($schedule->meeting_url)<span>جلسه آنلاین</span>@endif
                                </div>
                            </div>
                            <div class="teacher-workspace-item-actions">
                                <form method="POST" action="{{ route('teacher.schedule.destroy',$schedule) }}" onsubmit="return confirm('این زمان از برنامه هفتگی حذف شود؟')">
                                    @csrf @method('DELETE')
                                    <button class="teacher-workspace-link" type="submit">حذف</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="teacher-workspace-empty"><strong>زمانی ثبت نشده.</strong>برای این کلاس هنوز برنامه ثابت تعریف نشده است.</div>
                    @endforelse
                </div>
            </article>
        @empty
            <div class="teacher-workspace-card" style="grid-column:1/-1"><div class="teacher-workspace-empty"><strong>کلاسی برای برنامه‌ریزی وجود ندارد.</strong>ابتدا یک کلاس بساز.</div></div>
        @endforelse
    </section>
</div>
@endsection
