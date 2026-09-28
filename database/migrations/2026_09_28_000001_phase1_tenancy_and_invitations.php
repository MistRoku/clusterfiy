<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tenant scoping columns (denormalised company_id for global-scope filtering)
        if (! Schema::hasColumn('comments', 'company_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('commentable_id')->constrained()->cascadeOnDelete();
                $table->index('company_id');
            });
        }

        if (! Schema::hasColumn('time_entries', 'company_id')) {
            Schema::table('time_entries', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('task_id')->constrained()->cascadeOnDelete();
                $table->index('company_id');
            });
        }

        if (! Schema::hasColumn('task_status_changes', 'company_id')) {
            Schema::table('task_status_changes', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('task_id')->constrained()->cascadeOnDelete();
                $table->index('company_id');
            });
        }

        if (! Schema::hasColumn('activity_logs', 'company_id')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
                $table->index('company_id');
            });
        }

        // Plan / subscription columns on companies
        if (! Schema::hasColumn('companies', 'plan')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('plan')->default('free')->after('is_active');
                $table->timestamp('trial_ends_at')->nullable();
            });
        }

        // Company invitations
        if (! Schema::hasTable('company_invitations')) {
            Schema::create('company_invitations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('email');
                $table->string('role')->default('member');
                $table->string('token', 64)->unique();
                $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('expires_at');
                $table->timestamp('accepted_at')->nullable();
                $table->timestamps();
                $table->index(['company_id', 'email']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_invitations');

        foreach (['comments', 'time_entries', 'task_status_changes', 'activity_logs'] as $t) {
            if (Schema::hasColumn($t, 'company_id')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('company_id');
                });
            }
        }

        if (Schema::hasColumn('companies', 'plan')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn(['plan', 'trial_ends_at']);
            });
        }
    }
};
