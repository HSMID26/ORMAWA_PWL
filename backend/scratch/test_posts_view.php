<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING POSTS VIEWS COMPILATION ===\n";

$user = User::first();
Auth::login($user);

$categories = Category::all();
$tags = Tag::all();

echo "1. Testing posts.create compilation...\n";
$htmlCreate = view('posts.create', compact('categories', 'tags'))->render();
assert(!empty($htmlCreate), 'create.blade.php must render');
assert(!str_contains($htmlCreate, '{{ __(\'Buat Artikel'), 'create.blade.php should compile blade directives');
echo "posts.create compiled successfully! (" . strlen($htmlCreate) . " bytes)\n";

echo "2. Testing posts.edit compilation...\n";
$post = Post::first();
if ($post) {
    $htmlEdit = view('posts.edit', compact('post', 'categories', 'tags'))->render();
    assert(!empty($htmlEdit), 'edit.blade.php must render');
    assert(!str_contains($htmlEdit, '{{ __(\'Edit Artikel'), 'edit.blade.php should compile blade directives');
    echo "posts.edit compiled successfully! (" . strlen($htmlEdit) . " bytes)\n";
}

echo "=== ALL POSTS VIEW TESTS PASSED SUCCESSFULLY! ===\n";
