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
        Schema::create('goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('match_result_id')->references('id')->on('match_results')->onDelete('cascade');
            $table->foreignUuid('player_id')->references('id')->on('players')->onDelete('cascade');
            $table->foreignUuid('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->integer('minute');
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
        Schema::dropIfExists('goals');
    }
};
