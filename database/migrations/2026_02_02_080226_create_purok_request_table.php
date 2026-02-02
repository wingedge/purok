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

        Schema::create('purok_certificates', function (Blueprint $table) {
            $table->id();
            // Links to 'id' on 'members' table
            $table->foreignId('member_id')->constrained()->onDelete('cascade');            
            $table->date('request_date');
            $table->text('purpose');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purok_certificates');
    }
};
