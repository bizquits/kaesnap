<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frames', function (Blueprint $table) {
            if (!Schema::hasColumn('frames', 'canvas_width')) {
                $table->unsignedInteger('canvas_width')->default(1200)->after('is_active');
            }
            if (!Schema::hasColumn('frames', 'canvas_height')) {
                $table->unsignedInteger('canvas_height')->default(1800)->after('canvas_width');
            }
            // 'behind' = foto di belakang overlay, 'front' = foto di depan overlay
            if (!Schema::hasColumn('frames', 'photo_layer')) {
                $table->string('photo_layer')->default('behind')->after('canvas_height');
            }
        });
    }

    public function down(): void
    {
        Schema::table('frames', function (Blueprint $table) {
            $table->dropColumn(['canvas_width', 'canvas_height', 'photo_layer']);
        });
    }
};
