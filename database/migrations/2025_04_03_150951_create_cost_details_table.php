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
        Schema::create('cost_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_detail_id')->constrained('quotation_details')->onDelete('cascade');
            $table->foreignId('cost_id')->constrained('costs')->onDelete('cascade');
            $table->string('concept');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_details');
    }
};
