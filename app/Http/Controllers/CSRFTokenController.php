<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CSRFTokenController extends Controller
{
    /**
     * Refreshes CSRF token and returns a new one.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([], 403);
        }

        $request->session()->regenerateToken();

        return response()->json([
            'csrfToken' => $request->session()->token(),
        ]);
    }
}
