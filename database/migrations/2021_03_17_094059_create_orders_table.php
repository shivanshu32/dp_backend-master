<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    // Selecting the Customer
    // Order Name
    // Order Type
    // Arrival / Due Date
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Customer or creator Related Info
            $table->integer('customer_id');
            $table->integer('created_by');


            // Order Related Info
            $table->string('name', 150)->comment('order name');
            $table->string('type', 150)->comment('order type');
            $table->text('type_notes')->nullable();
            $table->boolean('multiple_pages')->default(false);

            
            // Arrival info 
            $table->string('arrival_type', 150)->nullable();
            $table->string('arrival_other', 150)->nullable();
            $table->date('arrival_date')->nullable();
            $table->boolean('hard_due_date')->default(false);


            // Arrival info 
            $table->boolean('art_is_sized')->default(false);
            $table->string('art_notes', 150)->nullable();


            // Color Info 
            $table->text('color_1')->nullable();
            $table->text('color_2')->nullable();
            $table->text('color_3')->nullable();
            $table->text('color_4')->nullable();
            $table->text('color_5')->nullable();
            $table->text('color_6')->nullable();


            // Payment Info
            $table->text('payment_invoice_url')->nullable();
            $table->text('payment_notes')->nullable();
            $table->boolean('payment_terms')->default(false);

            // Ship Info
            $table->text('ship_type')->nullable();
            $table->text('ship_notes')->nullable();
            $table->boolean('ship_terms')->default(false);

            // Shipping User info
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_attn', 150)->nullable();
            $table->text('customer_track_url')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_state', 150)->nullable();
            $table->string('customer_zipcode', 150)->nullable();
            $table->string('customer_email', 150)->nullable();
            $table->string('customer_phone', 150)->nullable();
            $table->text('shipping_label_url')->nullable();
            $table->text('packing_list_url')->nullable();
         

            
            // Setup related info
            $table->string('setup_name', 150)->nullable();
            $table->string('setup_screen_1', 150)->nullable();
            $table->string('setup_screen_2', 150)->nullable();
            $table->text('setup_notes')->nullable();

            // Proof Related info
            $table->text('proof_url')->nullable();
            $table->text('proof_notes')->nullable();

            // Position related info
            $table->string('position_front', 150)->nullable();
            $table->string('position_back', 150)->nullable();
            $table->string('position_right_left')->nullable();
            $table->string('position_additional')->nullable();
            $table->text('position_notes')->nullable();
            $table->boolean('match_proof_position')->default(false);

             // product related info
             $table->string('product_user_type_1', 150)->nullable();
             $table->string('product_user_other_type_1', 150)->nullable();
             $table->string('per_piece_1', 20)->nullable();
             $table->boolean('tax_1')->default(false);
             $table->string('item_number_1', 30)->nullable();
             $table->string('apparel_type_1', 30)->nullable();
             $table->string('product_color_1', 20)->nullable();
             $table->text('product_description_1')->nullable();
             $table->string('product_apparel_source_1', 30)->nullable();
             $table->string('product_apparel_source_other_1', 50)->nullable();
             $table->string('xs_1', 10)->nullable();
             $table->string('s_1', 10)->nullable();
             $table->string('m_1', 10)->nullable();
             $table->string('l_1', 10)->nullable();
             $table->string('xl_1', 10)->nullable();
             $table->string('xxl_1', 10)->nullable();
             $table->string('xxxl_1', 10)->nullable();
             $table->string('other_size_1_1', 30)->nullable();
             $table->string('other_size_text_1_1', 30)->nullable();
             $table->string('pcs_1_1', 30)->nullable();
             $table->string('other_size_2_1', 30)->nullable();
             $table->string('other_size_text_2_1', 30)->nullable();
             $table->string('pcs_2_1', 30)->nullable();
             $table->string('other_size_3_1', 30)->nullable();
             $table->string('other_size_text_3_1', 30)->nullable();
             $table->string('pcs_3_1', 30)->nullable();
             $table->string('other_size_4_1', 30)->nullable();
             $table->string('other_size_text_4_1', 30)->nullable();
             $table->string('pcs_4_1', 30)->nullable();
 
             $table->string('product_user_type_2', 150)->nullable();
             $table->string('product_user_other_type_2', 150)->nullable();
             $table->string('per_piece_2', 20)->nullable();
             $table->boolean('tax_2')->default(false);
             $table->string('item_number_2', 30)->nullable();
             $table->string('apparel_type_2', 30)->nullable();
             $table->string('product_color_2', 20)->nullable();
             $table->text('product_description_2')->nullable();
             $table->string('product_apparel_source_2', 30)->nullable();
             $table->string('product_apparel_source_other_2', 50)->nullable();
             $table->string('xs_2', 30)->nullable();
             $table->string('s_2', 30)->nullable();
             $table->string('m_2', 30)->nullable();
             $table->string('l_2', 30)->nullable();
             $table->string('xl_2', 30)->nullable();
             $table->string('xxl_2', 30)->nullable();
             $table->string('xxxl_2', 30)->nullable();
             $table->string('other_size_1_2', 30)->nullable();
             $table->string('other_size_text_1_2', 30)->nullable();
             $table->string('pcs_1_2', 30)->nullable();
             $table->string('other_size_2_2', 30)->nullable();
             $table->string('other_size_text_2_2', 30)->nullable();
             $table->string('pcs_2_2', 30)->nullable();
             $table->string('other_size_3_2', 30)->nullable();
             $table->string('other_size_text_3_2', 30)->nullable();
             $table->string('pcs_3_2', 30)->nullable();
             $table->string('other_size_4_2', 30)->nullable();
             $table->string('other_size_text_4_2', 30)->nullable();
             $table->string('pcs_4_2', 30)->nullable();


             $table->string('product_user_type_3', 150)->nullable();
             $table->string('product_user_other_type_3', 150)->nullable();
             $table->string('per_piece_3', 20)->nullable();
             $table->boolean('tax_3')->default(false);
             $table->string('item_number_3', 30)->nullable();
             $table->string('apparel_type_3', 30)->nullable();
             $table->string('product_color_3', 20)->nullable();
             $table->text('product_description_3')->nullable();
             $table->string('product_apparel_source_3', 30)->nullable();
             $table->string('product_apparel_source_other_3', 50)->nullable();
             $table->string('xs_3', 30)->nullable();
             $table->string('s_3', 30)->nullable();
             $table->string('m_3', 30)->nullable();
             $table->string('l_3', 30)->nullable();
             $table->string('xl_3', 30)->nullable();
             $table->string('xxl_3', 30)->nullable();
             $table->string('xxxl_3', 30)->nullable();
             $table->string('other_size_1_3', 30)->nullable();
             $table->string('other_size_text_1_3', 30)->nullable();
             $table->string('pcs_1_3', 30)->nullable();
             $table->string('other_size_2_3', 30)->nullable();
             $table->string('other_size_text_2_3', 30)->nullable();
             $table->string('pcs_2_3', 30)->nullable();
             $table->string('other_size_3_3', 30)->nullable();
             $table->string('other_size_text_3_3', 30)->nullable();
             $table->string('pcs_3_3', 30)->nullable();
             $table->string('other_size_4_3', 30)->nullable();
             $table->string('other_size_text_4_3', 30)->nullable();
             $table->string('pcs_4_3', 30)->nullable();


             $table->string('product_user_type_4', 150)->nullable();
             $table->string('product_user_other_type_4', 150)->nullable();
             $table->string('per_piece_4', 20)->nullable();
             $table->boolean('tax_4')->default(false);
             $table->string('item_number_4', 30)->nullable();
             $table->string('apparel_type_4', 30)->nullable();
             $table->string('product_color_4', 20)->nullable();
             $table->text('product_description_4')->nullable();
             $table->string('product_apparel_source_4', 30)->nullable();
             $table->string('product_apparel_source_other_4', 50)->nullable();
             $table->string('xs_4', 30)->nullable();
             $table->string('s_4', 30)->nullable();
             $table->string('m_4', 30)->nullable();
             $table->string('l_4', 30)->nullable();
             $table->string('xl_4', 30)->nullable();
             $table->string('xxl_4', 30)->nullable();
             $table->string('xxxl_4', 30)->nullable();
             $table->string('other_size_1_4', 30)->nullable();
             $table->string('other_size_text_1_4', 30)->nullable();
             $table->string('pcs_1_4', 30)->nullable();
             $table->string('other_size_2_4', 30)->nullable();
             $table->string('other_size_text_2_4', 30)->nullable();
             $table->string('pcs_2_4', 30)->nullable();
             $table->string('other_size_3_4', 30)->nullable();
             $table->string('other_size_text_3_4', 30)->nullable();
             $table->string('pcs_3_4', 30)->nullable();
             $table->string('other_size_4_4', 30)->nullable();
             $table->string('other_size_text_4_4', 30)->nullable();
             $table->string('pcs_4_4', 30)->nullable();


             $table->string('product_user_type_5', 150)->nullable();
             $table->string('product_user_other_type_5', 150)->nullable();
             $table->string('per_piece_5', 20)->nullable();
             $table->boolean('tax_5')->default(false);
             $table->string('item_number_5', 30)->nullable();
             $table->string('apparel_type_5', 30)->nullable();
             $table->string('product_color_5', 20)->nullable();
             $table->text('product_description_5')->nullable();
             $table->string('product_apparel_source_5', 30)->nullable();
             $table->string('product_apparel_source_other_5', 50)->nullable();
             $table->string('xs_5', 30)->nullable();
             $table->string('s_5', 30)->nullable();
             $table->string('m_5', 30)->nullable();
             $table->string('l_5', 30)->nullable();
             $table->string('xl_5', 30)->nullable();
             $table->string('xxl_5', 30)->nullable();
             $table->string('xxxl_5', 30)->nullable();
             $table->string('other_size_1_5', 30)->nullable();
             $table->string('other_size_text_1_5', 30)->nullable();
             $table->string('pcs_1_5', 30)->nullable();
             $table->string('other_size_2_5', 30)->nullable();
             $table->string('other_size_text_2_5', 30)->nullable();
             $table->string('pcs_2_5', 30)->nullable();
             $table->string('other_size_3_5', 30)->nullable();
             $table->string('other_size_text_3_5', 30)->nullable();
             $table->string('pcs_3_5', 30)->nullable();
             $table->string('other_size_4_5', 30)->nullable();
             $table->string('other_size_text_4_5', 30)->nullable();
             $table->string('pcs_4_5', 30)->nullable();

          

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
