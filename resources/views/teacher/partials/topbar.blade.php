@php($teacherTopbarUser = auth()->user())
<div class="tb">
    <label class="sr">
        <svg class="i" aria-hidden="true"><use href="#se"/></svg>
        <input id="gs" type="search" placeholder="جست‌وجو در این صفحه…" aria-label="جست‌وجو در صفحه مدرس" autocomplete="off">
    </label>
    <div class="sp">
        @if($teacherTopbarUser?->hasPermission('profile.view'))
            <a class="me" href="{{ route('teacher.profile.edit') }}" aria-label="رفتن به پروفایل مدرس">
                <span class="av">
                    @if($teacherPanelAvatarUrl)
                        <img src="{{ $teacherPanelAvatarUrl }}" alt="" width="36" height="36" loading="eager" decoding="async">
                    @else
                        {{ \Illuminate\Support\Str::substr(trim((string) $teacherTopbarUser->name), 0, 1) ?: 'م' }}
                    @endif
                </span>
                <span>{{ $teacherTopbarUser->name }}</span>
            </a>
        @else
            <span class="me">
                <span class="av">
                    @if($teacherPanelAvatarUrl)
                        <img src="{{ $teacherPanelAvatarUrl }}" alt="" width="36" height="36" loading="eager" decoding="async">
                    @else
                        {{ \Illuminate\Support\Str::substr(trim((string) ($teacherTopbarUser?->name ?? 'م')), 0, 1) ?: 'م' }}
                    @endif
                </span>
                <span>{{ $teacherTopbarUser?->name }}</span>
            </span>
        @endif
        <button class="ib" id="th" type="button" aria-label="تغییر تم" title="تغییر تم" aria-pressed="false">
            <svg class="i" aria-hidden="true"><use href="#mo"/></svg>
        </button>
        @if($teacherTopbarUser?->hasPermission('parent_messages.view'))
            <a class="ib" href="{{ route('teacher.messages.index') }}" title="پیام‌های اولیا" aria-label="رفتن به پیام‌های اولیا">
                <svg class="i" aria-hidden="true"><use href="#mail"/></svg>
            </a>
        @endif
    </div>
</div>