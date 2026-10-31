@props(['value' => 0])
<div class="tp-progress" role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100"><i style="width: {{ $value }}%"></i></div>