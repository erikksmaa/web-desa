<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class PublicationDate
{
    public static function resolve(array $data, ?CarbonInterface $previous = null): ?CarbonInterface
    {
        if (! empty($data['published_at'])) {
            return Carbon::parse($data['published_at']);
        }

        return $previous ?? ($data['status'] === 'published' ? now() : null);
    }
}
