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
        Schema::create('shared_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();        // who shared
            $t->foreignId('shared_user_id')->constrained('users')->cascadeOnDelete(); // profile shared
            $t->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();   // sent to
            $t->timestamps();
            $t->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_profiles');
    }
};
