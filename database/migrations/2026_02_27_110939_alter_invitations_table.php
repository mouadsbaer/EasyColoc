<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // Check if column exists, though we know it does not in the DB right now
            if (!Schema::hasColumn('invitations', 'sendBy_id')) {
                $table->foreignId('sendBy_id')->after('collocation_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('invitations', 'acceptedBy_id')) {
                $table->foreignId('acceptedBy_id')->after('sendBy_id')->nullable()->constrained('users')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (Schema::hasColumn('invitations', 'sendBy_id')) {
                $table->dropForeign(['sendBy_id']);
                $table->dropColumn('sendBy_id');
            }
            if (Schema::hasColumn('invitations', 'acceptedBy_id')) {
                $table->dropForeign(['acceptedBy_id']);
                $table->dropColumn('acceptedBy_id');
            }
        });
    }
};
