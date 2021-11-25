<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CSRFTokenController extends Controller
{
    /**
     * Create new controller instance.
     * Enforce throttle middleware to only allow up to ten requests per minute.
     */
    public function __construct()
    {
        $this->middleware('throttle:10,1');
    }

    /**
     * Refreshes CSRF token and returns a new one.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->session()->regenerateToken();

        return response()->json([
            'csrfToken' => $request->session()->token(),
        ]);
    }
}
