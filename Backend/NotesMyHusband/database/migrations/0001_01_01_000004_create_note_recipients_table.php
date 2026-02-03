<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained('notes')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['note_id', 'recipient_id']);
        });
        Schema::table('note_recipients', function (Blueprint $table) {
            $table->index('note_id');
            $table->index('recipient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_recipients');
    }
};
