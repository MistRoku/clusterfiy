<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('email');
            $table->boolean('is_master_admin')->default(false)->after('is_super_admin');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete()->after('is_master_admin');
            $table->string('timezone')->default('UTC')->after('company_id');
            $table->string('avatar')->nullable()->after('timezone');
            $table->timestamp('last_login_at')->nullable()->after('avatar');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->string('last_login_device')->nullable()->after('last_login_ip');
            $table->integer('failed_login_attempts')->default(0)->after('last_login_device');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_super_admin',
                'is_master_admin',
                'company_id',
                'timezone',
                'avatar',
                'last_login_at',
                'last_login_ip',
                'last_login_device',
                'failed_login_attempts',
                'locked_until'
            ]);
        });
    }
};
