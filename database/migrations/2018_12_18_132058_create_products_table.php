<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Modele;
use App\Brand;
use App\Product;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title',100);
            $table->string('description',500)->nullable();
            $table->float('price',8,2)->unsigned();
            $table->float('discount',8,2)->unsigned()->default(0);
            $table->integer('stock')->unsigned();
            $table->boolean('isVehicle');
            $table->boolean('isNew')->default(false);
            $table->float('weight')->default(0.0);
            $table->boolean('isDeliverable')->default(true);
            $table->integer('modele_id')->unsigned();
            $table->foreign('modele_id')->references('id')->on('modeles')->onDelete('cascade');  
            $table->timestamps(); 
        });

        //Only for test
        $brand = Brand::create(["name"=>"BMWTEST"]);
        $modele = $brand->modeles()->create(['name' => "BM100TEST"]);
        for ($x = 0; $x<1000;$x++) {
        Product::create(['title'=> 'test'.$x, 'description'=>'This is a test description to increase data length' ,'price'=>100, 'stock'=>1, 'weight'=>1.2, 'isVehicle'=>true, 'modele_id'=>$modele->id]);
        Product::create(['title'=> 'test'.$x, 'description'=>'This is a test description to increase data length' ,'price'=>200, 'stock'=>3, 'discount'=>100, 'weight'=>0.1, 'isVehicle'=>false, 'modele_id'=>$modele->id]);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
