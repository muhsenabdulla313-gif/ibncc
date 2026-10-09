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
    Schema::table('users', function (Blueprint $t) {
        $t->uuid('uuid')->nullable()->unique()->after('id');
    });

    // give existing users one
    DB::table('users')->whereNull('uuid')->orderBy('id')->each(function ($u) {
        DB::table('users')->where('id', $u->id)->update(['uuid' => (string) Str::uuid()]);
    });
}

public function down(): void
{
    Schema::table('users', fn (Blueprint $t) => $t->dropColumn('uuid'));
}
};
