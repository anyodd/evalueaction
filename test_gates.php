<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(1);
if (!$user) {
    echo "User 1 not found\n";
    exit;
}

echo "Testing Gates for User: " . $user->name . " (Role: " . ($user->role->name ?? 'None') . ")\n";

\Auth::login($user);

$gates = ['superadmin', 'rendal', 'admin-perwakilan'];
foreach ($gates as $gate) {
    echo "Gate '$gate': " . (\Gate::allows($gate) ? 'ALLOWED' : 'DENIED') . "\n";
}
