@props(['icon'])
<span class="role-mobile-bottom-icon" aria-hidden="true">
    @switch($icon)
        @case('⌂')
            <svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>
            @break
        @case('▣')
        @case('▤')
            <svg viewBox="0 0 24 24"><path d="M5 3h10l4 4v14H5zM15 3v5h4M8 12h8M8 16h8"/></svg>
            @break
        @case('✓')
            <svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>
            @break
        @case('◷')
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            @break
        @case('↗')
            <svg viewBox="0 0 24 24"><path d="M4 19V5M4 19h16M8 15v-4M13 15V7M18 15v-7M14 4h6v6"/></svg>
            @break
        @case('✉')
            <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
            @break
        @case('▦')
        @case('◎')
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            @break
        @case('◉')
            <svg viewBox="0 0 24 24"><rect x="3" y="6" width="13" height="12" rx="2"/><path d="m16 10 5-3v10l-5-3z"/></svg>
            @break
        @case('⌕')
            <svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
            @break
        @case('⚙')
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.8 1.8 0 0 0-3 .9v.2a1.7 1.7 0 0 1-3.4 0v-.2a1.8 1.8 0 0 0-3-.9l-.1.1a1.7 1.7 0 0 1-2.4-2.4l.1-.1a1.8 1.8 0 0 0-.9-3H4a1.7 1.7 0 0 1 0-3.4h.2a1.8 1.8 0 0 0 .9-3l-.1-.1a1.7 1.7 0 0 1 2.4-2.4l.1.1a1.8 1.8 0 0 0 3-.9V2.1a1.7 1.7 0 0 1 3.4 0v.2a1.8 1.8 0 0 0 3 .9l.1-.1a1.7 1.7 0 0 1 2.4 2.4l-.1.1a1.8 1.8 0 0 0 .9 3h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.8 1.8 0 0 0-.8 3z"/></svg>
            @break
        @default
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg>
    @endswitch
</span>
