<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('stock');
            }
        });

        if (! Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('size')->nullable();
                $table->string('color')->nullable();
                $table->unsignedInteger('stock')->default(0);
                $table->unsignedBigInteger('price')->nullable();
                $table->timestamps();

                $table->unique(['product_id', 'size', 'color']);
            });
        }

        if (Schema::hasTable('cart_items') && ! Schema::hasColumn('cart_items', 'variant_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreignId('variant_id')->nullable()->after('product_id')
                    ->constrained('product_variants')->nullOnDelete();
                $table->index(['cart_id', 'product_id', 'variant_id']);
            });
        }

        if (Schema::hasTable('order_items') && ! Schema::hasColumn('order_items', 'variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('variant_id')->nullable()->after('product_id')
                    ->constrained('product_variants')->nullOnDelete();
                $table->string('size')->nullable()->after('product_name');
                $table->string('color')->nullable()->after('size');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['variant_id']);
                $table->dropColumn(['variant_id', 'size', 'color']);
            });
        }

        if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'variant_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropForeign(['variant_id']);
                $table->dropIndex(['cart_id', 'product_id', 'variant_id']);
                $table->dropColumn('variant_id');
            });
        }

        Schema::dropIfExists('product_variants');

        if (Schema::hasColumn('products', 'views_count')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('views_count');
            });
        }
    }
};
