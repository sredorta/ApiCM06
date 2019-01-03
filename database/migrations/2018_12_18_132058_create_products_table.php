<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->integer('modele_id')->unsigned();
            $table->foreign('modele_id')->references('id')->on('modeles')->onDelete('cascade');  
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
        Schema::dropIfExists('products');
    }
}
