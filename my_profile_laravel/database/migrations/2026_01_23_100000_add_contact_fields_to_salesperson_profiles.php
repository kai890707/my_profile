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
        Schema::table('salesperson_profiles', function (Blueprint $table): void {
            // Add contact method fields after phone
            $table->string('email_public', 255)->nullable()->after('phone');
            $table->string('line_id', 50)->nullable()->after('email_public');
            $table->string('wechat_id', 50)->nullable()->after('line_id');
            $table->json('contact_preferences')->nullable()->after('wechat_id');

            // Add indexes for search optimization
            $table->index('email_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesperson_profiles', function (Blueprint $table): void {
            // Drop indexes first
            $table->dropIndex(['email_public']);

            // Drop columns
            $table->dropColumn([
                'email_public',
                'line_id',
                'wechat_id',
                'contact_preferences',
            ]);
        });
    }
};
