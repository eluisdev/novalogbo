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
            $table->string('reference_number');
            $table->string('reference_customer')->nullable();
            $table->string('currency');
            $table->decimal('exchange_rate', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('pending');
            $table->text('insurance')->nullable();
            $table->text('payment_method')->nullable();
            $table->text('validity')->nullable();
            $table->text('observations')->nullable();
            $table->string('juncture')->nullable();
            $table->unsignedBigInteger('users_id');
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('quotations');
    }
};
