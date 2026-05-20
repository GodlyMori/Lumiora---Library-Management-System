<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\BookCategory;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin account
        User::firstOrCreate(
            ['email' => 'admin@lumiora.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('lumiora123'),
            ]
        );

        // Default book categories
        $categories = [
            'Fiction',
            'Non-Fiction',
            'Science & Technology',
            'History',
            'Mathematics',
            'Literature',
            'Reference',
            'Biography',
            'Arts & Music',
            'Religion & Philosophy',
        ];

        foreach ($categories as $cat) {
            BookCategory::firstOrCreate(['name' => $cat]);
        }

        $this->command->info('✅ Default admin: admin@lumiora.com / lumiora123');
    }
}
