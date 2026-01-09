<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salesperson_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('full_name', 200);
            $table->string('phone', 20)->nullable();
            $table->text('bio')->nullable();
            $table->text('specialties')->nullable();
            $table->json('service_regions')->nullable();
            $table->string('avatar_mime', 50)->nullable();
            $table->unsignedInteger('avatar_size')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejected_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('company_id');
            $table->index('approval_status');

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // Add MEDIUMBLOB column using raw SQL (database-specific)
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE salesperson_profiles ADD COLUMN avatar_data BLOB NULL');
        } else {
            DB::statement('ALTER TABLE salesperson_profiles ADD COLUMN avatar_data MEDIUMBLOB NULL AFTER service_regions');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesperson_profiles');
    }
};
