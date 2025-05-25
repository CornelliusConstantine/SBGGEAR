<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->integer('quantity_change');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('type')->comment('in, out, adjustment');
            $table->string('reference_type')->nullable()->comment('order, manual, return');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index('product_id');
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_histories');
    }
}; 