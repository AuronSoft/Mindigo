<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Mindigo\Auth\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Model tương ứng
     */
    protected $model = User::class;

    /**
     * Password cache
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => \fake()->name(),
            'email' => \fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('123456'),
            'role' => \fake()->randomElement([
                'admin',
                'teacher',
                'student',
            ]),

            'phone' => \fake()->phoneNumber(),
            'avatar' => null,
            'gender' => \fake()->randomElement([
                'male',
                'female',
                'other',
            ]),

            'date_of_birth' => \fake()->date(),
            'address' => \fake()->address(),
            'bio' => \fake()->sentence(),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Email chưa verify
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
