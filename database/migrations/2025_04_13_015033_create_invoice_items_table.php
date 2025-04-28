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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->boolean('is_amount_parallel')->default(false);
            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->decimal('amount_parallel', 15, 2)->nullable();
            $table->decimal('quantity', 15, 2)->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_rate', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('currency', 10);
            $table->decimal('exchange_rate', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
