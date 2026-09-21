<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_logs', function (Blueprint $table) {
            $table->text('message')->nullable()->after('ip_address');
        });
    }

    public function down()
    {
        Schema::table('product_logs', function (Blueprint $table) {
            $table->dropColumn('message');
        });
    }
};