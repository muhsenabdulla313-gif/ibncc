<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();      // seller
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('unit_id')->constrained()->restrictOnDelete();
            $t->foreignId('stock_unit_id')->nullable()->constrained('units')->restrictOnDelete(); // NULL means use unit_id
            $t->string('title', 300);
            $t->string('slug')->unique();
            $t->string('model_no')->nullable();
            $t->text('short_description')->nullable();
            $t->longText('description')->nullable();
            $t->enum('sale_type', ['retail', 'wholesale', 'both'])->default('retail');
            $t->decimal('pack_qty', 12, 3)->default(1);
            // wholesale: minimum order. both: qty at/above this uses wholesale pricing. retail: keep 1
            $t->decimal('min_order_qty', 12, 3)->default(1);
            $t->decimal('max_order_qty', 12, 3)->nullable();                  // NULL = no max
            $t->boolean('track_stock')->default(true);
            // tax
            $t->string('hsn_code', 20)->nullable();
            $t->decimal('tax_rate', 5, 2)->default(0);                        // GST %
            $t->boolean('price_includes_tax')->default(true);
            $t->string('offer_text')->nullable();
            $t->boolean('is_featured')->default(false);
            $t->boolean('is_assured')->default(false);
            $t->decimal('rating_avg', 3, 2)->default(0);
            $t->unsignedInteger('rating_count')->default(0);
            // moderation / publishing
            $t->enum('status', ['draft', 'pending', 'active', 'rejected', 'inactive'])->default('pending');
            $t->string('rejection_reason', 500)->nullable();
            $t->timestamp('published_at')->nullable();
            // seo
            $t->string('meta_title')->nullable();
            $t->string('meta_description', 500)->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['category_id', 'status']);
            $t->index(['sale_type', 'status']);
            $t->index(['user_id', 'status']);
            $t->index(['brand_id', 'status']);
            $t->fullText('title');
        });
    }

    public function down(): void
    {
        foreach ([
            'products',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
