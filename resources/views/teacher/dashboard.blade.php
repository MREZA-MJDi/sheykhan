@extends('layouts.teacher')
@section('title', 'داشبورد مدرس | شیخان')
@section('page_title', 'خوش آمدید، ' . (auth()->user()->name ?? 'مدرس'))
@section('page_subtitle', $dashboardDate ?? \App\Support\PersianUi::date(now()))
@section('content')
@php
  $user = $teacher ?? auth()->user();
  $metrics = $metrics ?? [];
  $classes = max(0,(int)($metrics['activeClasses'] ?? 0));
  $students = max(0,(int)($metrics['studentCount'] ?? 0));
  $pendingAssignments = max(0,(int)($metrics['pendingAssignmentReviews'] ?? 0));
  $pendingExams = max(0,(int)($metrics['pendingExamReviews'] ?? 0));
  $weeklySessions = max(0,(int)($metrics['weeklySessions'] ?? 0));
  $avatar = $user?->teacherProfile?->media?->firstWhere('pivot.collection','teacher-avatar');
  $avatarUrl = $avatar?->visibility === 'public' ? $avatar->url() : ($avatar ? route('media.view',$avatar) : null);
  $quick = [
    ['icon'=>'✓','label'=>'ثبت حضور و غیاب','hint'=>'ثبت وضعیت کلاس‌ها','route'=>'teacher.attendance.index','permission'=>'attendance.view','tone'=>'#22b573'],
    ['icon'=>'✎','label'=>'تکلیف جدید','hint'=>'ایجاد و انتشار تکلیف','route'=>'teacher.assignments.create','permission'=>'assignments.manage','tone'=>'#ff9f43'],
    ['icon'=>'◈','label'=>'ساخت آزمون','hint'=>'طراحی برگه و کلید پاسخ','route'=>'teacher.exams.create','permission'=>'exams.manage','tone'=>'#8b5cf6'],
    ['icon'=>'✉','label'=>'پیام‌ها','hint'=>'پیگیری گفتگوها','route'=>'teacher.direct-messages.index','permission'=>'parent_messages.manage','tone'=>'#14b8c4'],
    ['icon'=>'◷','label'=>'برنامه هفتگی','hint'=>'زمان‌بندی جلسات','route'=>'teacher.schedule.index','permission'=>'live_classes.view','tone'=>'#4a5fe0'],
    ['icon'=>'♥','label'=>'دانش‌آموزان','hint'=>'فهرست و وضعیت یادگیری','route'=>'teacher.students.index','permission'=>'students.view','tone'=>'#f0566b'],
  ];
  $quick = array_values(array_filter($quick,fn($item)=>$user?->hasPermission($item['permission'])));
  $kpis = [
    ['icon'=>'▤','value'=>$classes,'label'=>'کلاس فعال','hint'=>'مشاهده کلاس‌ها','url'=>route('teacher.classrooms.index'),'permission'=>'classrooms.view','tone'=>'#4a5fe0'],
    ['icon'=>'♥','value'=>$students,'label'=>'دانش‌آموز','hint'=>'مشاهده فهرست','url'=>route('teacher.students.index'),'permission'=>'students.view','tone'=>'#22b573'],
    ['icon'=>'✎','value'=>$pendingAssignments,'label'=>'تکلیف منتظر نمره','hint'=>'بررسی تحویل‌ها','url'=>route('teacher.assignments.index'),'permission'=>'assignments.view','tone'=>'#ff9f43'],
    ['icon'=>'✓','value'=>$pendingExams,'label'=>'آزمون منتظر بررسی','hint'=>'تصحیح پاسخ‌ها','url'=>route('teacher.exams.index'),'permission'=>'exams.view','tone'=>'#8b5cf6'],
    ['icon'=>'◷','value'=>$weeklySessions,'label'=>'جلسه این هفته','hint'=>'نمایش برنامه','url'=>route('teacher.schedule.index'),'permission'=>'live_classes.view','tone'=>'#14b8c4'],
  ];
  $kpis = array_values(array_filter($kpis,fn($item)=>$user?->hasPermission($item['permission'])));
  $classPreviews = collect($classroomRosterPreviews ?? []);
  $recent = collect($activities ?? []);
  $upcoming = collect($upcomingClasses ?? []);
  $today = collect($todaySessions ?? []);
  $weekChart = $chart['week'] ?? ['labels'=>[],'values'=>[]];
  $monthChart = $chart['month'] ?? ['labels'=>[],'values'=>[]];
@endphp
<section class="hero2">
  <div class="cov"></div>
  <div class="hp2">
    <div class="pa">@if($avatarUrl)<img src="{{ $avatarUrl }}" alt="تصویر {{ $user?->name }}">@else{{ \Illuminate\Support\Str::substr(trim((string)($user?->name ?? 'م')),0,1) }}@endif</div>
    <div style="min-width:0;flex:1"><b style="font-size:19px">{{ $user?->name }}</b><small style="display:block;color:var(--mu)">مدرس · {{ \App\Support\PersianUi::digits($classes) }} کلاس · {{ \App\Support\PersianUi::digits($students) }} دانش‌آموز</small></div>
    @if($user?->hasPermission('profile.view'))<a class="btn l" href="{{ route('teacher.profile.edit') }}">ویرایش پروفایل</a>@endif
  </div>
</section>

<div class="st3" aria-label="شاخص‌های آموزشی">
  @forelse($kpis as $item)
    <x-teacher.stat-card :href="$item['url']" :label="$item['label']" :value="\App\Support\PersianUi::digits($item['value'])" :hint="$item['hint']" :icon="$item['icon']" :tone="$item['tone']" />
  @empty
    <div class="c">برای این حساب شاخص قابل‌نمایشی وجود ندارد.</div>
  @endforelse
</div>

<div class="sh"><b>اقدام سریع</b><small>میانبرهای دسترسی به کارهای روزانه</small></div>
<div class="ql">
  @forelse($quick as $item)
    <x-teacher.quick-action :href="route($item['route'])" :label="$item['label']" :hint="$item['hint']" :icon="$item['icon']" :tone="$item['tone']" />
  @empty
    <div class="c">هیچ میانبر فعالی برای سطح دسترسی این حساب تعریف نشده است.</div>
  @endforelse
</div>

<div class="g g3">
  <section class="c" style="--ca:#4a5fe0">
    <h3>کلاس‌های من <a href="{{ route('teacher.classrooms.index') }}">مشاهده همه ←</a></h3>
    @forelse($classPreviews as $class)
      <a class="row" href="{{ $class['url'] ?? route('teacher.classrooms.index') }}">
        <span class="ic">▤</span><span class="t"><b>{{ $class['title'] }}</b><small>{{ $class['course_title'] ?? 'دوره آموزشی' }}</small></span>
        <span class="pill">{{ \App\Support\PersianUi::digits($class['student_count'] ?? 0) }} نفر</span>
      </a>
    @empty
      <div class="emp"><i>▤</i><b>هنوز کلاسی ثبت نشده است</b><small>کلاس‌های متصل به حساب شما پس از تخصیص در این قسمت نمایش داده می‌شوند.</small></div>
    @endforelse
  </section>
  <section class="c" style="--ca:#22b573">
    <h3>برنامه امروز <a href="{{ route('teacher.schedule.index') }}">برنامه کامل ←</a></h3>
    @forelse($today as $session)
      <div class="row"><span class="pill">{{ $session['time'] ?? '—' }}</span><span class="t"><b>{{ $session['title'] ?? 'جلسه آموزشی' }}</b><small>{{ $session['meta'] ?? '' }}</small></span>
        @if(!empty($session['can_join']) && filled($session['meeting_url'] ?? null))<a class="btn s" href="{{ $session['meeting_url'] }}" target="_blank" rel="noopener noreferrer">ورود</a>@endif
      </div>
    @empty
      <div class="emp"><i>◷</i><b>جلسه‌ای برای امروز ثبت نشده است</b><small>برنامه جلسات واقعی شما در این بخش قرار می‌گیرد.</small></div>
    @endforelse
  </section>
</div>

<div class="g g3">
  <section class="c" style="--ca:#ff9f43">
    <h3>آخرین تکالیف و فعالیت‌ها <a href="{{ route('teacher.assignments.index') }}">مدیریت تکالیف ←</a></h3>
    @forelse($recent->take(6) as $activity)
      <a class="row" href="{{ route('teacher.assignments.index') }}"><span class="ic">✎</span><span class="t"><b>{{ $activity->title }}</b><small>{{ $activity->classroom?->title ?? 'دوره' }} · {{ $activity->due_at ? \App\Support\PersianUi::date($activity->due_at) : 'بدون مهلت' }}</small></span><span class="pill {{ (int)($activity->pending_review_count ?? 0)>0?'or':'gr' }}">{{ \App\Support\PersianUi::digits($activity->pending_review_count ?? 0) }} در انتظار</span></a>
    @empty
      <div class="emp"><i>✎</i><b>فعالیتی برای نمایش نیست</b><small>تکالیف ثبت‌شده شما بعد از ایجاد در این بخش دیده می‌شوند.</small></div>
    @endforelse
  </section>
  <section class="c" style="--ca:#8b5cf6">
    <h3>کلاس‌های پیش‌رو <a href="{{ route('teacher.live-classes.index') }}">همه ←</a></h3>
    @forelse($upcoming as $class)
      <div class="row"><span class="ic">◷</span><span class="t"><b>{{ $class->course?->title ?? 'کلاس آنلاین' }}</b><small>{{ $class->classroom?->title ?? '' }} · {{ \App\Support\PersianUi::date($class->scheduled_at) }} {{ \App\Support\PersianUi::time($class->scheduled_at) }}</small></span>
        @if(($class->can_join ?? false) && filled($class->meeting_url))<a class="btn s" href="{{ $class->meeting_url }}" target="_blank" rel="noopener noreferrer">ورود ↗</a>@else<span class="pill">{{ $class->status }}</span>@endif
      </div>
    @empty
      <div class="emp"><i>◷</i><b>کلاس پیش‌رویی ثبت نشده است</b><small>جلسات آینده پس از زمان‌بندی نمایش داده می‌شوند.</small></div>
    @endforelse
  </section>
</div>

@if($user?->hasPermission('reports.view'))
<section class="c" data-dashboard-chart data-chart='@json(["week"=>$weekChart,"month"=>$monthChart])' style="--ca:#14b8c4" id="teacher-progress">
  <h3>گزارش عملکرد <span class="tabs" style="margin:0"><button type="button" class="on" data-chart-range="week" aria-pressed="true">هفته</button><button type="button" data-chart-range="month" aria-pressed="false">ماه</button></span></h3>
  <div class="bars" data-chart-bars aria-label="نمودار درصد پیشرفت">
    @foreach(($weekChart['values'] ?? []) as $index=>$value)
      <div><b>{{ \App\Support\PersianUi::digits(round(max(0,min(100,(float)$value)))) }}٪</b><i style="height:{{ max(0,min(100,(float)$value)) }}%"></i><small>{{ $weekChart['labels'][$index] ?? '' }}</small></div>
    @endforeach
  </div>
  <p class="muted-note" data-chart-empty @if(collect($weekChart['values'] ?? [])->contains(fn($value)=>(float)$value>0)) hidden @endif>برای این بازه هنوز داده‌ای جهت رسم نمودار ثبت نشده است.</p>
  <p class="muted-note">نمودار فقط از داده‌های واقعی پیشرفت ذخیره‌شده در سامانه ساخته می‌شود.</p>
</section>
@endif
@endsection