<?php

namespace Database\Seeders;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
 public function run(): void
    {
        $this->call(RoleSeeder::class);
 
        User::firstOrCreate(
            ['email' => 'admin@ibncc.com'],
            [
                'role_id' => Role::where('name', 'admin')->first()->id,
                'name' => 'Admin',
                'password' => 'admin@123', 
            ]
        );
    }
}
