<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Modify ENUM using raw SQL for better cross-compatibility
        DB::statement("ALTER TABLE program_kerja MODIFY status ENUM('draft', 'active', 'completed', 'archived', 'published') DEFAULT 'draft'");
    }

    public function down()
    {
        // Note: Removing an ENUM value requires careful handling of existing rows with that value.
        // We'll just revert the allowed values, assuming no 'published' rows exist if we rollback.
        DB::statement("ALTER TABLE program_kerja MODIFY status ENUM('draft', 'active', 'completed', 'archived') DEFAULT 'draft'");
    }
};
