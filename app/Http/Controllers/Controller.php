<?php

declare(strict_types=1);

namespace App\Http\Controllers;

abstract class Controller
{
    protected function buildPropertyAddress(array $property): string
    {
        $parts = array_filter([
            $property['country']  ?? null,
            $property['city']     ?? null,
            $property['district'] ?? null,
            $property['address']  ?? null,
        ]);
        return implode(', ', $parts);
    }

    protected function extractPrimaryImageUrl(array $property): ?string
    {
        $media = $property['media'] ?? [];
        foreach ($media as $m) {
            if (!empty($m['isPrimary'])) return $m['url'] ?? null;
        }
        return $media[0]['url'] ?? null;
    }
}
