<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cors_stations', function (Blueprint $table) {
            $table->string('hero_text')->nullable()->after('provider');
            $table->string('partner_logo_primary')->nullable()->after('hero_text');
            $table->string('partner_logo_secondary')->nullable()->after('partner_logo_primary');
        });
    }

    public function down(): void
    {
        Schema::table('cors_stations', function (Blueprint $table) {
            $table->dropColumn(['hero_text', 'partner_logo_primary', 'partner_logo_secondary']);
        });
    }
};
