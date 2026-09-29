<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            // Daily-rotating anonymous visitor key (see App\Support\Analytics\Visitor); replaces storing raw IPs.
            $table->string('visitor_hash', 64)->nullable()->index();
            $table->string('session_id', 64)->nullable()->index();
            $table->string('device', 16)->nullable();
            $table->string('os', 30)->nullable();
            $table->string('lang', 8)->nullable();
            $table->string('referrer_host', 120)->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
        });

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40)->index();
            $table->string('label', 100)->nullable();
            $table->string('path', 255)->nullable();
            $table->string('visitor_hash', 64)->nullable()->index();
            $table->string('session_id', 64)->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
        Schema::table('page_visits', function (Blueprint $table) {
            $table->dropIndex(['visitor_hash']);
            $table->dropIndex(['session_id']);
            $table->dropColumn(['visitor_hash', 'session_id', 'device', 'os', 'lang', 'referrer_host', 'utm_source', 'utm_medium', 'utm_campaign']);
        });
    }
};
