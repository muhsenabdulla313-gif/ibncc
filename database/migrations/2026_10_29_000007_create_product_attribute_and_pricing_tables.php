<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_attribute_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $t->foreignId('attribute_option_id')->nullable()->constrained('attribute_options')->cascadeOnDelete();
            $t->string('value_text', 500)->nullable();
            $t->decimal('value_number', 18, 4)->nullable();
            $t->boolean('value_bool')->nullable();
            $t->date('value_date')->nullable();
            $t->timestamps();
            $t->index(['attribute_id', 'attribute_option_id'], 'pav_attr_option');   // option filters
            $t->index(['attribute_id', 'value_number'], 'pav_attr_number');          // range filters
            $t->index(['product_id', 'attribute_id'], 'pav_product_attr');
        });

        Schema::create('variant_attribute_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $t->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $t->foreignId('attribute_option_id')->constrained('attribute_options')->restrictOnDelete();
            $t->timestamps();
            $t->unique(['variant_id', 'attribute_id']);
            $t->index(['attribute_id', 'attribute_option_id'], 'vav_attr_option');
        });

        Schema::create('product_price_tiers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $t->enum('price_type', ['retail', 'wholesale']);
            $t->decimal('min_qty', 12, 3);
            $t->decimal('max_qty', 12, 3)->nullable();       // NULL = and above
            $t->decimal('price', 12, 4);
$t->unsignedBigInteger('variant_key')->virtualAs('IFNULL(variant_id, 0)');
            $t->timestamps();
            $t->unique(['product_id', 'variant_key', 'price_type', 'min_qty'], 'tier_unique');
        });
    }

    public function down(): void
    {
        foreach ([
            'product_price_tiers',
            'variant_attribute_values',
            'product_attribute_values',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
