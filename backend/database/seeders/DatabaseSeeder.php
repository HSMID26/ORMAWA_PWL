<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Roles (Fitur 2 PRD)
        $superAdminRole  = Role::create(['name' => 'Super Admin']);
        $adminOrgRole    = Role::create(['name' => 'Admin Organisasi']);
        $editorRole      = Role::create(['name' => 'Editor']);
        $kontributorRole = Role::create(['name' => 'Kontributor']);

        // 2. Buat Dummy Organisasi (HMPS Teknik Informatika)
        $hmif = Organization::create([
            'nama'        => 'HMPS Teknik Informatika',
            'jenis'       => 'HMPS',
            'subdomain'   => 'hmif',
            'logo'        => null,
            'warna_tema'  => '#1d4ed8',
            'modul_aktif' => ['galeri' => true, 'proker' => true],
        ]);

        // 3. Buat User Super Admin (PKA Pusat - Tanpa organization_id)
        $superAdmin = User::create([
            'name'     => 'Super Admin PKA',
            'email'    => 'admin.pka@kampus.ac.id',
            'password' => bcrypt('password123'),
            'status'   => 'active',
        ]);
        $superAdmin->assignRole($superAdminRole);

        // 4. Buat User Admin Organisasi (Admin HMIF)
        $adminHmif = User::create([
            'organization_id' => $hmif->id,
            'name'            => 'Admin HMIF',
            'email'           => 'admin@hmif.kampus.ac.id',
            'password'        => bcrypt('password123'),
            'status'          => 'active',
        ]);
        $adminHmif->assignRole($adminOrgRole);
    }
}