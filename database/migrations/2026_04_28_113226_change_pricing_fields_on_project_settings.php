<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('project_settings', function (Blueprint $table) {
            // price_per_session: integer → json (array by slot count)
            $table->json('price_per_session')->nullable()->change();
            // copy_prices: json → integer (harga flat per eksemplar)
            $table->integer('copy_prices')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_settings', function (Blueprint $table) {
            $table->integer('price_per_session')->default(0)->change();
            $table->json('copy_prices')->nullable()->change();
        });
    }
};
