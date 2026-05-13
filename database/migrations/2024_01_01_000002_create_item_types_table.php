<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('item_categories')->onDelete('cascade');
            $table->string('type_name', 100);
            $table->decimal('price', 10, 2)->default(0.00);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_types');
    }
};
