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
        Schema::create('midtrans_transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->string('transaction_id')->nullable();
            $table->string('transaction_status')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('fraud_status')->nullable();
            $table->decimal('gross_amount', 12, 2)->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index('order_number');
            $table->index('transaction_id');
            $table->index('transaction_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('midtrans_transaction_logs');
    }
}; 