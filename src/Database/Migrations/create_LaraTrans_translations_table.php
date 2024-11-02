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
        Schema::create(config('laratrans.table_name', 'LaraTrans_translations'), function (Blueprint $table) {
            $table->id();
            $table->morphs('translatable');
            $table->string('property_name');
            $table->string('locale');
            $table->string('value');
            $table->timestamps();

            // Indexes for translatable_type and translatable_id
            // Check if the index already exists before adding it
            if (!Schema::hasIndex('translations', 'translations_translatable_type_translatable_id_index')) {
                $table->index(['translatable_type', 'translatable_id'], 'translations_translatable_type_translatable_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('laratrans.table_name', 'LaraTrans_translations'));
    }
};
