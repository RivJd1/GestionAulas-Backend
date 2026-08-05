<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@unicah.edu.hn',
            'password' => bcrypt('password'),
            'rol' => 'administrador',
            'activo' => true,
        ]);
    }
}
