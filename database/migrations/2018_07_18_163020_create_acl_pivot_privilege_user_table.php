<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAclPivotPrivilegeUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acl_pivot_privilege_user', function (Blueprint $table) {
            $table->string('privilege_id', \Omadonex\LaravelSupport\Classes\ConstantsCustom::DB_FIELD_LEN_PRIMARY_STR)->index();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedTinyInteger('assign_type')->default(\Omadonex\LaravelAcl\Classes\ConstantsAcl::ASSIGN_TYPE_SYSTEM)->index();
            $table->unsignedInteger('assigner_user_id')->nullable()->index();
            $table->timestamp('starting_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();

            $table->unique(['privilege_id', 'user_id'], 'privilege_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acl_pivot_privilege_user');
    }
}
