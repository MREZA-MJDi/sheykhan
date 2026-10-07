<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>دسترسی محدود | شیخان</title>
    <style>
        :root{color-scheme:light}
        *{box-sizing:border-box}
        body{margin:0;background:#f6f8fc;color:#111827;font-family:Tahoma,Arial,sans-serif}
        main{min-height:100vh;display:grid;place-items:center;padding:24px}
        .box{width:min(560px,100%);padding:34px;text-align:center;background:#fff;border:1px solid #e7ebf1;border-radius:24px;box-shadow:0 18px 45px rgba(17,24,39,.07)}
        .icon{width:58px;height:58px;margin:0 auto 16px;display:grid;place-items:center;border-radius:18px;background:#fff3e8;color:#b45309;font-size:26px;font-weight:900}
        .code{font-size:13px;font-weight:900;letter-spacing:.08em;color:#9ca3af}
        h1{margin:8px 0 10px;font-size:22px}
        p{margin:0 auto;max-width:450px;color:#667085;line-height:2;font-size:13px}
        .hint{margin-top:14px;font-size:12px;color:#98a2b3}
        .actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:24px}
        .btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 18px;border-radius:12px;background:#5362df;color:#fff;text-decoration:none;font-weight:800;font-size:12px}
        .btn.secondary{background:#eef2ff;color:#3949ab}
    </style>
</head>
<body>
<main>
    <section class="box" role="alert" aria-live="assertive">
        <div class="icon" aria-hidden="true">!</div>
        <div class="code">403</div>
        <h1>{{ $exception?->getMessage() ?: 'این عملیات برای حساب شما مجاز نیست.' }}</h1>
        <p>
            نگران نباش؛ این خطا به معنی خراب بودن حساب یا فایل نیست.
            دسترسی این بخش بر اساس نوع محتوا، خرید، کلاس یا مجوز حساب کنترل می‌شود.
        </p>
        <div class="hint">برای ادامه، به صفحه قبلی یا داشبورد خودت برگرد.</div>
        <div class="actions">
            <a class="btn" href="{{ url()->previous() }}">بازگشت</a>
            @auth
                <a class="btn secondary" href="{{ route('dashboard') }}">داشبورد من</a>
            @else
                <a class="btn secondary" href="{{ route('login') }}">ورود به حساب</a>
            @endauth
        </div>
    </section>
</main>
</body>
</html>