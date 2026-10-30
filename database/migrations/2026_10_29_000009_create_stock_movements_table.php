<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();   // who changed it
            $t->enum('type', ['initial', 'adjustment', 'sale', 'cancel', 'return']);
            $t->decimal('qty_change', 12, 3);                 // negative for outgoing
            $t->decimal('qty_after', 12, 3);
            $t->string('note')->nullable();
            $t->nullableMorphs('reference');                  // e.g. order_items row
            $t->timestamps();
            $t->index(['variant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach ([
            'stock_movements',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
