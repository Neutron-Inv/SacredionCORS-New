<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cors_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ftp_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 8);
            $table->string('provider')->nullable();
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('state_name');
            $table->string('region');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('map_embed_url')->nullable();
            $table->string('ftp_file_prefix')->nullable();
            $table->string('ftp_local_directory')->nullable();
            $table->string('legacy_table')->nullable();
            $table->boolean('is_external')->default(false);
            $table->string('external_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cors_stations');
    }
};
