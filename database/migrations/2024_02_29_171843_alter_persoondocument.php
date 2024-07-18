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
        Schema::table('persoondocument', function (Blueprint $table) {
            $table->dropPrimary(['personenID','documentenID']);
            $table->dropColumn('personenID');
            $table->dropColumn('documentenID');
            $table->unsignedBigInteger('persoonID');
            $table->unsignedBigInteger('documentID');
            $table->primary(['persoonID','documentID']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persoondocument', function (Blueprint $table) {
            $table->dropPrimary(['persoonID','documentID']);
            $table->dropColumn('persoonID');
            $table->dropColumn('documentID');
            $table->unsignedBigInteger('personenID');
            $table->unsignedBigInteger('documentenID');
            $table->primary(['personenID','documentenID']);

        });
    }
    
};
