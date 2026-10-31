@extends('layouts.student')
@section('title', 'خانه یادگیری')
@section('heading', 'خانه یادگیری من')

@section('content')
@php
    $liveItems = collect($upcomingLiveClasses ?? []);
    $assignmentItems = collect($assignments ?? []);
    $pendingAssignmentItems = $assignmentItems->filter(fn ($item) => $item->submitted_at === null)->take(4);
    $courseItems = collect($courses ?? [])->take(4);
    $recordingItems = collect($sessions ?? [])->filter(fn ($item) => ($item['available'] ?? false) === true)->take(3);
    $weekItems = collect($studyWeek ?? []);
    $maxStudyMinutes = max(1, (int) $weekItems->max('minutes'));
    $firstAssignment = $pendingAssignmentItems->first();
    $firstLive = $liveItems->first();

    if (!empty($nextLesson)) {
        $nextAction = (object) [
            'text' => 'درس بعدی تو آماده است؛ از همان‌جایی که مانده‌ای ادامه بده.',
            'url' => route('student.lessons.show', $nextLesson->id),
            'label' => 'ادامه یادگیری',
        ];
    } elseif ($firstLive) {
        $nextAction = (object) [
            'text' => 'برنامه کلاس‌های پیش‌رو را بررسی کن و برای جلسه بعدی آماده شو.',
            'url' => route('student.live-classes.index'),
            'label' => 'دیدن برنامه کلاس‌ها',
        ];
    } elseif ($firstAssignment) {
        $nextAction = (object) [
            'text' => 'یک تکلیف در انتظار ارسال داری؛ قبل از پایان مهلت آن را بررسی کن.',
            'url' => route('student.assignments.show', $firstAssignment->id),
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

<div class="stats">
  <x-stat color="blue" icon="i-video" label="کلاس‌های پیش‌رو" :value="\App\Support\PersianUi::digits($upcomingLiveClassCount)" hint="جلسه‌های قابل‌دسترسی تو" :href="route('student.live-classes.index')" />
  <x-stat color="red" icon="i-task" label="تکالیف ارسال‌نشده" :value="\App\Support\PersianUi::digits($pendingAssignments)" hint="تعداد کل تکالیف باز" :href="route('student.assignments.index')" />
  <x-stat color="amber" icon="i-book" label="دوره‌های فعال" :value="\App\Support\PersianUi::digits($activeCourseCount)" hint="دوره‌های در دسترس تو" :href="route('student.courses.index')" />
  <x-stat color="mint" icon="i-rocket" label="پیشرفت یادگیری" :value="\App\Support\PersianUi::digits($overallProgress).'٪'" hint="میانگین پیشرفت دوره‌ها" :href="route('student.results.index')" />
</div>

<div class="row">
  <section class="card">
    <div class="chead"><h2>کلاس‌های پیش‌رو</h2><a class="pill" href="{{ route('student.live-classes.index') }}">همه کلاس‌ها</a></div>
    @forelse ($liveItems as $class)
      <div class="trk">
        <div class="th blue"></div>
        <div>
          <b>{{ $class->title }}</b>
          <span>{{ $class->course_title }} · {{ $class->scheduled_at?->format('Y/m/d H:i') }}</span>
        </div>
        @if ($class->can_join)
          <a class="pl" href="{{ route('student.live-classes.join', $class->id) }}" aria-label="ورود به کلاس"><svg class="i"><use href="#i-play"/></svg></a>
        @else
          <span class="tag b">برنامه‌ریزی‌شده</span>
        @endif
      </div>
    @empty
      <x-empty icon="i-video" text="در حال حاضر جلسه‌ای برای نمایش وجود ندارد." />
    @endforelse
  </section>

  <section class="card">
    <div class="chead"><h2>مهلت تکالیف</h2><a class="pill" href="{{ route('student.assignments.index') }}">همه تکالیف</a></div>
    @forelse ($pendingAssignmentItems as $assignment)
      <a class="trk" href="{{ route('student.assignments.show', $assignment->id) }}">
        <div class="th amber"></div>
        <div><b>{{ $assignment->title }}</b><span>تکلیف دوره فعال</span></div>
        <x-due :at="$assignment->due_at" />
      </a>
    @empty
      <x-empty icon="i-task" text="تکلیف باز و ارسال‌نشده‌ای در فهرست فعلی نداری." />
    @endforelse
  </section>
</div>

<section class="card">
  <div class="chead"><h2>پیشرفت درس‌ها</h2><a class="pill" href="{{ route('student.courses.index') }}">دوره‌های من</a></div>
  @forelse ($courseItems as $course)
    @php($progress = max(0, min(100, (float) ($course->learning_progress ?? 0))))
    <div class="meter">
      <div>
        <div class="h"><span>{{ $course->title }}</span><span>{{ \App\Support\PersianUi::digits(round($progress)) }}٪</span></div>
        <div class="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($progress) }}">
          <i style="width:{{ $progress }}%;background:linear-gradient(90deg,#5bb8c4,#3d7bff)"></i>
        </div>
      </div>
    </div>
  @empty
    <x-empty icon="i-book" text="هنوز دوره فعالی به حساب تو متصل نشده است." :href="route('student.courses.index')" action="مشاهده دوره‌ها" />
  @endforelse
</section>

<div class="row">
  <section class="card">
    <div class="chead"><h2>زمان مطالعه در هفته گذشته</h2><span class="pill">{{ \App\Support\PersianUi::digits($studyMinutesLast7Days) }} دقیقه</span></div>
    <div class="bars" role="img" aria-label="زمان مطالعه در هفت روز گذشته">
      <div class="cols">
        @foreach ($weekItems as $day)
          @php($height = min(100, round(((int) $day['minutes'] / $maxStudyMinutes) * 100)))
          <div class="col">
            <b>{{ \App\Support\PersianUi::digits($day['minutes']) }}</b>
            <div class="th blue" style="height:{{ max(4, $height) }}%"></div>
            <span>{{ $day['weekday'] }}</span>
          </div>
        @endforeach
      </div>
    </div>
    <p class="small muted">پیوستگی مطالعه: {{ \App\Support\PersianUi::digits($studyStreak) }} روز</p>
  </section>

  <section class="card">
    <div class="chead"><h2>آخرین نتیجه‌ها</h2><a class="pill" href="{{ route('student.results.index') }}">کارنامه</a></div>
    @forelse ($recentResults as $result)
      <div class="trk">
        <div class="th violet"><svg class="i"><use href="#{{ $result->type === 'exam' ? 'i-exam' : 'i-task' }}"/></svg></div>
        <div>
          <b>{{ $result->title }}</b>
          <span>{{ $result->status_label }} @if($result->occurred_at) · {{ \App\Support\PersianUi::date($result->occurred_at) }} @endif</span>
        </div>
        @if ($result->score !== null)<x-tag tone="g">{{ \App\Support\PersianUi::number($result->score, 1) }}</x-tag>@endif
      </div>
    @empty
      <x-empty icon="i-chart" text="هنوز نتیجه‌ای برای نمایش ثبت نشده است." />
    @endforelse
  </section>
</div>

@if ($recordingItems->isNotEmpty())
<section class="card">
  <div class="chead"><h2>فیلم جلسه‌های منتشرشده</h2><a class="pill" href="{{ route('student.live-classes.index') }}">همه ضبط‌ها</a></div>
  <div class="g3">
    @foreach ($recordingItems as $recording)
      <a class="card cc" href="{{ $recording['href'] }}">
        <div class="th blue"><svg class="i"><use href="#i-play"/></svg></div>
        <h3>{{ $recording['title'] }}</h3>
        <p>{{ $recording['course'] }} · {{ $recording['date'] }}</p>
      </a>
    @endforeach
  </div>
</section>
@endif

@if ($achievements->isNotEmpty())
<section class="card">
  <div class="chead"><h2>دستاوردهای اخیر</h2><a class="pill" href="{{ route('student.achievements.index') }}">همه دستاوردها</a></div>
  @foreach ($achievements as $achievement)
    <div class="trk"><div class="pl"><svg class="i"><use href="#i-trophy"/></svg></div><div><b>{{ $achievement->display_name ?: $achievement->title }}</b><span>{{ $achievement->school_name ?: 'دستاورد ثبت‌شده' }}</span></div></div>
  @endforeach
</section>
@endif
@endsection
