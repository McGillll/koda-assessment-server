<?php

namespace App\Interfaces\Project;

interface ProjectServiceInterface
{
    public function paginate(object $payload);

    public function find(string $uuid);

    public function create(object $payload);

    public function update(string $uuid, object $payload);

    public function delete(string $uuid);
}
