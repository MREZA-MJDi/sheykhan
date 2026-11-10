@php
    $schedule = $schedule ?? null;
    $method = strtoupper($method ?? 'POST');
    $formModal = (string) ($formModal ?? 'schedule');
    $action = (string) $action;
    $buttonLabel = $buttonLabel ?? ($method === 'PATCH' ? 'ذخیره تغییرات' : 'ذخیره زمان و لینک');
@endphp
<form method="POST" action="{{ $action }}" class="grid gap-3 sm:grid-cols-2" data-meeting-provider-form>
    @csrf
    @if($method === 'PATCH')
        @method('PATCH')
    @endif
    <input type="hidden" name="form_modal" value="{{ $formModal }}">
    <label class="grid gap-1">
        <span class="text-xs font-bold">روز هفته</span>
        <select name="weekday" required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
            @foreach([6 => 'شنبه', 0 => 'یکشنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنجشنبه', 5 => 'جمعه'] as $value => $label)
                <option value="{{ $value }}" @selected((string) old('weekday', $schedule?->weekday ?? 6) === (string) $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('weekday')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
    </label>
    <label class="grid gap-1">
        <span class="text-xs font-bold">شروع · ساعت ایران</span>
        <input lang="fa-IR" dir="ltr" type="time" name="start_time" value="{{ old('start_time', $schedule?->start_time ? \Illuminate\Support\Carbon::parse($schedule->start_time)->format('H:i') : '') }}" required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
        @error('start_time')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
    </label>
    <label class="grid gap-1">
        <span class="text-xs font-bold">پایان · ساعت ایران</span>
        <input lang="fa-IR" dir="ltr" type="time" name="end_time" value="{{ old('end_time', $schedule?->end_time ? \Illuminate\Support\Carbon::parse($schedule->end_time)->format('H:i') : '') }}" required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
        @error('end_time')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
    </label>
    <label class="grid gap-1">
        <span class="text-xs font-bold">اتاق / عنوان <small class="font-normal text-slate-400">اختیاری</small></span>
        <input name="room" value="{{ old('room', $schedule?->room) }}" maxlength="255" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm" placeholder="مثلاً کلاس ۳">
        @error('room')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
    </label>
    <label class="grid gap-1">
        <span class="text-xs font-bold">سرویس</span>
        <select name="provider" data-meeting-provider required class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
            <option value="Jitsi" @selected(old('provider', $schedule?->provider ?? 'Jitsi') === 'Jitsi')>Jitsi · خودکار</option>
            <option value="Google Meet" @selected(old('provider', $schedule?->provider) === 'Google Meet')>Google Meet</option>
            <option value="Zoom" @selected(old('provider', $schedule?->provider) === 'Zoom')>Zoom</option>
            <option value="سایر" @selected(old('provider', $schedule?->provider) === 'سایر')>سایر سرویس‌ها</option>
        </select>
        @error('provider')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
    </label>
    <label class="grid gap-1 sm:col-span-2">
        <span class="text-xs font-bold">لینک ورود <small class="font-normal text-slate-400">(برای Jitsi اختیاری، برای سرویس‌های دیگر الزامی)</small></span>
        <input type="url" name="meeting_url" value="{{ old('meeting_url', $schedule?->meeting_url) }}" maxlength="2000" autocomplete="url" dir="ltr" class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm" placeholder="https://...">
        @error('meeting_url')<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>@enderror
    </label>
    @if($errors->any() && old('form_modal') === $formModal)
        <div class="rounded-xl bg-rose-50 p-3 text-xs leading-7 text-rose-700 sm:col-span-2" role="alert">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif
    <div class="flex items-end sm:col-span-2">
        <button type="submit" class="owner-primary-action w-full rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">{{ $buttonLabel }}</button>
    </div>
</form>
