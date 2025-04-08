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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->foreignId('origin_id')->constrained('cities');
            $table->foreignId('destination_id')->constrained('cities');
            $table->foreignId('incoterm_id')->constrained('incoterms');
            $table->foreignId('quantity_description_id')->constrained('quantity_descriptions');
            $table->string('quantity',20);
            $table->decimal('weight', 10, 2);
            $table->decimal('volume', 10, 2);
            $table->enum('volume_unit', ['kg_vol', 'm3']);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
