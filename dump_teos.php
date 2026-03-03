<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kk = \App\Models\KertasKerja::find(15);
if (!$kk) {
    echo "KK not found\n";
    exit;
}

$indicators = \App\Models\TemplateIndicator::where('template_id', $kk->template_id)
    ->whereNull('parent_id')
    ->with(['children' => function($q) {
        $q->orderBy('id')->with(['teos', 'children' => function($q2) {
            $q2->orderBy('id')->with(['teos']);
        }]);
    }])
    ->orderBy('id')
    ->get();

foreach ($indicators as $h) {
    echo "Header: " . $h->uraian . "\n";
    foreach ($h->children as $c) {
        echo "  Indicator (Lv 2): " . $c->uraian . " (TEOs: " . $c->teos->count() . ")\n";
        foreach ($c->children as $gc) {
            echo "    Param (Lv 3) ID {$gc->id}: " . substr($gc->uraian, 0, 50) . "... (TEOs: " . $gc->teos->count() . ")\n";
            foreach ($gc->teos as $teo) {
                echo "      - TEO: {$teo->teo}\n";
            }
        }
    }
}
