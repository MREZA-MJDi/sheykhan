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
                <div data-ui-dismissible class="ui-flash ui-flash-{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}" aria-live="{{ $type === 'error' ? 'assertive' : 'polite' }}">
                    <span class="ui-flash-icon" aria-hidden="true">{{ $meta['icon'] }}</span>
                    <div class="ui-flash-copy"><strong>{{ $meta['title'] }}</strong><span>{{ session($type) }}</span></div>
                    <button type="button" class="ui-flash-close" data-ui-dismiss aria-label="بستن پیام">×</button>
                </div>
            @endif
        @endforeach
        @if($errors->any())
            <div data-ui-dismissible class="ui-flash ui-flash-error" role="alert" aria-live="assertive">
                <span class="ui-flash-icon" aria-hidden="true">!</span>
                <div class="ui-flash-copy"><strong>اطلاعات بررسی نشد</strong><span>{{ $errors->first() }}</span></div>
                <button type="button" class="ui-flash-close" data-ui-flash-close aria-label="بستن پیام">×</button>
            </div>
        @endif
    </div>
@endif

