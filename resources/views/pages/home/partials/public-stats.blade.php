<section class="public-proof-stats" aria-label="آمار واقعی محتوای منتشرشده شیخان">
    <div class="public-shell public-proof-stats__grid">
        <div><strong>{{ \App\Support\PersianUi::digits($stats['courses'] ?? 0) }}</strong><span>دوره منتشرشده</span></div>
        <div><strong>{{ \App\Support\PersianUi::digits($stats['teachers'] ?? 0) }}</strong><span>مدرس تأییدشده</span></div>
        <div><strong>{{ \App\Support\PersianUi::digits($stats['students'] ?? 0) }}</strong><span>دانش‌آموز فعال</span></div>
    </div>
</section>