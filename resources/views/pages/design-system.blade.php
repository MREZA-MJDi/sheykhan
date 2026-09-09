@extends('layouts.app')

@section('title', 'Design System')

@section('description', 'نمایش و تست تمام Componentهای رابط کاربری فرزین.')

@section('content')

    <x-layout.section
        spacing="default"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            {{-- =========================================================
                HEADER
            ========================================================== --}}
            <div class="mb-12">
                <x-layout.page-header
                    eyebrow="Farzin UI System"
                    title="سیستم طراحی فرزین"
                    description="مرجع تست و نمایش Componentهای رابط کاربری فرزین. هر Component قبل از استفاده در صفحات واقعی اینجا بررسی می‌شود."
                />
            </div>


            {{-- =========================================================
                COLORS
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="رنگ‌ها"
                    description="Design Tokens اصلی پروژه."
                />

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                    <div class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                        <div class="h-20 rounded-xl bg-[var(--color-brand-600)]"></div>

                        <p class="mt-4 text-sm font-bold text-[var(--color-text-primary)]">
                            Brand
                        </p>

                        <code class="mt-1 block text-xs text-[var(--color-text-muted)]">
                            --color-brand-600
                        </code>
                    </div>

                    <div class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                        <div class="h-20 rounded-xl bg-[var(--color-success-600)]"></div>

                        <p class="mt-4 text-sm font-bold text-[var(--color-text-primary)]">
                            Success
                        </p>

                        <code class="mt-1 block text-xs text-[var(--color-text-muted)]">
                            --color-success-600
                        </code>
                    </div>

                    <div class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                        <div class="h-20 rounded-xl bg-[var(--color-warning-500)]"></div>

                        <p class="mt-4 text-sm font-bold text-[var(--color-text-primary)]">
                            Warning
                        </p>

                        <code class="mt-1 block text-xs text-[var(--color-text-muted)]">
                            --color-warning-500
                        </code>
                    </div>

                    <div class="rounded-2xl border border-[var(--color-border)] bg-white p-5">
                        <div class="h-20 rounded-xl bg-[var(--color-danger-600)]"></div>

                        <p class="mt-4 text-sm font-bold text-[var(--color-text-primary)]">
                            Danger
                        </p>

                        <code class="mt-1 block text-xs text-[var(--color-text-muted)]">
                            --color-danger-600
                        </code>
                    </div>

                </div>

            </section>


            {{-- =========================================================
                BUTTONS
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Button"
                    description="تمام Variantها و Sizeهای اصلی."
                />

                <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-white p-6">

                    <div class="flex flex-wrap items-center gap-3">

                        <x-ui.button>
                            Primary
                        </x-ui.button>

                        <x-ui.button variant="secondary">
                            Secondary
                        </x-ui.button>

                        <x-ui.button variant="soft">
                            Soft
                        </x-ui.button>

                        <x-ui.button variant="ghost">
                            Ghost
                        </x-ui.button>

                        <x-ui.button variant="danger">
                            Danger
                        </x-ui.button>

                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-3">

                        <x-ui.button size="sm">
                            Small
                        </x-ui.button>

                        <x-ui.button size="md">
                            Medium
                        </x-ui.button>

                        <x-ui.button size="lg">
                            Large
                        </x-ui.button>

                    </div>

                    <div class="mt-6 max-w-sm">
                        <x-ui.button full-width>
                            Full Width
                        </x-ui.button>
                    </div>

                </div>

            </section>


            {{-- =========================================================
                BADGES
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Badge"
                    description="وضعیت‌ها و برچسب‌های کاربردی."
                />

                <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-white p-6">

                    <div class="flex flex-wrap items-center gap-3">

                        <x-ui.badge variant="brand">
                            Brand
                        </x-ui.badge>

                        <x-ui.badge variant="success">
                            Success
                        </x-ui.badge>

                        <x-ui.badge variant="warning">
                            Warning
                        </x-ui.badge>

                        <x-ui.badge variant="danger">
                            Danger
                        </x-ui.badge>

                        <x-ui.badge variant="info">
                            Info
                        </x-ui.badge>

                        <x-ui.badge variant="neutral">
                            Neutral
                        </x-ui.badge>

                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-3">

                        <x-ui.badge
                            variant="brand"
                            style="outline"
                        >
                            Outline
                        </x-ui.badge>

                        <x-ui.badge size="sm">
                            Small
                        </x-ui.badge>

                        <x-ui.badge size="lg">
                            Large
                        </x-ui.badge>

                    </div>

                </div>

            </section>


            {{-- =========================================================
                AVATAR
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Avatar"
                    description="سایزها و حالت fallback."
                />

                <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-white p-6">

                    <div class="flex flex-wrap items-center gap-5">

                        <x-ui.avatar
                            name="محمد رضایی"
                            size="xs"
                        />

                        <x-ui.avatar
                            name="محمد رضایی"
                            size="sm"
                        />

                        <x-ui.avatar
                            name="محمد رضایی"
                            size="md"
                        />

                        <x-ui.avatar
                            name="محمد رضایی"
                            size="lg"
                        />

                        <x-ui.avatar
                            name="محمد رضایی"
                            size="xl"
                        />

                        <x-ui.avatar
                            name="محمد رضایی"
                            size="2xl"
                        />

                    </div>

                </div>

            </section>


            {{-- =========================================================
                FORMS
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Form Elements"
                    description="Input، Select، Textarea، Checkbox و Radio."
                />

                <div class="mt-6 grid gap-8 lg:grid-cols-2">

                    {{-- Inputs --}}
                    <div class="rounded-2xl border border-[var(--color-border)] bg-white p-6">

                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Input
                        </h3>

                        <div class="mt-5 space-y-5">

                            <x-ui.input
                                name="name"
                                label="نام و نام خانوادگی"
                                placeholder="نام خود را وارد کنید"
                            />

                            <x-ui.input
                                type="email"
                                name="email"
                                label="ایمیل"
                                placeholder="example@email.com"
                                required
                            />

                            <x-ui.input
                                name="phone"
                                label="شماره موبایل"
                                hint="شماره برای ورود به حساب استفاده می‌شود."
                            />

                            <x-ui.input
                                name="username"
                                label="نام کاربری"
                                error="این نام کاربری قبلاً استفاده شده است."
                            />

                        </div>

                    </div>

                    {{-- Select / Textarea --}}
                    <div class="rounded-2xl border border-[var(--color-border)] bg-white p-6">

                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Select & Textarea
                        </h3>

                        <div class="mt-5 space-y-5">

                            <x-ui.select
                                name="grade"
                                label="پایه تحصیلی"
                                placeholder="پایه را انتخاب کنید"
                            >
                                <option value="4">پایه چهارم</option>
                                <option value="5">پایه پنجم</option>
                                <option value="6">پایه ششم</option>
                                <option value="7">پایه هفتم</option>
                                <option value="8">پایه هشتم</option>
                                <option value="9">پایه نهم</option>
                            </x-ui.select>

                            <x-ui.textarea
                                name="description"
                                label="توضیحات"
                                placeholder="توضیحات خود را وارد کنید..."
                                rows="5"
                            />

                        </div>

                    </div>

                    {{-- Checkbox / Radio --}}
                    <div class="rounded-2xl border border-[var(--color-border)] bg-white p-6">

                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Checkbox
                        </h3>

                        <div class="mt-5 space-y-4">

                            <x-ui.checkbox
                                name="terms"
                                value="1"
                                label="قوانین و مقررات را می‌پذیرم."
                            />

                            <x-ui.checkbox
                                name="notifications"
                                value="1"
                                label="دریافت اعلان‌ها"
                                description="اعلان‌های آموزشی و اطلاعیه‌های مهم را دریافت می‌کنم."
                                checked
                            />

                            <x-ui.checkbox
                                name="disabled"
                                value="1"
                                label="گزینه غیرفعال"
                                disabled
                            />

                        </div>

                    </div>

                    <div class="rounded-2xl border border-[var(--color-border)] bg-white p-6">

                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Radio
                        </h3>

                        <div class="mt-5 space-y-3">

                            <x-ui.radio
                                name="demo-grade"
                                value="5"
                                label="پایه پنجم"
                                description="مناسب دانش‌آموزان پایه پنجم"
                                suffix="پنجم"
                            />

                            <x-ui.radio
                                name="demo-grade"
                                value="6"
                                label="پایه ششم"
                                description="مناسب دانش‌آموزان پایه ششم"
                                suffix="ششم"
                                checked
                            />

                            <x-ui.radio
                                name="demo-grade"
                                value="7"
                                label="پایه هفتم"
                                description="مناسب دانش‌آموزان پایه هفتم"
                                suffix="هفتم"
                            />

                        </div>

                    </div>

                </div>

            </section>


            {{-- =========================================================
                CARD
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Card"
                    description="Base Card و حالت‌های مختلف."
                />

                <div class="mt-6 grid gap-6 lg:grid-cols-2">

                    <x-ui.card
                        title="مسیر یادگیری فرزین"
                        author="فرزین"
                        description="یک Card پایه برای نمایش محتوای عمومی، اطلاعیه یا محتوای آموزشی."
                        date="۱۰ شهریور ۱۴۰۵"
                        meta="مطالعه ۵ دقیقه"
                        href="#"
                        variant="interactive"
                    />

                    <x-ui.card
                        title="Card بدون لینک"
                        description="این نسخه برای محتوای داخلی و غیرقابل کلیک استفاده می‌شود."
                    >
                        <x-ui.badge variant="success">
                            فعال
                        </x-ui.badge>
                    </x-ui.card>

                </div>

            </section>


            {{-- =========================================================
                PROGRESS
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Progress"
                    description="پیشرفت دوره و فعالیت‌ها."
                />

                <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-white p-6 space-y-6">

                    <x-ui.progress
                        value="25"
                        label="شروع مسیر"
                    />

                    <x-ui.progress
                        value="55"
                        label="در حال یادگیری"
                        variant="brand"
                    />

                    <x-ui.progress
                        value="78"
                        label="پیشرفت عالی"
                        variant="info"
                    />

                    <x-ui.progress
                        value="100"
                        label="تکمیل شده"
                        variant="success"
                    />

                </div>

            </section>


            {{-- =========================================================
                FEEDBACK
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Feedback"
                    description="Alert، Error، Loading و Toast."
                />

                <div class="mt-6 space-y-5">

                    <x-feedback.alert
                        type="info"
                        title="اطلاعیه"
                    >
                        این یک پیام اطلاعاتی نمونه است.
                    </x-feedback.alert>

                    <x-feedback.alert
                        type="success"
                        title="عملیات موفق"
                    >
                        اطلاعات با موفقیت ذخیره شد.
                    </x-feedback.alert>

                    <x-feedback.alert
                        type="warning"
                        title="توجه"
                    >
                        قبل از ادامه اطلاعات را بررسی کنید.
                    </x-feedback.alert>

                    <x-feedback.alert
                        type="danger"
                        title="خطا"
                    >
                        در پردازش درخواست مشکلی پیش آمد.
                    </x-feedback.alert>

                    <x-feedback.error
                        title="خطاهای فرم"
                        :errors="[
                            'شماره موبایل وارد نشده است.',
                            'رمز عبور حداقل ۸ کاراکتر باشد.',
                        ]"
                    />

                    <div class="rounded-2xl border border-[var(--color-border)] bg-white p-6">
                        <x-feedback.loading
                            label="در حال بارگذاری اطلاعات..."
                        />
                    </div>

                </div>

            </section>


            {{-- =========================================================
                EMPTY STATE
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Empty State"
                    description="حالت‌های بدون داده."
                />

                <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-white">

                    <x-ui.empty-state
                        icon="course"
                        title="هنوز دوره‌ای نداری"
                        description="یک دوره مناسب انتخاب کن و مسیر یادگیری خودت را شروع کن."
                        action="مشاهده دوره‌ها"
                        action-href="{{ route('courses.index') }}"
                    />

                </div>

            </section>


            {{-- =========================================================
                DROPDOWN
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Dropdown"
                    description="منوی بازشونده."
                />

                <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-white p-8">

                    <x-ui.dropdown label="دوره‌ها">

                        <a
                            href="#"
                            role="menuitem"
                            class="block px-4 py-2.5 text-sm font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-neutral-50)] hover:text-[var(--color-text-primary)]"
                        >
                            دوره‌های من
                        </a>

                        <a
                            href="#"
                            role="menuitem"
                            class="block px-4 py-2.5 text-sm font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-neutral-50)] hover:text-[var(--color-text-primary)]"
                        >
                            دوره‌های پیشنهادی
                        </a>

                        <button
                            type="button"
                            role="menuitem"
                            class="block w-full px-4 py-2.5 text-right text-sm font-medium text-[var(--color-danger-600)] transition-colors hover:bg-[var(--color-danger-50)]"
                        >
                            حذف
                        </button>

                    </x-ui.dropdown>

                </div>

            </section>


            {{-- =========================================================
                TABS
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Tabs"
                    description="نمایش محتوای چندبخشی."
                />

                <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-white p-6">

                    <x-ui.tabs
                        :tabs="[
                            [
                                'id' => 'profile',
                                'label' => 'پروفایل',
                            ],
                            [
                                'id' => 'courses',
                                'label' => 'دوره‌ها',
                                'badge' => 8,
                            ],
                            [
                                'id' => 'activity',
                                'label' => 'فعالیت‌ها',
                            ],
                        ]"
                        active="profile"
                    >

                        <div
                            x-show="active === 'profile'"
                            x-cloak
                            class="rounded-xl bg-[var(--color-neutral-50)] p-5"
                        >
                            <p class="text-sm leading-7 text-[var(--color-text-secondary)]">
                                محتوای پروفایل در این بخش نمایش داده می‌شود.
                            </p>
                        </div>

                        <div
                            x-show="active === 'courses'"
                            x-cloak
                            class="rounded-xl bg-[var(--color-neutral-50)] p-5"
                        >
                            <p class="text-sm leading-7 text-[var(--color-text-secondary)]">
                                لیست دوره‌ها در این بخش نمایش داده می‌شود.
                            </p>
                        </div>

                        <div
                            x-show="active === 'activity'"
                            x-cloak
                            class="rounded-xl bg-[var(--color-neutral-50)] p-5"
                        >
                            <p class="text-sm leading-7 text-[var(--color-text-secondary)]">
                                فعالیت‌های کاربر در این بخش نمایش داده می‌شود.
                            </p>
                        </div>

                    </x-ui.tabs>

                </div>

            </section>


            {{-- =========================================================
                MODAL
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Modal"
                    description="پنجره‌های تعاملی."
                />

                <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-white p-8">

                    <button
                        type="button"
                        @click="$dispatch('open-modal', 'design-system-modal')"
                        class="ui-button ui-button-primary"
                    >
                        باز کردن Modal
                    </button>

                    <x-ui.modal
                        id="design-system-modal"
                        title="نمونه Modal"
                        description="این Modal برای تست component تعاملی فرزین ساخته شده است."
                    >
                        <div class="space-y-4">

                            <p class="text-sm leading-7 text-[var(--color-text-secondary)]">
                                محتوای Modal می‌تواند فرم، تأیید عملیات، اطلاعات دوره یا هر محتوای دیگری باشد.
                            </p>

                            <x-ui.input
                                name="modal-demo"
                                label="نام"
                                placeholder="نام خود را وارد کنید"
                            />

                        </div>

                        <x-slot:footer>
                            <x-ui.button
                                variant="secondary"
                                @click="open = false"
                            >
                                انصراف
                            </x-ui.button>

                            <x-ui.button>
                                ذخیره
                            </x-ui.button>
                        </x-slot:footer>
                    </x-ui.modal>

                </div>

            </section>


            {{-- =========================================================
                SKELETON
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Skeleton"
                    description="حالت Loading برای محتوای در حال دریافت."
                />

                <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-white p-6">

                    <div class="flex items-start gap-4">
                        <x-ui.skeleton
                            variant="avatar"
                            class="shrink-0"
                        />

                        <div class="flex-1 space-y-3">
                            <x-ui.skeleton variant="title" />
                            <x-ui.skeleton variant="text" class="w-1/2" />
                            <x-ui.skeleton variant="text" />
                            <x-ui.skeleton variant="text" class="w-4/5" />
                        </div>
                    </div>

                </div>

            </section>


            {{-- =========================================================
                TOOLTIP
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    title="Tooltip"
                    description="راهنمای کوتاه برای actionها و اطلاعات جانبی."
                />

                <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-white p-10">

                    <div class="flex items-center justify-center gap-5">

                        <x-ui.tooltip text="نمایش اطلاعات بیشتر">
                            <button
                                type="button"
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-brand-50)] font-bold text-[var(--color-brand-700)]"
                                aria-label="اطلاعات بیشتر"
                            >
                                ?
                            </button>
                        </x-ui.tooltip>

                        <x-ui.tooltip
                            text="این دوره مناسب پایه ششم است."
                            position="bottom"
                        >
                            <x-ui.badge variant="brand">
                                پایه ششم
                            </x-ui.badge>
                        </x-ui.tooltip>

                    </div>

                </div>

            </section>


            {{-- =========================================================
                EDUCATION COMPONENTS
            ========================================================== --}}
            <section class="mb-16">

                <x-layout.page-header
                    eyebrow="Education"
                    title="Education Components"
                    description="Componentهای اختصاصی سیستم آموزشی فرزین."
                />

                <div class="mt-8 space-y-10">

                    {{-- Course Cards --}}
                    <div>
                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Course Card
                        </h3>

                        <div class="mt-5 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                            <x-education.course-card
                                title="دوره جامع آمادگی تیزهوشان ششم"
                                description="آموزش مفهومی و تستی برای آمادگی آزمون تیزهوشان."
                                grade="پایه ششم"
                                subject="هوش و استعداد"
                                level="پیشرفته"
                                :teacher="[
                                    'name' => 'استاد محمد احمدی',
                                ]"
                                sessions="24"
                                duration="32 ساعت"
                                price="۲٬۴۵۰٬۰۰۰ تومان"
                                old-price="۲٬۹۰۰٬۰۰۰ تومان"
                                discount="15"
                                featured
                            />

                            <x-education.course-card
                                title="هوش و استعداد تحلیلی"
                                description="تقویت مهارت تحلیل و حل مسئله."
                                grade="پایه ششم"
                                subject="استعداد تحلیلی"
                                level="متوسط"
                                teacher="استاد علی رضایی"
                                sessions="18"
                                duration="21 ساعت"
                                price="۱٬۹۵۰٬۰۰۰ تومان"
                            />

                        </div>
                    </div>


                    {{-- Teacher Cards --}}
                    <div>
                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Teacher Card
                        </h3>

                        <div class="mt-5 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                            <x-education.teacher-card
                                name="دکتر محمد احمدی"
                                specialty="مدرس هوش و استعداد تحلیلی"
                                experience="12"
                                courses-count="8"
                                students-count="۲٬۴۰۰"
                                rating="4.9"
                                verified
                            />

                            <x-education.teacher-card
                                name="استاد علی رضایی"
                                specialty="مدرس استعداد منطقی"
                                experience="9"
                                courses-count="6"
                                students-count="۱٬۸۰۰"
                                rating="4.8"
                                verified
                            />

                        </div>
                    </div>


                    {{-- Lesson Cards --}}
                    <div>
                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Lesson Card
                        </h3>

                        <div class="mt-5 space-y-3">

                            <x-education.lesson-card
                                number="1"
                                title="آشنایی با هوش کلامی"
                                subtitle="مفاهیم پایه و تکنیک‌های حل سؤال"
                                type="video"
                                duration="۲۵ دقیقه"
                                status="completed"
                                progress="100"
                            />

                            <x-education.lesson-card
                                number="2"
                                title="سؤالات هوش تصویری"
                                type="video"
                                duration="۴۰ دقیقه"
                                status="current"
                                progress="65"
                            />

                            <x-education.lesson-card
                                number="3"
                                title="آزمون جامع"
                                type="quiz"
                                duration="۲۰ دقیقه"
                                status="locked"
                            />

                        </div>
                    </div>


                    {{-- Live Class --}}
                    <div>
                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Live Class
                        </h3>

                        <div class="mt-5 grid gap-6 lg:grid-cols-2">

                            <x-education.live-class-card
                                title="کلاس هوش و استعداد تحلیلی"
                                subject="هوش"
                                grade="پایه ششم"
                                teacher="استاد احمدی"
                                date="امروز"
                                start-time="۱۷:۰۰"
                                end-time="۱۸:۳۰"
                                status="live"
                                students-count="۲۸"
                            />

                            <x-education.live-class-card
                                title="حل تست‌های هوش تصویری"
                                subject="هوش تصویری"
                                grade="پایه ششم"
                                teacher="استاد رضایی"
                                date="فردا"
                                start-time="۱۸:۰۰"
                                end-time="۱۹:۳۰"
                                status="upcoming"
                            />

                        </div>
                    </div>


                    {{-- Assignment --}}
                    <div>
                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Assignment
                        </h3>

                        <div class="mt-5 grid gap-6 lg:grid-cols-2">

                            <x-education.assignment-card
                                title="تمرین هوش کلامی"
                                course="دوره جامع تیزهوشان ششم"
                                description="۲۰ سؤال از مباحث تدریس‌شده."
                                due-date="شنبه ۱۵ شهریور"
                                due-time="۲۳:۵۹"
                                status="pending"
                            />

                            <x-education.assignment-card
                                title="تمرین الگوهای عددی"
                                course="هوش و استعداد"
                                status="graded"
                                score="۱۸"
                                max-score="۲۰"
                            />

                        </div>
                    </div>


                    {{-- Exam --}}
                    <div>
                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Exam
                        </h3>

                        <div class="mt-5 grid gap-6 lg:grid-cols-2">

                            <x-education.exam-card
                                title="آزمون جامع شماره ۲"
                                course="آمادگی تیزهوشان"
                                subject="هوش"
                                date="شنبه ۱۵ شهریور"
                                start-time="۱۸:۰۰"
                                duration="۶۰ دقیقه"
                                questions-count="۳۰"
                                status="available"
                            />

                            <x-education.exam-card
                                title="آزمون هوش تصویری"
                                course="هوش و استعداد"
                                subject="هوش تصویری"
                                date="۱۰ شهریور"
                                duration="۴۵ دقیقه"
                                questions-count="۲۵"
                                status="completed"
                                score="۲۲"
                                max-score="۲۵"
                            />

                        </div>
                    </div>


                    {{-- Progress Card --}}
                    <div>
                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Progress Card
                        </h3>

                        <div class="mt-5 grid gap-6 lg:grid-cols-2">

                            <x-education.progress-card
                                title="دوره جامع آمادگی تیزهوشان ششم"
                                subtitle="هوش و استعداد تحلیلی"
                                teacher="استاد احمدی"
                                :progress="72"
                                completed-lessons="18"
                                total-lessons="25"
                                last-lesson="الگوهای عددی"
                            />

                            <x-education.progress-card
                                title="ریاضی ششم"
                                subtitle="آمادگی تیزهوشان"
                                teacher="استاد محمدی"
                                :progress="100"
                                completed-lessons="20"
                                total-lessons="20"
                                last-lesson="آزمون پایانی"
                            />

                        </div>
                    </div>


                    {{-- Schedule --}}
                    <div>
                        <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                            Schedule
                        </h3>

                        <div class="mt-5 space-y-3">

                            <x-education.schedule-card
                                title="کلاس هوش و استعداد تحلیلی"
                                subtitle="جلسه دوازدهم"
                                day="شنبه"
                                date="۱۵ شهریور"
                                start-time="۱۷:۰۰"
                                end-time="۱۸:۳۰"
                                course="دوره جامع تیزهوشان ششم"
                                teacher="استاد احمدی"
                                type="class"
                                status="today"
                            />

                            <x-education.schedule-card
                                title="آزمون جامع شماره ۲"
                                day="یکشنبه"
                                date="۱۶ شهریور"
                                start-time="۱۸:۰۰"
                                end-time="۱۹:۰۰"
                                course="آمادگی تیزهوشان"
                                type="exam"
                                status="upcoming"
                            />

                        </div>
                    </div>

                </div>

            </section>


            {{-- =========================================================
                FOOTER NOTE
            ========================================================== --}}
            <div class="border-t border-[var(--color-border)] pt-8 text-center">

                <p class="text-xs leading-6 text-[var(--color-text-muted)]">
                    Farzin UI System — تمام Componentها قبل از استفاده در صفحات اصلی در این صفحه تست می‌شوند.
                </p>

            </div>

        </x-layout.container>
    </x-layout.section>

@endsection
