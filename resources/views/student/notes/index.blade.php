@extends('layouts.student')

@section('title', 'یادداشت‌های من')
@section('header-title', 'یادداشت‌های من')

@section('content')
<section class="page" id="notes">
    <div class="card">
        <div class="chead"><h2>یادداشت‌های درس</h2><span class="pill">{{ \App\Support\PersianUi::digits($notes->total()) }} یادداشت</span></div>
        <p class="note">برای ساخت یا ویرایش یادداشت، وارد صفحهٔ همان درس شو؛ یادداشت‌ها فقط به حساب خودت تعلق دارند.</p>
        @forelse($notes as $note)
            <article class="note" style="margin-top:10px">
                <div class="chead">
                    <strong>{{ $note->lesson?->title ?? 'درس' }}</strong>
                    <span class="tag">{{ \App\Support\PersianUi::dateTime($note->updated_at) }}</span>
                </div>
                <p style="white-space:pre-line">{{ $note->content }}</p>
                @if($note->lesson)
                    <div class="frm">
                        <a class="btn s" href="{{ route('student.lessons.show', $note->lesson->id) }}">باز کردن درس</a>
                        <form method="POST" action="{{ route('student.notes.destroy', $note->id) }}" onsubmit="return confirm('این یادداشت حذف شود؟')">
                            @csrf
                            @method('DELETE')
                            <button class="pill" type="submit">حذف یادداشت</button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <div class="empty">هنوز یادداشتی ذخیره نکرده‌ای. از صفحهٔ هر درس می‌توانی یادداشت اضافه کنی.</div>
        @endforelse
        @if($notes->hasPages())<div class="frm">{{ $notes->links() }}</div>@endif
    </div>
</section>
@endsection
