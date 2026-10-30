<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->string('logo')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('units', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('short_name', 20);
            $t->enum('type', ['count', 'weight', 'length', 'area', 'volume', 'pack'])->default('count');
            $t->foreignId('base_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $t->decimal('conversion_factor', 14, 6)->default(1);   // how many base units in 1 of this unit
            $t->boolean('allow_decimal')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'units',
            'brands',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
