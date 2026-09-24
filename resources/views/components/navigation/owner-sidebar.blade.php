@php($academy = auth()->user()->ownedAcademy()->first())
<aside class="role-sidebar owner-sidebar">
<div class="role-sidebar-brand"><a href="{{ route('owner.dashboard') }}" class="role-brand"><span class="role-brand-mark">ش</span><span><strong>شیخان</strong><small>مدیریت آموزشگاه</small></span></a></div>
<nav class="role-sidebar-nav" aria-label="منوی مدیریت آموزشگاه">
<div class="role-sidebar-label">مرکز کنترل</div>
<a href="{{ route('owner.dashboard') }}" class="role-nav-link {{ request()->routeIs('owner.dashboard')?'is-active':'' }}"><span class="role-nav-icon"></span><span>داشبورد</span></a>
<a href="{{ route('owner.courses.index') }}" class="role-nav-link {{ request()->routeIs('owner.courses.*')?'is-active':'' }}"><span class="role-nav-icon"></span><span>دوره‌ها</span></a>
@if($academy)
<a href="{{ route('owner.people.index',$academy) }}" class="role-nav-link {{ request()->routeIs('owner.people.*')?'is-active':'' }}"><span class="role-nav-icon"></span><span>اعضا</span></a>
<a href="{{ route('owner.academy.edit',$academy) }}" class="role-nav-link {{ request()->routeIs('owner.academy.*')?'is-active':'' }}"><span class="role-nav-icon"></span><span>آموزشگاه</span></a>
@endif
<div class="role-sidebar-label mt-6">نظارت</div>
<a href="{{ $academy?route('owner.classrooms.index',$academy):'#' }}" class="role-nav-link {{ request()->routeIs('owner.classrooms.*')?'is-active':'' }}"><span class="role-nav-icon"></span><span>کلاس‌ها</span></a>
<a href="{{ route('owner.reports.index') }}" class="role-nav-link {{ request()->routeIs('owner.reports.*')?'is-active':'' }}"><span class="role-nav-icon"></span><span>گزارش‌ها</span></a>
</nav>
<div class="role-sidebar-footer"><a href="{{ route('home') }}" class="role-footer-link">مشاهده سایت</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="role-footer-link role-logout">خروج</button></form></div>
</aside>