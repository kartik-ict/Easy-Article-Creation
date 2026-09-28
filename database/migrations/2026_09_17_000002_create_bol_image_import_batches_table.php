<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bol_image_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('product_number')->index();
            $table->string('offer_id');
            $table->string('batch_id')->nullable()->index();
            $table->string('status')->default('PENDING'); // PENDING, COMPLETED, FAILED (our own tracking, mirrors Bol's batch status)
            $table->json('submitted_urls')->nullable();
            $table->json('asset_results')->nullable(); // per-asset {url, status, subStatusDescription} once polled
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bol_image_import_batches');
    }
};
