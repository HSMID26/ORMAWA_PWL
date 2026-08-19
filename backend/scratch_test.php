<?php
require 'vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

// Buat gambar test 1500x800 
$manager = new ImageManager(new Driver());
$image = $manager->create(1500, 800)->fill('ff0000');

echo "Width sebelum: " . $image->width() . "\n";

// Cek method scale
if ($image->width() > 1200) {
    $image->scale(1200);
}
echo "Width sesudah: " . $image->width() . "\n";

// Cek toWebp
$encoded = $image->toWebp(80);
echo "Encoded size: " . strlen((string)$encoded) . " bytes\n";

echo "Semua method berjalan dengan baik!\n";
