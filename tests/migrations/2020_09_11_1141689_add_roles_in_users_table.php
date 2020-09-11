<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRolesInUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('roles')->nullable();
        });
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'roles')) {
            Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('roles');
            });
        }
    }
}
