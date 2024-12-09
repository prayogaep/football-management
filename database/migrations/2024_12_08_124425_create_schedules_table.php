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
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('home_team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreignUuid('away_team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->date('date_schedule');
            $table->time('time_schedule');
            $table->foreignUuid('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
