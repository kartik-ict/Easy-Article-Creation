<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bol_offer_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('product_number')->unique();
            $table->string('ean')->index();
            $table->string('offer_id')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bol_offer_mappings');
    }
};
