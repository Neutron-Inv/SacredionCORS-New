<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cors_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cors_station_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('ext', 32)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('path');
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedTinyInteger('day')->nullable();
            $table->timestamp('observed_at')->nullable();
            $table->timestamps();

            $table->unique(['cors_station_id', 'path']);
            $table->index(['cors_station_id', 'year', 'month', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cors_files');
    }
};
