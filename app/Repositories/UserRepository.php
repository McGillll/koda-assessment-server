<?php

namespace App\Repositories;

use App\Interfaces\User\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function create(object $payload)
    {
        $user = new User();
        $user->name = $payload->name;
        $user->email = $payload->email;
        $user->password = $payload->password;
        $user->save();

        return $user;
    }
}
