<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_logs', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->index();
            $table->unsignedBigInteger('user_id');
            $table->string('action'); // 'created', 'updated', 'stock_changed', 'price_changed'
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('admins');
            $table->index(['product_id', 'action']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_logs');
    }
};