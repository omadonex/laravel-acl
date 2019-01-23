<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAclRoleTranslatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acl_role_translates', function (Blueprint $table) {
            \Omadonex\LaravelSupport\Classes\Utils\UtilsDb::addTransFields($table, true);

            $table->string('name');
            $table->text('description');

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
        Schema::dropIfExists('acl_role_translates');
    }
}
