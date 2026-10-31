@props(['title', 'subtitle' => null])
<div class="tp-sechead"><b>{{ $title }}</b>@if ($subtitle)<small>{{ $subtitle }}</small>@endif</div>