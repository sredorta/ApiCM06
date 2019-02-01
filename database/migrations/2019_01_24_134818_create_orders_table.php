<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Order;
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
        for ($x = 0; $x<100;$x++) {

            $cart = json_encode((object)['id'=> 4, 'title' => "test1", 'stock'=> 3, 'weight'=> 0.1, 'price'=> 100, 'url'=> "", 'quantity'=> 2, 'tprice'=> 200]);
        Order::create([
            'user_id'           => 2,
            'firstName'         => 'Sergi',
            'lastName'          => 'Redorta',
            'email'             => 'sergi.redorta@hotmail.com',            
            'mobile'            => '0623133212',
            'delivery'          => true,
            'address1'          => '6,rue roger Avon',
            'address2'          => 'Bat A',
            'cp'                => '06610',
            'city'              => 'La Gaude',
            'total'             => 100,
            'deliveryCost'      => 10,
            'price'             => 90,
            'cart'              => $cart,
            'paypalOrderId'     => 'ORDERID'. $x,
            'paypalPaymentId'   => 'PAYMENTID'. $x,
            'status'            => 'en traitement'
        ]);
        }



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
