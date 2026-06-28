<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('position');
            $table->date('term_start')->nullable();
            $table->date('term_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['position', 'is_active']);
            $table->index(['term_start', 'term_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officers');
    }
};
