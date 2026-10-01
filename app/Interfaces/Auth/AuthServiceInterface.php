<?php

namespace App\Interfaces\Auth;

use App\Models\User;

interface AuthServiceInterface
{
    public function register(object $payload);

    public function login(object $payload);

    public function logout(User $user);
}
