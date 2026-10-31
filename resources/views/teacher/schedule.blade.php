@extends('layouts.teacher')
@section('title', 'برنامه هفتگی')
@section('heading', 'برنامه هفتگی')
@section('content')
    <x-teacher.section-head title="برنامه کلاس‌های من" subtitle="زمان‌های هفتگی هر کلاس از برنامه ثبت‌شده در شیخان خوانده می‌شود." />
    <div class="tp-grid tp-grid--main tp-grid--start">
        <x-teacher.card title="زمان‌بندی ثبت‌شده">
            @php($dayNames = [0 => 'یکشنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنجشنبه', 5 => 'جمعه', 6 => 'شنبه'])
            @php($hasSchedule = false)
            @foreach ($classrooms as $classroom)
                @if ($classroom->schedules->isNotEmpty())
                    @php($hasSchedule = true)
                    <section class="tp-day-group">
                        <h3>{{ $classroom->title }} <small class="tp-muted">· {{ $classroom->course?->title }}</small></h3>
                        @foreach ($classroom->schedules->sortBy(fn ($schedule) => str_pad((string) $schedule->weekday, 2, '0', STR_PAD_LEFT) . '-' . substr((string) $schedule->start_time, 0, 5)) as $schedule)
                            <div class="tp-schedule-item">
                                <span class="tp-schedule-time">{{ $dayNames[$schedule->weekday] ?? '—' }}</span>
                                <div class="tp-schedule-copy"><strong>{{ substr((string) $schedule->start_time, 0, 5) }} تا {{ substr((string) $schedule->end_time, 0, 5) }}</strong><small>{{ $schedule->room ?: 'بدون محل مشخص' }} · {{ $schedule->provider }}</small></div>
                                @if ($schedule->meeting_url && app(\App\Services\MeetingLinkService::class)->urlMatchesProvider((string) $schedule->provider, (string) $schedule->meeting_url))<a class="tp-link" href="{{ $schedule->meeting_url }}" target="_blank" rel="noopener noreferrer">لینک جلسه</a>@endif
                                @if($tpUser->hasPermission('live_classes.manage'))<form method="POST" action="{{ route('teacher.schedule.destroy', $schedule) }}" onsubmit="return confirm('این زمان هفتگی حذف شود؟')">@csrf @method('DELETE')<button class="tp-btn tp-btn--light tp-btn--sm" type="submit">حذف</button></form>@endif
                            </div>
                        @endforeach
                    </section>
                @endif
            @endforeach
            @if (!$hasSchedule)<div class="tp-empty"><strong>برنامه هفتگی ثبت نشده است.</strong><span>از فرم کنار صفحه برای افزودن زمان کلاس استفاده کن.</span></div>@endif
        </x-teacher.card>

        @if($tpUser->hasPermission('live_classes.manage'))<x-teacher.card title="افزودن زمان هفتگی">
            @if ($classrooms->isEmpty())
                <div class="tp-empty"><strong>ابتدا یک کلاس فعال ایجاد یا به خودت تخصیص بده.</strong><a class="tp-link" href="{{ route('teacher.classrooms.create') }}">ایجاد کلاس</a></div>
            @else
                <form method="POST" action="{{ route('teacher.schedule.store') }}" class="tp-stack-form" data-meeting-provider-form>
                    @csrf
                    <x-teacher.field label="کلاس"><select name="classroom_id" required>@foreach ($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->title }} · {{ $classroom->course?->title }}</option>@endforeach</select></x-teacher.field>
                    <x-teacher.field label="روز هفته"><select name="weekday" required>@foreach ($dayNames as $value => $label)<option value="{{ $value }}" @selected((string) old('weekday') === (string) $value)>{{ $label }}</option>@endforeach</select></x-teacher.field>
                    <div class="tp-form-grid">
                        <x-teacher.field label="شروع"><input type="time" name="start_time" value="{{ old('start_time') }}" required></x-teacher.field>
                        <x-teacher.field label="پایان"><input type="time" name="end_time" value="{{ old('end_time') }}" required></x-teacher.field>
                    </div>
                    <x-teacher.field label="محل / اتاق (اختیاری)"><input name="room" maxlength="255" value="{{ old('room') }}"></x-teacher.field>
                    <x-teacher.field label="سرویس جلسه"><select name="provider" data-meeting-provider required><option value="Jitsi" @selected(old('provider', 'Jitsi') === 'Jitsi')>Jitsi شیخان</option><option value="Google Meet" @selected(old('provider') === 'Google Meet')>Google Meet</option><option value="Zoom" @selected(old('provider') === 'Zoom')>Zoom</option><option value="سایر" @selected(old('provider') === 'سایر')>سایر</option></select></x-teacher.field>
                    <x-teacher.field label="لینک جلسه (برای سرویس خارجی الزامی)"><input type="url" name="meeting_url" data-meeting-url value="{{ old('meeting_url') }}" dir="ltr" placeholder="https://"><small data-meeting-provider-help class="tp-muted" role="status" aria-live="polite">برای Jitsi لینک خودکار ساخته می‌شود؛ سرویس‌های دیگر نیازمند لینک واقعی هستند.</small></x-teacher.field>
                    <x-teacher.button type="submit">ثبت برنامه</x-teacher.button>
                </form>
            @endif
        </x-teacher.card>@endif
    </div>
@endsection