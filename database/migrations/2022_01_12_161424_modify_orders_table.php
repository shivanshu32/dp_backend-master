<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('s_intro_email')->default('false');
            $table->string('s_send_proof')->default('false');
            $table->string('s_proof_approved')->default('false');
            $table->string('s_rush_shipping_paid')->default('false');
            $table->string('s_follow_up')->default('false');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('s_intro_email');
            $table->dropColumn('s_send_proof');
            $table->dropColumn('s_proof_approved');
            $table->dropColumn('s_rush_shipping_paid');
            $table->dropColumn('s_follow_up');
        });
    }
}
