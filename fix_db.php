<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

try {
    if (!Schema::hasColumn('template_langkahs', 'jenis_prosedur')) {
        Schema::table('template_langkahs', function (Blueprint $table) {
            $table->string('jenis_prosedur')->nullable()->after('uraian');
        });
        echo "Kolom ditambahkan.";
    } else {
        echo "Kolom sudah ada.";
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
