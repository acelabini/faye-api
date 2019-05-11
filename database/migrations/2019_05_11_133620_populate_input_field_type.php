<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\InputFieldType;
use Illuminate\Support\Facades\DB;

class PopulateInputFieldType extends Migration
{
    const TYPES = [
        'text',
        'number',
        'select',
        'textarea',
        'checkbox',
        'radio',
        'file',
        'date',
        'range',
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::TYPES as $type) {
            DB::table('input_field_type')->insert([
                'type'          =>  $type,
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
        foreach (self::TYPES as $type) {
            DB::table('input_field_type')->where('type', $type)->delete();
        }
    }
}
