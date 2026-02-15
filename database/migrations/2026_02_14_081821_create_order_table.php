<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {

            $table->id();

            // 🔹 Relationships
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // 🔹 Order Info
            $table->string('order_number')->unique();

            // 🔹 Financial Info
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);


            // Flexible and future-proof
            $table->string('status')->default('pending'); 
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('unpaid');

            // 🔹 Customer Info
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();

            // 🔹 Dates
            $table->timestamp('order_date')->useCurrent();
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('delivery_date')->nullable();

            // 🔹 Soft Delete & Timestamps
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
