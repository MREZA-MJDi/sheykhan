<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="referrer" content="no-referrer">
    <title>دریافت مستقیم غیرفعال است | شیخان</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--color-background)] p-5 text-[var(--color-text)]">
    <main class="mx-auto mt-12 max-w-xl rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-[var(--shadow-md)] sm:p-9">
        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-black text-amber-900">دسترسی امن · 403</span>
        <h1 class="mt-5 text-2xl font-black">فایل‌های آموزشی محافظت‌شده</h1>
        <p class="mt-3 text-sm leading-8 text-[var(--color-text-secondary)]">این فایل از مسیر مشاهدهٔ احرازشده در دسترس است، اما دانلود مستقیم آن مجاز نیست. فایل اصلی ویدئویی یا جزوه را از لینک مشاهده باز کن.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('student.resources.view', $resource) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-4 text-sm font-black text-white">مشاهدهٔ امن فایل</a>
            <a href="{{ route('student.resources.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[var(--color-border)] px-4 text-sm font-bold">بازگشت به منابع</a>
        </div>
    </main>
</body>
</html>
