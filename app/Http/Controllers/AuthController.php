<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create($request->getData());
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;

        return response([
            'token' => $token,
        ]);
    }

    public function login(LoginRequest $request)
    {
        $user = User::query()
            ->where('email', $request->email)
            ->firstOrFail()
        ;

        if (!Hash::check($request->password, $user->password)) {
            return response(['message' => 'Incorrect password'], 422);
        }

        $token = $user->createToken('Laravel Password Grant Client')->accessToken;

        return response([
            'token' => $token,
        ]);
    }

    public function logout(LogoutRequest $request)
    {
        $request->user()->token()->revoke();

        return response([
            'message' => 'Logged out',
        ]);
    }
}
