@php($tableLabel = isset($caption) ? trim(strip_tags((string) $caption)) : 'Tabel data')
<div tabindex="0" role="region" aria-label="{{ $tableLabel }} — dapat digulir mendatar" {{ $attributes->class(['table-responsive', 'admin-table-wrap']) }}>
    <table class="table admin-table align-middle mb-0">
        @isset($caption)<caption>{{ $caption }}</caption>@endisset
        @isset($head)<thead>{{ $head }}</thead>@endisset
        <tbody>{{ $slot }}</tbody>
    </table>
</div>
