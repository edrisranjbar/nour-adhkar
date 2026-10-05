<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The team's reply and «like» on in-app feedback; signed-in senders see both in the app. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_feedback', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('message');
            $table->timestamp('replied_at')->nullable()->after('reply');
            $table->timestamp('liked_at')->nullable()->after('replied_at');
        });
    }

    public function down(): void
    {
        Schema::table('app_feedback', function (Blueprint $table) {
            $table->dropColumn(['reply', 'replied_at', 'liked_at']);
        });
    }
};
