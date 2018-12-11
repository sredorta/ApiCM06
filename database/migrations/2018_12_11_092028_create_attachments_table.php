<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attachable_type');
            $table->integer('attachable_id')->unsigned();
            $table->string('url',500);
            $table->string('alt_text',100)->default("Alt text");
            $table->string('title',100)->nullable(true)->default(null);
            $table->string('description',200)->nullable(true)->default(null);
            $table->string('mime_type',100);     //Type if is document or image...
            $table->string('file_name',500);
            $table->string('file_extension',50);
            $table->integer('file_size')->unsigned();
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
        Schema::dropIfExists('attachments');
    }
}
