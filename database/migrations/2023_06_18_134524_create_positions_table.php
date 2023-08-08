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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained();
            $table->smallInteger('page');
            $table->string('position', 10);
            $table->decimal('top', 5, 2);
            $table->decimal('left', 5, 2);
            $table->decimal('width', 5, 2);
            $table->decimal('height', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
