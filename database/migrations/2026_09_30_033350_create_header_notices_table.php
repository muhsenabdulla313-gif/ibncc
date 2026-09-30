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
        Schema::create('header_notices', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('header_notices')->insert([
            'text' => 'GLOBAL BUSINESS CONCLAVE — 13th September, 9 AM to 8 PM — Monsoon Empress Hotel, NH Bypass, Palarivattom, Kochi',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_notices');
    }
};
