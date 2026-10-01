<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate()
    {
        $token = User::factory()->create()->createToken('auth-token', ['auth:user'])->plainTextToken;

        return $this->withToken($token);
    }

    private function validPayload(array $overrides = [])
    {
        return array_merge([
            'client_name' => 'Acme Corp',
            'project_name' => 'Website Redesign',
            'description' => 'Full redesign of the marketing site.',
            'status' => 'in_progress',
            'priority' => 'high',
            'start_date' => '2026-10-01',
            'due_date' => '2026-12-15',
        ], $overrides);
    }

    public function test_projects_require_authentication(): void
    {
        $this->getJson('/api/projects')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_user_can_list_projects_with_pagination(): void
    {
        Project::factory()->count(3)->create();

        $this->authenticate()
            ->getJson('/api/projects')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.projects')
            ->assertJsonPath('data.meta.total', 3)
            ->assertJsonStructure([
                'data' => [
                    'projects' => [[
                        'uuid', 'client_name', 'project_name', 'description',
                        'status', 'status_label', 'priority', 'priority_label',
                        'start_date', 'due_date', 'created_at', 'updated_at',
                    ]],
                    'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                ],
            ]);
    }

    public function test_user_can_filter_projects(): void
    {
        Project::factory()->create(['status' => 'on_hold', 'priority' => 'low', 'client_name' => 'Globex']);
        Project::factory()->create(['status' => 'completed', 'priority' => 'high', 'client_name' => 'Initech']);

        $client = $this->authenticate();

        $client->getJson('/api/projects?status=on_hold')
            ->assertOk()
            ->assertJsonCount(1, 'data.projects')
            ->assertJsonPath('data.projects.0.client_name', 'Globex');

        $client->getJson('/api/projects?priority=high')
            ->assertJsonCount(1, 'data.projects')
            ->assertJsonPath('data.projects.0.client_name', 'Initech');

        $client->getJson('/api/projects?search=initech')
            ->assertJsonCount(1, 'data.projects');

        $client->getJson('/api/projects?status=archived')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status'], 'data');
    }

    public function test_projects_are_sorted_by_due_date_by_default_with_nulls_last(): void
    {
        Project::factory()->create(['project_name' => 'No due', 'start_date' => null, 'due_date' => null]);
        Project::factory()->create(['project_name' => 'Later', 'start_date' => null, 'due_date' => '2026-12-01']);
        Project::factory()->create(['project_name' => 'Sooner', 'start_date' => null, 'due_date' => '2026-11-01']);

        $client = $this->authenticate();

        $this->assertSame(
            ['Sooner', 'Later', 'No due'],
            $client->getJson('/api/projects')->json('data.projects.*.project_name'),
        );

        $this->assertSame(
            ['Later', 'Sooner', 'No due'],
            $client->getJson('/api/projects?sort_by=due_date&sort_dir=desc')->json('data.projects.*.project_name'),
        );
    }

    public function test_projects_can_be_sorted_by_name(): void
    {
        foreach (['Bravo', 'Charlie', 'Alpha'] as $name) {
            Project::factory()->create(['client_name' => $name]);
        }

        $client = $this->authenticate();

        $this->assertSame(
            ['Alpha', 'Bravo', 'Charlie'],
            $client->getJson('/api/projects?sort_by=client_name')->json('data.projects.*.client_name'),
        );

        $this->assertSame(
            ['Charlie', 'Bravo', 'Alpha'],
            $client->getJson('/api/projects?sort_by=client_name&sort_dir=desc')->json('data.projects.*.client_name'),
        );
    }

    public function test_priority_and_status_sort_by_rank_not_alphabetically(): void
    {
        Project::factory()->create(['priority' => 'medium', 'status' => 'on_hold']);
        Project::factory()->create(['priority' => 'high', 'status' => 'completed']);
        Project::factory()->create(['priority' => 'low', 'status' => 'planning']);
        Project::factory()->create(['priority' => 'medium', 'status' => 'in_progress']);

        $client = $this->authenticate();

        $this->assertSame(
            ['high', 'medium', 'medium', 'low'],
            $client->getJson('/api/projects?sort_by=priority&sort_dir=desc')->json('data.projects.*.priority'),
        );

        $this->assertSame(
            ['planning', 'in_progress', 'on_hold', 'completed'],
            $client->getJson('/api/projects?sort_by=status')->json('data.projects.*.status'),
        );
    }

    public function test_ties_are_broken_by_soonest_due_date(): void
    {
        Project::factory()->create(['priority' => 'high', 'project_name' => 'Due later', 'start_date' => null, 'due_date' => '2026-12-01']);
        Project::factory()->create(['priority' => 'high', 'project_name' => 'Due sooner', 'start_date' => null, 'due_date' => '2026-11-01']);

        $this->assertSame(
            ['Due sooner', 'Due later'],
            $this->authenticate()->getJson('/api/projects?sort_by=priority&sort_dir=desc')->json('data.projects.*.project_name'),
        );
    }

    public function test_invalid_sort_parameters_are_rejected(): void
    {
        $this->authenticate()
            ->getJson('/api/projects?sort_by=password&sort_dir=sideways')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort_by', 'sort_dir'], 'data');
    }

    public function test_user_can_view_a_single_project(): void
    {
        $project = Project::factory()->create();

        $this->authenticate()
            ->getJson("/api/projects/{$project->uuid}")
            ->assertOk()
            ->assertJsonPath('data.project.uuid', $project->uuid);
    }

    public function test_viewing_a_missing_project_returns_not_found(): void
    {
        $this->authenticate()
            ->getJson('/api/projects/00000000-0000-0000-0000-000000000000')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Project not found.');
    }

    public function test_user_can_create_a_project(): void
    {
        $this->authenticate()
            ->postJson('/api/projects', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.project.status', 'in_progress')
            ->assertJsonPath('data.project.status_label', 'In Progress')
            ->assertJsonPath('data.project.priority', 'high')
            ->assertJsonPath('data.project.due_date', '2026-12-15');

        $this->assertDatabaseHas('projects', ['project_name' => 'Website Redesign']);
    }

    public function test_optional_fields_can_be_omitted(): void
    {
        $this->authenticate()
            ->postJson('/api/projects', [
                'client_name' => 'Acme Corp',
                'project_name' => 'Logo Refresh',
                'status' => 'planning',
                'priority' => 'low',
                'due_date' => '2026-12-15',
            ])
            ->assertCreated()
            ->assertJsonPath('data.project.description', null)
            ->assertJsonPath('data.project.start_date', null);
    }

    public function test_create_validates_required_and_enum_fields(): void
    {
        $this->authenticate()
            ->postJson('/api/projects', [
                'status' => 'archived',
                'priority' => 'urgent',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['client_name', 'project_name', 'status', 'priority'], 'data')
            ->assertJsonPath('data.status.0', 'Status must be one of: planning, in_progress, on_hold, completed.')
            ->assertJsonPath('data.priority.0', 'Priority must be one of: low, medium, high.');
    }

    public function test_due_date_cannot_be_earlier_than_start_date(): void
    {
        $this->authenticate()
            ->postJson('/api/projects', $this->validPayload([
                'start_date' => '2026-12-15',
                'due_date' => '2026-12-01',
            ]))
            ->assertUnprocessable()
            ->assertJsonPath('data.due_date.0', 'Due date cannot be earlier than the start date.');
    }

    public function test_user_can_update_a_project(): void
    {
        $project = Project::factory()->create();

        $this->authenticate()
            ->putJson("/api/projects/{$project->uuid}", $this->validPayload([
                'status' => 'completed',
                'priority' => 'medium',
            ]))
            ->assertOk()
            ->assertJsonPath('data.project.uuid', $project->uuid)
            ->assertJsonPath('data.project.status', 'completed');

        $this->assertDatabaseHas('projects', ['uuid' => $project->uuid, 'status' => 'completed']);
    }

    public function test_update_validates_payload(): void
    {
        $project = Project::factory()->create();

        $this->authenticate()
            ->putJson("/api/projects/{$project->uuid}", $this->validPayload(['client_name' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['client_name'], 'data');
    }

    public function test_user_can_soft_delete_a_project(): void
    {
        $project = Project::factory()->create();
        $client = $this->authenticate();

        $client->deleteJson("/api/projects/{$project->uuid}")
            ->assertOk()
            ->assertJsonPath('message', 'Project deleted.');

        $this->assertSoftDeleted('projects', ['uuid' => $project->uuid]);

        $client->getJson("/api/projects/{$project->uuid}")
            ->assertNotFound();
    }
}
