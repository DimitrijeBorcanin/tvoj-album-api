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
        Schema::create('titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained();
            $table->smallInteger('page');
            $table->string('content')->default('Novi tekst');
            $table->foreignId('font_id')->default(1)->constained();
            $table->decimal('size', 8, 1)->default(3);
            $table->string('color')->default('#333333');
            $table->decimal('top', 8, 1)->default(15);
            $table->decimal('left', 8, 1)->default(0);
            $table->decimal('width', 8, 1)->default(100);
            $table->string('align')->default('center');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titles');
    }
};
