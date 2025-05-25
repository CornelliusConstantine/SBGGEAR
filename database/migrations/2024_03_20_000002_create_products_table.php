<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->integer('stock')->default(0);
            $table->string('sku')->unique();
            $table->json('specifications')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('weight', 8, 2)->comment('Weight in grams');
            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index('slug');
            $table->index('is_active');
            $table->index('price');
            $table->index('stock');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}; 