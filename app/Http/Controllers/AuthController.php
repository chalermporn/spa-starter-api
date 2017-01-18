<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Make authenticated tokens based on users credentials.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function token(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'error' => ['message' => 'Invalid credentials'],
            ], 401);
        }

        return response()->json([
            'data' => ['token' => $token],
        ]);
    }
}