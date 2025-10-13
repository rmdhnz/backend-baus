<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ["supervisor","driver","staff_im"];
        foreach($roles as $role) { 
            Role::create([
                "name" => $role,
            ]);
        }
    }
}
