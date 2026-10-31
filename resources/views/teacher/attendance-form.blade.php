@extends('layouts.teacher')
@section('title', 'ثبت حضور و غیاب')
@section('heading', 'حضور و غیاب · ' . $classroom->title)
@section('content')
    <div class="tp-toolbar">
        <div><h2>ثبت حضور روزانه</h2><p class="tp-muted">{{ $classroom->course?->title }} · وضعیت اعضای فعال این کلاس</p></div>
        <a class="tp-btn tp-btn--light" href="{{ route('teacher.attendance.index') }}">بازگشت به کلاس‌ها</a>
    </div>
    <form method="POST" action="{{ route('teacher.classrooms.attendance.store', $classroom) }}" data-tp-attendance class="tp-stack-form">
        @csrf
        <x-teacher.card title="تاریخ و اطلاعات جلسه">
            <div class="tp-form-grid">
                <x-teacher.field label="تاریخ حضور و غیاب"><input type="date" name="attendance_date" value="{{ old('attendance_date', $attendanceDate) }}" required @disabled(!$tpUser->hasPermission('attendance.manage'))></x-teacher.field>
                <x-teacher.field label="مدت برنامه‌ریزی‌شده"><input value="{{ $scheduledMinutes ? \App\Support\PersianUi::digits($scheduledMinutes) . ' دقیقه' : 'برای این روز برنامه‌ای ثبت نشده' }}" disabled></x-teacher.field>
            </div>
            @error('attendance_date')<p class="tp-error">{{ $message }}</p>@enderror
        </x-teacher.card>

        <x-teacher.card title="وضعیت دانش‌آموزان">
            <div class="tp-toolbar"><span class="tp-muted">{{ \App\Support\PersianUi::digits($classroom->students->count()) }} دانش‌آموز فعال</span>@if($tpUser->hasPermission('attendance.manage'))<button type="button" class="tp-btn tp-btn--light tp-btn--sm" data-tp-all-present>همه حاضر</button>@endif</div>
            <div class="tp-roster">
                @forelse($classroom->students as $student)
                    @php
                        $studentId = (int) $student->id;
                        $saved = $existingAttendance->get($studentId);
                        $status = old("attendance.{$studentId}", $saved?->status ?? 'present');
                        $minutes = old("attendance_minutes.{$studentId}", $saved?->attended_minutes);
                    @endphp
                    <article class="tp-stu" data-tp-stu data-status="{{ $status }}">
                        <x-teacher.avatar :name="$student->name" :size="42" />
                        <strong>{{ $student->name }}</strong>
                        <input type="hidden" name="attendance[{{ $studentId }}]" value="{{ $status }}">
                        <div class="tp-seg" role="group" aria-label="وضعیت حضور {{ $student->name }}">
                            @foreach(['present' => 'حاضر', 'absent' => 'غایب', 'late' => 'تأخیر', 'excused' => 'موجه'] as $key => $label)
                                <button type="button" data-tp-set="{{ $key }}" class="{{ $status === $key ? 'is-on' : '' }}" aria-pressed="{{ $status === $key ? 'true' : 'false' }}" @disabled(!$tpUser->hasPermission('attendance.manage'))>{{ $label }}</button>
                            @endforeach
                        </div>
                        @if($scheduledMinutes > 0)
                            <label class="tp-field" style="margin-top:9px;text-align:right">
                                دقیقه حضور واقعی
                                <input type="number" min="0" max="{{ $scheduledMinutes }}" name="attendance_minutes[{{ $studentId }}]" value="{{ $minutes }}" inputmode="numeric" placeholder="{{ $status === 'late' ? 'برای تأخیر الزامی' : 'اختیاری' }}" @disabled(!$tpUser->hasPermission('attendance.manage'))>
                            </label>
                        @endif
                    </article>
                @empty
                    <div class="tp-empty"><strong>دانش‌آموز فعالی در این کلاس وجود ندارد.</strong></div>
                @endforelse
            </div>
            @error('attendance')<p class="tp-error">{{ $message }}</p>@enderror
            @foreach($errors->getMessages() as $key => $messages)
                @if(str_starts_with($key, 'attendance_minutes.'))<p class="tp-error">{{ $messages[0] }}</p>@endif
            @endforeach
            @if($tpUser->hasPermission('attendance.manage'))<div class="tp-form-actions"><button type="submit" class="tp-btn">ثبت حضور و غیاب</button></div>@endif
        </x-teacher.card>
    </form>
@endsection