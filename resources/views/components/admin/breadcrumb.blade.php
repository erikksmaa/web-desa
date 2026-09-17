@props(['items'])

<nav aria-label="Breadcrumb">
    <ol class="breadcrumb small mb-3">
        @foreach ($items as $item)
            <li @class(['breadcrumb-item', 'active' => empty($item['url'])]) @if (empty($item['url'])) aria-current="page" @endif>
                @if (! empty($item['url']))
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    {{ $item['label'] }}
                @endif
            </li>
        @endforeach
    </ol>
</nav>
