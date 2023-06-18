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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained();
            $table->decimal('price');
            $table->decimal('expense')->nullable();
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('address');
            $table->string('city');
            $table->integer('zip');
            $table->string('note', 500)->nullable();
            $table->dateTime('ordered');
            $table->dateTime('accepted')->nullable();
            $table->dateTime('delivery')->nullable();
            $table->dateTime('payment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
