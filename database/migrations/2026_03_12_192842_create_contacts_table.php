<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('contacts');

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['pending', 'read', 'replied'])->default('pending');
            $table->timestamp('replied_at')->nullable();

            // ✅ Now unsignedBigInteger to match users.id
            $table->unsignedBigInteger('replied_by')->nullable();
            $table->timestamps();
           
        });

        
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreign('replied_by')
                  ->references('id')       // ✅ users.id
                  ->on('users')            // ✅ users table
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['replied_by']);
        });
        Schema::dropIfExists('contacts');
    }
};