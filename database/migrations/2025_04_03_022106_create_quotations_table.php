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
            $table->dateTime('delivery_date');
            $table->integer('reference_number');
            $table->string('currency');
            $table->decimal('exchange_rate', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->text('observations')->nullable();
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->integer('customer_nit');
            $table->foreign('customer_nit')->references('NIT')->on('customers')->onDelete('cascade');
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
