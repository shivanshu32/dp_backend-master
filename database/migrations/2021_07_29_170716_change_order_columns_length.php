<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOrderColumnsLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function ($table) {
            $table->string('item_number_1', 150)->nullable()->change();
            $table->string('item_number_2', 150)->nullable()->change();
            $table->string('item_number_3', 150)->nullable()->change();
            $table->string('item_number_4', 150)->nullable()->change();
            $table->string('item_number_5', 150)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function ($table) {
            $table->string('item_number_1', 30)->nullable()->change();
            $table->string('item_number_2', 30)->nullable()->change();
            $table->string('item_number_3', 30)->nullable()->change();
            $table->string('item_number_4', 30)->nullable()->change();
            $table->string('item_number_5', 30)->nullable()->change();
        });
    }
}
