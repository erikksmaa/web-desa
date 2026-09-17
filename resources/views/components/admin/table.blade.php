<div {{ $attributes->class(['table-responsive', 'admin-table-wrap']) }}>
    <table class="table admin-table align-middle mb-0">
        @isset($caption)<caption>{{ $caption }}</caption>@endisset
        @isset($head)<thead>{{ $head }}</thead>@endisset
        <tbody>{{ $slot }}</tbody>
    </table>
</div>
