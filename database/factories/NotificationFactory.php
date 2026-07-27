<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'note_created',
            'title' => $this->faker->sentence(3),
            'body' => $this->faker->sentence(12),
            'read_at' => null,
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
        ]);
    }
}
