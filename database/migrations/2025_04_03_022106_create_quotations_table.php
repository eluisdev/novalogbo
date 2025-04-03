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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->int('number');
            $table->dateTime('delivery_date');
            $table->decimal('amount', 10, 2);
            $table->string('currency_origin');
            $table->string('currency_destination');
            $table->decimal('exchange_rate', 10, 2);
            $table->foreignId('incoterm_id')->constrained('incoterms')->onDelete('cascade');
            $table->foreignId('customers_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
