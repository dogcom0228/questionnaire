<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('questionnaire.table_names.responses', 'questionnaire_responses');
        $questionnairesTable = config('questionnaire.table_names.questionnaires', 'questionnaires');

        Schema::create($tableName, function (Blueprint $table) use ($questionnairesTable) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained($questionnairesTable)->cascadeOnDelete();
            $table->nullableMorphs('respondent'); // For authenticated users
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();

            $table->index('questionnaire_id');

            $table->index('ip_address');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        $tableName = config('questionnaire.table_names.responses', 'questionnaire_responses');
        Schema::dropIfExists($tableName);
    }
};
