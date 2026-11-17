<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Lets a product image belong to one variant option (e.g. the "Black" colour). Skips itself if your 000012 migration already added the column. */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('product_images', 'attribute_option_id')) return;

        Schema::table('product_images', function (Blueprint $t) {
            $t->foreignId('attribute_option_id')->nullable()->constrained('attribute_options')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('product_images', 'attribute_option_id')) return;
        Schema::table('product_images', function (Blueprint $t) {
            $t->dropConstrainedForeignId('attribute_option_id');
        });
    }
};
