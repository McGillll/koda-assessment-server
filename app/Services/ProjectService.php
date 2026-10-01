<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Interfaces\Project\ProjectRepositoryInterface;
use App\Interfaces\Project\ProjectServiceInterface;

class ProjectService implements ProjectServiceInterface
{
    public function __construct(private ProjectRepositoryInterface $projectRepository)
    {
    }

    public function paginate(object $payload)
    {
        return $this->projectRepository->paginate($payload);
    }

    public function find(string $uuid)
    {
        $project = $this->projectRepository->findByUuid($uuid);

        if (! $project) {
            throw new NotFoundException('Project not found.');
        }

        return $project;
    }

    public function create(object $payload)
    {
        return $this->projectRepository->create($payload);
    }

    public function update(string $uuid, object $payload)
    {
        return $this->projectRepository->update($this->find($uuid), $payload);
    }

    public function delete(string $uuid)
    {
        $this->projectRepository->delete($this->find($uuid));
    }
}
