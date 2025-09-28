<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ApiTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // API tokens should be created through the admin panel
        // This ensures proper token generation and secure handling
        $this->command->info('API tokens should be created through the admin panel.');
        $this->command->line('Navigate to Admin Panel > API Tokens > Create to generate tokens.');
    }
}
