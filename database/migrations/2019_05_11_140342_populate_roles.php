<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class PopulateRoles extends Migration
{
    const ROLES = [
        'guest',
        'user',
        'admin'
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::ROLES as $role) {
            DB::table('roles')->insert([
                'name'          =>  $role,
                'created_at'    =>  \Carbon\Carbon::now()
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
        foreach (self::ROLES as $role) {
            DB::table('roles')->where('name', $role)->delete();
        }
    }
}
