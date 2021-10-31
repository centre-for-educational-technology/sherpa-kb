<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HelperActivityController extends Controller
{
    /**
     * Inserts Helper activity question-answer data into the database.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'question' => 'required',
            'answer' => 'nullable',
            'languageCode' => 'required|exists:App\Language,code',
        ]);

        DB::table('helper_activity_log')->insert([
            'question' => $validatedData['question'],
            'answer' => $validatedData['answer'] ?? '',
            'language_code' => $validatedData['languageCode'],
            'ip' => $request->ip(),
            'created_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'OK',
        ]);
    }
}
