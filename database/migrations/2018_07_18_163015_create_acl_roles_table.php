<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAclRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acl_roles', function (Blueprint $table) {
            \Omadonex\LaravelSupport\Classes\Utils\UtilsDb::addPrimaryStr($table);

            $table->boolean('is_root')->default(false)->index();
            $table->boolean('is_staff')->default(false)->index();

            \Omadonex\LaravelSupport\Classes\Utils\UtilsDb::addProtectedGenerateField($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acl_roles');
    }
}
