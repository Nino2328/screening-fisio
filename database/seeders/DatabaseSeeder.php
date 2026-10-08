<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat akun admin default
        User::firstOrCreate(
            ['email' => 'admin@footscreen.com'],
            [
                'name' => 'Admin Fisioterapis',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Buat akun fisio
        User::firstOrCreate(
            ['email' => 'fisio@footscreen.com'],
            [
                'name' => 'Dr. Fisioterapis',
                'password' => Hash::make('password123'),
                'role' => 'fisio',
            ]
        );

        // Buat akun pelatih
        User::firstOrCreate(
            ['email' => 'pelatih@footscreen.com'],
            [
                'name' => 'Coach Pelatih',
                'password' => Hash::make('password123'),
                'role' => 'pelatih',
            ]
        );

        // Buat beberapa atlet contoh
        $athletes = [
            ['name' => 'Ahmad Rizki', 'age' => 22, 'gender' => 'putra', 'position' => 'Striker', 'height' => 175, 'weight' => 68],
            ['name' => 'Budi Santoso', 'age' => 20, 'gender' => 'putra', 'position' => 'Gelandang', 'height' => 172, 'weight' => 65],
            ['name' => 'Candra Putra', 'age' => 24, 'gender' => 'putra', 'position' => 'Defender', 'height' => 180, 'weight' => 72],
            ['name' => 'Deni Kurniawan', 'age' => 23, 'gender' => 'putra', 'position' => 'Kiper', 'height' => 183, 'weight' => 80],
            ['name' => 'Eko Prasetyo', 'age' => 21, 'gender' => 'putra', 'position' => 'Winger', 'height' => 170, 'weight' => 63],
        ];

        foreach ($athletes as $data) {
            \App\Models\Athlete::firstOrCreate(['name' => $data['name']], $data);
        }

        $this->command->info('✅ Seeder selesai.');
        $this->command->info('📋 Akun Admin: admin@footscreen.com / password123');
        $this->command->info('🏥 Akun Fisio: fisio@footscreen.com / password123');
        $this->command->info('⚽ Akun Pelatih: pelatih@footscreen.com / password123');
    }
}
