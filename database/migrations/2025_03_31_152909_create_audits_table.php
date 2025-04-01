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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type'); // Modelo afectado (e.g., 'App\Models\Customer')
            $table->unsignedBigInteger('auditable_id'); // ID del registro afectado
            $table->string('action'); // 'created', 'updated', 'deleted'
            $table->unsignedBigInteger('user_id')->nullable(); // Usuario que realizó la acción
            $table->text('old_values')->nullable(); // Valores antiguos (JSON)
            $table->text('new_values')->nullable(); // Valores nuevos (JSON)
            $table->timestamp('created_at')->nullable();
            $table->index(['auditable_type', 'auditable_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
