@props([
    'eyebrow' => 'قدم بعدی',
    'title' => 'مسیر یادگیریت را ادامه بده',
    'description' => 'از دوره‌ها و مدرس‌ها دیدن کن و با توجه به نیازت، قدم بعدی را انتخاب کن.',
    'primaryHref' => '/courses',
    'primaryLabel' => 'مشاهده دوره‌ها',
    'secondaryHref' => '/teachers',
    'secondaryLabel' => 'آشنایی با مدرس‌ها',
])

<section class="public-next-step" aria-label="{{ $eyebrow }}">
    <x-layout.container size="wide">
        <div class="public-next-step__card">
            <div class="public-next-step__copy">
                <span>{{ $eyebrow }}</span>
                <h2>{{ $title }}</h2>
                <p>{{ $description }}</p>
            </div>
            <div class="public-next-step__actions">
                <a class="public-next-step__button public-next-step__button--primary" href="{{ $primaryHref }}">{{ $primaryLabel }} <span aria-hidden="true">←</span></a>
                <a class="public-next-step__button public-next-step__button--secondary" href="{{ $secondaryHref }}">{{ $secondaryLabel }} <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </x-layout.container>
</section>
