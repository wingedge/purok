<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('community_funding_donations')) {
            Schema::create('community_funding_donations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('community_funding_event_id');
                $table->unsignedBigInteger('member_id');
                $table->decimal('amount', 10, 2);
                $table->date('received_at');
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        $this->addConstraintIfMissing(function (): void {
            Schema::table('community_funding_donations', function (Blueprint $table) {
                $table->index('received_at', 'cf_donations_received_at_idx');
            });
        });

        $this->addConstraintIfMissing(function (): void {
            Schema::table('community_funding_donations', function (Blueprint $table) {
                $table->foreign('community_funding_event_id', 'cf_donations_event_fk')
                    ->references('id')
                    ->on('community_funding_events')
                    ->cascadeOnDelete();
            });
        });

        $this->addConstraintIfMissing(function (): void {
            Schema::table('community_funding_donations', function (Blueprint $table) {
                $table->foreign('member_id', 'cf_donations_member_fk')
                    ->references('id')
                    ->on('members')
                    ->cascadeOnDelete();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_funding_donations');
    }

    private function addConstraintIfMissing(callable $callback): void
    {
        try {
            $callback();
        } catch (QueryException $exception) {
            $errorCode = (int) ($exception->errorInfo[1] ?? 0);

            if (! in_array($errorCode, [1061, 1826], true)) {
                throw $exception;
            }
        }
    }
};
