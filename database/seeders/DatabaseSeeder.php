<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // 40 notatek (5 przypiętych) generowane przez Fakera — wystarczająco
        // dużo, by zademonstrować paginację (3 strony po 15).
        Note::factory(5)->pinned()->for($testUser)->create();
        Note::factory(35)->for($testUser)->create();

        Notification::factory(3)->for($testUser)->create();
        Notification::factory(2)->read()->for($testUser)->create();

        $otherUsers = User::factory(3)->create();
        $otherUsers->each(function (User $user) {
            Note::factory(5)->for($user)->create();
        });
    }
}
