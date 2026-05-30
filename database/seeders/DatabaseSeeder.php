<?php

namespace Database\Seeders;

use App\Models\Container;
use App\Models\TrackingLog;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ---------------------------------------------------------------
        // Seed Users (Admin & Operator)
        // ---------------------------------------------------------------
        User::create([
            'name' => 'Admin WowoClean',
            'email' => 'admin@wowoclean.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Operator Lapangan',
            'email' => 'operator@wowoclean.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // ---------------------------------------------------------------
        // Seed Containers (data dummy dari controller lama)
        // ---------------------------------------------------------------
        $container1 = Container::create([
            'container_id' => 'AB12345',
            'waste_type' => 'Chemical',
            'weight_kg' => 450,
            'status' => 'Active',
        ]);

        $container2 = Container::create([
            'container_id' => 'CD67890',
            'waste_type' => 'General',
            'weight_kg' => 1200,
            'status' => 'Active',
        ]);

        $container3 = Container::create([
            'container_id' => 'EF11111',
            'waste_type' => 'General',
            'weight_kg' => 780,
            'status' => 'Archived',
        ]);

        $container4 = Container::create([
            'container_id' => 'GH22222',
            'waste_type' => 'General',
            'weight_kg' => 3200,
            'status' => 'Active',
        ]);

        $container5 = Container::create([
            'container_id' => 'IJ33333',
            'waste_type' => 'Chemical',
            'weight_kg' => 95,
            'status' => 'Active',
        ]);

        // ---------------------------------------------------------------
        // Seed Tracking Logs (data dummy dari controller lama)
        // ---------------------------------------------------------------
        TrackingLog::create([
            'container_id' => $container1->id,
            'location' => 'Gudang A - Jakarta',
            'timestamp' => '2026-04-10 08:00:00',
            'description' => 'Kontainer diterima di gudang utama.',
        ]);

        TrackingLog::create([
            'container_id' => $container1->id,
            'location' => 'Transit Hub - Bekasi',
            'timestamp' => '2026-04-11 14:30:00',
            'description' => 'Dalam perjalanan ke fasilitas pengolahan.',
        ]);

        TrackingLog::create([
            'container_id' => $container2->id,
            'location' => 'Laboratorium B - Surabaya',
            'timestamp' => '2026-04-12 09:15:00',
            'description' => 'Pengambilan sampel awal.',
        ]);

        TrackingLog::create([
            'container_id' => $container4->id,
            'location' => 'Gudang C - Bandung',
            'timestamp' => '2026-04-14 07:45:00',
            'description' => 'Kontainer diisi dan disegel.',
        ]);

        TrackingLog::create([
            'container_id' => $container4->id,
            'location' => 'Pelabuhan - Semarang',
            'timestamp' => '2026-04-15 16:00:00',
            'description' => 'Menunggu pengiriman laut.',
        ]);
    }
}
