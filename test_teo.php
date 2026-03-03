<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t = \App\Models\TemplateTeo::first();
$k = \App\Models\KkAnswer::first();
if ($t && $k) {
    try {
        $teo = \App\Models\KkTeo::create(['kk_answer_id' => $k->id, 'teo' => 'Test TEO', 'template_teo_id' => $t->id]);
        echo "Successfully created KkTeo with template_teo_id: " . $teo->template_teo_id . "\n";
        
        $templateTeo = \App\Models\TemplateTeo::with('causes.recommendations')->find($teo->template_teo_id);
        echo "Found template TEO via relation? " . ($templateTeo ? "Yes" : "No") . "\n";
        
        $teo->delete(); // cleanup
    } catch (\Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
} else {
    echo "No TemplateTeo or KkAnswer found to test with.\n";
}
