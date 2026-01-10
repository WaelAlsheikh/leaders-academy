<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accreditations', function (Blueprint $table) {
            $table->id();
            $table->string('title');          // اسم الاعتماد / الجهة
            $table->string('logo')->nullable(); // مسار لوجو
            $table->text('description')->nullable();
            $table->string('link')->nullable(); // رابط خارجي
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditations');
    }
};
