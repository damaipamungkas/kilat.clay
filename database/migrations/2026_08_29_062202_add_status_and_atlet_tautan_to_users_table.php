<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom status dan atlet_tautan (jika belum ada di database)
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('Aktif')->after('password');
            }
            if (!Schema::hasColumn('users', 'atlet_tautan')) {
                $table->string('atlet_tautan')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'atlet_tautan']);
        });
    }
};
