<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
      
      User::truncate(); 

      
      User::create([
          'name' => 'Kepala Pusdatik',
          'email' => 'kapusdatik@example.com',
          'password' => Hash::make('password'),
          'role' => 'kepala_pusdatik', // Set role khusus
      ]);

      
      User::create([
          'name' => 'Ketua Tim Alpha',
          'email' => 'ketua.alpha@example.com',
          'password' => Hash::make('password'),
          'role' => 'leader',
      ]);
      
      
      User::factory(3)->create();
    }
}