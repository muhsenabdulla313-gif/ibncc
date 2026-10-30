<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('rating');
            $t->string('title')->nullable();
            $t->text('comment')->nullable();
            $t->boolean('is_verified_purchase')->default(false);
            $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $t->timestamps();
            $t->unique(['product_id', 'user_id']);
            $t->index(['product_id', 'status']);
        });

        Schema::create('wishlists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'product_id']);
        });

        Schema::create('cart_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete(); // required
            $t->decimal('qty', 12, 3);
            $t->timestamps();
            $t->unique(['user_id', 'variant_id']);
        });
    }

    public function down(): void
    {
        foreach ([
            'cart_items',
            'wishlists',
            'product_reviews',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
