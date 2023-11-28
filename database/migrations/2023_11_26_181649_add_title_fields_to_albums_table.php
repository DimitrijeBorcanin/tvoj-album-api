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
        Schema::table('albums', function (Blueprint $table) {
            $table->decimal('title_size', 8, 1)->default(3);
            $table->string('title_color')->default('#333333');
            $table->decimal('title_top', 8, 1)->default(15);
            $table->decimal('title_left', 8, 1)->default(0);
            $table->decimal('title_width', 8, 1)->default(100);
            $table->string('title_align', 8, 1)->default('center');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('title_size');
            $table->dropColumn('title_color');
            $table->dropColumn('title_top');
            $table->dropColumn('title_left');
            $table->dropColumn('title_width');
            $table->dropColumn('title_align');
        });
    }
};
