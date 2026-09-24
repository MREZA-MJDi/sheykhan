@props(['course','academies','action','method'=>'POST','routePrefix'=>'owner.courses'])

@php
    $isEdit = $course->exists;
    $currentAccess = old('access_type',$course->access_type ?: 'paid');
    $currentStatus = old('status',$course->status ?: 'draft');
    $oldSlugWasProvided = old('slug') !== null && old('slug') !== '';
    $initialPrice = old('price', $course->price !== null ? (string) $course->price : '');
    $initialPublishedAt = old(
        'published_at',
        $course->published_at?->format('Y-m-d\\TH:i')
    );
@endphp

<div
    x-data="{
        isEdit: @js($isEdit),
        accessType: @js($currentAccess),
        status: @js($currentStatus),
        title: @js(old('title',$course->title)),
        slug: @js(old('slug',$course->slug)),
        slugTouched: @js($oldSlugWasProvided || $isEdit),
        priceDisplay: '',
        publishedDate: '',
        publishedTime: '',
        dateError: '',
        transliterate(value) {
            const map = {
                'آ':'a','ا':'a','ب':'b','پ':'p','ت':'t','ث':'s','ج':'j','چ':'ch','ح':'h','خ':'kh',
                'د':'d','ذ':'z','ر':'r','ز':'z','ژ':'zh','س':'s','ش':'sh','ص':'s','ض':'z','ط':'t',
                'ظ':'z','ع':'a','غ':'gh','ف':'f','ق':'gh','ک':'k','ك':'k','گ':'g','ل':'l','م':'m',
                'ن':'n','و':'v','ه':'h','ی':'y','ي':'y','ئ':'y','ؤ':'v','ء':'','ة':'h'
            };

            return value
                .trim()
                .toLowerCase()
                .split('')
                .map((char) => map[char] ?? char)
                .join('')
                .normalize('NFKD')
                .replace(/[\u0300-\u036f]/g,'')
                .replace(/[^a-z0-9]+/g,'-')
                .replace(/^-+|-+$/g,'')
                .slice(0, 120);
        },
        syncSlug() {
            if (this.isEdit || this.slugTouched) return;
            this.slug = this.transliterate(this.title);
        },
        markSlugTouched() {
            this.slugTouched = true;
        },
        normalizeDigits(value) {
            return String(value ?? '').replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
        },
        formatPrice(value) {
            const digits = this.normalizeDigits(value).replace(/\D/g, '');
            return digits ? Number(digits).toLocaleString('en-US') : '';
        },
        syncPrice() {
            const digits = this.normalizeDigits(this.priceDisplay).replace(/\D/g, '');
            this.priceDisplay = digits ? Number(digits).toLocaleString('en-US') : '';
            if (this.$refs.priceRaw) {
                this.$refs.priceRaw.value = this.accessType === 'free' ? '0' : digits;
            }
        },
        formatJalaliDate(value) {
            if (!value) return '';
            const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (!match) return '';

            const date = new Date(Date.UTC(
                Number(match[1]),
                Number(match[2]) - 1,
                Number(match[3]),
                12
            ));

            const formatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian-nu-latn', {
                timeZone: 'UTC',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });

            const parts = Object.fromEntries(formatter.formatToParts(date).map((item) => [item.type, item.value]));
            return `${parts.year}/${parts.month}/${parts.day}`;
        },
        jalaliPartsForGregorian(gregorianDate) {
            const match = String(gregorianDate || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (!match) return null;

            const date = new Date(Date.UTC(
                Number(match[1]),
                Number(match[2]) - 1,
                Number(match[3]),
                12
            ));

            const formatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian-nu-latn', {
                timeZone: 'UTC',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });

            const parts = Object.fromEntries(formatter.formatToParts(date).map((item) => [item.type, item.value]));
            return parts;
        },
        hydratePublishedAt(value) {
            if (!value) return;

            const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
            if (!match) return;

            const parts = this.jalaliPartsForGregorian(match[0]);
            if (!parts) return;

            this.publishedDate = `${parts.year}/${parts.month}/${parts.day}`;
            this.publishedTime = `${match[4]}:${match[5]}`;
        },
        jalaliToGregorian() {
            const normalized = this.normalizeDigits(this.publishedDate).replace(/-/g, '/').trim();
            const match = normalized.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
            const time = this.normalizeDigits(this.publishedTime).match(/^(\d{1,2}):(\d{2})$/);

            if (!match || !time) {
                this.dateError = 'تاریخ و ساعت انتشار را کامل وارد کن؛ مثلاً ۱۴۰۵/۰۷/۰۲ و ۱۸:۳۰.';
                return false;
            }

            const jy = Number(match[1]);
            const jm = Number(match[2]);
            const jd = Number(match[3]);
            const hour = Number(time[1]);
            const minute = Number(time[2]);

            if (jm < 1 || jm > 12 || jd < 1 || jd > 31 || hour > 23 || minute > 59) {
                this.dateError = 'تاریخ یا ساعت واردشده معتبر نیست.';
                return false;
            }

            const formatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian-nu-latn', {
                timeZone: 'UTC',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });

            const start = new Date(Date.UTC(jy + 620, 0, 1, 12));
            for (let offset = 0; offset <= 800; offset++) {
                const candidate = new Date(start.getTime() + offset * 86400000);
                const parts = Object.fromEntries(formatter.formatToParts(candidate).map((item) => [item.type, item.value]));
                if (
                    Number(parts.year) === jy &&
                    Number(parts.month) === jm &&
                    Number(parts.day) === jd
                ) {
                    const year = candidate.getUTCFullYear();
                    const month = String(candidate.getUTCMonth() + 1).padStart(2, '0');
                    const day = String(candidate.getUTCDate()).padStart(2, '0');
                    const h = String(hour).padStart(2, '0');
                    const m = String(minute).padStart(2, '0');

                    if (this.$refs.publishedAt) {
                        this.$refs.publishedAt.value = `${year}-${month}-${day} ${h}:${m}:00`;
                    }
                    this.dateError = '';
                    return true;
                }
            }

            this.dateError = 'این تاریخ جلالی پیدا نشد. تاریخ را بررسی کن.';
            return false;
        },
        setNow() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const parts = this.jalaliPartsForGregorian(`${year}-${month}-${day}`);

            if (parts) {
                this.publishedDate = `${parts.year}/${parts.month}/${parts.day}`;
                this.publishedTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
                this.jalaliToGregorian();
            }
        },
        syncPublishedAt() {
            if (this.status !== 'published') {
                if (this.$refs.publishedAt) this.$refs.publishedAt.value = '';
                this.dateError = '';
                return true;
            }

            if (!this.publishedDate || !this.publishedTime) {
                if (this.$refs.publishedAt) this.$refs.publishedAt.value = '';
                this.dateError = '';
                return true;
            }

            return this.jalaliToGregorian();
        },
        init() {
            this.priceDisplay = this.formatPrice(@js($initialPrice));
            this.hydratePublishedAt(@js($initialPublishedAt));
            this.syncPrice();
        }
    }"
    class="course-form-shell space-y-6"
>
    <form method="POST" action="{{ $action }}" class="space-y-6" @submit="syncPrice(); return syncPublishedAt()">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <section class="panel-card p-5 sm:p-7">
                <div class="course-section-heading">
                    <div class="course-step">۱</div>
                    <div>
                        <span>اطلاعات پایه</span>
                        <h2>دوره را معرفی کن</h2>
                        <p>عنوان، آدرس، آموزشگاه و توضیحات اصلی اینجا ثبت می‌شوند.</p>
                    </div>
                </div>

                <div class="mt-7 grid gap-5 sm:grid-cols-2">
                    <label class="course-field sm:col-span-2">
                        <span class="course-label">عنوان دوره <b>*</b></span>
                        <input name="title" x-model="title" @input="syncSlug()" value="{{ old('title',$course->title) }}" required maxlength="255" autocomplete="off" placeholder="مثلاً فیزیک دهم؛ از صفر تا تسلط" class="course-input">
                        <span class="course-help">عنوانی را بنویس که دانش‌آموز با دیدنش دقیقاً بداند این دوره درباره چیست.</span>
                        <x-owner.field-error field="title"/>
                    </label>

                    <label class="course-field">
                        <span class="course-label">Slug <small>اختیاری</small></span>
                        <input name="slug" x-model="slug" @input="markSlugTouched()" value="{{ old('slug',$course->slug) }}" maxlength="120" dir="ltr" autocomplete="off" placeholder="physics-grade10" class="course-input text-left">
                        <span class="course-help">اگر خالی بگذاری، سیستم از عنوان دوره یک slug مناسب می‌سازد.</span>
                        <x-owner.field-error field="slug"/>
                    </label>

                    <div class="course-field">
                        <span class="course-label">آموزشگاه <b>*</b></span>
                        @if($isEdit)
                            <input type="hidden" name="academy_id" value="{{ $course->academy_id }}">
                            <div class="course-static-field" aria-label="آموزشگاه غیرقابل تغییر">
                                <span>{{ $course->academy?->name ?: 'آموزشگاه فعلی' }}</span>
                                <small>قابل تغییر نیست</small>
                            </div>
                        @else
                            <select name="academy_id" required class="course-input">
                                @foreach($academies as $academy)
                                    <option value="{{ $academy->id }}" @selected((string)old('academy_id',$course->academy_id)===(string)$academy->id)>{{ $academy->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        <x-owner.field-error field="academy_id"/>
                    </div>

                    <label class="course-field">
                        <span class="course-label">سطح</span>
                        <input name="level" value="{{ old('level',$course->level) }}" maxlength="100" placeholder="مثلاً پایه دهم" class="course-input">
                        <x-owner.field-error field="level"/>
                    </label>

                    <label class="course-field">
                        <span class="course-label">مدت دوره <small>دقیقه</small></span>
                        <input name="duration_minutes" type="number" min="0" max="100000" value="{{ old('duration_minutes',$course->duration_minutes ?? 0) }}" inputmode="numeric" placeholder="180" class="course-input">
                        <x-owner.field-error field="duration_minutes"/>
                    </label>

                    <label class="course-field sm:col-span-2">
                        <span class="course-label">خلاصه دوره</span>
                        <textarea name="short_description" rows="3" maxlength="500" placeholder="در دو یا سه جمله بگو این دوره چه چیزی به دانش‌آموز یاد می‌دهد." class="course-input min-h-28 py-3">{{ old('short_description',$course->short_description) }}</textarea>
                        <x-owner.field-error field="short_description"/>
                    </label>

                    <label class="course-field sm:col-span-2">
                        <span class="course-label">توضیحات کامل</span>
                        <textarea name="description" rows="8" placeholder="سرفصل کلی، شیوه تدریس، پیش‌نیازها و هر توضیح مهم دیگر..." class="course-input min-h-48 py-3 leading-7">{{ old('description',$course->description) }}</textarea>
                        <x-owner.field-error field="description"/>
                    </label>
                </div>
            </section>

            <aside class="space-y-5">
                <section class="panel-card p-5">
                    <div class="course-section-heading compact">
                        <div class="course-step">۲</div>
                        <div><span>دسترسی</span><h2>رایگان یا پولی؟</h2></div>
                    </div>

                    <div class="mt-5 grid gap-3">
                        <label class="course-choice" :class="{ 'is-selected': accessType === 'free' }">
                            <input type="radio" name="access_type" value="free" class="sr-only" x-model="accessType" @change="syncPrice()">
                            <span class="course-choice-mark">✓</span>
                            <span><strong>دوره رایگان</strong><small>دانش‌آموز بدون پرداخت می‌تواند ثبت‌نام کند.</small></span>
                        </label>
                        <label class="course-choice" :class="{ 'is-selected': accessType === 'paid' }">
                            <input type="radio" name="access_type" value="paid" class="sr-only" x-model="accessType" @change="syncPrice()">
                            <span class="course-choice-mark">✓</span>
                            <span><strong>دوره پولی</strong><small>برای دسترسی به دوره، پرداخت لازم است.</small></span>
                        </label>
                    </div>

                    <div x-show="accessType === 'paid'" x-cloak class="mt-5">
                        <label for="price-display" class="course-label">قیمت دوره <small>تومان</small> <b>*</b></label>
                        <div class="course-money-input mt-2">
                            <input
                                id="price-display"
                                type="text"
                                inputmode="numeric"
                                dir="ltr"
                                x-model="priceDisplay"
                                @input="syncPrice()"
                                autocomplete="off"
                                placeholder="۲۵۰٬۰۰۰"
                                class="course-input course-money-field"
                            >
                            <span>تومان</span>
                        </div>
                        <input x-ref="priceRaw" type="hidden" name="price" value="{{ $initialPrice }}">
                        <span class="course-help">اعداد هر سه رقم جدا می‌شوند تا مبلغ خواناتر باشد.</span>
                        <x-owner.field-error field="price"/>
                    </div>
                </section>

                <section class="panel-card p-5">
                    <div class="course-section-heading compact">
                        <div class="course-step">۳</div>
                        <div><span>انتشار</span><h2>چه زمانی دیده شود؟</h2></div>
                    </div>

                    <div class="course-status-preview" :class="status">
                        <div class="course-status-preview-dot"></div>
                        <div>
                            <strong x-text="status === 'published' ? 'منتشرشده' : (status === 'archived' ? 'آرشیو' : 'پیش‌نویس')"></strong>
                            <span x-show="status === 'published'">این دوره در وضعیت ارائه قرار می‌گیرد.</span>
                            <span x-show="status === 'draft'">دوره هنوز برای انتشار آماده‌سازی می‌شود.</span>
                            <span x-show="status === 'archived'">دوره نگهداری می‌شود اما فعال نیست.</span>
                        </div>
                    </div>

                    <label class="course-field mt-5">
                        <span class="course-label">وضعیت <b>*</b></span>
                        <select name="status" x-model="status" class="course-input">
                            <option value="draft">پیش‌نویس</option>
                            <option value="published">منتشرشده</option>
                            <option value="archived">آرشیو</option>
                        </select>
                        <x-owner.field-error field="status"/>
                    </label>

                    <div x-show="status === 'published'" x-cloak class="mt-5">
                        <div class="flex items-center justify-between gap-3">
                            <span class="course-label">تاریخ انتشار <small>شمسی</small></span>
                            <button type="button" class="course-date-now" @click="setNow()">همین الان</button>
                        </div>
                        <div class="mt-2 grid gap-2 sm:grid-cols-[1fr_7.5rem]">
                            <input type="text" x-model="publishedDate" dir="ltr" inputmode="numeric" maxlength="10" placeholder="۱۴۰۵/۰۷/۰۲" class="course-input text-left" aria-label="تاریخ انتشار شمسی">
                            <input type="text" x-model="publishedTime" dir="ltr" inputmode="numeric" maxlength="5" placeholder="۱۸:۳۰" class="course-input text-left" aria-label="ساعت انتشار">
                        </div>
                        <input x-ref="publishedAt" type="hidden" name="published_at" value="{{ $initialPublishedAt }}">
                        <span class="course-help">تاریخ را شمسی وارد کن؛ سیستم مقدار استاندارد میلادی را برای ذخیره‌سازی تبدیل می‌کند. برای انتشار فوری، «همین الان» را بزن.</span>
                        <p x-show="dateError" x-text="dateError" class="course-inline-error"></p>
                        <x-owner.field-error field="published_at"/>
                    </div>
                </section>

                <div class="course-submitbar">
                    <button type="submit" class="course-primary-btn w-full">{{ $isEdit?'ذخیره تغییرات':'ساخت دوره' }}</button>
                    <a href="{{ route($routePrefix.'.index') }}" class="course-secondary-btn w-full">انصراف</a>
                </div>
            </aside>
        </div>
    </form>

    @if($isEdit)
        <section class="panel-card p-5 sm:p-7" data-media-uploader>
            <div class="course-media-head">
                <div>
                    <div class="course-section-heading compact">
                        <div class="course-step">۴</div>
                        <div>
                            <span>رسانه</span>
                            <h2>فایل‌های خصوصی دوره</h2>
                            <p>ویدیو، PDF و فایل‌های آموزشی اینجا نگهداری می‌شوند.</p>
                        </div>
                    </div>
                    <div class="course-upload-specs mt-4">
                        <span><b>حداکثر:</b> ۵۰۰ مگابایت</span>
                        <span><b>ویدیو:</b> MP4 / WebM / MOV</span>
                        <span><b>فایل:</b> PDF / ZIP</span>
                        <span><b>تصویر:</b> JPG / PNG / WEBP</span>
                    </div>
                </div>

                <form method="POST" action="{{ route($routePrefix.'.media.store',$course) }}" enctype="multipart/form-data" class="course-upload-box" data-upload-form data-max-bytes="524288000">
                    @csrf
                    <input type="hidden" name="collection" value="course-assets">
                    <label class="course-file-input">
                        <span>انتخاب فایل</span>
                        <input type="file" name="media" required accept=".jpg,.jpeg,.png,.webp,.pdf,.mp4,.webm,.mov,.zip">
                    </label>

                    <div class="course-upload-preview" data-upload-preview hidden>
                        <div class="course-upload-preview-media">
                            <img data-preview-image alt="" hidden>
                            <video data-preview-video controls preload="metadata" hidden></video>
                            <div data-preview-generic class="course-preview-generic" hidden>FILE</div>
                        </div>
                        <div class="min-w-0">
                            <strong data-preview-name class="block truncate text-xs font-black"></strong>
                            <span data-preview-size class="mt-1 block text-[9px] text-[var(--color-text-muted)]"></span>
                        </div>
                    </div>

                    <div class="course-upload-progress" data-upload-progress hidden>
                        <div class="course-upload-progress-head">
                            <span>در حال آپلود</span>
                            <strong data-upload-status>۰٪</strong>
                        </div>
                        <div class="course-progress-track"><span data-upload-progress-bar></span></div>
                    </div>

                    <p class="course-upload-status" data-upload-status aria-live="polite"></p>
                    <button type="submit" class="course-action-btn primary">آپلود فایل</button>
                </form>
            </div>

            <div class="mt-6 grid gap-3">
                @forelse($course->media as $media)
                    <div class="course-media-row">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-bold">{{ $media->original_name }}</div>
                            <div class="mt-1 text-xs text-[var(--color-text-muted)]">
                                {{ $media->mime_type ?: 'فایل' }}
                                <span class="mx-1">·</span>
                                {{ number_format($media->size/1024/1024,2) }} MB
                                <span class="mx-1">·</span>
                                خصوصی
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('media.download',$media) }}" class="course-action-btn" target="_blank" rel="noopener">دریافت</a>
                            <form method="POST" action="{{ route($routePrefix.'.media.destroy',[$course,$media]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" data-confirm="این فایل حذف شود؟" class="course-action-btn danger">حذف فایل</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="course-empty-state">
                        <div class="text-sm font-black">هنوز فایلی اضافه نشده</div>
                        <p class="mt-1 text-xs leading-6 text-[var(--color-text-muted)]">یک فایل آموزشی انتخاب کن و با «آپلود فایل» اضافه‌اش کن.</p>
                    </div>
                @endforelse
            </div>
        </section>
    @endif
</div>