<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSectionsToStudentPlatformsTable extends Migration
{
    public function up()
    {
        Schema::table('student_platforms', function (Blueprint $table) {
            $table->json('sections')->nullable()->after('content')->comment('JSON array of sections: [{title,image,summary,content,button_text,button_link}]');
        });
    }

    public function down()
    {
        Schema::table('student_platforms', function (Blueprint $table) {
            $table->dropColumn('sections');
        });
    }
}
