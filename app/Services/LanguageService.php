<?php

namespace App\Services;

use App\Language;

class LanguageService
{
    /**
     * Get language object by its code.
     *
     * @param  string  $code
     * @return Language|null
     */
    public function getLanguageByCode(string $code): ?Language
    {
        return Language::where('code', $code)->first();
    }

    /**
     * Get language identifier by its code.
     *
     * @param  string  $code
     * @return int|null
     */
    public function getLanguageIdByCode(string $code): ?int
    {
        $language = $this->getLanguageByCode($code);

        return $language ? $language->id : null;
    }
}
