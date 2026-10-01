<?php

namespace App\Interfaces\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email);

    public function create(object $payload);
}
