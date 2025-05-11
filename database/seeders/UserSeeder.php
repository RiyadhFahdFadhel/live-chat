<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        User::factory()->create([
            'name' => 'Riyad',
            'email' => 'ryadhfadhel86886@example.com',
            'password' => Hash::make('password123')
        ]);
        
        User::create([
            'name' => 'Akram',
            'email' => 'riyadhfadhel97@example.com',
            'password' => Hash::make('password123'), // Always hash passwords!
        ]);
    }
}
