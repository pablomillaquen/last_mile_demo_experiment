<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiments', function (Blueprint $table) {
            $table->id();
            $table->string('identifier', 100)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->text('objective');
            $table->text('hypothesis')->nullable();
            $table->foreignId('baseline_evaluation_id')->nullable()->constrained('evaluations');
            $table->jsonb('evaluation_ids')->default('[]');
            $table->string('author', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiments');
    }
};
