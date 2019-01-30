<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();   //Store user id if ordered with account
            $table->string('firstName',50);
            $table->string('lastName',50);
            $table->string('email',100); //Email max length is 191 chars and cannot be changed
            $table->string('mobile',10);
            $table->boolean('delivery');
            $table->string('address1',200)->nullable();
            $table->string('address2',200)->nullable();
            $table->string('cp',10)->nullable();
            $table->string('city',100)->nullable();
            $table->float('price');
            $table->float('deliveryCost');
            $table->float('total');
            $table->string('cart',1000);
            $table->string('status',50)->default('preorder');
            $table->string('paypalOrderId');
            $table->string('paypalPaymentId');
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