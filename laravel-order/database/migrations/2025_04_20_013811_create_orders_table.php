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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->nullable()->index();
            $table->string('currency', 10)->nullable();
            $table->string('type', 20)->nullable()->index();
            $table->decimal('tax_amount', 26, 8)->nullable();
            $table->decimal('total_amount', 26, 8)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('billing_email', 320)->nullable()->index();
            $table->string('payment_method', 100)->nullable();
            $table->text('payment_method_title')->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('ip_address', 100)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('customer_note')->nullable();
            $table->unsignedBigInteger('parent_order_id')->nullable()->index();
            $table->timestamps();
            
            // 為了與 WooCommerce 保持一致，添加 date_created_gmt 和 date_updated_gmt 欄位
            $table->dateTime('date_created_gmt')->nullable()->index();
            $table->dateTime('date_updated_gmt')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
