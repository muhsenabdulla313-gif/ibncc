<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'is_trading')) {
                $table->boolean('is_trading')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('categories', 'icon')) {
                $table->string('icon')->nullable()->after('is_trading');
            }
        });

        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'eyebrow')) {
                $table->string('eyebrow')->nullable()->after('link');
            }
            if (!Schema::hasColumn('banners', 'title')) {
                $table->string('title')->nullable()->after('eyebrow');
            }
            if (!Schema::hasColumn('banners', 'subtitle')) {
                $table->string('subtitle')->nullable()->after('title');
            }
            if (!Schema::hasColumn('banners', 'cta_text')) {
                $table->string('cta_text')->nullable()->after('subtitle');
            }
            if (!Schema::hasColumn('banners', 'text_position')) {
                $table->string('text_position')->default('right')->after('cta_text');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'is_trading')) {
                $table->dropColumn('is_trading');
            }
            if (Schema::hasColumn('categories', 'icon')) {
                $table->dropColumn('icon');
            }
        });

        Schema::table('banners', function (Blueprint $table) {
            $cols = ['eyebrow', 'title', 'subtitle', 'cta_text', 'text_position'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('banners', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
