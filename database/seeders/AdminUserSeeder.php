<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un admin par défaut
        User::updateOrCreate(
            ['email' => 'admin@edumap.tg'],
            [
                'name' => 'Administrateur EduMap',
                'email' => 'admin@edumap.tg',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Créer un modérateur par défaut
        User::updateOrCreate(
            ['email' => 'moderateur@edumap.tg'],
            [
                'name' => 'Modérateur EduMap',
                'email' => 'moderateur@edumap.tg',
                'password' => Hash::make('moderateur123'),
                'role' => 'moderateur',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Utilisateurs admin créés avec succès !');
        $this->command->info('Admin: admin@edumap.tg / admin123');
        $this->command->info('Modérateur: moderateur@edumap.tg / moderateur123');
    }
}
