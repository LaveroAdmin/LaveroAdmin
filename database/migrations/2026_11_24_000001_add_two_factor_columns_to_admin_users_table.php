<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwoFactorColumnsToAdminUsersTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return config('admin.database.connection') ?: config('database.default');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = config('admin.database.users_table', 'admin_users');

        if (Schema::connection($this->getConnection())->hasTable($table)) {
            Schema::connection($this->getConnection())->table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'two_factor_secret')) {
                    $table->text('two_factor_secret')->nullable()->after('password');
                }
                if (!Schema::hasColumn($table->getTable(), 'two_factor_recovery_codes')) {
                    $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
                }
                if (!Schema::hasColumn($table->getTable(), 'two_factor_confirmed_at')) {
                    $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = config('admin.database.users_table', 'admin_users');

        if (Schema::connection($this->getConnection())->hasTable($table)) {
            Schema::connection($this->getConnection())->table($table, function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn($table->getTable(), 'two_factor_secret')) {
                    $columns[] = 'two_factor_secret';
                }
                if (Schema::hasColumn($table->getTable(), 'two_factor_recovery_codes')) {
                    $columns[] = 'two_factor_recovery_codes';
                }
                if (Schema::hasColumn($table->getTable(), 'two_factor_confirmed_at')) {
                    $columns[] = 'two_factor_confirmed_at';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
}
