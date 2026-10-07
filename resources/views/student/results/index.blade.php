@extends('layouts.student')

@section('title','نمرات و عملکرد | شیخان')
@section('header-title','نمرات و عملکرد')

@section('content')
<section class="student-panel dashboard-panel" aria-labelledby="results-title">
    <div class="student-panel-head"><div><span class="student-kicker" style="color:var(--panel-primary)">کارنامه</span><h2 id="results-title">نمرات و عملکرد</h2><p>نتیجه‌های منتشرشده و ارزیابی‌های متعلق به همین حساب.</p></div></div>
    <div class="student-list">
        @forelse($results as $result)
            <article class="student-list-row">
                <div class="student-date"><strong>{{ $result->score !== null ? \App\Support\PersianUi::digits($result->score) : '—' }}</strong><small>نمره</small></div>
                <div class="student-row-content"><div class="student-row-title">{{ $result->title }}</div><span class="student-row-meta">{{ $result->occurred_at ? \App\Support\PersianUi::date($result->occurred_at) : '—' }}</span></div>
                <span class="student-status {{ $result->score !== null ? 'success' : 'warning' }}">{{ $result->status_label }}</span>
            </article>
        @empty
            <div class="student-empty"><strong>هنوز نتیجه‌ای ثبت نشده است.</strong><span>بعد از ارزیابی، نتایج در اینجا دیده می‌شوند.</span></div>
        @endforelse
    </div>
    @if($results->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی نتایج">{{ $results->links() }}</nav>@endif
</section>
@endsection
