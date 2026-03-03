<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $priorities = Task::PRIORITIES;

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->boolean(70) ? fake()->paragraph() : null,
            'due_date' => fake()->optional()->dateTimeBetween('now', '+2 months'),
            'priority' => fake()->randomElement(['high', 'medium', 'low']),
            'status' => fake()->boolean(40),
        ];
    }
}
