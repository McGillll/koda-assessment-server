<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Interfaces\Auth\AuthServiceInterface;

class AuthController extends Controller
{
    public function __construct(private AuthServiceInterface $authService)
    {
    }

    public function register(RegisterRequest $request)
    {
        $payload = (object) $request->validated();
        $authPayload = $this->authService->register($payload);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => [
                'user' => new UserResource($authPayload['user']),
                'token' => $authPayload['token'],
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $payload = (object) $request->validated();
        $authPayload = $this->authService->login($payload);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($authPayload['user']),
                'token' => $authPayload['token'],
            ],
        ]);
    }

    public function me()
    {
        return response()->json([
            'success' => true,
            'message' => 'Authenticated user retrieved.',
            'data' => [
                'user' => new UserResource(request()->user()),
            ],
        ]);
    }

    public function logout()
    {
        $this->authService->logout(request()->user());

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
            'data' => null,
        ]);
    }
}
