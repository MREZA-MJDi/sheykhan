@props(['title'=>'','href'=>null,'link'=>'مشاهده همه'])
<div class="teacher-section-heading"><b>{{ $title }}</b>@if($href)<a href="{{ $href }}">{{ $link }} ←</a>@endif</div>