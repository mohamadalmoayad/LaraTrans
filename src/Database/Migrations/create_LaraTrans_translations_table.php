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
        $tableName = config('laratrans.table_name', 'laratrans_translations');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->morphs('translatable');
            $table->string('property_name');
            $table->string('locale');
            $table->text('value');  // Changed from string to text for longer translations
            $table->timestamps();

            // Create a unique compound index to prevent duplicate translations
            $table->unique(
                ['translatable_type', 'translatable_id', 'property_name', 'locale'],
                'unique_translation'
            );

            // Index for faster queries when filtering by locale
            $table->index('locale');

            // Index for property name searches
            $table->index('property_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('laratrans.table_name', 'laratrans_translations');
        Schema::dropIfExists($tableName);
    }
};