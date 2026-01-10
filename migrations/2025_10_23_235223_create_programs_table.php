<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');                      // عنوان البرنامج
            $table->string('slug')->unique();             // رابط ودّي
            $table->string('short_description')->nullable(); // وصف قصير
            $table->text('long_description')->nullable();    // وصف طويل (HTML مسموح)
            $table->string('duration')->nullable();       // مدة (مثال: 3 أسابيع)
            $table->string('certificate')->nullable();    // اسم الشهادة/الاعتماد
            $table->string('image')->nullable();          // مسار صورة العرض (public/assets/...)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
