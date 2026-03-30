<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'reply_message')) {
                $table->text('reply_message')->nullable()->after('message');
            }
            
            if (!Schema::hasColumn('contacts', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('reply_message');
            }

            if (!Schema::hasColumn('contacts', 'replied_by')) {
                $table->foreignId('replied_by')->nullable()->constrained('users')->after('replied_at');
            }
        });
    }

    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['replied_by']); // Drop foreign key first
            $table->dropColumn(['reply_message', 'replied_at', 'replied_by']);
        });
    }
};
