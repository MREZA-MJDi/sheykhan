@props(['course','academies','action','method'=>'POST','routePrefix'=>'owner.courses'])

@php
    $isEdit = $course->exists;
    $currentAccess = old('access_type',$course->access_type ?: 'paid');
    $currentStatus = old('status',$course->status ?: 'draft');
    $oldSlugWasProvided = old('slug') !== null && old('slug') !== '';
@endphp

<div
    x-data="{
        isEdit: @js($isEdit),
        accessType: @js($currentAccess),
        status: @js($currentStatus),
        title: @js(old('title',$course->title)),
        slug: @js(old('slug',$course->slug)),
        slugTouched: @js($oldSlugWasProvided || $isEdit),
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
        }
    }"
    class="course-form-shell space-y-6"
>
    <form method="POST" action="{{ $action }}" class="space-y-6">
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
                        <input
                            name="title"
                            x-model="title"
                            @input="syncSlug()"
                            value="{{ old('title',$course->title) }}"
                            required
                            maxlength="255"
                            autocomplete="off"
                            placeholder="مثلاً فیزیک دهم؛ از صفر تا تسلط"
                            class="course-input"
                        >
                        <span class="course-help">عنوانی را بنویس که دانش‌آموز با دیدنش دقیقاً بداند این دوره درباره چیست.</span>
                        <x-owner.field-error field="title"/>
                    </label>

                    <label class="course-field">
                        <span class="course-label">Slug <small>اختیاری</small></span>
                        <input
                            name="slug"
                            x-model="slug"
                            @input="markSlugTouched()"
                            value="{{ old('slug',$course->slug) }}"
                            maxlength="120"
                            dir="ltr"
                            autocomplete="off"
                            placeholder="physics-grade10"
                            class="course-input text-left"
                        >
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
                        <div>
                            <span>دسترسی</span>
                            <h2>رایگان یا پولی؟</h2>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3">
                        <label class="course-choice" :class="{ 'is-selected': accessType === 'free' }">
                            <input type="radio" name="access_type" value="free" class="sr-only" x-model="accessType">
                            <span class="course-choice-mark">✓</span>
                            <span>
                                <strong>دوره رایگان</strong>
                                <small>دانش‌آموز بدون پرداخت می‌تواند ثبت‌نام کند.</small>
                            </span>
                        </label>

                        <label class="course-choice" :class="{ 'is-selected': accessType === 'paid' }">
                            <input type="radio" name="access_type" value="paid" class="sr-only" x-model="accessType">
                            <span class="course-choice-mark">✓</span>
                            <span>
                                <strong>دوره پولی</strong>
                                <small>برای دسترسی به دوره، پرداخت لازم است.</small>
                            </span>
                        </label>
                    </div>

                    <div x-show="accessType === 'paid'" x-cloak class="mt-5">
                        <label for="price" class="course-label">قیمت دوره <small>تومان</small> <b>*</b></label>
                        <div class="relative mt-2">
                            <input
                                id="price"
                                name="price"
                                type="number"
                                min="1"
                                step="1"
                                inputmode="decimal"
                                :required="accessType === 'paid'"
                                :disabled="accessType === 'free'"
                                value="{{ old('price',$course->price) }}"
                                placeholder="250000"
                                class="course-input pl-16"
                            >
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-xs font-bold text-[var(--color-text-muted)]">تومان</span>
                        </div>
                        <x-owner.field-error field="price"/>
                    </div>
                </section>

                <section class="panel-card p-5">
                    <div class="course-section-heading compact">
                        <div class="course-step">۳</div>
                        <div>
                            <span>انتشار</span>
                            <h2>چه زمانی دیده شود؟</h2>
                        </div>
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
                        <span class="course-help" x-show="status === 'draft'">فعلاً فقط مدیر و افراد مجاز آن را می‌بینند.</span>
                        <span class="course-help" x-show="status === 'published'">دوره برای استفاده عادی آماده است.</span>
                        <span class="course-help" x-show="status === 'archived'">دوره از حالت فعال خارج می‌شود و برای نگهداری باقی می‌ماند.</span>
                        <x-owner.field-error field="status"/>
                    </label>

                    <label class="course-field mt-5">
                        <span class="course-label">تاریخ انتشار <small>اختیاری</small></span>
                        <input name="published_at" type="datetime-local" value="{{ old('published_at',$course->published_at?->format('Y-m-dTH:i')) }}" class="course-input">
                        <span class="course-help">برای انتشار فوری خالی بگذار؛ برای زمان‌بندی می‌توانی تاریخ بدهی.</span>
                        <x-owner.field-error field="published_at"/>
                    </label>
                </section>

                <div class="course-submitbar">
                    <button type="submit" class="course-primary-btn w-full">{{ $isEdit?'ذخیره تغییرات':'ساخت دوره' }}</button>
                    <a href="{{ route($routePrefix.'.index') }}" class="course-secondary-btn w-full">انصراف</a>
                </div>
            </aside>
        </div>
    </form>

    @if($isEdit)
        <section class="panel-card p-5 sm:p-7">
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
                </div>
                <form method="POST" action="{{ route($routePrefix.'.media.store',$course) }}" enctype="multipart/form-data" class="course-upload-box">
                    @csrf
                    <input type="hidden" name="collection" value="course-assets">
                    <label class="course-file-input">
                        <span>انتخاب فایل</span>
                        <input type="file" name="media" required accept=".jpg,.jpeg,.png,.webp,.pdf,.mp4,.webm,.mov,.zip">
                    </label>
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
                                Private
                            </div>
                        </div>
                        <form method="POST" action="{{ route($routePrefix.'.media.destroy',[$course,$media]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" data-confirm="این فایل حذف شود؟" class="course-action-btn danger">حذف فایل</button>
                        </form>
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