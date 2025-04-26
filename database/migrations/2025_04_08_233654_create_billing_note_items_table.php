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
        Schema::create('billing_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_note_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10); // USD or BOB
            $table->decimal('exchange_rate', 15, 2)->nullable();
            $table->timestamps();
            $table->index('billing_note_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_note_items');
    }
};
