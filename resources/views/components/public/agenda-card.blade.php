@props(['item', 'past' => false, 'compact' => false])
<article @class(['public-agenda-card', 'public-agenda-card--past' => $past])>
    <div class="public-date-block" aria-hidden="true"><strong>{{ $item->start_at->format('d') }}</strong><span>{{ $item->start_at->translatedFormat('M Y') }}</span></div>
    <div class="flex-grow-1">
        <p class="public-metadata mb-2"><x-admin.date-text :date="$item->start_at" time /> WIB @if ($item->end_at && !$compact) — <x-admin.date-text :date="$item->end_at" time /> WIB @endif</p>
        <h3 class="h5 public-card-title"><a href="{{ route('agendas.show', $item->slug) }}">{{ $item->title }}</a></h3>
        @if ($item->location)<p class="public-agenda-location mb-2">{{ $item->location }}</p>@endif
        @if (!$compact && $item->description)<p class="text-muted mb-0">{{ Str::limit($item->description, 180) }}</p>@endif
    </div>
</article>
