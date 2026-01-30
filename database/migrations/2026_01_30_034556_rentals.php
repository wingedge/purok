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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->string('renter_name');
            $table->string('renter_contact');
            $table->integer('quantity');
            $table->date('rent_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['rented', 'returned'])->default('rented');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
