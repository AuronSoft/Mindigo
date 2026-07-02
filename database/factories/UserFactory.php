<?php

namespace Database\Factories;

use Faker\Factory as FakerFactory;
use Faker\Generator;
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

    protected static ?Generator $fakerGenerator = null;

    private function generator(): Generator
    {
        return static::$fakerGenerator ??= FakerFactory::create();
    }

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $faker = $this->generator();

        return [
            'name' => $faker->name(),
            'email' => $faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('123456'),
            'role' => $faker->randomElement([
                'admin',
                'teacher',
                'student',
            ]),

            'phone' => $faker->phoneNumber(),
            'avatar' => null,
            'gender' => $faker->randomElement([
                'male',
                'female',
                'other',
            ]),

            'date_of_birth' => $faker->date(),
            'address' => $faker->address(),
            'bio' => $faker->sentence(),
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
