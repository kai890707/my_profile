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
        Schema::create('contact_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('salesperson_id')->constrained('users')->onDelete('cascade');
            $table->enum('event_type', ['profile_view', 'contact_form_submission']);
            $table->char('ip_address_hash', 64); // SHA256 hash = 64 characters
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // No updated_at column (events are immutable)

            // Composite index for salesperson stats (event type by time)
            $table->index(['salesperson_id', 'event_type', 'created_at'], 'idx_salesperson_type_created');

            // Composite index for user behavior tracking
            $table->index(['user_id', 'event_type', 'created_at'], 'idx_user_type_created');

            // Index for global event stats
            $table->index(['event_type', 'created_at'], 'idx_event_type_created');

            // Index for IP-based analysis (unique visitors)
            $table->index('ip_address_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_events');
    }
};
