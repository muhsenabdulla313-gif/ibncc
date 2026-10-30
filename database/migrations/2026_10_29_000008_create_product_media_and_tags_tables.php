<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $t->string('path');
            $t->string('alt')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_primary')->default(false);
            $t->timestamps();
        });

        Schema::create('product_videos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('url', 500);
            $t->enum('provider', ['youtube', 'vimeo', 'upload'])->default('youtube');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('product_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('title');                              // Spec sheet, Test certificate, Manual
            $t->string('path');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('tags', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('slug')->unique();
            $t->timestamps();
        });

        Schema::create('product_tag', function (Blueprint $t) {
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $t->primary(['product_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        foreach ([
            'product_tag',
            'tags',
            'product_documents',
            'product_videos',
            'product_images',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
