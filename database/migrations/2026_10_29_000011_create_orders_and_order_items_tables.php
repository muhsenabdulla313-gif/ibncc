<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('order_no')->unique();
            $t->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            // buyer contact snapshot (sellers use this to contact the buyer)
            $t->string('buyer_name');
            $t->string('buyer_phone', 20);
            $t->string('buyer_email')->nullable();
            $t->json('shipping_address');
            $t->text('buyer_note')->nullable();
            $t->decimal('tax_total', 14, 2)->default(0);
            $t->decimal('total', 14, 2);
            $t->enum('status', ['placed', 'processing', 'completed', 'cancelled'])->default('placed');
            $t->timestamps();
            $t->index(['buyer_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $t->foreignId('seller_id')->constrained('users')->restrictOnDelete();
            // seller contact snapshot (buyer sees this)
            $t->string('seller_name');
            $t->string('seller_phone', 20)->nullable();
            // product snapshot
            $t->string('title', 300);
            $t->string('sku')->nullable();
            $t->json('options')->nullable();
            $t->string('unit_name', 50);
            $t->string('hsn_code', 20)->nullable();
            $t->enum('price_type', ['retail', 'wholesale']);
            $t->decimal('qty', 12, 3);
            $t->decimal('unit_price', 12, 4);
            $t->decimal('tax_rate', 5, 2)->default(0);
            $t->decimal('tax_amount', 14, 2)->default(0);
            $t->decimal('line_total', 14, 2);
            $t->enum('status', ['placed', 'contacted', 'confirmed', 'shipped', 'delivered', 'cancelled'])->default('placed');
            $t->text('seller_note')->nullable();
            $t->timestamp('contacted_at')->nullable();
            $t->timestamps();
            $t->index(['seller_id', 'status']);
        });
    }

    public function down(): void
    {
        foreach ([
            'order_items',
            'orders',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
