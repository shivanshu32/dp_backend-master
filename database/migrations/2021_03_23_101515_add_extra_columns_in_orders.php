<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraColumnsInOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('pay')->default(false);
            $table->boolean('apparel')->default(false);
            $table->boolean('film')->default(false);
            $table->string('invoice_number')->nullable();
            $table->string('status')->default('Processing');
            $table->integer('printer_id')->nullable();
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
            $table->dropColumns([
                'pay', 'apparel', 'film', 'invoice_number', 'status', 'printer_id'
            ]);
        });
    }
}
