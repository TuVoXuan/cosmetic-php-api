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
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('public_id');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->string('public_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
    }
};
