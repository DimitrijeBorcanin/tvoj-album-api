<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $user = \App\Models\User::create([
            'first_name' => 'Dimitrije',
            'last_name' => 'Admin',
            'phone' => '+38166123456',
            'address' => 'Sarajevska 13',
            'city' => 'Beograd',
            'zip' => 11000,
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => '$2a$12$Q5qH7fgnbp01XPA7dMtU5uYfNpPpWshOPYVe3hGxsW8HTNpv2Ga1.', // Lozinka123!
            'role_id' => RoleEnum::Admin
        ]);

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'App\Models\User',
            'tokenable_id' => $user->id,
            'name' => 'API TOKEN',
            'token' => 'e124ceaa63aa99d944ae6c1e60bac4808af27713a67e8b2f9d3f2f3af6ddfd95',
            'abilities' => '["*"]'
        ]);
    }
}
