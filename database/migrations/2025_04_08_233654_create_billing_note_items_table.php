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
            $table->foreignId('cost_id')->constrained()->onDelete('restrict');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10);
            $table->timestamps(); // Para auditoría
            $table->index('billing_note_id'); // Mejora rendimiento en consultas
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
