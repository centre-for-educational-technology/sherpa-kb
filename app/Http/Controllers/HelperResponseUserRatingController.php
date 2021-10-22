<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HelperResponseUserRatingController extends Controller
{
    /**
     * Inserts Helper user response rating question-answer-rating data into the database.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'question' => 'required',
            'answer' => 'required',
            'languageCode' => 'required|exists:App\Language,code',
            'rating' => 'required|integer|digits_between:1,3',
        ]);

        DB::table('helper_response_user_ratings')->insert([
            'question' => $validatedData['question'],
            'answer' => $validatedData['answer'],
            'language_code' => $validatedData['languageCode'],
            'rating' => $validatedData['rating'],
            'ip' => $request->ip(),
            'created_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'OK',
        ]);
    }
}
