<?php

namespace App\Repositories;

use App\Interfaces\Project\ProjectRepositoryInterface;
use App\Models\Project;

class ProjectRepository implements ProjectRepositoryInterface
{
    private const DEFAULT_PER_PAGE = 15;

    public function paginate(object $payload)
    {
        return Project::filter((array) $payload)
            ->orderByDesc('id')
            ->paginate($payload->per_page ?? self::DEFAULT_PER_PAGE);
    }

    public function findByUuid(string $uuid)
    {
        return Project::where('uuid', $uuid)->first();
    }

    public function create(object $payload)
    {
        $project = new Project();
        $this->assign($project, $payload);
        $project->save();

        return $project;
    }

    public function update(Project $project, object $payload)
    {
        $this->assign($project, $payload);
        $project->save();

        return $project;
    }

    public function delete(Project $project)
    {
        $project->delete();
    }

    private function assign(Project $project, object $payload)
    {
        $project->client_name = $payload->client_name;
        $project->project_name = $payload->project_name;
        $project->description = $payload->description ?? null;
        $project->status = $payload->status;
        $project->priority = $payload->priority;
        $project->start_date = $payload->start_date ?? null;
        $project->due_date = $payload->due_date ?? null;
    }
}
