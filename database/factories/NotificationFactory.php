<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
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
        $types = ['info', 'success', 'warning', 'error'];
        $categories = ['leave', 'timesheet', 'shift', 'general', 'system'];
        
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement($types),
            'category' => $this->faker->randomElement($categories),
            'data' => null,
            'read_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 week', 'now'),
            'is_important' => $this->faker->boolean(20), // 20% chance of being important
            'action_url' => $this->faker->optional(0.3)->url(),
            'action_text' => $this->faker->optional(0.3)->words(2, true),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the notification is important.
     */
    public function important(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_important' => true,
        ]);
    }

    /**
     * Create a leave notification.
     */
    public function leave(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'leave',
            'title' => 'Leave Request ' . $this->faker->randomElement(['Approved', 'Rejected', 'Submitted']),
            'message' => 'Your leave request has been ' . strtolower($this->faker->randomElement(['approved', 'rejected', 'submitted'])).
        ]);
    }

    /**
     * Create a timesheet notification.
     */
    public function timesheet(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'timesheet',
            'title' => 'Timesheet ' . $this->faker->randomElement(['Approved', 'Rejected', 'Submitted']),
            'message' => 'Your timesheet has been ' . strtolower($this->faker->randomElement(['approved', 'rejected', 'submitted'])).
        ]);
    }
}
