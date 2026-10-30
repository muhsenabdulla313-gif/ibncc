<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('sku')->unique();
            $t->string('barcode', 64)->nullable();
            $t->json('options')->nullable();                 // display cache only: {"color":"Blue","size":"M"}
            $t->decimal('mrp', 12, 2)->nullable();
            $t->decimal('price', 12, 2)->nullable();         // base retail price per unit
            $t->decimal('cost_price', 12, 2)->nullable();    // seller internal
            $t->decimal('stock', 12, 3)->default(0); // measured in products.stock_unit_id
            $t->decimal('reserved_stock', 12, 3)->default(0); // reserved for pending orders
            $t->decimal('low_stock_alert', 12, 3)->nullable();
            // logistics (kg / cm)
            $t->decimal('weight', 10, 3)->nullable();
            $t->decimal('length', 10, 2)->nullable();
            $t->decimal('width', 10, 2)->nullable();
            $t->decimal('height', 10, 2)->nullable();
            $t->boolean('is_default')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        foreach ([
            'product_variants',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
