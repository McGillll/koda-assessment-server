<?php

namespace App\Services;

use App\Interfaces\Auth\AuthServiceInterface;
use App\Interfaces\User\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    private const TOKEN_ABILITIES = ['auth:user'];

    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function register(object $payload)
    {
        $user = $this->userRepository->create($payload);

        return [
            'user' => $user,
            'token' => $this->createToken($user),
        ];
    }

    public function login(object $payload)
    {
        $user = $this->userRepository->findByEmail($payload->email);

        if (! $user || ! Hash::check($payload->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return [
            'user' => $user,
            'token' => $this->createToken($user),
        ];
    }

    public function logout(User $user)
    {
        $user->currentAccessToken()?->delete();
    }

    private function createToken(User $user)
    {
        return $user->createToken('auth-token', self::TOKEN_ABILITIES)->plainTextToken;
    }
}
