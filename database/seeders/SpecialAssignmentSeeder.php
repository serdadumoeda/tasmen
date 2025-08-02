<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpecialAssignment;
use App\Models\User;

class SpecialAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $users = User::where('role', '!=', User::ROLE_SUPERADMIN)->get();
        $managers = User::whereIn('role', [User::ROLE_ESELON_I, User::ROLE_ESELON_II, User::ROLE_KOORDINATOR])->get();

        if ($users->count() < 2 || $managers->isEmpty()) {
            $this->command->info('Not enough users or managers to create special assignments.');
            return;
        }

        SpecialAssignment::factory()->count(20)->make()->each(function ($assignment) use ($users, $managers) {
            $assignment->assignor_id = $managers->random()->id;
            $assignment->save();
            $assignment->assignees()->attach(
                $users->random(rand(1, min(3, $users->count())))->pluck('id')->toArray()
            );
        });
    }
}
