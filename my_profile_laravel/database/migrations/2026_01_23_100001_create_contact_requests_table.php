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
        Schema::create('contact_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('salesperson_id')->constrained('users')->onDelete('cascade');
            $table->string('customer_name', 100);
            $table->text('customer_email'); // Will use encrypted cast in Model (encrypted data is longer)
            $table->text('customer_phone')->nullable(); // Will use encrypted cast in Model (encrypted data is longer)
            $table->text('message');
            $table->enum('status', ['pending', 'contacted', 'closed'])->default('pending');
            $table->timestamps();

            // Composite index for rate limiting check (24h same salesperson)
            $table->index(['user_id', 'salesperson_id', 'created_at'], 'idx_user_salesperson_created');

            // Index for salesperson to query their requests
            $table->index(['salesperson_id', 'status', 'created_at'], 'idx_salesperson_status_created');

            // Index for admin to query all requests by time
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_requests');
    }
};
