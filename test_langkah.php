<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/indicators/1/langkah', 'POST', [
    'uraian' => 'test test',
    'jenis_prosedur' => 'inspeksi_dokumen'
]);
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

$user = App\Models\User::first();
if ($user) {
    auth()->login($user);
}

$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
