<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInputFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('input_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('type_id');
            $table->integer('question_id');
            $table->string('name');
            $table->string('label');
            $table->text('description')->nullable();
            $table->text('validations')->nullable();
            $table->text('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('input_fields');
    }
}
