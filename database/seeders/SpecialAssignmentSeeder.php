<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpecialAssignment;
use App\Models\User;
use Faker\Factory as Faker;

class SpecialAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Superadmin');
        })->get();
        $managers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Eselon I', 'Eselon II', 'Koordinator']);
        })->get();

        if ($users->count() < 2 || $managers->isEmpty()) {
            $this->command->info('Not enough users or managers to create special assignments.');
            return;
        }

        for ($i = 0; $i < 15; $i++) {
            $assignment = SpecialAssignment::create([
                'title' => 'SK ' . $faker->sentence(3),
                'description' => $faker->realText(200),
                'creator_id' => $managers->random()->id,
                'start_date' => $faker->dateTimeBetween('-1 month', '+1 month'),
                'end_date' => $faker->dateTimeBetween('+2 months', '+6 months'),
                'status' => $faker->randomElement(['AKTIF', 'SELESAI']),
                'feedback' => $faker->optional()->sentence,
            ]);

            // Assign 1 to 3 random users to the assignment
            $assignment->members()->attach(
                $users->random(rand(1, min(3, $users->count())))->pluck('id')->toArray()
            );
        }
    }
}
