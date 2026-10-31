@extends('layouts.student')
@section('title', 'خانه یادگیری')
@section('heading', 'خانه یادگیری من')

@section('content')
@php
    $firstLiveClass = $todayClasses->first() ?? $nextLiveClass;
    $firstDeadline = $deadlines->first();
    $firstPendingAssignment = $assignments->first(fn ($item) => $item->submitted_at === null);

    if (!empty($nextLesson)) {
        $nextAction = (object) [
            'text' => 'درس بعدی تو آماده است؛ از همان‌جایی که مانده‌ای ادامه بده.',
            'url' => route('student.lessons.show', $nextLesson->id),
            'label' => 'ادامه یادگیری',
        ];
    } elseif ($firstLiveClass) {
        $nextAction = (object) [
            'text' => 'برنامه کلاس‌های پیش‌رو را بررسی کن و برای جلسه بعدی آماده شو.',
            'url' => route('student.live-classes.index'),
            'label' => 'دیدن برنامه کلاس‌ها',
        ];
    } elseif ($firstPendingAssignment) {
        $nextAction = (object) [
            'text' => 'یک تکلیف در انتظار ارسال داری؛ قبل از پایان مهلت آن را بررسی کن.',
            'url' => route('student.assignments.show', $firstPendingAssignment->id),
            'label' => 'بررسی تکلیف',
        ];
    } else {
        $nextAction = (object) [
            'text' => 'از دوره‌های فعال خودت شروع کن و قدم بعدی مسیر یادگیری را بردار.',
            'url' => route('student.courses.index'),
            'label' => 'رفتن به دوره‌ها',
        ];
    }
@endphp

<section class="hero">
  <svg class="av" viewBox="0 0 120 120" role="img" aria-label="تصویر دانش‌آموز"><use href="#avatar"/></svg>
  <div class="txt">
    <h2>سلام، {{ $student->name }}!</h2>
    <p>{{ $nextAction->text }}</p>
    <a class="btn" href="{{ $nextAction->url }}">{{ $nextAction->label }}</a>
  </div>
</section>

@if ($announcements->isNotEmpty())
<section class="card">
  <div class="chead"><h2>اطلاعیه‌های آموزشگاه</h2><a class="pill" href="{{ route('student.messages.index') }}">همه پیام‌ها</a></div>
  @foreach ($announcements as $announcement)
    <a class="trk" href="{{ route('student.messages.index') }}">
      <div class="th violet"><svg class="i" aria-hidden="true"><use href="#i-bell"/></svg></div>
      <div>
        <b>{{ $announcement->subject }}</b>
        <span>{{ $announcement->excerpt }}</span>
      </div>
      <span class="pill">{{ \App\Support\PersianUi::date($announcement->published_at) }}</span>
    </a>
  @endforeach
</section>
@endif

<div class="stats">
  <x-stat color="blue" icon="i-video" label="کلاس‌های امروز" :value="\App\Support\PersianUi::digits($todayLiveClassCount)" hint="جلسه‌های برنامه‌ریزی‌شده و در حال برگزاری" :href="route('student.live-classes.index')" />
  <x-stat color="red" icon="i-task" label="تکالیف ارسال‌نشده" :value="\App\Support\PersianUi::digits($pendingAssignments)" hint="{{ \App\Support\PersianUi::digits($deadlines->count()) }} مهلت نزدیک در فهرست" :href="route('student.assignments.index')" />
  <x-stat color="amber" icon="i-exam" label="آزمون‌های پیش‌رو" :value="\App\Support\PersianUi::digits($upcomingExamCount)" hint="{{ $nextExamDate ? 'نزدیک‌ترین آزمون: '.\App\Support\PersianUi::date($nextExamDate) : 'هنوز آزمون زمان‌دار اعلام نشده' }}" :href="route('student.exams.index')" />
  <x-stat color="mint" icon="i-rocket" label="پیشرفت دوره‌ها" :value="\App\Support\PersianUi::digits($overallProgress).'٪'" hint="میانگین پیشرفت دوره‌های فعال" :href="route('student.results.index')" />
</div>

<div class="row">
  <section class="card">
    <div class="chead"><h2>کلاس‌های امروز</h2><a class="pill" href="{{ route('student.live-classes.index') }}">همه کلاس‌ها</a></div>
    @forelse ($todayClasses as $class)
      <div class="trk">
        <div class="th {{ $class->status === 'live' ? 'mint' : 'blue' }}"><svg class="i" aria-hidden="true"><use href="#i-video"/></svg></div>
        <div>
          <b>{{ $class->title }}</b>
          <span>{{ $class->course_title }} · {{ $class->teacher_name ?: 'مدرس دوره' }} · {{ \App\Support\PersianUi::time($class->scheduled_at) }}</span>
        </div>
        @if ($class->can_join)
          <a class="pl" href="{{ route('student.live-classes.join', $class->id) }}" aria-label="ورود امن به کلاس"><svg class="i"><use href="#i-play"/></svg></a>
        @elseif ($class->status === 'live')
          <x-tag tone="a">در حال برگزاری</x-tag>
        @else
          <x-tag tone="b">برنامه‌ریزی‌شده</x-tag>
        @endif
      </div>
    @empty
      <x-empty icon="i-video" text="برای امروز کلاسی برنامه‌ریزی نشده است." :href="route('student.live-classes.index')" action="دیدن برنامه هفتگی" />
    @endforelse
  </section>

  <section class="card">
    <div class="chead"><h2>مهلت تکالیف</h2><a class="pill" href="{{ route('student.assignments.index') }}">همه تکالیف</a></div>
    @forelse ($deadlines as $assignment)
      <a class="trk" href="{{ route('student.assignments.show', $assignment->id) }}">
        <div class="th amber"><svg class="i" aria-hidden="true"><use href="#i-task"/></svg></div>
        <div>
          <b>{{ $assignment->title }}</b>
          <span>{{ $assignment->course_title ?: 'دوره فعال' }}</span>
        </div>
        <x-due :at="$assignment->due_at" />
      </a>
    @empty
      <x-empty icon="i-task" text="تکلیف ارسال‌نشده‌ای با مهلت نزدیک نداری." :href="route('student.assignments.index')" action="مشاهده تکالیف" />
    @endforelse
  </section>
</div>

@if ($sessions->contains(fn ($session) => ($session['available'] ?? false) === true))
<section class="card">
  <div class="chead"><h2>ضبط جلسه‌های اخیر</h2><a class="pill" href="{{ route('student.live-classes.index') }}">همه ضبط‌ها</a></div>
  <div class="g3">
    @foreach ($sessions->where('available', true)->take(3) as $recording)
      <a class="card cc" href="{{ $recording['href'] }}">
        <div class="th blue"><svg class="i" aria-hidden="true"><use href="#i-play"/></svg></div>
        <h3>{{ $recording['title'] }}</h3>
        <p>{{ $recording['course'] }} · {{ $recording['date'] }}</p>
      </a>
    @endforeach
  </div>
</section>
@endif

<section class="card">
  <div class="chead"><h2>پیشرفت درس‌ها</h2><a class="pill" href="{{ route('student.courses.index') }}">دوره‌های من</a></div>
  <div class="meter">
    @forelse ($courses as $course)
      @php($progress = max(0, min(100, (float) ($course->learning_progress ?? 0))))
      <div>
        <div class="h">
          <span>{{ $course->title }}</span>
          <span>{{ \App\Support\PersianUi::digits(round($progress)) }}٪</span>
        </div>
        <div class="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($progress) }}">
          <i style="width:{{ $progress }}%;background:linear-gradient(90deg,#5bb8c4,#3d7bff)"></i>
        </div>
        <small>{{ \App\Support\PersianUi::digits($course->completed_lessons ?? 0) }} از {{ \App\Support\PersianUi::digits($course->total_lessons ?? 0) }} درس تکمیل‌شده</small>
        <a class="pill" href="{{ route('student.courses.show', $course->id) }}">ادامه دوره</a>
      </div>
    @empty
      <x-empty icon="i-book" text="هنوز دوره فعالی به حساب تو متصل نشده است." :href="route('student.courses.index')" action="مشاهده دوره‌ها" />
    @endforelse
  </div>
</section>
@endsection
