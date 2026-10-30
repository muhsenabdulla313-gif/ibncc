<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('parent_id')->nullable()->constrained('categories')->restrictOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('path')->nullable()->index();          // materialized path e.g. "1/5/12/"
            $t->unsignedTinyInteger('depth')->default(0);
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->string('meta_title')->nullable();
            $t->string('meta_description', 500)->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['parent_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        foreach ([
            'categories',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
