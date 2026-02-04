<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('incomes', function (Blueprint $table) {
            // constrained() handles the foreign key logic automatically
            // nullOnDelete() ensures if a rental is deleted, the income record stays but loses the link
            $table->foreignId('rental_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            Schema::dropIfExists('rental_id');
        });
    }
};
