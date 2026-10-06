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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('order_code')->unique(); // VD: ORD-20240901-001
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedBigInteger('total');
            $table->enum('status', [
                'pending', 'confirmed', 'shipping', 'delivered', 'cancelled',
            ])->default('pending');
            $table->enum('payment_method', ['cod', 'vnpay']);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            // Địa chỉ giao hàng — snapshot, không FK
            $table->string('shipping_name');
            $table->string('shipping_phone');
            $table->text('shipping_address');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('product_name');         // Snapshot tên tại thời điểm mua
            $table->unsignedBigInteger('price');    // Snapshot giá tại thời điểm mua
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('subtotal'); // price * quantity
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
