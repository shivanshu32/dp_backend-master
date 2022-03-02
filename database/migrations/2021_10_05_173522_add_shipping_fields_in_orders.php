<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingFieldsInOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_order_id', 30)->nullable();

            $table->string('carriers_code', 30)->nullable();
            $table->string('package_code', 30)->nullable();

            $table->integer('package_size')->default(0);
            $table->integer('package_length')->default(0);
            $table->integer('package_width')->default(0);
            $table->string('package_unit', 20)->nullable();

            $table->string('shipping_confirmation', 20)->nullable();

            $table->integer('weight_value')->default(0);
            $table->string('weight_unit', 20)->nullable();
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
            $table->dropColumn('shipping_order_id');
            $table->dropColumn('shipping_order_id');
            $table->dropColumn('package_code');
            $table->dropColumn('package_size');
            $table->dropColumn('package_length');
            $table->dropColumn('package_width');
            $table->dropColumn('package_unit');
            $table->dropColumn('shipping_confirmation');
            $table->dropColumn('weight_value');
            $table->dropColumn('weight_unit');
        });
    }
}
