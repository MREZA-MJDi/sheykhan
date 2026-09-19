@extends('layouts.owner')

@section('title', 'اعضای آموزشگاه | شیخان')
@section('header-title', 'اعضای آموزشگاه')

@section('content')
<div class="owner-page">
    @if(session('success'))
        <div class="owner-alert owner-alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="owner-alert owner-alert-danger">
            <strong>عملیات انجام نشد.</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="owner-page-hero">
        <div>
            <span class="owner-eyebrow">مدیریت اعضا</span>
            <h1>{{ $academy->name }}</h1>
            <p>مدرس‌ها، دانش‌آموزان و والدین را مدیریت کن و مسیر هر مدرس را به دوره‌های مربوط وصل نگه دار.</p>
        </div>
        <a href="{{ route('owner.classrooms.index', $academy) }}" class="owner-page-action">مدیریت کلاس‌ها</a>
    </section>

    <section class="owner-mini-stats">
        <article><span>مدرس فعال</span><strong>{{ $teachers->count() }}</strong></article>
        <article><span>دانش‌آموز فعال</span><strong>{{ $students->count() }}</strong></article>
        <article><span>والد فعال</span><strong>{{ $parents->count() }}</strong></article>
        <article><span>دوره</span><strong>{{ $courses->count() }}</strong></article>
    </section>

    <div class="owner-admin-grid">
        <section class="dashboard-panel owner-admin-card">
            <div class="owner-card-head">
                <div>
                    <span class="owner-eyebrow">مدرس جدید</span>
                    <h2>ساخت حساب مدرس</h2>
                    <p>اکانت، نقش و پروفایل مدرس را در یک مرحله بساز.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('owner.people.store-teacher', $academy) }}" class="owner-form-grid">
                @csrf
                <label><span>نام و نام خانوادگی</span><input name="name" value="{{ old('name') }}" required></label>
                <label><span>ایمیل</span><input name="email" type="email" value="{{ old('email') }}" required dir="ltr"></label>
                <label><span>رمز عبور</span><input name="password" type="password" required dir="ltr"></label>
                <label><span>تکرار رمز</span><input name="password_confirmation" type="password" required dir="ltr"></label>
                <label><span>تخصص</span><input name="specialization" value="{{ old('specialization') }}"></label>
                <label><span>سابقه (سال)</span><input name="experience_years" type="number" min="0" value="{{ old('experience_years', 0) }}"></label>
                <button class="owner-submit owner-submit-dark">ساخت حساب مدرس</button>
            </form>
        </section>

        <section class="dashboard-panel owner-admin-card">
            <div class="owner-card-head">
                <div>
                    <span class="owner-eyebrow">اختصاص دوره</span>
                    <h2>انتساب مدرس به دوره</h2>
                    <p>مدرس بعد از اتصال می‌تواند فضای آموزشی همان دوره را مدیریت کند.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('owner.people.assign-teacher', $academy) }}" class="owner-form-grid">
                @csrf
                <label><span>مدرس</span><select name="teacher_id" required>@forelse($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@empty<option value="">مدرسی وجود ندارد</option>@endforelse</select></label>
                <label><span>دوره</span><select name="course_id" required>@forelse($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@empty<option value="">دوره‌ای وجود ندارد</option>@endforelse</select></label>
                <label class="owner-check"><input type="checkbox" name="is_primary" value="1"><span>مدرس اصلی دوره باشد</span></label>
                <button class="owner-submit">اختصاص مدرس</button>
            </form>
        </section>
    </div>

    <section class="dashboard-panel owner-admin-card">
        <div class="owner-card-head">
            <div>
                <span class="owner-eyebrow">ثبت‌نام</span>
                <h2>ثبت‌نام دانش‌آموز در دوره</h2>
                <p>در صورت نیاز، دانش‌آموز را هم‌زمان به یک کلاس فعال وصل کن. ظرفیت کلاس قبل از ثبت‌نام کنترل می‌شود.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('owner.people.enroll-student', $academy) }}" class="owner-enroll-grid" data-owner-enrollment-form>
            @csrf
            <label><span>دانش‌آموز</span><select name="student_id" required>@forelse($students as $student)<option value="{{ $student->id }}">{{ $student->name }} · {{ $student->email }}</option>@empty<option value="">دانش‌آموزی وجود ندارد</option>@endforelse</select></label>
            <label>
                <span>دوره</span>
                <select name="course_id" required data-owner-enrollment-course>
                    @forelse($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @empty
                        <option value="">دوره‌ای وجود ندارد</option>
                    @endforelse
                </select>
            </label>
            <label>
                <span>کلاس</span>
                <select name="classroom_id" data-owner-enrollment-classroom>
                    <option value="">بدون کلاس</option>
                </select>
            </label>
            <label><span>مبلغ پرداختی</span><input name="paid_amount" type="number" min="0" step="1" placeholder="برای دوره رایگان خالی بگذار" dir="ltr"></label>
            <button class="owner-submit owner-submit-wide">ثبت‌نام / بروزرسانی ثبت‌نام</button>
        </form>

        @php
            $ownerClassrooms = $courses->flatMap(function ($course) {
                return $course->classrooms->map(function ($classroom) use ($course) {
                    return [
                        'id' => $classroom->id,
                        'course_id' => $course->id,
                        'title' => $classroom->title,
                        'status' => $classroom->status,
                        'capacity' => $classroom->capacity,
                    ];
                });
            })->values()->all();
        @endphp

        <script type="application/json" data-owner-classrooms>{!! json_encode($ownerClassrooms, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    </section>

    <section class="dashboard-panel owner-admin-card">
        <div class="owner-card-head">
            <div>
                <span class="owner-eyebrow">انتساب‌های فعلی</span>
                <h2>مدرس‌ها و دوره‌هایشان</h2>
                <p>اتصال‌ها را از همین‌جا بررسی و در صورت نیاز حذف کن.</p>
            </div>
        </div>

        <div class="owner-assignment-list">
            @forelse($assignments as $course)
                <article class="owner-assignment-card">
                    <div class="owner-assignment-title">
                        <strong>{{ $course->title }}</strong>
                        <span>{{ $course->active_students_count }} دانش‌آموز فعال</span>
                    </div>
                    <div class="owner-assignment-teachers">
                        @forelse($course->teachers as $teacher)
                            <div class="owner-assignment-teacher">
                                <span class="owner-avatar">{{ mb_substr($teacher->name, 0, 1) }}</span>
                                <div>
                                    <strong>{{ $teacher->name }}</strong>
                                    <small>{{ $teacher->pivot->is_primary ? 'مدرس اصلی' : 'مدرس دوره' }}</small>
                                </div>
                                <form method="POST" action="{{ route('owner.people.detach-teacher', [$academy, $teacher->id, $course->id]) }}" data-confirm="مدرس از این دوره حذف شود؟">
                                    @csrf
                                    @method('DELETE')
                                    <button class="owner-remove-btn" type="submit">حذف اتصال</button>
                                </form>
                            </div>
                        @empty
                            <div class="owner-empty">هنوز مدرس به این دوره متصل نیست.</div>
                        @endforelse
                    </div>
                </article>
            @empty
                <div class="owner-empty">دوره‌ای برای نمایش وجود ندارد.</div>
            @endforelse
        </div>
    </section>

    <div class="owner-people-columns">
        @foreach([['مدرس‌ها',$teachers,'owner-person-teacher'],['دانش‌آموزان',$students,'owner-person-student'],['والدین',$parents,'owner-person-parent']] as [$title,$items,$class])
            <section class="dashboard-panel owner-person-card {{ $class }}">
                <div class="owner-card-head">
                    <div><span class="owner-eyebrow">اعضا</span><h2>{{ $title }}</h2></div>
                    <span class="owner-count-badge">{{ $items->count() }}</span>
                </div>
                <div class="owner-person-list">
                    @forelse($items as $person)
                        <article>
                            <span class="owner-avatar">{{ mb_substr($person->name, 0, 1) }}</span>
                            <div><strong>{{ $person->name }}</strong><small>{{ $person->email }}</small></div>
                        </article>
                    @empty
                        <div class="owner-empty">عضوی نیست.</div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection
