<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->integer('sequence')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->unique('package_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_packages');
    }
};
