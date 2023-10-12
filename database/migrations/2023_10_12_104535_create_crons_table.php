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
        Schema::create('crons', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->integer('next_run');
            $table->integer('last_run');
            $table->timestamps();

            $table->index('next_run');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crons');
    }
};
