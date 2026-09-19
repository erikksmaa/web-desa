<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class UniqueSlug
{
    public static function generate(Builder $query, string $value, ?int $ignoreId = null): string
    {
        $base = substr(Str::slug($value) ?: 'item', 0, 175);
        $slug = $base;
        $suffix = 2;

        while ((clone $query)
            ->when($ignoreId, fn (Builder $builder): Builder => $builder->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
