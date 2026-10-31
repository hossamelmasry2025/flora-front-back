<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@flora.test'],
            ['name' => 'Admin', 'password' => 'password'],
        );

        $this->call(StoreSeeder::class);
    }
}
