<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            // This stores the ID of the email inside our own database
        $table->unsignedBigInteger('parent_id')->nullable()->after('id');
        
        // Optional: Store the raw 'In-Reply-To' string from Gmail for safety
        $table->string('in_reply_to_id')->nullable()->after('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            //
        });
    }
};
