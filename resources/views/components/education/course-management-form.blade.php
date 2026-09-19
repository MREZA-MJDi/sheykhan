@props([
    'course',
    'academies',
    'action',
    'method' => 'POST',
    'routePrefix' => 'owner.courses',
])

@php
    $isEdit = $course->exists;
    $currentAccess = old('access_type', $course->access_type ?: 'paid');
    $currentStatus = old('status', $course->status ?: 'draft');
@endphp

<div
    x-data="{
        accessType: @js($currentAccess),
        slug: @js(old('slug', $course->slug)),
        title: @js(old('title', $course->title)),
        syncSlug() {
            if (@js($isEdit)) return;
            this.slug = this.title
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    }"
    class="space-y-6"
>
    @if($errors->any())
        <div class="rounded-2xl border border-[var(--color-danger-100)] bg-[var(--color-danger-50)] p-4 text-sm text-[var(--color-danger-700)]">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-6">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <section class="panel-card p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-bold text-[var(--color-brand-600)]">اطلاعات اصلی</div>
                        <h2 class="mt-2 text-xl font-black text-[var(--color-text)]">تعریف دوره</h2>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                            اطلاعات عمومی، مسیر انتشار و توضیحات دوره را تنظیم کن.
                        </p>
                    </div>
                </div>

                <div class="mt-7 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="title" class="mb-2 block text-sm font-bold text-[var(--color-text)]">عنوان دوره</label>
                        <input
                            id="title"
                            name="title"
                            x-model="title"
                            @input="syncSlug()"
                            value="{{ old('title', $course->title) }}"
                            required
                            class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm outline-none transition focus:border-[var(--color-brand-400)] focus:ring-4 focus:ring-[var(--color-brand-100)]"
                            placeholder="مثلاً مبانی برنامه‌نویسی وب"
                        >
                    </div>

                    <div>
                        <label for="slug" class="mb-2 block text-sm font-bold text-[var(--color-text)]">Slug</label>
                        <input
                            id="slug"
                            name="slug"
                            x-model="slug"
                            value="{{ old('slug', $course->slug) }}"
                            required
                            dir="ltr"
                            class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm outline-none transition focus:border-[var(--color-brand-400)] focus:ring-4 focus:ring-[var(--color-brand-100)]"
                            placeholder="web-programming"
                        >
                    </div>

                    <div>
                        <label for="academy_id" class="mb-2 block text-sm font-bold text-[var(--color-text)]">آموزشگاه</label>
                        <select
                            id="academy_id"
                            name="academy_id"
                            required
                            class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm outline-none focus:border-[var(--color-brand-400)] focus:ring-4 focus:ring-[var(--color-brand-100)]"
                        >
                            <option value="">انتخاب آموزشگاه</option>
                            @foreach($academies as $academy)
                                <option
                                    value="{{ $academy->id }}"
                                    @selected((string) old('academy_id', $course->academy_id) === (string) $academy->id)
                                >
                                    {{ $academy->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="level" class="mb-2 block text-sm font-bold text-[var(--color-text)]">سطح</label>
                        <input
                            id="level"
                            name="level"
                            value="{{ old('level', $course->level) }}"
                            class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm outline-none transition focus:border-[var(--color-brand-400)] focus:ring-4 focus:ring-[var(--color-brand-100)]"
                            placeholder="مقدماتی"
                        >
                    </div>

                    <div>
                        <label for="duration_minutes" class="mb-2 block text-sm font-bold text-[var(--color-text)]">مدت دوره (دقیقه)</label>
                        <input
                            id="duration_minutes"
                            name="duration_minutes"
                            type="number"
                            min="0"
                            value="{{ old('duration_minutes', $course->duration_minutes ?? 0) }}"
                            class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm outline-none transition focus:border-[var(--color-brand-400)] focus:ring-4 focus:ring-[var(--color-brand-100)]"
                        >
                    </div>

                    <div class="sm:col-span-2">
                        <label for="short_description" class="mb-2 block text-sm font-bold text-[var(--color-text)]">خلاصه</label>
                        <textarea
                            id="short_description"
                            name="short_description"
                            rows="3"
                            class="block w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm outline-none transition focus:border-[var(--color-brand-400)] focus:ring-4 focus:ring-[var(--color-brand-100)]"
                            placeholder="یک توضیح کوتاه و قابل نمایش در کارت دوره"
                        >{{ old('short_description', $course->short_description) }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="mb-2 block text-sm font-bold text-[var(--color-text)]">توضیحات کامل</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="8"
                            class="block w-full rounded-xl border border-[var(--color-border)] bg-white px-4 py-3 text-sm leading-7 outline-none transition focus:border-[var(--color-brand-400)] focus:ring-4 focus:ring-[var(--color-brand-100)]"
                        >{{ old('description', $course->description) }}</textarea>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="panel-card p-5">
                    <div class="text-xs font-bold text-[var(--color-brand-600)]">مدل دسترسی</div>
                    <h2 class="mt-2 text-lg font-black">رایگان یا پولی؟</h2>

                    <div class="mt-5 grid gap-3">
                        <label
                            class="cursor-pointer rounded-2xl border p-4 transition"
                            :class="accessType === 'free'
                                ? 'border-[var(--color-success-500)] bg-[var(--color-success-50)]'
                                : 'border-[var(--color-border)] bg-white'"
                        >
                            <input type="radio" name="access_type" value="free" class="sr-only" x-model="accessType">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full border" :class="accessType === 'free' ? 'border-[var(--color-success-600)]' : 'border-[var(--color-border-strong)]'">
                                    <span x-show="accessType === 'free'" class="h-2.5 w-2.5 rounded-full bg-[var(--color-success-600)]"></span>
                                </span>
                                <div>
                                    <div class="text-sm font-black">دوره رایگان</div>
                                    <p class="mt-1 text-xs leading-6 text-[var(--color-text-secondary)]">برای دسترسی به محتوای محافظت‌شده پرداخت لازم نیست.</p>
                                </div>
                            </div>
                        </label>

                        <label
                            class="cursor-pointer rounded-2xl border p-4 transition"
                            :class="accessType === 'paid'
                                ? 'border-[var(--color-brand-500)] bg-[var(--color-brand-50)]'
                                : 'border-[var(--color-border)] bg-white'"
                        >
                            <input type="radio" name="access_type" value="paid" class="sr-only" x-model="accessType">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full border" :class="accessType === 'paid' ? 'border-[var(--color-brand-600)]' : 'border-[var(--color-border-strong)]'">
                                    <span x-show="accessType === 'paid'" class="h-2.5 w-2.5 rounded-full bg-[var(--color-brand-600)]"></span>
                                </span>
                                <div>
                                    <div class="text-sm font-black">دوره پولی</div>
                                    <p class="mt-1 text-xs leading-6 text-[var(--color-text-secondary)]">تا زمان ثبت پرداخت معتبر، فایل‌های محافظت‌شده قفل می‌مانند.</p>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div x-show="accessType === 'paid'" x-cloak class="mt-5">
                        <label for="price" class="mb-2 block text-sm font-bold">قیمت دوره (تومان)</label>
                        <input
                            id="price"
                            name="price"
                            type="number"
                            min="0.01"
                            step="1"
                            :required="accessType === 'paid'"
                            value="{{ old('price', $course->price) }}"
                            class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm outline-none focus:border-[var(--color-brand-400)] focus:ring-4 focus:ring-[var(--color-brand-100)]"
                            placeholder="890000"
                        >
                    </div>
                </section>

                <section class="panel-card p-5">
                    <div class="text-xs font-bold text-[var(--color-brand-600)]">انتشار</div>
                    <h2 class="mt-2 text-lg font-black">وضعیت دوره</h2>

                    <div class="mt-5">
                        <select name="status" class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm outline-none focus:border-[var(--color-brand-400)] focus:ring-4 focus:ring-[var(--color-brand-100)]">
                            <option value="draft" @selected($currentStatus === 'draft')>پیش‌نویس</option>
                            <option value="published" @selected($currentStatus === 'published')>منتشرشده</option>
                            <option value="archived" @selected($currentStatus === 'archived')>آرشیوشده</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <label for="published_at" class="mb-2 block text-xs font-bold text-[var(--color-text-secondary)]">تاریخ انتشار</label>
                        <input
                            id="published_at"
                            name="published_at"
                            type="datetime-local"
                            value="{{ old('published_at', $course->published_at?->format('Y-m-d\TH:i')) }}"
                            class="block min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm outline-none"
                        >
                    </div>
                </section>

                <div class="flex flex-col gap-3">
                    <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[var(--color-brand-600)] px-5 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-[var(--color-brand-700)]">
                        {{ $isEdit ? 'ذخیره تغییرات' : 'ساخت دوره' }}
                    </button>
                    <a href="{{ route($routePrefix . '.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[var(--color-border)] bg-white px-5 text-sm font-bold text-[var(--color-text-secondary)] transition hover:bg-[var(--color-background-soft)]">
                        انصراف
                    </a>
                </div>
            </aside>
        </div>
    </form>

    @if($isEdit)
        <section class="panel-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xs font-bold text-[var(--color-brand-600)]">رسانه دوره</div>
                    <h2 class="mt-2 text-xl font-black">فایل‌های خصوصی و آموزشی</h2>
                    <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                        این فایل‌ها private ذخیره می‌شوند و دانلودشان فقط بعد از عبور از CourseAccessPolicy ممکن است.
                    </p>
                </div>

                <form method="POST" action="{{ route($routePrefix . '.media.store', $course) }}" enctype="multipart/form-data" class="flex flex-col gap-2 sm:min-w-[24rem] sm:flex-row">
                    @csrf
                    <input type="hidden" name="collection" value="course-assets">
                    <input
                        type="file"
                        name="media"
                        required
                        class="min-h-11 flex-1 rounded-xl border border-[var(--color-border)] bg-white px-3 py-2 text-xs"
                        accept=".jpg,.jpeg,.png,.webp,.pdf,.mp4,.webm,.mov,.zip"
                    >
                    <button type="submit" class="min-h-11 rounded-xl bg-[var(--color-slate-900)] px-4 text-xs font-black text-white">
                        آپلود
                    </button>
                </form>
            </div>

            <div class="mt-6 grid gap-3">
                @forelse($course->media as $media)
                    <div class="flex flex-col gap-3 rounded-2xl border border-[var(--color-border)] bg-[var(--color-background-soft)] p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-bold text-[var(--color-text)]">{{ $media->original_name }}</div>
                            <div class="mt-1 text-xs text-[var(--color-text-muted)]">
                                {{ $media->mime_type ?: 'فایل' }}
                                ·
                                {{ number_format($media->size / 1024 / 1024, 2) }} MB
                                ·
                                <span class="font-bold text-[var(--color-warning-600)]">Private</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route($routePrefix . '.media.destroy', [$course, $media]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--color-danger-100)] bg-[var(--color-danger-50)] px-3 text-xs font-bold text-[var(--color-danger-600)]">
                                حذف فایل
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-[var(--color-border-strong)] bg-[var(--color-background-soft)] p-8 text-center">
                        <div class="text-sm font-bold text-[var(--color-text)]">هنوز فایلی اضافه نشده</div>
                        <p class="mt-1 text-xs text-[var(--color-text-muted)]">ویدیو، PDF یا فایل آموزشی را از همین‌جا اضافه کن.</p>
                    </div>
                @endforelse
            </div>
        </section>
    @endif
</div>
