<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Omadonex\LaravelSupport\Classes\Utils\UtilsDb;

class CreateAclPermissionGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acl_permission_groups', function (Blueprint $table) {
            UtilsDb::addPrimaryStr($table);
            $table->string('parent_id')->nullable()->index();
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
        Schema::dropIfExists('acl_permission_groups');
    }
}
