<div class="tp-hero">
    <div class="tp-hero__cover" data-tp-preview-bg="cover" @if($tpCoverUrl) style="background-image:url('{{ $tpCoverUrl }}')" @endif></div>
    <div class="tp-hero__body">
        <div class="tp-hero__photo" data-tp-preview-bg="avatar" @if($tpAvatarUrl) style="background-image:url('{{ $tpAvatarUrl }}')" @endif>
            <span data-tp-photo-initials @if($tpAvatarUrl) hidden @endif>{{ $tpInitial }}</span>
        </div>
        <div>
            <b class="tp-hero__name">{{ $tpUser?->name ?? 'مدرس' }}</b>
            <small>{{ $tpProfile?->specialization ?: 'مدرس' }} · {{ $metrics['activeClasses'] ?? 0 }} کلاس · {{ $metrics['studentCount'] ?? 0 }} دانش‌آموز</small>
        </div>
    </div>
</div>