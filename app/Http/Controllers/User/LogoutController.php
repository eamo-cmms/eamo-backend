<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogoutController extends Controller
{
    /**
     * Revoke the user's current OAuth access token.
     */
    public function __invoke(Request $request): Response
    {
        $token = $request->user()->token();

        if ($token) {
            $token->revoke();
        }

        return response()->noContent();
    }
}
