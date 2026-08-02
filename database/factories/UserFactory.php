<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;

/**
 * @extends Factory<User>
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

    protected static int $sequence = 0;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $sequence = ++static::$sequence;

        return [
            'name' => 'Demo User '.$sequence,
            'email' => 'user'.$sequence.'.'.Str::lower(Str::random(8)).'@example.com',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('123456'),
            'role' => $this->randomElement([
                'admin',
                'teacher',
                'student',
            ]),

            'phone' => '09'.random_int(10000000, 99999999),
            'avatar' => null,
            'gender' => $this->randomElement([
                'male',
                'female',
                'other',
            ]),

            'date_of_birth' => now()->subYears(random_int(18, 45))->subDays(random_int(0, 365))->toDateString(),
            'address' => 'Demo address '.$sequence,
            'bio' => 'Demo user account for Mindigo testing.',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    private function randomElement(array $items): mixed
    {
        return $items[array_rand($items)];
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
