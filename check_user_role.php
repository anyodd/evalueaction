<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(1);
if ($user) {
    echo "User: " . $user->name . " (ID: " . $user->id . ")\n";
    echo "Role ID: " . $user->role_id . "\n";
    if ($user->role) {
        echo "Role Name: " . $user->role->name . "\n";
    } else {
        echo "Role Relation is null.\n";
    }
    echo "Has Superadmin? " . ($user->hasRole('Superadmin') ? 'Yes' : 'No') . "\n";
} else {
    echo "User ID 1 not found.\n";
}
