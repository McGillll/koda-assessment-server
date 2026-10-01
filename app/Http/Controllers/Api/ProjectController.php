<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectIndexRequest;
use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Http\Resources\ProjectResource;
use App\Interfaces\Project\ProjectServiceInterface;

class ProjectController extends Controller
{
    public function __construct(private ProjectServiceInterface $projectService)
    {
    }

    public function index(ProjectIndexRequest $request)
    {
        $payload = (object) $request->validated();
        $projects = $this->projectService->paginate($payload);

        return response()->json([
            'success' => true,
            'message' => 'Projects retrieved.',
            'data' => [
                'projects' => ProjectResource::collection($projects->items()),
                'meta' => [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                ],
            ],
        ]);
    }

    public function show(string $uuid)
    {
        return response()->json([
            'success' => true,
            'message' => 'Project retrieved.',
            'data' => [
                'project' => new ProjectResource($this->projectService->find($uuid)),
            ],
        ]);
    }

    public function store(ProjectStoreRequest $request)
    {
        $payload = (object) $request->validated();
        $project = $this->projectService->create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Project created.',
            'data' => [
                'project' => new ProjectResource($project),
            ],
        ], 201);
    }

    public function update(ProjectUpdateRequest $request, string $uuid)
    {
        $payload = (object) $request->validated();
        $project = $this->projectService->update($uuid, $payload);

        return response()->json([
            'success' => true,
            'message' => 'Project updated.',
            'data' => [
                'project' => new ProjectResource($project),
            ],
        ]);
    }

    public function destroy(string $uuid)
    {
        $this->projectService->delete($uuid);

        return response()->json([
            'success' => true,
            'message' => 'Project deleted.',
            'data' => null,
        ]);
    }
}
