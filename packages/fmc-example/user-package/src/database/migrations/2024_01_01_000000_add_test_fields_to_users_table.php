<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTestFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->boolean('is_verified')->default(0)->nullable();
            $table->string('country')->nullable();
            $table->date('birth_day')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('is_verified');
            $table->dropColumn('country');
            $table->dropColumn('birth_day');
        });
    }
}
