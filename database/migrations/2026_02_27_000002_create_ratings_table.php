<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rater_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rated_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('stars'); // 1 to 5
            $table->timestamps();

            // A user can only rate another user once
            $table->unique(['rater_id', 'rated_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
