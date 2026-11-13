@extends('layouts.student')
@section('title', 'خانه یادگیری')
@section('heading', 'مرکز یادگیری')
@section('body-class', 'student-dashboard-live')

@section('content')
@php
    $firstLiveClass = $todayClasses->first() ?? $nextLiveClass;
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
    <a class="btn" href="{{ route('account.guide', ['role' => 'student']) }}" style="margin-inline-start:8px">راهنمای پنل و آموزش کار با آن</a>
  </div>
</section>

<x-dashboard-next-step href="{{ route('student.courses.index') }}" link-label="رفتن به دوره‌های من" eyebrow="ادامهٔ مسیر یادگیری">
  برای ادامهٔ درس‌ها، «دوره‌های من» را باز کن. تکلیف‌ها و آزمون‌ها در بخش‌های جداگانهٔ منوی پنل هستند؛ پیام مدرس و اطلاعیه‌ها را هم از «پیام‌ها» دنبال کن.
</x-dashboard-next-step>

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
  <x-stat color="red" icon="i-task" label="تکالیف باز" :value="\App\Support\PersianUi::digits($pendingAssignments)" hint="{{ \App\Support\PersianUi::digits($dueTomorrowCount) }} مورد تا فردا" :href="route('student.assignments.index')" />
  <x-stat color="amber" icon="i-exam" label="آزمون پیش‌رو" :value="\App\Support\PersianUi::digits($upcomingExamCount)" hint="{{ $nextExamDate ? 'نزدیک‌ترین آزمون: '.\App\Support\PersianUi::date($nextExamDate) : 'هنوز آزمون زمان‌دار اعلام نشده' }}" :href="route('student.exams.index')" />
  <x-stat color="mint" icon="i-rocket" label="پیشرفت دوره‌ها" :value="\App\Support\PersianUi::digits($overallProgress)" hint="{{ \App\Support\PersianUi::digits($completedLessonsCount) }} از {{ \App\Support\PersianUi::digits($totalLessonsCount) }} درس تکمیل شده" :href="route('student.growth')" />
</div>

<section class="card student-dashboard-weekly" aria-labelledby="student-weekly-title">
  @php
    $studyWeek = collect($studyWeek ?? []);
    $maxStudyMinutes = max(1, (int) $studyWeek->max('minutes'));
  @endphp
  <div class="chead">
    <div>
      <h2 id="student-weekly-title">ریتم یادگیری این هفته</h2>
      <p class="muted small">زمان تماشای درس‌های دوره‌های خودت در هفت روز گذشته</p>
    </div>
    <span class="pill">{{ \App\Support\PersianUi::digits($studyStreak ?? 0) }} روز پیوسته</span>
  </div>
  <div class="student-weekly-summary">
    <strong>{{ \App\Support\PersianUi::digits($studyMinutesLast7Days ?? 0) }} <small>دقیقه</small></strong>
    <span>یادگیری ثبت‌شده در هفت روز گذشته</span>
  </div>
  <div class="student-weekly-bars" role="img" aria-label="نمودار زمان مطالعه در هفت روز گذشته">
    @foreach($studyWeek as $day)
      @php($minutes = max(0, (int) ($day['minutes'] ?? 0)))
      <div class="student-weekly-day" title="{{ $day['weekday'] ?? '' }}: {{ \App\Support\PersianUi::digits($minutes) }} دقیقه">
        <span class="student-weekly-bar-track"><i class="{{ $minutes > 0 ? '' : 'is-empty' }}" style="height: {{ $minutes > 0 ? min(100, max(6, ($minutes / $maxStudyMinutes) * 100)) : 0 }}%"></i></span>
        <strong>{{ $day['weekday'] ?? '' }}</strong>
        <small>{{ $day['label'] ?? '' }}</small>
      </div>
    @endforeach
  </div>
</section>

<div class="row student-dashboard-learning-feed">
  <section class="card" aria-labelledby="student-results-preview-title">
    <div class="chead">
      <h2 id="student-results-preview-title">آخرین نتیجه‌ها</h2>
      <a class="pill" href="{{ route('student.results.index') }}">کارنامه کامل</a>
    </div>
    @forelse($recentResults as $result)
      <a class="trk" href="{{ route('student.results.index') }}">
        <div class="th {{ $result->type === 'exam' ? 'violet' : 'blue' }}">
          <svg class="i" aria-hidden="true"><use href="#{{ $result->type === 'exam' ? 'i-exam' : 'i-task' }}"/></svg>
        </div>
        <div>
          <b>{{ $result->title }}</b>
          <span>{{ $result->status_label }} · {{ $result->occurred_at ? \App\Support\PersianUi::date($result->occurred_at) : '—' }}</span>
        </div>
        @if($result->score !== null)
          <x-tag :tone="($result->is_provisional ?? false) ? 'a' : 'g'">{{ \App\Support\PersianUi::number($result->score, 1) }}</x-tag>
        @else
          <x-tag tone="b">در انتظار نمره</x-tag>
        @endif
      </a>
    @empty
      <x-empty icon="i-chart" text="هنوز نتیجه‌ای برای نمایش ثبت نشده است." :href="route('student.results.index')" action="مشاهده کارنامه" />
    @endforelse
  </section>

  <section class="card" aria-labelledby="student-resources-preview-title">
    <div class="chead">
      <h2 id="student-resources-preview-title">جزوه‌ها و منابع تازه</h2>
      <a class="pill" href="{{ route('student.resources.index') }}">همه منابع</a>
    </div>
    @forelse($resources as $resource)
      <a class="trk" href="{{ route('student.resources.index') }}">
        <div class="th mint"><svg class="i" aria-hidden="true"><use href="#i-file"/></svg></div>
        <div>
          <b>{{ $resource->title }}</b>
          <span>{{ $resource->course?->title ?? $resource->academy?->name ?? 'منبع آموزشی' }}</span>
        </div>
        <span class="pill">مشاهده</span>
      </a>
    @empty
      <x-empty icon="i-file" text="هنوز منبع آموزشی تازه‌ای برایت منتشر نشده است." :href="route('student.resources.index')" action="رفتن به منابع" />
    @endforelse
  </section>
</div>

<section class="card student-dashboard-contact">
  <div class="chead">
    <div>
      <h2>ارتباط با مدرس</h2>
      <p class="muted small">اگر درباره درس، تکلیف یا آزمون سؤالی داری، از همین‌جا برای مدرس مرتبط پیام بفرست و پاسخ را در کارتابلت دنبال کن.</p>
    </div>
    <a class="btn s" href="{{ route('student.messages.index') }}">پیام به مدرس‌ها</a>
  </div>
</section>

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
          <span class="tag b" data-class-countdown="{{ $class->scheduled_at?->toIso8601String() }}"></span>
        @endif
      </div>
    @empty
      <x-empty icon="i-video" text="برای امروز کلاسی برنامه‌ریزی نشده است." :href="route('student.live-classes.index')" action="دیدن برنامه هفتگی" />
    @endforelse
  </section>

  <section class="card">
    <div class="chead"><h2>مهلت‌های نزدیک</h2><a class="pill" href="{{ route('student.assignments.index') }}">همه تکالیف</a></div>
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
  <div class="chead"><h2>فیلم جلسه‌های اخیر</h2><a class="pill" href="{{ route('student.live-classes.index') }}">همه ضبط‌ها</a></div>
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
