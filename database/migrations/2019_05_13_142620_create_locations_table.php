<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('center');
            $table->float('area');
            $table->timestamps();
        });

        DB::table('locations')->insert([
            'name'      =>  'Legazpi City',
            'center'    =>  json_encode([
                'longitude' =>  13.1391,
                'latitude'  =>  123.7438
            ]),
            'area'          =>    204.2,
            'created_at'    =>  \Carbon\Carbon::now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
