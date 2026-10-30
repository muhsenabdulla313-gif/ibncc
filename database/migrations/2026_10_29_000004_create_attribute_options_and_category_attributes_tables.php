<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attribute_options', function (Blueprint $t) {
            $t->id();
            $t->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $t->string('label');
            $t->string('value', 100);
            $t->string('color_hex', 9)->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->unique(['attribute_id', 'value']);
        });

        Schema::create('category_attributes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $t->boolean('is_required')->default(false);
            $t->boolean('is_filterable')->nullable();             // NULL = use attribute default
            $t->boolean('is_variant_axis')->nullable();           // NULL = use attribute default
            $t->enum('applies_to', ['retail', 'wholesale', 'both'])->default('both');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->unique(['category_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        foreach ([
            'category_attributes',
            'attribute_options',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
