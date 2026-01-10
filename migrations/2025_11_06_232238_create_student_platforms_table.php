<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentPlatformsTable extends Migration
{
    public function up()
    {
        Schema::create('student_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('icon')->nullable(); // اسم ملف أيقونة أو كلاس أيقون
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('file')->nullable(); // إن أردت ملف/صورة
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_platforms');
    }
}
