<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Omadonex\LaravelAcl\Classes\ConstAcl;
use Omadonex\LaravelSupport\Classes\ConstCustom;

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
            $table->string('role_id', ConstCustom::DB_FIELD_LEN_STR_KEY)->index();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedTinyInteger('assign_type')->default(ConstAcl::ASSIGN_TYPE_SYSTEM)->index();
            $table->unsignedInteger('assign_user_id')->nullable()->index();
            $table->timestamp('assign_starting_at')->nullable()->index();
            $table->timestamp('assign_expires_at')->nullable()->index();

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
