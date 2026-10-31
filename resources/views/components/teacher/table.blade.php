{{-- Usage: <x-teacher.table :heads="['نام','کلاس']"> <tr>…</tr> </x-teacher.table> --}}
@props(['heads' => []])
<div class="tp-tw">
    <table {{ $attributes->class(['tp-table']) }}>
        @if ($heads)<thead><tr>@foreach ($heads as $h)<th>{{ $h }}</th>@endforeach</tr></thead>@endif
        <tbody>{{ $slot }}</tbody>
    </table>
</div>