<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('salesperson_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('contact_request_id')->constrained()->onDelete('cascade');
            $table->decimal('rating', 2, 1); // 1.0 - 5.0 with 0.5 increments
            $table->text('comment')->nullable();
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->boolean('is_hidden')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->integer('edit_count')->default(0);
            $table->string('ip_address', 45); // IPv6 max length
            $table->timestamps();

            // Indexes for performance
            $table->index(['salesperson_id', 'status', 'is_hidden']); // For public listing
            $table->index(['user_id', 'salesperson_id']); // For duplicate check
            $table->index('ip_address'); // For IP-based spam detection
            $table->unique(['user_id', 'salesperson_id']); // One rating per user-salesperson pair
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
