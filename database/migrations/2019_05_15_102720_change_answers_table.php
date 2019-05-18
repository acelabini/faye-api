<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->renameColumn('set_id', 'questionnaire_id');
            $table->renameColumn('question_id', 'field_id');
            $table->text('device_address')->after('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->renameColumn('questionnaire_id', 'set_id');
            $table->renameColumn('field_id', 'question_id');
            $table->dropColumn('device_address');
        });
    }
}
