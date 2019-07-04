<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationBarangaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_barangays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('location_id');
            $table->string('name');
            $table->string('center');
            $table->float('area')->nullable();
            $table->timestamps();
        });

        \App\Services\CoordinateService::generateBarangays();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_barangays');
    }
}
