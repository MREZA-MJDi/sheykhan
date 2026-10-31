@props(['name', 'title', 'desc' => null, 'on' => false])
<div class="tp-switch">
    <div><b>{{ $title }}</b>@if ($desc)<small>{{ $desc }}</small>@endif</div>
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked($on)>
</div>