@extends('owner.admin.layout')
@section('title','داشبورد آکادمی شیخان')
@section('header-title','داشبورد')
@section('content')
@php
$ownerUser=auth()->user();
$period=in_array($period ?? 'month',['today','week','month'],true)?($period ?? 'month'):'month';
$periodCaption=$periodCaption ?? 'این ماه';
$academies=collect($academies ?? []);
$firstAcademy=$academies->first();
$metrics=$metrics ?? [];
$capacity=$capacity ?? [];
$upcomingLiveClasses=collect($upcomingLiveClasses ?? []);
$todayLiveClasses=collect($todayLiveClasses ?? []);
$pendingApprovalItems=collect($pendingApprovalItems ?? []);
$absentStudents=collect($absentStudents ?? []);
$recentStudentLogins=collect($recentStudentLogins ?? []);
$students=max(0,(int)data_get($metrics,'students',0));
$teachers=max(0,(int)data_get($metrics,'teachers',0));
$classrooms=max(0,(int)data_get($metrics,'classrooms',0));
$courses=max(0,(int)data_get($metrics,'courses',0));
$published=max(0,(int)data_get($metrics,'published',0));
$liveNow=max(0,(int)data_get($metrics,'liveNow',0));
$pending=max(0,(int)data_get($metrics,'pendingReviews',0));
$attendanceRate=data_get($metrics,'attendanceRate',data_get($metrics,'todayAttendanceRate'));
$absent=max(0,(int)data_get($metrics,'absentCount',0));
$sales=(float)data_get($metrics,'sales',0);
$transactionCount=max(0,(int)data_get($metrics,'transactionCount',0));
$capacityTotal=max(0,(int)data_get($capacity,'total',0));
$capacityUsed=max(0,(int)data_get($capacity,'used',0));
$capacityRate=data_get($capacity,'rate');
$attention=$pending+(int)data_get($metrics,'capacityAlerts',0)+(int)data_get($metrics,'classroomsWithoutTeacher',0);
$periodLabel=match($period){'today'=>'امروز','week'=>'این هفته',default=>'این ماه'};
@endphp
<section class="admin-welcome c12">
<div><span class="admin-eyebrow">مرکز کنترل آموزشگاه</span><h1>{{ $firstAcademy?->name ?? 'آکادمی شیخان' }}</h1><p>نمای کلی فعالیت‌ها، وضعیت کلاس‌ها و موارد نیازمند پیگیری بر اساس اطلاعات ثبت‌شده سامانه.</p></div>
<div class="admin-welcome-actions"><a class="admin-btn admin-btn-primary" href="{{ route('account.guide',['role'=>'owner']) }}">راهنمای پنل</a>@if($firstAcademy && $ownerUser?->hasPermission('academy.view'))<a class="admin-btn" href="{{ route('owner.people.index',$firstAcademy) }}">مدیریت اعضا</a>@endif</div>
</section>
<div class="admin-kpis c12">
@if($ownerUser?->hasPermission('academy.view'))<a class="admin-kpi" href="{{ $firstAcademy ? route('owner.people.index',$firstAcademy) : route('owner.dashboard') }}"><span>دانش‌آموزان فعال</span><strong>{{ \\App\\Support\\PersianUi::digits($students) }}</strong><small>{{ \\App\\Support\\PersianUi::digits($teachers) }} مدرس فعال</small></a>@endif
@if($ownerUser?->hasPermission('classrooms.view'))<a class="admin-kpi" href="{{ $firstAcademy ? route('owner.classrooms.index',$firstAcademy) : route('owner.dashboard') }}"><span>کلاس‌های فعال</span><strong>{{ \\App\\Support\\PersianUi::digits($classrooms) }}</strong><small>{{ \\App\\Support\\PersianUi::digits($liveNow) }} کلاس در حال اجرا</small></a>@endif
@if($ownerUser?->hasPermission('attendance.view'))<a class="admin-kpi" href="{{ $firstAcademy ? route('owner.attendance.index',$firstAcademy) : route('owner.dashboard') }}"><span>میانگین حضور · {{ $periodLabel }}</span><strong>{{ $attendanceRate===null?'—':\\App\\Support\\PersianUi::number(round((float)$attendanceRate,1)).'٪' }}</strong><small>{{ \\App\\Support\\PersianUi::digits($absent) }} غیبت در بازه</small></a>@endif
@if($ownerUser?->hasPermission('assignments.view') || $ownerUser?->hasPermission('classrooms.view'))<a class="admin-kpi" href="{{ $firstAcademy ? ($pending>0 && $ownerUser?->hasPermission('assignments.view') ? route('owner.assignments.index',$firstAcademy) : route('owner.classrooms.index',$firstAcademy)) : route('owner.dashboard') }}"><span>نیازمند پیگیری</span><strong>{{ \\App\\Support\\PersianUi::digits($attention) }}</strong><small>تکالیف و هشدارهای کلاس</small></a>@endif
@if($ownerUser?->hasPermission('finance.view'))<a class="admin-kpi" href="{{ route('owner.finance.index') }}"><span>دریافتی · {{ $periodLabel }}</span><strong>{{ $transactionCount ? \\App\\Support\\PersianUi::number($sales) : '—' }}</strong><small>تومان · تراکنش‌های موفق</small></a>@endif
@if($ownerUser?->hasPermission('courses.view'))<a class="admin-kpi" href="{{ route('owner.courses.index') }}"><span>دوره‌های منتشرشده</span><strong>{{ \\App\\Support\\PersianUi::digits($published) }}</strong><small>از {{ \\App\\Support\\PersianUi::digits($courses) }} دوره</small></a>@endif
</div>
<div class="admin-dashboard-grid c12">
@if($ownerUser?->hasPermission('live_classes.view'))
<section class="admin-panel admin-panel-wide"><div class="admin-panel-head"><div><h2>برنامه کلاس‌ها</h2><p>جلسه‌های ثبت‌شده برای امروز</p></div>@if($firstAcademy)<a href="{{ route('owner.schedules.index',$firstAcademy) }}">مشاهده برنامه ←</a>@endif</div>
@forelse($todayLiveClasses as $item)<div class="admin-list-row"><span class="admin-list-time">{{ \\App\\Support\\PersianUi::time($item->scheduled_at) }}</span><div><strong>{{ $item->title }}</strong><small>{{ $item->course?->title ?? 'بدون دوره' }} · {{ $item->teacher?->name ?? 'مدرس تعیین نشده' }}</small></div><span class="admin-status {{ $item->status==='live'?'is-live':'is-planned' }}">{{ $item->status==='live'?'در حال اجرا':'زمان‌بندی‌شده' }}</span></div>@empty<div class="admin-empty">برای امروز جلسه آنلاین زمان‌بندی‌شده‌ای ثبت نشده است.</div>@endforelse
</section>
@endif
<section class="admin-panel"><div class="admin-panel-head"><div><h2>ظرفیت کلاس‌ها</h2><p>بر اساس عضویت‌های فعال</p></div></div>
@if($capacityTotal>0)<div class="admin-capacity"><strong>{{ \\App\\Support\\PersianUi::digits($capacityRate===null?round($capacityUsed/$capacityTotal*100):round((float)$capacityRate)) }}٪</strong><span>ظرفیت تکمیل‌شده</span></div><div class="admin-progress"><i style="width:{{ min(100,max(0,$capacityRate===null?($capacityUsed/$capacityTotal*100):(float)$capacityRate)) }}%"></i></div><small>{{ \\App\\Support\\PersianUi::digits($capacityUsed) }} از {{ \\App\\Support\\PersianUi::digits($capacityTotal) }} ظرفیت</small>@else<div class="admin-empty">برای کلاس‌ها ظرفیتی تعریف نشده است.</div>@endif
</section>
@if($ownerUser?->hasPermission('assignments.view'))
<section class="admin-panel"><div class="admin-panel-head"><div><h2>تکالیف منتظر بررسی</h2><p>{{ \\App\\Support\\PersianUi::digits($pending) }} پاسخ ثبت‌شده</p></div>@if($firstAcademy)<a href="{{ route('owner.assignments.index',$firstAcademy) }}">همه تکالیف ←</a>@endif</div>
@forelse($pendingApprovalItems as $item)<a class="admin-list-row admin-list-link" href="{{ route('owner.assignments.show',[$item->academy_id,$item->assignment_id]) }}"><span class="admin-avatar">{{ mb_substr($item->student_name,0,1) }}</span><div><strong>{{ $item->student_name }}</strong><small>{{ $item->course_title }} · {{ $item->assignment_title }}</small></div><span class="admin-row-arrow">←</span></a>@empty<div class="admin-empty">پاسخ بررسی‌نشده‌ای وجود ندارد.</div>@endforelse
</section>
@endif
@if($ownerUser?->hasPermission('attendance.view') && $firstAcademy)
<section class="admin-panel"><div class="admin-panel-head"><div><h2>آخرین غیبت‌ها</h2><p>{{ $periodLabel }}</p></div><a href="{{ route('owner.attendance.index',$firstAcademy) }}">سوابق حضور ←</a></div>
@forelse($absentStudents as $absentStudent)<div class="admin-list-row"><span class="admin-avatar is-absent">!</span><div><strong>{{ $absentStudent->student_name }}</strong><small>{{ $absentStudent->classroom_title }} · {{ \\App\\Support\\PersianUi::date($absentStudent->attendance_date) }}</small></div></div>@empty<div class="admin-empty">غیبتی برای نمایش ثبت نشده است.</div>@endforelse
</section>
@endif
@if($ownerUser?->hasPermission('academy.view'))
<section class="admin-panel"><div class="admin-panel-head"><div><h2>آخرین ورود دانش‌آموزان</h2><p>رویدادهای ثبت‌شده در سامانه</p></div>@if($firstAcademy)<a href="{{ route('owner.people.index',$firstAcademy) }}">فهرست اعضا ←</a>@endif</div>
@forelse($recentStudentLogins as $login)<div class="admin-list-row"><span class="admin-avatar">{{ mb_substr($login->subject?->name ?? '؟',0,1) }}</span><div><strong>{{ $login->subject?->name ?? 'حساب حذف‌شده' }}</strong><small>{{ \\App\\Support\\PersianUi::dateTime($login->created_at) }}</small></div></div>@empty<div class="admin-empty">رویداد ورود جدیدی برای نمایش وجود ندارد.</div>@endforelse
</section>
@endif
<section class="admin-panel"><div class="admin-panel-head"><div><h2>دسترسی سریع</h2><p>میان‌بر بخش‌های مجاز حساب شما</p></div></div><div class="admin-shortcuts">
@if($firstAcademy && $ownerUser?->hasPermission('academy.view'))<a href="{{ route('owner.people.index',$firstAcademy) }}">اعضا و پرونده‌ها</a>@endif
@if($firstAcademy && $ownerUser?->hasPermission('classrooms.view'))<a href="{{ route('owner.classrooms.index',$firstAcademy) }}">کلاس‌ها</a>@endif
@if($firstAcademy && $ownerUser?->hasPermission('attendance.view'))<a href="{{ route('owner.attendance.index',$firstAcademy) }}">حضور و غیاب</a>@endif
@if($ownerUser?->hasPermission('courses.view'))<a href="{{ route('owner.courses.index') }}">دوره‌ها</a>@endif
@if($ownerUser?->hasPermission('finance.view'))<a href="{{ route('owner.finance.index') }}">مالی</a>@endif
@if($ownerUser?->hasPermission('reports.view'))<a href="{{ route('owner.reports.index') }}">گزارش‌ها</a>@endif
</div></section>
</div>
@endsection