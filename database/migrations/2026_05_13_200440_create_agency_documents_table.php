<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('document_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_documents');
    }
};
