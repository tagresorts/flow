<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        User::factory(10)->create()->each(function ($user) {
            if ($user->id % 3 === 0) {
                $user->assignRole('Admin');
            } elseif ($user->id % 3 === 1) {
                $user->assignRole('Approver');
            } else {
                $user->assignRole('Requester');
            }
        });

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->assignRole('Admin');
    }
}
