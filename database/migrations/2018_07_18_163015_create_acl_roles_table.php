<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Omadonex\LaravelSupport\Classes\Utils\UtilsDb;

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
            UtilsDb::addPrimaryStr($table);
            UtilsDb::addProtectedGenerateField($table);

            $table->boolean('is_root')->default(false)->index();
            $table->boolean('is_staff')->default(false)->index();
            $table->unsignedSmallInteger('order')->default(0)->index();
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
