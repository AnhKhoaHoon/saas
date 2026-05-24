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
        Schema::table('team_invites', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('accepted_at');
            $table->index(['project_id', 'cancelled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_invites', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'cancelled_at']);
            $table->dropColumn('cancelled_at');
        });
    }
};
