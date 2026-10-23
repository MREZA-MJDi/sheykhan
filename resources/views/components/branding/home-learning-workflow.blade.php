{{-- Public view of the academy workflow requested by the client. This is product information, not sample learner data. --}}
<section id="academy-workflow" class="home-section home-workflow" aria-labelledby="academy-workflow-title">
    <x-layout.container size="wide">
        <header class="home-workflow__header">
            <span class="home-workflow__eyebrow">گردش کار آموزشگاه</span>
            <h2 id="academy-workflow-title">از برنامه‌ریزی کلاس تا پیگیری نتیجه؛ همه در یک مسیر</h2>
            <p>هر نقش، کارتابل مشخص خودش را دارد؛ اطلاعات کلاس، حضور و غیاب، تکلیف، آزمون و ارتباط خانواده‌ها از هم جدا نمی‌ماند.</p>
        </header>

        <div class="home-workflow__flow" aria-label="مراحل پیوسته آموزش">
            <span><b>۰۱</b> برنامه‌ریزی آموزشگاه</span>
            <i aria-hidden="true">←</i>
            <span><b>۰۲</b> اجرای کلاس و ارزیابی</span>
            <i aria-hidden="true">←</i>
            <span><b>۰۳</b> یادگیری دانش‌آموز</span>
            <i aria-hidden="true">←</i>
            <span><b>۰۴</b> پیگیری خانواده</span>
        </div>

        @php
            $workflowRoles = [
                [
                    'key' => 'admin',
                    'number' => '۰۱',
                    'eyebrow' => 'مدیریت و هماهنگی',
                    'title' => 'مدیر / ادمین',
                    'description' => 'کنترل عملیات روزمره و آماده نگه‌داشتن مسیر آموزش.',
                    'items' => [
                        'مدیریت کلاس‌ها، دوره‌ها و برنامه زمان‌بندی',
                        'مدیریت منابع، کتاب‌ها و محصولات آموزشی',
                        'انتشار لینک کلاس‌های آنلاین در زمان مقرر',
                        'ساخت آزمون، بررسی تکالیف و مشاهده گزارش‌ها',
                        'پیگیری پیام‌ها، جلسات مشاوره و حضور و غیاب',
                    ],
                ],
                [
                    'key' => 'teacher',
                    'number' => '۰۲',
                    'eyebrow' => 'اجرا و بازخورد',
                    'title' => 'مدرس',
                    'description' => 'هدایت کلاس و تبدیل فعالیت آموزشی به پیشرفت قابل پیگیری.',
                    'items' => [
                        'مدیریت کلاس‌ها و دوره‌های واگذارشده',
                        'ثبت حضور و غیاب و مشاهده وضعیت دانش‌آموزان',
                        'بارگذاری تکلیف و طراحی آزمون',
                        'بررسی پاسخ‌ها، ثبت نمره و بازخورد',
                        'پیام‌رسانی و هماهنگی جلسه با خانواده',
                    ],
                ],
                [
                    'key' => 'student',
                    'number' => '۰۳',
                    'eyebrow' => 'یادگیری و تمرین',
                    'title' => 'دانش‌آموز',
                    'description' => 'دیدن قدم بعدی و انجام کارهای آموزشی از یک فضای روشن.',
                    'items' => [
                        'مشاهده دوره‌ها، کلاس‌ها و برنامه جلسات',
                        'دسترسی به لینک کلاس آنلاین و منابع آموزشی',
                        'دریافت تکالیف و ارسال پاسخ',
                        'شرکت در آزمون‌های در دسترس',
                        'مشاهده نتایج، نمره‌ها و وضعیت حضور',
                    ],
                ],
                [
                    'key' => 'parent',
                    'number' => '۰۴',
                    'eyebrow' => 'همراهی خانواده',
                    'title' => 'والد',
                    'description' => 'پیگیری پیشرفت فرزند و ارتباط مستقیم با آموزشگاه.',
                    'items' => [
                        'مشاهده حضور و غیاب و نتایج فرزند',
                        'پیگیری فعالیت‌ها و وضعیت تکالیف',
                        'ثبت درخواست جلسه مشاوره',
                        'ارسال پیام، پرسش، پیشنهاد یا اطلاع‌رسانی',
                        'پیگیری مسیر آموزشی از پنل خانواده',
                    ],
                ],
            ];
        @endphp

        <div class="home-workflow__grid">
            @foreach($workflowRoles as $role)
                <article class="home-workflow-card home-workflow-card--{{ $role['key'] }}">
                    <div class="home-workflow-card__top">
                        <span class="home-workflow-card__number">{{ $role['number'] }}</span>
                        <span class="home-workflow-card__eyebrow">{{ $role['eyebrow'] }}</span>
                    </div>
                    <h3>{{ $role['title'] }}</h3>
                    <p>{{ $role['description'] }}</p>
                    <ul>
                        @foreach($role['items'] as $item)
                            <li><span aria-hidden="true">✓</span>{{ $item }}</li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>

        <div class="home-workflow__footer">
            <div>
                <strong>همه‌چیز از یک مسیر مشخص شروع می‌شود.</strong>
                <p>برای دیدن دوره‌های منتشرشده از کاتالوگ عمومی شروع کن؛ برای ادامه کارتابل خودت وارد حساب شو.</p>
            </div>
            <div class="home-workflow__actions">
                <a href="{{ route('courses.index') }}" class="home-workflow__button home-workflow__button--primary">دیدن دوره‌ها <span aria-hidden="true">←</span></a>
                <a href="{{ route('login') }}" class="home-workflow__button home-workflow__button--secondary">ورود به کارتابل <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </x-layout.container>
</section>
