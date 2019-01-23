<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAclPivotRoleUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acl_pivot_role_user', function (Blueprint $table) {
            $table->string('role_id', \Omadonex\LaravelSupport\Classes\ConstantsCustom::DB_FIELD_LEN_PRIMARY_STR)->index();
            $table->unsignedInteger('user_id')->index();

            $table->unique(['role_id', 'user_id'], 'role_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acl_pivot_role_user');
    }
}
