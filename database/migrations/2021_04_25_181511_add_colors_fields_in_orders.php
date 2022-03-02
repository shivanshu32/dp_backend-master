<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColorsFieldsInOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('color_1_pantone')->nullable();
            $table->string('color_2_pantone')->nullable();
            $table->string('color_3_pantone')->nullable();
            $table->string('color_4_pantone')->nullable();
            $table->string('color_5_pantone')->nullable();
            $table->string('color_6_pantone')->nullable();

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
            $table->dropColumn('color_1_pantone');
            $table->dropColumn('color_2_pantone');
            $table->dropColumn('color_3_pantone');
            $table->dropColumn('color_4_pantone');
            $table->dropColumn('color_5_pantone');
            $table->dropColumn('color_6_pantone');
        });
    }
}
