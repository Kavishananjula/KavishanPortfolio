<?php

declare(strict_types=1);

/**
 * Slugify helper to ensure safe keys.
 */
function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';

    return trim($value ?? '', '-');
}

/**
 * Normalises stack string to array.
 *
 * @param string|null $stack
 *
 * @return array<int, string>
 */
function parseStack(?string $stack): array
{
    if (!$stack) {
        return [];
    }

    $items = array_map('trim', explode(',', $stack));
    $items = array_filter($items, static fn ($item) => $item !== '');

    return array_values($items);
}
