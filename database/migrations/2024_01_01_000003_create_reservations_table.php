<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ball_type_id')->nullable()->constrained('item_types')->onDelete('set null');
            $table->foreignId('pitch_type_id')->constrained('item_types')->onDelete('restrict');
            $table->foreignId('racket_type_id')->nullable()->constrained('item_types')->onDelete('set null');
            $table->integer('number_of_balls')->default(0);
            $table->integer('number_of_rackets')->default(0);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->enum('status', ['pending', 'approved', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
