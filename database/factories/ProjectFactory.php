<?php

namespace Database\Factories;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition()
    {
        $startDate = fake()->dateTimeBetween('-2 months', '+1 month');

        return [
            'client_name' => fake()->company(),
            'project_name' => fake()->catchPhrase(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::cases()),
            'priority' => fake()->randomElement(ProjectPriority::cases()),
            'start_date' => $startDate,
            'due_date' => fake()->dateTimeBetween($startDate, '+4 months'),
        ];
    }
}
