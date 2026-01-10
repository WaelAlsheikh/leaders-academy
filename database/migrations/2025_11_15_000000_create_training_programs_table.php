<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingProgramsTable extends Migration
{
    public function up()
    {
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->string('category')->nullable(); // مثال: "تنمية بشرية" أو "مهنية"
            $table->string('duration')->nullable();
            $table->string('certificate')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('training_programs');
    }
}
