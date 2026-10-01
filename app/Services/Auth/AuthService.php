<?php

namespace App\Services\Auth;

use App\Interfaces\Auth\AuthServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    private const TOKEN_ABILITIES = ['auth:user'];

    public function register(object $payload)
    {
        $user = new User();
        $user->name = $payload->name;
        $user->email = $payload->email;
        $user->password = Hash::make($payload->password);
        $user->save();

        return [
            'user' => $user,
            'token' => $this->createToken($user),
        ];
    }

    public function login(object $payload)
    {
        $user = User::where('email', $payload->email)->first();

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
