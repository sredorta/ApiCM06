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
        Configuration::create(['key' => 'address', 'value'=>null]);
        Configuration::create(['key' => 'email', 'value'=>null]);
        Configuration::create(['key' => 'phone', 'value'=>null]);
        Configuration::create(['key' => 'latitude', 'value'=>0]);
        Configuration::create(['key' => 'longitude', 'value'=>0]);
        Configuration::create(['key' => 'zoom', 'value'=>14]);
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
