<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up(): void
    {
        Schema::table('profiles', function (Blueprint $t) {
            if (! Schema::hasColumn('profiles', 'status')) {
                $t->string('status', 10)->default('available');          // available | busy
            }
            if (! Schema::hasColumn('profiles', 'work_description')) {
                $t->text('work_description')->nullable();
            }
            if (! Schema::hasColumn('profiles', 'hidden_sections')) {
                $t->json('hidden_sections')->nullable();                 // sections hidden from other viewers
            }
        });
    }
 
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $t) {
            $t->dropColumn(['status', 'work_description', 'hidden_sections']);
        });
    }
};
