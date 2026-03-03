<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teo = \App\Models\KkTeo::whereNotNull('template_teo_id')->first();
if ($teo) {
    echo "Found KkTeo: " . $teo->id . " linked to TemplateTeo: " . $teo->template_teo_id . "\n";
    $templateTeo = \App\Models\TemplateTeo::with('causes.recommendations')->find($teo->template_teo_id);
    if ($templateTeo) {
        $data = $templateTeo->toArray();
        echo json_encode(['success' => true, 'data' => $data], JSON_PRETTY_PRINT);
    } else {
        echo "TemplateTeo not found\n";
    }
} else {
    echo "No KkTeo with template_teo_id found. If none exists, test with a raw TemplateTeo id.\n";
    
    // Fallback: Test if any TemplateTeo has causes
    $tteo = \App\Models\TemplateTeo::has('causes')->with('causes.recommendations')->first();
    if($tteo) {
        echo "Found TemplateTeo (not linked to KK yet): " . $tteo->id . "\n";
        echo json_encode(['success' => true, 'data' => $tteo->toArray()], JSON_PRETTY_PRINT);
    } else {
        echo "NO TemplateTeo with Causes found at all.\n";
    }
}
