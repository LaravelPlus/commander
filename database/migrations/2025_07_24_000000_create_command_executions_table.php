<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('command_executions', function (Blueprint $table): void {
            $table->id();
            $table->string('command_name');
            $table->text('arguments')->nullable();
            $table->text('options')->nullable();
            $table->text('output')->nullable();
            $table->integer('return_code')->default(0);
            $table->boolean('success')->default(true);
            $table->string('executed_by')->nullable(); // User who executed the command
            $table->string('environment')->default('production');
            $table->decimal('execution_time', 8, 3)->nullable(); // Execution time in seconds
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['command_name', 'started_at']);
            $table->index(['success', 'started_at']);
            $table->index(['executed_by', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('command_executions');
    }
};
