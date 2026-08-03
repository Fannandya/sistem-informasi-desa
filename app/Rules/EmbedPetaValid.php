<?php

namespace App\Rules;

use App\Support\EmbedPeta;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmbedPetaValid implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (! EmbedPeta::sah(is_string($value) ? $value : null)) {
            $fail(
                'Kode peta tidak dikenali. Salin kode embed dari Google Maps '
                .'(menu Bagikan → Sematkan peta), lalu tempelkan di sini.',
            );
        }
    }
}
