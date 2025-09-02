<?php

namespace Database\Factories;

use App\Models\SpecialAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpecialAssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpecialAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => 'SK ' . $this->faker->sentence(3),
            'description' => $this->faker->realText(200),
            'assignor_id' => function () {
                $manager_roles = ['eselon_i', 'eselon_ii', 'koordinator'];
                return User::whereHas('role', function ($query) use ($manager_roles) {
                    $query->whereIn('name', $manager_roles);
                })->get()->random()->id;
            },
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+6 months'),
            'status' => $this->faker->randomElement(['diajukan', 'disetujui', 'ditolak', 'selesai']),
            'feedback' => $this->faker->optional()->sentence,
        ];
    }
}
