@extends('layouts.owner')

@section('title','اعضای آموزشگاه | شیخان')
@section('header-title','اعضای آموزشگاه')

@section('content')
<div class="people-page panel-page-enter space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--panel-primary)]">مدیریت افراد</p>
            <h1 class="mt-2 text-2xl font-black tracking-tight">{{ $academy->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
                عضویت، وضعیت مدرس‌ها و نمایش عمومی آن‌ها را مدیریت کن؛ آرشیو یعنی حذف عضویت، نه حذف سابقه.
            </p>
        </div>
        <div class="people-legend">
            <span><i class="active"></i>فعال</span>
            <span><i class="archive"></i>آرشیو</span>
            <span><i class="public"></i>نمایش عمومی</span>
        </div>
    </div>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['مدرس فعال',$memberStats['activeTeachers'],'عضویت فعال','active'],
            ['مدرس آرشیوشده',$memberStats['archivedTeachers'],'عضویت غیرفعال','archive'],
            ['نمایش عمومی',$memberStats['publicTeachers'],'پروفایل قابل نمایش','public'],
            ['دانش‌آموز',$memberStats['students'],'عضو فعال','student'],
            ['والد',$memberStats['parents'],'عضو فعال','parent'],
        ] as [$label,$value,$hint,$tone])
            <article class="people-stat {{ $tone }}">
                <div class="flex items-center justify-between gap-3">
                    <span>{{ $label }}</span><i aria-hidden="true"></i>
                </div>
                <strong>{{ number_format($value) }}</strong>
                <small>{{ $hint }}</small>
            </article>
        @endforeach
    </section>

    <div class="grid gap-5 xl:grid-cols-3">
        <section class="dashboard-panel p-5 sm:p-6">
            <div class="people-section-heading">
                <div class="people-step">۱</div>
                <div><span>افزودن مدرس</span><h2>ساخت حساب مدرس</h2><p>حساب، نقش، پروفایل و عضویت در یک تراکنش ساخته می‌شوند.</p></div>
            </div>

            <form method="POST" action="{{ route('owner.people.store-teacher',$academy) }}" class="mt-6 grid gap-3">
                @csrf
                <label class="people-field"><span>نام و نام خانوادگی <b>*</b></span><input name="name" value="{{ old('name') }}" required class="people-input" placeholder="مثلاً علی رضایی"><x-owner.field-error field="name"/></label>
                <label class="people-field"><span>ایمیل <b>*</b></span><input name="email" type="email" value="{{ old('email') }}" required class="people-input" dir="ltr" placeholder="teacher@example.com"><x-owner.field-error field="email"/></label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="people-field"><span>رمز عبور <b>*</b></span><input name="password" type="password" required class="people-input" dir="ltr"><x-owner.field-error field="password"/></label>
                    <label class="people-field"><span>تکرار رمز <b>*</b></span><input name="password_confirmation" type="password" required class="people-input" dir="ltr"></label>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="people-field"><span>تخصص</span><input name="specialization" value="{{ old('specialization') }}" class="people-input" placeholder="مثلاً فیزیک"></label>
                    <label class="people-field"><span>سابقه <small>سال</small></span><input name="experience_years" type="number" min="0" max="80" value="{{ old('experience_years') }}" class="people-input" inputmode="numeric"></label>
                </div>
                <label class="people-toggle">
                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public', true))>
                    <span class="people-toggle-check">✓</span>
                    <span><strong>نمایش در لیست مدرس‌های سایت</strong><small>این فقط نمایش عمومی پروفایل است و به عضویت فعال ربطی ندارد.</small></span>
                </label>
                <button type="submit" data-confirm="حساب مدرس ساخته شود؟" class="people-primary-btn mt-2">ساخت حساب مدرس</button>
            </form>
        </section>

        <section class="dashboard-panel p-5 sm:p-6">
            <div class="people-section-heading">
                <div class="people-step">۲</div>
                <div><span>اختصاص دوره</span><h2>مدرس را به دوره وصل کن</h2><p>فقط مدرس‌های فعال همین آموزشگاه در این لیست می‌آیند.</p></div>
            </div>

            <form method="POST" action="{{ route('owner.people.assign-teacher',$academy) }}" class="mt-6 grid gap-3">
                @csrf
                <label class="people-field"><span>مدرس <b>*</b></span><select name="teacher_id" required class="people-input">
                    @forelse($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@empty<option value="">مدرس فعال نداریم</option>@endforelse
                </select><x-owner.field-error field="teacher_id"/></label>
                <label class="people-field"><span>دوره <b>*</b></span><select name="course_id" required class="people-input">
                    @forelse($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@empty<option value="">دوره‌ای وجود ندارد</option>@endforelse
                </select><x-owner.field-error field="course_id"/></label>
                <div class="people-note"><strong>نکته:</strong> آرشیو کردن مدرس، assignmentهای قبلی او را پاک نمی‌کند؛ فقط دسترسی فعال مدرس را می‌بندد.</div>
                <button type="submit" class="people-primary-btn" @disabled(!$teachers->count() || !$courses->count())>اختصاص دوره</button>
            </form>
        </section>

        <section class="dashboard-panel p-5 sm:p-6">
            <div class="people-section-heading">
                <div class="people-step">۳</div>
                <div><span>ثبت‌نام</span><h2>ثبت‌نام دانش‌آموز</h2><p>ثبت duplicate-safe، کنترل ظرفیت و ثبت مالی از backend.</p></div>
            </div>

            <form method="POST" action="{{ route('owner.people.enroll-student',$academy) }}" class="mt-6 grid gap-3">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key',(string)IlluminateSupportStr::uuid()) }}">
                <label class="people-field"><span>دانش‌آموز <b>*</b></span><select name="student_id" required class="people-input">@forelse($students as $student)<option value="{{ $student->id }}">{{ $student->name }}</option>@empty<option value="">دانش‌آموزی وجود ندارد</option>@endforelse</select><x-owner.field-error field="student_id"/></label>
                <label class="people-field"><span>دوره <b>*</b></span><select id="enrollment-course" name="course_id" required class="people-input">@forelse($courses as $course)<option value="{{ $course->id }}">{{ $course->title }} — {{ $course->isFree()?'رایگان':number_format((float)$course->price,0,'.',',').' تومان' }}</option>@empty<option value="">دوره‌ای وجود ندارد</option>@endforelse</select><x-owner.field-error field="course_id"/></label>
                <label class="people-field"><span>کلاس</span><select data-classroom-select data-course-select="enrollment-course" name="classroom_id" class="people-input"><option value="">بدون کلاس</option>@foreach($courses as $course)@foreach($course->classrooms as $classroom)<option data-course-id="{{ $course->id }}" value="{{ $classroom->id }}">{{ $classroom->title }} — {{ $classroom->capacity??'بدون محدودیت' }}</option>@endforeach @endforeach</select><span data-no-classrooms hidden class="text-[9px] text-slate-500">برای این دوره کلاسی تعریف نشده است.</span><x-owner.field-error field="classroom_id"/></label>
                <label class="people-field"><span>مبلغ پرداختی <small>تومان</small></span><input name="paid_amount" type="number" step="0.01" min="0" value="{{ old('paid_amount') }}" class="people-input" inputmode="decimal"><x-owner.field-error field="paid_amount"/></label>
                <button type="submit" class="people-primary-btn" @disabled(!$students->count() || !$courses->count())>ثبت‌نام دانش‌آموز</button>
            </form>
        </section>
    </div>

    <section class="dashboard-panel overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:px-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div><h2 class="text-base font-black">مدرس‌های آموزشگاه</h2><p class="mt-1 text-[10px] text-slate-500">عضویت و نمایش عمومی دو وضعیت مستقل‌اند.</p></div>
                <span class="people-count-badge">{{ number_format($memberStats['activeTeachers'] + $memberStats['archivedTeachers']) }} پروفایل مدرس</span>
            </div>
        </div>

        <div class="p-5 sm:p-6">
            <div class="grid gap-3 lg:grid-cols-2">
                @forelse($teachers as $teacher)
                    <article class="people-person-card">
                        <div class="people-person-main">
                            <div class="people-avatar">{{ mb_substr($teacher->name,0,1) }}</div>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-black">{{ $teacher->name }}</h3>
                                <p class="mt-1 truncate text-[9px] text-slate-500">{{ $teacher->email }}</p>
                            </div>
                        </div>
                        <div class="people-badges">
                            <span class="people-status active"><i></i>عضو فعال</span>
                            @if($teacher->teacherProfile?->is_public)
                                <span class="people-status public"><i></i>نمایش عمومی</span>
                            @else
                                <span class="people-status hidden"><i></i>مخفی از سایت</span>
                            @endif
                        </div>
                        <div class="people-person-actions">
                            <form method="POST" action="{{ route('owner.people.teacher-visibility',[$academy,$teacher]) }}">@csrf @method('PATCH')
                                <input type="hidden" name="is_public" value="{{ $teacher->teacherProfile?->is_public ? 0 : 1 }}">
                                <button type="submit" class="people-action-btn">{{ $teacher->teacherProfile?->is_public ? 'مخفی از سایت' : 'نمایش در سایت' }}</button>
                            </form>
                            <form method="POST" action="{{ route('owner.people.archive-teacher',[$academy,$teacher]) }}">@csrf @method('PATCH')
                                <button type="submit" data-confirm="عضویت این مدرس آرشیو شود؟ سابقه و assignmentها حذف نمی‌شوند." class="people-action-btn danger">آرشیو عضویت</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="people-empty lg:col-span-2"><strong>مدرس فعالی وجود ندارد.</strong><span>از فرم بالای صفحه اولین مدرس را بساز.</span></div>
                @endforelse
            </div>

            <div class="mt-7 border-t border-slate-100 pt-6">
                <div class="flex items-center justify-between gap-3"><div><h3 class="text-sm font-black">مدرس‌های آرشیوشده</h3><p class="mt-1 text-[9px] text-slate-500">عضویت آن‌ها غیرفعال است؛ در صورت نیاز می‌توانی برگردانی‌شان.</p></div><span class="people-count-badge muted">{{ number_format($memberStats['archivedTeachers']) }}</span></div>
                <div class="mt-4 grid gap-3 lg:grid-cols-2">
                    @forelse($archivedTeachers as $teacher)
                        <article class="people-person-card archived">
                            <div class="people-person-main">
                                <div class="people-avatar muted">{{ mb_substr($teacher->name,0,1) }}</div>
                                <div class="min-w-0"><h3 class="truncate text-sm font-black">{{ $teacher->name }}</h3><p class="mt-1 truncate text-[9px] text-slate-500">{{ $teacher->email }}</p></div>
                            </div>
                            <div class="people-badges">
                                <span class="people-status archive"><i></i>آرشیوشده</span>
                                @if($teacher->teacherProfile?->is_public)<span class="people-status public"><i></i>هنوز عمومی</span>@else<span class="people-status hidden"><i></i>مخفی از سایت</span>@endif
                            </div>
                            <div class="people-person-actions">
                                <form method="POST" action="{{ route('owner.people.teacher-visibility',[$academy,$teacher]) }}">@csrf @method('PATCH')
                                    <input type="hidden" name="is_public" value="{{ $teacher->teacherProfile?->is_public ? 0 : 1 }}">
                                    <button type="submit" class="people-action-btn">{{ $teacher->teacherProfile?->is_public ? 'مخفی از سایت' : 'نمایش در سایت' }}</button>
                                </form>
                                <form method="POST" action="{{ route('owner.people.restore-teacher',[$academy,$teacher]) }}">@csrf @method('PATCH')
                                    <button type="submit" data-confirm="عضویت این مدرس دوباره فعال شود؟" class="people-action-btn success">فعال‌سازی مجدد</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="people-empty lg:col-span-2"><span>مدرس آرشیوشده‌ای وجود ندارد.</span></div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-4 xl:grid-cols-2">
        @foreach([['دانش‌آموزان',$students],['والدین',$parents]] as [$title,$items])
            <section class="dashboard-panel p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3"><div><h2 class="text-base font-black">{{ $title }}</h2><p class="mt-1 text-[9px] text-slate-500">اعضای فعال آموزشگاه</p></div><span class="people-count-badge">{{ number_format($items->count()) }}</span></div>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    @forelse($items as $person)
                        <div class="people-simple-row"><div class="people-avatar small">{{ mb_substr($person->name,0,1) }}</div><div class="min-w-0"><strong class="block truncate text-xs">{{ $person->name }}</strong><span class="mt-1 block truncate text-[9px] text-slate-500">{{ $person->email }}</span></div></div>
                    @empty
                        <div class="people-empty sm:col-span-2"><span>عضوی نیست.</span></div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection