<?php

declare(strict_types=1);

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
        Schema::create('approval_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('approvable_type', 50);
            $table->unsignedBigInteger('approvable_id');
            $table->enum('action', ['approved', 'rejected']);
            $table->unsignedBigInteger('admin_id');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes for polymorphic relationship
            $table->index(['approvable_type', 'approvable_id']);
            $table->index('admin_id');

            // Foreign key
            $table->foreign('admin_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};
