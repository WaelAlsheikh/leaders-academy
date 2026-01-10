<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccreditationSectionsTable extends Migration
{
    public function up()
    {
        Schema::create('accreditation_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('short_description')->nullable();
            // سنخزن روابط الآيقونات كـ JSON array: ["uploads/icon1.png","uploads/icon2.png",...]
            $table->json('icons')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accreditation_sections');
    }
}
