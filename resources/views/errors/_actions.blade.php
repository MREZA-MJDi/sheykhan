<div class="actions">
    <button type="button" class="btn secondary" onclick="if(history.length > 1){history.back()}else{window.location.href='{{ route('home') }}'}">بازگشت</button>
    @auth
        <a class="btn" href="{{ route('dashboard') }}">داشبورد من</a>
    @else
        <a class="btn" href="{{ route('login') }}">ورود</a>
    @endauth
    <a class="btn secondary" href="{{ route('home') }}">صفحه اصلی</a>
</div>
