<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->createMany([
            [
                'name' => 'Alice Test',
                'email' => 'alice@mail.com',
                'password' => bcrypt('123456'),
            ],
            [
                'name' => 'Bob Test',
                'email' => 'bob@mail.com',
                'password' => bcrypt('123456'),
            ],
        ]);
    }
}
