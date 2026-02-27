<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$admins = User::where('is_admin', true)->get();
echo "Admins in DB:\n";
foreach ($admins as $admin) {
    echo "- " . $admin->name . " (" . $admin->email . ")\n";
}

// Ensure the first user is an admin as per standard fallback
$firstUser = User::first();
if ($firstUser && !$firstUser->is_admin) {
    $firstUser->is_admin = true;
    $firstUser->save();
    echo "\nMade the first user (" . $firstUser->email . ") an admin successfully.\n";
}
