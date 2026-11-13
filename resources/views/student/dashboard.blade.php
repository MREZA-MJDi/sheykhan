@extends('layouts.student')
@section('title', 'خانه یادگیری')
@section('heading', 'مرکز یادگیری')
@section('body-class', 'student-dashboard-live')

@section('content')
@php
    $firstLiveClass = $todayClasses->first() ?? $nextLiveClass;
    $firstPendingAssignment = $assignments->first(fn ($item) => $item->submitted_at === null);

    if (!empty($nextLesson)) {
        $nextAction = [
            'eyebrow' => 'ادامه مسیر یادگیری',
            'title' => 'درس بعدی آماده است',
            'description' => 'از همان‌جایی که متوقف شدی ادامه بده و پیشرفتت را ثبت کن.',
            'url' => route('student.lessons.show', $nextLesson->id),
            'label' => 'ادامه یادگیری',
            'icon' => 'book',
        ];
    } elseif ($firstLiveClass) {
        $nextAction = [
            'eyebrow' => 'برنامه آموزشی',
            'title' => 'کلاس پیش‌رو را از دست نده',
            'description' => 'زمان جلسه و امکان ورود امن به کلاس را در برنامه کلاس‌ها بررسی کن.',
            'url' => route('student.live-classes.index'),
            'label' => 'دیدن برنامه کلاس‌ها',
            'icon' => 'video',
        ];
    } elseif ($firstPendingAssignment) {
        $nextAction = [
            'eyebrow' => 'قدم بعدی یادگیری',
            'title' => 'یک تکلیف در انتظار ارسال داری',
            'description' => 'تکلیف «' . $firstPendingAssignment->title . '» را باز کن و پاسخ خودت را پیش از پایان مهلت ثبت کن.',
            'url' => route('student.assignments.show', $firstPendingAssignment->id),
            'label' => 'بررسی تکلیف',
            'icon' => 'task',
        ];
    } elseif ($resources->isNotEmpty()) {
        $nextAction = [
            'eyebrow' => 'منابع آموزشی',
            'title' => 'برای شروع، منابع آموزشی را ببین',
            'description' => 'جزوه‌ها و فایل‌هایی که برای حساب تو منتشر شده‌اند از بخش منابع در دسترس هستند.',
            'url' => route('student.resources.index'),
            'label' => 'مشاهده منابع',
            'icon' => 'file',
        ];
    } else {
        $nextAction = [
            'eyebrow' => 'خوش آمدی',
            'title' => 'مسیر یادگیری تو از اینجا شروع می‌شود',
            'description' => 'به‌محض اتصال کلاس یا انتشار تکلیف و منبع آموزشی، اطلاعات آن در این کارتابل نمایش داده می‌شود.',
            'url' => route('student.courses.index'),
            'label' => 'رفتن به دوره‌ها',
            'icon' => 'rocket',
        ];
    }
@endphp

<section class="hero student-welcome" id="overview" aria-labelledby="student-welcome-title">
  <div class="txt">
    <span class="student-welcome__eyebrow">کارتابل آموزشی شخصی تو</span>
    <h2 id="student-welcome-title">سلام، {{ $student->name }}!</h2>
    <p>وضعیت کلاس‌ها، تکالیف، منابع و نتیجه‌های آموزشی‌ات را یک‌جا دنبال کن.</p>
    <div class="student-welcome__actions">
      <a class="btn" href="{{ $nextAction['url'] }}">{{ $nextAction['label'] }}</a>
      @can('messages.view.own')
        <a class="btn g" href="{{ route('student.messages.index') }}">پیام‌های من</a>
      @endcan
      <a class="btn g" href="{{ route('account.guide', ['role' => 'student']) }}">راهنمای پنل</a>
    </div>
  </div>
  <div class="student-welcome__art" aria-hidden="true">
    <div class="student-welcome__art-orbit"></div>
    <div class="student-welcome__art-icon"><svg class="i" aria-hidden="true"><use href="#i-book"/></svg></div>
    <span class="student-welcome__art-dot student-welcome__art-dot--one"></span>
    <span class="student-welcome__art-dot student-welcome__art-dot--two"></span>
  </div>
</section>

<section class="student-next-step card" aria-labelledby="student-next-step-title">
  <div class="student-next-step__icon"><svg class="i" aria-hidden="true"><use href="#i-{{ $nextAction['icon'] }}"/></svg></div>
  <div class="student-next-step__copy">
    <span class="student-section-eyebrow">{{ $nextAction['eyebrow'] }}</span>
    <h2 id="student-next-step-title">{{ $nextAction['title'] }}</h2>
    <p>{{ $nextAction['description'] }}</p>
  </div>
  <a class="btn s" href="{{ $nextAction['url'] }}">{{ $nextAction['label'] }}</a>
</section>

@if ($announcements->isNotEmpty())
<section class="card student-announcements" id="announcements" aria-labelledby="student-announcements-title">
  <div class="chead">
    <div><span class="student-section-eyebrow">از طرف آموزشگاه</span><h2 id="student-announcements-title">اطلاعیه‌های مهم</h2></div>
    <span class="pill">{{ \App\Support\PersianUi::digits($announcements->count()) }} اطلاعیه</span>
  </div>
  <div class="student-announcements__list">
    @foreach ($announcements as $announcement)
      <a class="student-announcement" href="{{ route('student.messages.index') }}">
        <span class="student-announcement__icon"><svg class="i" aria-hidden="true"><use href="#i-bell"/></svg></span>
        <span class="student-announcement__copy">
          <strong>{{ $announcement->subject }}</strong>
          <span>{{ $announcement->excerpt }}</span>
          <time datetime="{{ $announcement->published_at?->toIso8601String() }}">{{ \App\Support\PersianUi::dateTime($announcement->published_at) }}</time>
        </span>
      </a>
    @endforeach
  </div>
</section>
@endif

<div class="stats student-dashboard-stats" aria-label="خلاصه وضعیت آموزشی">
  <x-stat color="blue" icon="i-video" label="کلاس‌های امروز" :value="\App\Support\PersianUi::digits($todayLiveClassCount)" hint="جلسه‌های برنامه‌ریزی‌شده و در حال برگزاری" :href="route('student.live-classes.index')" />
  <x-stat color="red" icon="i-task" label="تکالیف باز" :value="\App\Support\PersianUi::digits($pendingAssignments)" hint="{{ \App\Support\PersianUi::digits($dueTomorrowCount) }} مورد تا فردا" :href="route('student.assignments.index')" />
  @can('attendance.view.own')
    <x-stat color="amber" icon="i-checksq" label="نرخ حضور" :value="$attendanceRate === null ? '—' : \App\Support\PersianUi::digits($attendanceRate).'٪'" hint="{{ \App\Support\PersianUi::digits($attendanceCount) }} جلسه اندازه‌گیری‌شده" :href="route('student.growth')" />
  @else
    <x-stat color="amber" icon="i-exam" label="آزمون پیش‌رو" :value="\App\Support\PersianUi::digits($upcomingExamCount)" hint="{{ $nextExamDate ? 'نزدیک‌ترین آزمون: '.\App\Support\PersianUi::date($nextExamDate) : 'هنوز آزمون زمان‌دار اعلام نشده' }}" :href="route('student.exams.index')" />
  @endcan
  <x-stat color="mint" icon="i-chart" label="نتایج اخیر" :value="\App\Support\PersianUi::digits($recentResults->count())" hint="نمره‌های ثبت‌شده برای تو" :href="route('student.results.index')" />
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

<div class="row student-dashboard-secondary">
  <section class="card" id="results" aria-labelledby="student-results-preview-title">
    <div class="chead">
      <h2 id="student-results-preview-title">آخرین نتیجه‌ها</h2>
      <div class="student-results-actions">
        @if($upcomingExamCount > 0)
          <a class="pill" href="{{ route('student.exams.index') }}">{{ \\App\\Support\\PersianUi::digits($upcomingExamCount) }} آزمون پیش‌رو</a>
        @endif
        <a class="pill" href="{{ route('student.results.index') }}">کارنامه کامل</a>
      </div>
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

  <section class="card" id="resources" aria-labelledby="student-resources-preview-title">
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



<div class="row student-dashboard-primary">
  <section class="card" id="classes">
    <div class="chead"><h2>کلاس‌های امروز</h2><a class="pill" href="{{ route('student.live-classes.index') }}">همه کلاس‌ها</a></div>
    @forelse ($todayClasses as $class)
      <div class="trk student-class-item">
        <div class="th student-class-item__icon {{ $class->status === 'live' ? 'mint' : 'blue' }}"><svg class="i" aria-hidden="true"><use href="#i-video"/></svg></div>
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

  <section class="card" id="homework">
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
<section class="card" id="recordings">
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

<section class="card student-course-progress" id="course-progress" aria-labelledby="student-course-progress-title">
  <div class="chead">
    <div><span class="student-section-eyebrow">مسیر یادگیری</span><h2 id="student-course-progress-title">پیشرفت دوره‌های من</h2></div>
    <a class="btn g s" href="{{ route('student.courses.index') }}">همه دوره‌ها</a>
  </div>
  <div class="student-course-progress__list">
    @forelse ($courses as $course)
      @php($progress = max(0, min(100, (float) ($course->learning_progress ?? 0))))
      <a class="student-course-progress__item" href="{{ route('student.courses.show', $course->id) }}">
        <div class="student-course-progress__head">
          <strong>{{ $course->title }}</strong>
          <span>{{ \App\Support\PersianUi::digits(round($progress)) }}٪</span>
        </div>
        <div class="student-attendance-meter__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($progress) }}">
          <span style="width: {{ $progress }}%"></span>
        </div>
        <small class="mu">{{ \App\Support\PersianUi::digits($course->completed_lessons ?? 0) }} از {{ \App\Support\PersianUi::digits($course->total_lessons ?? 0) }} درس تکمیل‌شده</small>
      </a>
    @empty
      <x-empty icon="i-book" text="هنوز دوره فعالی به حساب تو متصل نشده است." :href="route('student.courses.index')" action="مشاهده دوره‌ها" />
    @endforelse
  </div>
</section>
@endsection
