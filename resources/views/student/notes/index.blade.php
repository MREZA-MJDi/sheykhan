@extends('layouts.student')

@section('title','یادداشت‌های من | شیخان')
@section('header-title','یادداشت‌های من')

@section('content')
<div class="student-workspace-page"><header class="student-workspace-head"><div class="student-workspace-head-copy"><span class="student-workspace-kicker">یادگیری شخصی</span><h1 class="student-workspace-title">یادداشت‌های من</h1><p class="student-workspace-description">یادداشت‌ها فقط متعلق به حساب فعلی هستند و به درس اصلی خود متصل می‌مانند.</p></div><a class="student-workspace-btn secondary" href="{{ route('student.dashboard') }}">داشبورد</a></header><section class="student-workspace-card" aria-labelledby="notes-title">
    <div class="student-workspace-card-head"><div><h2 id="notes-title">یادداشت‌های ذخیره‌شده</h2><p>از هر یادداشت می‌توانی مستقیم به همان درس برگردی.</p></div></div>
    <div class="student-workspace-list">
        @forelse($notes as $note)
            <article class="student-workspace-row">
                <div class="student-workspace-date"><strong>✎</strong><small>یادداشت</small></div>
                <div class="student-workspace-row-main"><a class="student-row-title" href="{{ route('student.lessons.show',$note->lesson) }}">{{ $note->lesson?->title }}</a><span class="student-row-meta">{{ \Illuminate\Support\Str::limit($note->content,120) }}</span></div>
                <form method="POST" action="{{ route('student.notes.destroy',$note) }}" onsubmit="return confirm('این یادداشت حذف شود؟')">
                    @csrf @method('DELETE')
                    <button class="student-action" type="submit">حذف</button>
                </form>
            </article>
        @empty
            <div class="student-workspace-empty"><strong>یادداشتی ندارید.</strong><span>از صفحه هر درس می‌توانید یادداشت شخصی ثبت کنید.</span></div>
        @endforelse
    </div>
    @if($notes->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی یادداشت‌ها">{{ $notes->links() }}</nav>@endif
</section></div>
@endsection
