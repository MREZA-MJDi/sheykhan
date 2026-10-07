@extends('layouts.student')

@section('title','یادداشت‌های من | شیخان')
@section('header-title','یادداشت‌های من')

@section('content')
<section class="student-panel dashboard-panel" aria-labelledby="notes-title">
    <div class="student-panel-head"><div><span class="student-kicker" style="color:var(--panel-primary)">یادگیری شخصی</span><h2 id="notes-title">یادداشت‌های من</h2><p>یادداشت‌ها فقط متعلق به حساب فعلی هستند.</p></div></div>
    <div class="student-list">
        @forelse($notes as $note)
            <article class="student-list-row">
                <div class="student-date"><strong>✎</strong><small>یادداشت</small></div>
                <div class="student-row-content"><a class="student-row-title" href="{{ route('student.lessons.show',$note->lesson) }}">{{ $note->lesson?->title }}</a><span class="student-row-meta">{{ \Illuminate\Support\Str::limit($note->content,120) }}</span></div>
                <form method="POST" action="{{ route('student.notes.destroy',$note) }}" onsubmit="return confirm('این یادداشت حذف شود؟')">
                    @csrf @method('DELETE')
                    <button class="student-action" type="submit">حذف</button>
                </form>
            </article>
        @empty
            <div class="student-empty"><strong>یادداشتی ندارید.</strong><span>از صفحه هر درس می‌توانید یادداشت شخصی ثبت کنید.</span></div>
        @endforelse
    </div>
    @if($notes->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی یادداشت‌ها">{{ $notes->links() }}</nav>@endif
</section>
@endsection
