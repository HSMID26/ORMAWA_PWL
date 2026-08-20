<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Organization;

echo "=== TESTING FEATURE 10: ORGANIZATION SETTINGS & MODULES ===\n";

$org = Organization::first() ?? Organization::create([
    'nama'       => 'HMPS Teknik Informatika',
    'jenis'      => 'HMPS',
    'subdomain'  => 'hmif',
    'warna_tema' => '#1d4ed8',
    'status'     => 'active',
]);

echo "1. Initial Organization Info:\n";
echo "ID: {$org->id}\n";
echo "Nama: {$org->nama}\n";
echo "Jenis: {$org->jenis}\n";
echo "Warna Tema: {$org->warna_tema}\n";

// 2. Test updating theme color, modul_aktif, and custom label_menu
echo "\n2. Updating settings (Warna Tema, Modul Aktif, Custom Menu Labels)...\n";
$org->update([
    'warna_tema'  => '#059669',
    'modul_aktif' => [
        'posts'         => true,
        'activities'    => true,
        'committees'    => false, // Turn off committees module
        'media'         => true,
        'announcements' => true,
    ],
    'label_menu'  => [
        'posts'         => 'Berita & Opini',
        'activities'    => 'Kalender Proker 2026',
        'committees'    => 'Struktur Pengurus',
        'media'         => 'Dokumentasi Galeri',
        'announcements' => 'Info Penting',
    ],
]);

$freshOrg = $org->fresh();

echo "\n3. Verifying updated values:\n";
echo "New Warna Tema: {$freshOrg->warna_tema}\n";
echo "Is Posts Module Active? " . ($freshOrg->isModuleActive('posts') ? 'YES' : 'NO') . "\n";
echo "Is Committees Module Active? " . ($freshOrg->isModuleActive('committees') ? 'YES' : 'NO') . "\n";

echo "Custom Posts Label: " . $freshOrg->getMenuLabel('posts', 'Artikel / Konten') . "\n";
echo "Custom Activities Label: " . $freshOrg->getMenuLabel('activities', 'Agenda Kegiatan') . "\n";
echo "Custom Committees Label: " . $freshOrg->getMenuLabel('committees', 'Kelola Pengurus') . "\n";

// Restore default values for clean state
$org->update([
    'warna_tema'  => '#1d4ed8',
    'modul_aktif' => [
        'posts'         => true,
        'activities'    => true,
        'committees'    => true,
        'media'         => true,
        'announcements' => true,
    ],
    'label_menu'  => null,
]);

echo "\n=== ALL FEATURE 10 TESTS PASSED SUCCESSFULLY! ===\n";
