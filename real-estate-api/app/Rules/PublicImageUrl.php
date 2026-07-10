<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;
use Throwable;

class PublicImageUrl implements ValidationRule
{
    /**
     * Reject URLs that reachably resolve to a non-image (e.g. an HTML page).
     *
     * Fails soft: unreachable hosts, timeouts, and servers that refuse HEAD
     * requests pass validation — the frontend renders a placeholder when an
     * image cannot be displayed, so only a confirmed non-image is rejected.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $response = Http::timeout(3)->connectTimeout(3)->head((string) $value);
        } catch (Throwable) {
            return;
        }

        if (! $response->successful()) {
            return;
        }

        $contentType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));

        if ($contentType !== '' && ! str_starts_with($contentType, 'image/')) {
            $fail('The image URL does not point to an image file. Paste a direct image link (usually ending in .jpg, .png, or .webp), not a web page.');
        }
    }
}
