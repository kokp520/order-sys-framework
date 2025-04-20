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
        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('address_type', 20)->nullable()->index();
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('company')->nullable();
            $table->text('address_1')->nullable();
            $table->text('address_2')->nullable();
            $table->text('city')->nullable();
            $table->text('state')->nullable();
            $table->text('postcode')->nullable();
            $table->text('country')->nullable();
            $table->string('email', 320)->nullable()->index();
            $table->string('phone', 100)->nullable()->index();
            $table->timestamps();
            
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_addresses');
    }
};
