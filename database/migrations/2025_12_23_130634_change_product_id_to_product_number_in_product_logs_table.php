<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_logs', function (Blueprint $table) {
            $table->string('product_number')->after('id');
            $table->dropColumn('product_id');
        });
    }

    public function down()
    {
        Schema::table('product_logs', function (Blueprint $table) {
            $table->string('product_id')->after('id');
            $table->dropColumn('product_number');
        });
    }
};