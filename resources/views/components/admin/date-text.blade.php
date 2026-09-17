<time @if ($date) datetime="{{ $time ? $date->toIso8601String() : $date->toDateString() }}" @endif {{ $attributes }}>
    {{ $display() }}
</time>
