<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['company_id', 'status', 'due_date']);
            $table->index(['company_id', 'created_at']);
            $table->index(['assigned_to', 'status']);
            $table->index(['company_id', 'priority']);
        });
        Schema::table('time_entries', function (Blueprint $table) {
            $table->index(['task_id', 'user_id', 'started_at']);
            $table->index(['user_id', 'started_at']);
        });
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['company_id', 'created_at']);
            $table->index(['loggable_type', 'loggable_id']);
        });
        Schema::table('login_history', function (Blueprint $table) {
            $table->index(['user_id', 'login_at']);
        });
        Schema::table('task_assignees', function (Blueprint $table) {
            $table->index(['task_id', 'user_id']);
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->index(['commentable_type', 'commentable_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'status', 'due_date']);
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['company_id', 'priority']);
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropIndex(['task_id', 'user_id', 'started_at']);
            $table->dropIndex(['user_id', 'started_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropIndex(['loggable_type', 'loggable_id']);
        });

        Schema::table('login_history', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'login_at']);
        });

        Schema::table('task_assignees', function (Blueprint $table) {
            $table->dropIndex(['task_id', 'user_id']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['commentable_type', 'commentable_id']);
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};
