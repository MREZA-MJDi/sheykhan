@props(['name' => 'files[]', 'label' => 'فایل را اینجا رها کنید یا کلیک کنید', 'multiple' => true])
<div>
    <label class="tp-drop">
        <input type="file" name="{{ $name }}" @if($multiple) multiple @endif data-tp-files>
        <b>⬆ {{ $label }}</b>
        <small>PDF، Word، تصویر · حداکثر ۲۰ مگابایت</small>
    </label>
    <div class="tp-chips" data-tp-files-list></div>
</div>