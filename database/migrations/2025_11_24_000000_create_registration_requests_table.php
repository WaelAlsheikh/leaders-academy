<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistrationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('program_type')->nullable(); // program | training
            $table->unsignedBigInteger('program_id')->nullable();
            $table->string('program_title')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->string('source')->nullable(); // eg. "Website - Leaders Institute"
            $table->string('status')->default('new'); // new, reviewed, contacted, etc.
            $table->json('meta')->nullable(); // any extra data as JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('registration_requests');
    }
}
