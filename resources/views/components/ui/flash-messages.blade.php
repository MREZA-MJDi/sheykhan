@php
    $alerts = [
        'success' => ['title' => 'انجام شد', 'icon' => '✓'],
        'error' => ['title' => 'نیاز به توجه', 'icon' => '!'],
        'warning' => ['title' => 'توجه', 'icon' => '⚠'],
        'info' => ['title' => 'اطلاع', 'icon' => 'i'],
    ];
@endphp

@if(session()->hasAny(array_keys($alerts)) || $errors->any())
    <div class="ui-flash-stack" data-ui-flash-stack>
        @foreach($alerts as $type => $meta)
            @if(session($type))
                <div class="ui-flash ui-flash-{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}" aria-live="{{ $type === 'error' ? 'assertive' : 'polite' }}">
                    <span class="ui-flash-icon" aria-hidden="true">{{ $meta['icon'] }}</span>
                    <div class="ui-flash-copy"><strong>{{ $meta['title'] }}</strong><span>{{ session($type) }}</span></div>
                    <button type="button" class="ui-flash-close" data-ui-flash-close aria-label="بستن پیام">×</button>
                </div>
            @endif
        @endforeach
        @if($errors->any())
            <div class="ui-flash ui-flash-error" role="alert" aria-live="assertive">
                <span class="ui-flash-icon" aria-hidden="true">!</span>
                <div class="ui-flash-copy"><strong>اطلاعات بررسی نشد</strong><span>{{ $errors->first() }}</span></div>
                <button type="button" class="ui-flash-close" data-ui-flash-close aria-label="بستن پیام">×</button>
            </div>
        @endif
    </div>
@endif

<style>
.ui-flash-stack{display:grid;gap:10px;margin:0 0 20px}
.ui-flash{display:flex;align-items:center;gap:12px;padding:13px 15px;border:1px solid;border-radius:16px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.07)}
.ui-flash-icon{width:30px;height:30px;flex:0 0 30px;display:grid;place-items:center;border-radius:10px;font-weight:900}
.ui-flash-copy{min-width:0;display:grid;gap:3px;line-height:1.8}
.ui-flash-copy strong{font-size:12px}.ui-flash-copy span{font-size:12px;color:#667085}
.ui-flash-close{margin-inline-start:auto;border:0;background:transparent;color:#98a2b3;font-size:22px;line-height:1;cursor:pointer;padding:4px 8px}
.ui-flash-success{border-color:#b7e4c7;background:#f4fff7}.ui-flash-success .ui-flash-icon{background:#dcfce7;color:#15803d}
.ui-flash-error{border-color:#fecaca;background:#fff7f7}.ui-flash-error .ui-flash-icon{background:#fee2e2;color:#b91c1c}
.ui-flash-warning{border-color:#fde68a;background:#fffbeb}.ui-flash-warning .ui-flash-icon{background:#fef3c7;color:#a16207}
.ui-flash-info{border-color:#bfdbfe;background:#eff6ff}.ui-flash-info .ui-flash-icon{background:#dbeafe;color:#1d4ed8}
@media(max-width:640px){.ui-flash{align-items:flex-start;padding:12px}.ui-flash-copy span{font-size:11px}.ui-flash-close{font-size:20px}}
</style>
<script>
document.addEventListener('click',function(e){const b=e.target.closest('[data-ui-flash-close]');if(!b)return;const box=b.closest('.ui-flash');if(box){box.style.opacity='0';box.style.transform='translateY(-4px)';box.style.transition='opacity .18s ease,transform .18s ease';setTimeout(()=>box.remove(),180)}});
</script>
