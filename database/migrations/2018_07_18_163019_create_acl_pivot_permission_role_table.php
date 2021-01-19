<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Omadonex\LaravelSupport\Classes\ConstCustom;
use Omadonex\LaravelSupport\Classes\Utils\UtilsDb;

class CreateAclPivotPermissionRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acl_pivot_permission_role', function (Blueprint $table) {
            UtilsDb::addProtectedGenerateField($table);
            $table->string('permission_id', ConstCustom::DB_FIELD_LEN_STR_KEY)->index();
            $table->string('role_id', ConstCustom::DB_FIELD_LEN_STR_KEY)->index();

            $table->unique(['permission_id', 'role_id'], 'permission_role_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acl_pivot_permission_role');
    }
}
