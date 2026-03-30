<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abouts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->integer('established_year')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abouts');
    }
};