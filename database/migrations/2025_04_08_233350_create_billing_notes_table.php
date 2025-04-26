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
        Schema::create('billing_notes', function (Blueprint $table) {
            $table->id();
            $table->string('op_number')->unique()->comment('Formato OP-001-25');
            $table->string('note_number')->unique()->comment('Formato No-001-25');
            $table->date('emission_date');
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 10);
            $table->decimal('exchange_rate', 15, 2);
            $table->string('status');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
            $table->integer('customer_nit');
            $table->foreign('customer_nit')->references('NIT')->on('customers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_notes');
    }
};
