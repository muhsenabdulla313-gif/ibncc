<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attribute_groups', function (Blueprint $t) {
            $t->id();
            $t->string('name');                                   // General, Dimensions, Material, Care
            $t->string('slug')->unique();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('attributes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('attribute_group_id')->nullable()->constrained('attribute_groups')->nullOnDelete();
            $t->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();   // mm, cm, kg for number types
            $t->string('name');
            $t->string('slug')->unique();
            $t->enum('input_type', ['text', 'textarea', 'number', 'select', 'multiselect', 'boolean', 'color', 'date']);
            $t->boolean('is_filterable')->default(false);
            $t->boolean('is_searchable')->default(false);
            $t->boolean('is_comparable')->default(false);
            $t->boolean('is_variant_axis')->default(false);       // used to generate variants (Color, Size)
            $t->boolean('is_visible')->default(true);             // show on product page
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'attributes',
            'attribute_groups',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
