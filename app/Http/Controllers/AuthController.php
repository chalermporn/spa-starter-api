<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Transformers\UserTransformer;

class AuthController extends Controller
{
    /**
     * Make authenticated tokens based on users credentials.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function token(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return $this->response->withUnauthorized('Invalid credentials');
        }

        return $this->response->withResource([
            'token' => $token,
            'user' => $this->transform->item(Auth::user(), new UserTransformer)['data'],
        ]);
    }
}
