<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_agency', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_agency');
    }
};
