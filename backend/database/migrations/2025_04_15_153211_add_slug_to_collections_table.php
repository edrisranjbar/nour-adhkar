<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Generate slugs for existing collections
        DB::table('collections')->orderBy('id')->each(function ($collection) {
            DB::table('collections')
                ->where('id', $collection->id)
                ->update(['slug' => Str::slug($collection->name)]);
        });

        // Now make it unique and not nullable
        Schema::table('collections', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
