<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Inisialisasi Canonical Permissions dan Roles Matrix (Single Source of Truth)
        \App\Http\Controllers\Api\RoleController::ensureDefaultPermissions();

        // 2. Dummy Organisasi (HMPS Teknik Informatika)
        $hmif = Organization::firstOrCreate(
            ['subdomain' => 'hmif'],
            [
                'nama'        => 'HMPS Teknik Informatika',
                'jenis'       => 'HMPS',
                'logo'        => null,
                'warna_tema'  => '#1d4ed8',
                'modul_aktif' => ['galeri' => true, 'proker' => true],
            ]
        );

        foreach ([
            ['name' => 'Akademik', 'slug' => 'akademik'],
            ['name' => 'Kegiatan', 'slug' => 'kegiatan'],
            ['name' => 'Prestasi', 'slug' => 'prestasi'],
            ['name' => 'Informasi', 'slug' => 'informasi'],
            ['name' => 'Opini', 'slug' => 'opini'],
        ] as $category) {
            Category::firstOrCreate(
                ['organization_id' => $hmif->id, 'slug' => $category['slug']],
                ['name' => $category['name']]
            );
        }

        // Seed Tags Global
        foreach (['Mahasiswa', 'Prestasi', 'Kegiatan', 'Akademik', 'Workshop', 'Seminar', 'Teknologi', 'Kepanitiaan', 'Kompetisi'] as $tagName) {
            \App\Models\Tag::firstOrCreate(
                ['name' => $tagName],
                ['slug' => \Illuminate\Support\Str::slug($tagName)]
            );
        }

        // 3. Buat User Super Admin (PKA Pusat - Tanpa organization_id)
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin.pka@kampus.ac.id'],
            [
                'name'     => 'Super Admin PKA',
                'password' => bcrypt('password123'),
                'status'   => 'active',
            ]
        );
        $superAdmin->syncRoles(['Super Admin']);

        // 4. Buat User Admin Organisasi (Admin HMIF)
        $adminHmif = User::firstOrCreate(
            ['email' => 'admin@hmif.kampus.ac.id'],
            [
                'organization_id' => $hmif->id,
                'name'            => 'Admin HMIF',
                'password'        => bcrypt('password123'),
                'status'          => 'active',
            ]
        );
        $adminHmif->organization_id = $hmif->id;
        $adminHmif->save();
        $adminHmif->syncRoles(['Admin Organisasi']);
    }
}