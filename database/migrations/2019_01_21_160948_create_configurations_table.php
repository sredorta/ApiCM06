<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Configuration;

class CreateConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configurations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key',100);
            $table->string('value',500)->nullable();   
            $table->timestamps();
        });
        Configuration::create(['key' => 'message_title', 'value'=>null]);
        Configuration::create(['key' => 'message_text', 'value'=>null]);
        Configuration::create(['key' => 'address', 'value'=>"SARL Casse Moto 06, 80 chemin des cardelines, 06370 Mouans Sartoux, France"]);
        Configuration::create(['key' => 'email', 'value'=>"contact@cassemoto06.fr"]);
        Configuration::create(['key' => 'phone', 'value'=>"0492929180"]);
        Configuration::create(['key' => 'latitude', 'value'=>43.614260]);
        Configuration::create(['key' => 'longitude', 'value'=> 6.959808]);
        Configuration::create(['key' => 'zoom', 'value'=>14]);
        Configuration::create(["key"=>"delivery1", "value"=>10]);
        Configuration::create(["key"=>"delivery2", "value"=>20]);
        Configuration::create(["key"=>"delivery3", "value"=>30]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configurations');
    }
}
