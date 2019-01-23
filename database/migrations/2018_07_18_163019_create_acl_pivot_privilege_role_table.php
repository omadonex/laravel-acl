<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAclPivotPrivilegeRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acl_pivot_privilege_role', function (Blueprint $table) {
            $table->string('privilege_id', \Omadonex\LaravelSupport\Classes\ConstantsCustom::DB_FIELD_LEN_PRIMARY_STR)->index();
            $table->string('role_id', \Omadonex\LaravelSupport\Classes\ConstantsCustom::DB_FIELD_LEN_PRIMARY_STR)->index();

            \Omadonex\LaravelSupport\Classes\Utils\UtilsDb::addProtectedGenerateField($table);

            $table->unique(['privilege_id', 'role_id'], 'privilege_role_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acl_pivot_privilege_role');
    }
}
