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
        Schema::create('order_item_metas', function (Blueprint $table) {
            $table->id('meta_id');
            $table->unsignedBigInteger('order_item_id');
            $table->string('meta_key', 255)->nullable()->index();
            $table->longText('meta_value')->nullable();
            $table->timestamps();
            
            $table->foreign('order_item_id')->references('order_item_id')->on('order_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_metas');
    }
};
