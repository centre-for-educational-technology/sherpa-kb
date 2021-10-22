<?php

namespace App\Http\Controllers;

use App\Language;

class LanguageController extends Controller
{
    /**
     * Responds languages for API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function api()
    {
        $languages = Language::all()->map(function ($language) {
            return [
                'code' => $language->code,
                'name' => $language->name,
            ];
        });

        return response()->json($languages);
    }
}
