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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('order_item_id');
            $table->text('order_item_name');
            $table->string('order_item_type', 200);
            $table->unsignedBigInteger('order_id');
            $table->decimal('price', 26, 8)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 26, 8)->nullable();
            $table->decimal('tax_amount', 26, 8)->nullable();
            $table->decimal('total', 26, 8)->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
