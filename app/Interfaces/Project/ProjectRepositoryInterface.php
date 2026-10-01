<?php

namespace App\Interfaces\Project;

use App\Models\Project;

interface ProjectRepositoryInterface
{
    public function paginate(object $payload);

    public function findByUuid(string $uuid);

    public function create(object $payload);

    public function update(Project $project, object $payload);

    public function delete(Project $project);
}
