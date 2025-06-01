<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('question');
            $table->timestamps();
            
            $table->index('product_id');
            $table->index('user_id');
        });
        
        Schema::create('product_comment_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('product_comments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('reply');
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
            
            $table->index('comment_id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_comment_replies');
        Schema::dropIfExists('product_comments');
    }
}; 