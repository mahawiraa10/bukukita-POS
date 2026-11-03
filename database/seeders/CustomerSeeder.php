<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $firstNames = ['Budi', 'Sinta', 'Rina', 'Dimas', 'Maya', 'Agus', 'Siti', 'Hendra', 'Nia', 'Joko', 'Fajar', 'Lina', 'Rudi', 'Tia', 'Yudi', 'Anisa', 'Bayu', 'Dewi', 'Arif', 'Putri'];
        $lastNames = ['Santoso', 'Wulandari', 'Astuti', 'Anggara', 'Sari', 'Saputra', 'Lestari', 'Kusuma', 'Pratama', 'Nugroho', 'Setiawan', 'Handayani', 'Permana', 'Susanto', 'Utami'];
        $cities = ['Jakarta', 'Bekasi', 'Bogor', 'Depok', 'Tangerang', 'Bandung', 'Surabaya', 'Medan', 'Semarang', 'Yogyakarta'];

        for ($i = 1; $i <= 100; $i++) {
            $first = $firstNames[array_rand($firstNames)];
            $last = $lastNames[array_rand($lastNames)];

            Customer::create([
                'id_anggota' => 'MBR-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'name' => "{$first} {$last}",
                'phone' => '08' . rand(1000000000, 9999999999),
                'email' => strtolower($first) . rand(1, 9999) . '@example.com',
                'date_of_birth' => now()->subYears(rand(18, 60))->subDays(rand(0, 365)),
                'gender' => ['male', 'female'][rand(0, 1)],
                'address' => 'Jl. Contoh No. ' . rand(1, 100),
                'city' => $cities[array_rand($cities)],
                'postal_code' => (string) rand(10000, 99999),
                'total_spent' => 0,
                'points' => 0,
                'member_since' => now()->subDays(rand(1, 365)),
                'is_member' => (rand(0, 10) > 2), // 70% member
            ]);
        }
    }
}
