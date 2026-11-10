@php
$segments = $segments ?? [];
$labels = $labels ?? [];
$seriesClass = $seriesClass ?? 'owner-line-attendance';
$ariaLabel = $ariaLabel ?? 'نمودار روند';
$chartClass = $chartClass ?? '';
$showVerticalGrid = $showVerticalGrid ?? false;
@endphp
<svg class="ln owner-line-chart {{ $chartClass }}" viewBox="0 0 520 150" role="img" aria-label="{{ $ariaLabel }}">
<g class="owner-chart-grid">
@for($grid=0;$grid<6;$grid++)<line x1="0" y1="{{ 8+($grid*26) }}" x2="520" y2="{{ 8+($grid*26) }}"/>@endfor
@if($showVerticalGrid)@for($grid=0;$grid<13;$grid++)<line x1="{{ $grid*(520/12) }}" y1="8" x2="{{ $grid*(520/12) }}" y2="138"/>@endfor@endif
</g>
@foreach($segments as $points)<polyline class="{{ $seriesClass }}" points="{{ $points }}"/>@endforeach
</svg>
<div class="owner-chart-labels">@foreach([0,3,6,9,12] as $labelIndex)<span>{{ $labels[$labelIndex] ?? '' }}</span>@endforeach</div>
