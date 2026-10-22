<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Partial receive: status 'partial' dipakai POService saat qty diterima belum lengkap.
        DB::statement("ALTER TABLE purchase_orders MODIFY status ENUM('draft','approved','partial','received','cancelled') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE purchase_orders MODIFY status ENUM('draft','approved','received','cancelled') NOT NULL DEFAULT 'draft'");
    }
};