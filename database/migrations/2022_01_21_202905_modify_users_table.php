<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('company', 50)->nullable();
            $table->string('street_appartment', 50)->nullable();
            $table->string('shipping_last_name', 50)->nullable();
            $table->string('shipping_company', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('company');
            $table->dropColumn('street_appartment');
            $table->dropColumn('shipping_last_name');
            $table->dropColumn('shipping_company');
        });
    }
}
