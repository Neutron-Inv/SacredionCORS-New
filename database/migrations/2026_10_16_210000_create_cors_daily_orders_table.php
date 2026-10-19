<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cors_daily_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cors_station_id')->constrained()->cascadeOnDelete();
            $table->date('observed_on');
            $table->unsignedInteger('file_count')->default(0);
            $table->unsignedInteger('amount');
            $table->char('currency', 3);
            $table->string('status', 20)->default('pending');
            $table->string('payment_reference')->unique();
            $table->string('payment_access_code')->nullable();
            $table->string('payment_channel')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('archive_path')->nullable();
            $table->string('archive_name')->nullable();
            $table->unsignedBigInteger('archive_size')->nullable();
            $table->string('download_token', 80)->unique();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('ready_notified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'cors_station_id', 'observed_on']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cors_daily_orders');
    }
};
