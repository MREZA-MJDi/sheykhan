@extends('layouts.owner')

@section('title', 'owner | شیخان')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-sm text-[var(--color-muted)]">شیخان</p>
        <h1 class="mt-1 text-2xl font-black">مدیریت آموزشگاه</h1>
    </div>
    <div class="rounded-2xl border border-[var(--color-border)] bg-white p-6 shadow-sm">
        <p class="text-sm text-[var(--color-muted)]">این فضای اختصاصی نقش شماست. قابلیت‌های این بخش فقط از مسیر permissionهای همین نقش توسعه داده می‌شوند.</p>
    </div>
</div>
@endsection
